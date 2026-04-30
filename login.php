<?php
header('Content-Type: text/html; charset=UTF-8');

// Подключение к БД (такое же, как в index.php)
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

session_start();

// Если уже авторизован – редирект на форму
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
            body { font-family: system-ui; background: #f0ebe3; display: flex; justify-content: center; align-items: center; height: 100vh; }
            .login-card { background: white; padding: 2rem; border-radius: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.1); width: 300px; }
            input { width: 100%; padding: 0.7rem; margin: 0.5rem 0; border: 1px solid #ccc; border-radius: 1rem; }
            button { background: #2c3e2f; color: white; border: none; padding: 0.7rem; border-radius: 2rem; width: 100%; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="login-card">
            <h2>🔐 Вход для изменения данных</h2>
            <?php if (!empty($_GET['error'])) echo '<p style="color:red;">Неверный логин или пароль</p>'; ?>
            <form action="" method="post">
                <input type="text" name="login" placeholder="Логин" required>
                <input type="password" name="pass" placeholder="Пароль" required>
                <button type="submit">Войти</button>
            </form>
        </div>
    </body>
    </html>
    <?php
} else {
    // POST запрос – проверка логина/пароля
    $login = trim($_POST['login']);
    $pass = $_POST['pass'];

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