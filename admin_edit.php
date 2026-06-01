<?php
/**
 * Скрипт перехвата сессии для редактирования пользователя администратором.
 * Обеспечивает соблюдение принципа DRY.
 */
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

// Защита: проверяем, что этот файл дергает именно авторизованный админ
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    die('Доступ запрещен');
}

$target_uid = intval($_GET['uid'] ?? 0);

if ($target_uid > 0) {
    // Достаем логин редактируемого юзера
    $stmt = $pdo->prepare("SELECT login FROM applications WHERE id = ?");
    $stmt->execute([$target_uid]);
    $userLogin = $stmt->fetchColumn();

    if ($userLogin) {
        // Подменяем переменные сессии на данные этого пользователя
        $_SESSION['login'] = $userLogin;
        $_SESSION['uid'] = $target_uid;
        $_SESSION['admin_mode'] = true; // Указываем, что в систему зашел админ!
        
        // Отправляем в корень проекта на форму, она сама подгрузит данные из БД
        header('Location: index.php');
        exit();
    }
}

header('Location: admin.php');
exit();