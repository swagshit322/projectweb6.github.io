<?php
require_once 'common.php';
requireAdminAuth($pdo);

// Удаление записи
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Сначала удаляем связи с языками (каскадное удаление сработает, но можно явно)
    $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM applications WHERE id = ?")->execute([$id]);
    header('Location: admin.php');
    exit;
}

// Получение всех пользователей
$users = $pdo->query("SELECT * FROM applications ORDER BY id DESC")->fetchAll();
foreach ($users as &$user) {
    $user['languages'] = getUserLanguages($pdo, $user['id']);
}
unset($user);

// Статистика по языкам
$langStats = $pdo->query("
    SELECT pl.name, COUNT(al.application_id) as cnt 
    FROM programming_languages pl 
    LEFT JOIN application_languages al ON pl.id = al.language_id 
    GROUP BY pl.id 
    ORDER BY cnt DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0ebe3; padding: 2rem; margin: 0; }
        .container { max-width: 1400px; margin: 0 auto; background: white; border-radius: 1.5rem; padding: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        h1, h2 { color: #2c3e2f; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; overflow-x: auto; display: block; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; vertical-align: top; }
        th { background: #2c3e2f; color: white; position: sticky; top: 0; }
        tr:nth-child(even) { background: #f9f9f9; }
        .btn { display: inline-block; padding: 4px 12px; margin: 2px; background: #007bff; color: white; text-decoration: none; border-radius: 1.2rem; font-size: 0.8rem; }
        .btn-danger { background: #dc3545; }
        .btn-warning { background: #ffc107; color: black; }
        .stats { background: #e9ecef; padding: 1rem; border-radius: 1rem; margin-bottom: 2rem; }
        .stats ul { columns: 3; list-style: none; padding-left: 0; }
        .stats li { padding: 4px 0; }
        a { text-decoration: none; }
        .header-actions { margin-bottom: 1rem; text-align: right; }
    </style>
</head>
<body>
<div class="container">
    <div class="header-actions">
        <span>👋 Здравствуйте, <?= htmlspecialchars($_SERVER['PHP_AUTH_USER']) ?> | </span>
        <a href="index.php" target="_blank">📝 Форма регистрации</a>
    </div>
    <h1>🔧 Административная панель</h1>
    
    <div class="stats">
        <h2>📊 Статистика по языкам программирования</h2>
        <ul>
            <?php foreach ($langStats as $stat): ?>
                <li><strong><?= htmlspecialchars($stat['name']) ?></strong>: <?= $stat['cnt'] ?> пользователей</li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <h2>📋 Все анкеты пользователей</h2>
    <div style="overflow-x: auto;">
    <table>
        <thead>
            <tr><th>ID</th><th>ФИО</th><th>Телефон</th><th>Email</th><th>Дата рожд.</th><th>Пол</th><th>Языки</th><th>Биография</th><th>Согласие</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="10" style="text-align: center;">Нет ни одной анкеты</td></tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['fullname']) ?></td>
                    <td><?= htmlspecialchars($user['phone']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['birthdate']) ?></td>
                    <td><?= htmlspecialchars($user['gender']) ?></td>
                    <td><?= implode(', ', array_map('htmlspecialchars', $user['languages'])) ?></td>
                    <td><?= htmlspecialchars(mb_substr($user['biography'], 0, 100)) . (mb_strlen($user['biography']) > 100 ? '…' : '') ?></td>
                    <td><?= $user['contract_agreed'] ? '✅ Да' : '❌ Нет' ?></td>
                    <td>
                        <a class="btn btn-warning" href="admin_edit.php?id=<?= $user['id'] ?>">✏ Редактировать</a>
                        <a class="btn btn-danger" href="?delete=<?= $user['id'] ?>" onclick="return confirm('Удалить пользователя? Это действие необратимо.')">🗑 Удалить</a>
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