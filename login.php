<?php
header('Content-Type: text/html; charset=UTF-8');

session_start();

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
    die("Ошибка БД: " . $e->getMessage());
}

if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Вход для редактирования</title>
        <style>
            body { font-family: system-ui, sans-serif; background: #f0ebe3; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .login-card { background: white; padding: 2.5rem; border-radius: 2rem; box-shadow: 0 12px 30px rgba(0,0,0,0.15); width: 340px; text-align: center; }
            h2 { color: #2c3e2f; margin-bottom: 1.5rem; font-size: 1.4rem; }
            input { width: 100%; padding: 0.8rem; margin: 0.6rem 0; border: 1px solid #ccc; border-radius: 1rem; box-sizing: border-box; outline: none; }
            input:focus { border-color: #c9772e; }
            button { background: #2c3e2f; color: white; border: none; padding: 0.8rem; border-radius: 2rem; width: 100%; cursor: pointer; font-weight: bold; font-size: 1rem; margin-top: 1rem; }
            .error-msg { color: #dc2626; font-size: 0.9rem; margin-bottom: 1rem; }
            .back-link { display: inline-block; margin-top: 1.2rem; color: #c9772e; text-decoration: none; font-size: 0.9rem; }
        </style>
    </head>
    <body>
        <div class="login-card">
            <h2>🔐 Личный кабинет</h2>
            <?php if (!empty($_GET['error'])): ?>
                <p class="error-msg">Неверный логин или пароль</p>
            <?php endif; ?>
            <form action="" method="post">
                <input type="text" name="login" placeholder="Логин" required>
                <input type="password" name="pass" placeholder="Пароль" required>
                <button type="submit">Войти</button>
            </form>
            <a href="index.php" class="back-link">← Назад к анкете</a>
        </div>
    </body>
    </html>
    <?php
} else {
    $login = trim($_POST['login'] ?? '');
    $pass = $_POST['pass'] ?? '';

    $stmt = $pdo->prepare("SELECT id, password_hash FROM applications WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password_hash'])) {
        $_SESSION['login'] = $login;
        $_SESSION['uid'] = $user['id'];
        header('Location: index.php');
        exit();
    } else {
        header('Location: login.php?error=1');
        exit();
    }
}
?>