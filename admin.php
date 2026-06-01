<?php
/**
 * Задание 6: Панель администратора с HTTP-авторизацией.
 */

// Старт сессии для безопасного управления режимом редактирования
session_start();

header('Content-Type: text/html; charset=UTF-8');

$host = 'localhost';
$dbname = 'u82665';
$username = 'u82665';
$password = '3079533';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// ---------- 1. HTTP-Авторизация (Заголовки браузера) ----------
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    die('🤖 Доступ запрещен. Требуется авторизация.');
}

// Проверяем администратора по отдельной таблице `admins`
$stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE login = ?");
$stmt->execute([$_SERVER['PHP_AUTH_USER']]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    die('❌ Неверный логин или пароль администратора.');
}

// ---------- 2. Обработка POST-действий (Удаление анкеты) ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $app_id = intval($_POST['app_id'] ?? 0);

    if ($_POST['action'] == 'delete' && $app_id > 0) {
        try {
            $pdo->beginTransaction();
            
            // Сначала удаляем связи с языками программирования (внешний ключ)
            $stmt1 = $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?");
            $stmt1->execute([$app_id]);
            
            // Затем удаляем саму анкету из таблицы applications
            $stmt2 = $pdo->prepare("DELETE FROM applications WHERE id = ?");
            $stmt2->execute([$app_id]);
            
            $pdo->commit();
            header('Location: admin.php?success=deleted');
            exit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            die("Ошибка при удалении записи: " . $e->getMessage());
        }
    }
}

// ---------- 3. Сбор статистики по языкам (Запрос с GROUP BY) ----------
$stats_query = "
    SELECT pl.name, COUNT(al.application_id) as count_users
    FROM programming_languages pl
    LEFT JOIN application_languages al ON pl.id = al.language_id
    GROUP BY pl.id, pl.name
    ORDER BY count_users DESC, pl.name ASC
";
$stats = $pdo->query($stats_query)->fetchAll();

// ---------- 4. Получение списка всех пользователей ----------
$apps = $pdo->query("SELECT * FROM applications ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора | Контроль базы данных</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f4f6f8; padding: 2rem; color: #2d3e50; }
        .container { max-width: 1300px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 1.5rem; }
        h1 { font-size: 1.8rem; color: #1e293b; }
        h2 { font-size: 1.3rem; margin: 2rem 0 1rem 0; color: #1e293b; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; flex-wrap: wrap; gap: 1rem; }
        .stat-item { background: #fefaf4; border: 1px solid #f1e6da; padding: 0.6rem 1.2rem; border-radius: 2rem; font-size: 0.9rem; font-weight: 500; }
        .stat-item strong { color: #c9772e; font-size: 1.05rem; }
        .table-container { background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.04); margin-top: 1rem; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 1rem 1.2rem; border-bottom: 1px solid #edf2f7; font-size: 0.9rem; }
        th { background: #2c3e2f; color: white; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        tr:hover { background: #f8fafc; }
        .btn { padding: 0.45rem 0.9rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.8rem; font-weight: 600; border: none; cursor: pointer; display: inline-block; transition: background 0.2s; }
        .btn-edit { background: #e2e8f0; color: #334155; margin-right: 0.3rem; }
        .btn-edit:hover { background: #cbd5e1; }
        .btn-delete { background: #fee2e2; color: #991b1b; }
        .btn-delete:hover { background: #fca5a5; }
        .alert { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.75rem; margin-bottom: 1.5rem; border-left: 4px solid #22c55e; font-weight: 500; }
        code { background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-family: monospace; font-size: 0.85rem; color: #0f172a; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>🛠 Панель администратора Soft Vision</h1>
        <div>Вы вошли как: <code><?php echo htmlspecialchars($_SERVER['PHP_AUTH_USER']); ?></code></div>
    </header>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert">
            <?php 
                if ($_GET['success'] == 'deleted') echo "🗑 Анкета пользователя и связанные с ней языки успешно удалены."; 
                if ($_GET['success'] == 'updated') echo "📝 Данные пользователя успешно сохранены администратором."; 
            ?>
        </div>
    <?php endif; ?>

    <h2>📊 Популярность языков программирования</h2>
    <div class="stat-card">
        <?php foreach ($stats as $row): ?>
            <div class="stat-item">
                <?php echo htmlspecialchars($row['name']); ?>: <strong><?php echo $row['count_users']; ?></strong>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>👥 Все зарегистрированные анкеты (Всего: <?php echo count($apps); ?>)</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>E-mail</th>
                    <th>Дата рожд.</th>
                    <th>Пол</th>
                    <th>Биография</th>
                    <th>Логин</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($apps)): ?>
                    <tr><td colspan="9" style="text-align:center; color:#64748b; padding: 3rem;">В базе данных пока нет ни одной заполненной анкеты</td></tr>
                <?php else: ?>
                    <?php foreach ($apps as $app): ?>
                        <tr>
                            <td><?php echo $app['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($app['fullname']); ?></strong></td>
                            <td><?php echo htmlspecialchars($app['phone'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($app['email']); ?></td>
                            <td><?php echo htmlspecialchars($app['birthdate'] ?? '—'); ?></td>
                            <td><code><?php echo htmlspecialchars($app['gender']); ?></code></td>
                            <td>
                                <small title="<?php echo htmlspecialchars($app['biography'] ?? ''); ?>">
                                    <?php 
                                    $bio_text = $app['biography'] ?? '';
                                    // Безопасно обрезаем строку стандартными средствами, если она длиннее 40 символов
                                    echo htmlspecialchars(strlen($bio_text) > 40 ? substr($bio_text, 0, 40) . '...' : $bio_text); 
                                    ?>
                                </small>
                            </td>
                            <td><code><?php echo htmlspecialchars($app['login'] ?? '—'); ?></code></td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <a href="admin_edit.php?uid=<?php echo $app['id']; ?>" class="btn btn-edit">Редактировать</a>
                                    
                                    <form action="" method="POST" onsubmit="return confirm('Вы уверены, что хотите полностью удалить анкету ID <?php echo $app['id']; ?>?');" style="margin:0;">
                                        <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-delete">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
