<?php
// common.php – общие функции и подключение к БД

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

// ---------- Функции валидации (из задания 4) ----------
function validateFullname($fullname) {
    if (empty($fullname)) return 'ФИО обязательно для заполнения';
    if (mb_strlen($fullname) > 150) return 'ФИО не должно превышать 150 символов';
    if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $fullname)) {
        preg_match_all('/[^a-zA-Zа-яА-ЯёЁ\s\-]/u', $fullname, $matches);
        return 'ФИО содержит недопустимые символы: ' . implode(', ', array_unique($matches[0]));
    }
    return null;
}

function validatePhone($phone) {
    if (!empty($phone)) {
        if (strlen($phone) > 50) return 'Телефон не должен превышать 50 символов';
        if (!preg_match('/^[\+\d\s\-\(\)]+$/', $phone)) {
            preg_match_all('/[^+\d\s\-\(\)]/', $phone, $matches);
            return 'Телефон содержит недопустимые символы: ' . implode(', ', array_unique($matches[0]));
        }
    }
    return null;
}

function validateEmail($email) {
    if (empty($email)) return 'E-mail обязателен для заполнения';
    if (strlen($email) > 100) return 'E-mail не должен превышать 100 символов';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Некорректный формат e-mail';
    return null;
}

function validateBirthdate($birthdate) {
    if (!empty($birthdate)) {
        $date = DateTime::createFromFormat('Y-m-d', $birthdate);
        if (!$date || $date->format('Y-m-d') !== $birthdate) return 'Некорректная дата рождения';
        if ($date > new DateTime()) return 'Дата рождения не может быть в будущем';
        if ($date < new DateTime('1900-01-01')) return 'Дата рождения не может быть ранее 1900 года';
    }
    return null;
}

function validateGender($gender) {
    $allowed = ['male', 'female', 'other', 'unspecified'];
    return in_array($gender, $allowed) ? null : 'Некорректное значение пола';
}

function validateLanguages($languages, $pdo) {
    if (empty($languages)) return 'Выберите хотя бы один язык программирования';
    if (count($languages) > 12) return 'Выбрано слишком много языков (максимум 12)';
    $invalid = array_filter($languages, function($l) { return !preg_match('/^[a-zA-Z\+\#]+$/', $l); });
    if ($invalid) return 'Недопустимые символы в языках: ' . implode(', ', $invalid);
    $placeholders = implode(',', array_fill(0, count($languages), '?'));
    $stmt = $pdo->prepare("SELECT name FROM programming_languages WHERE name IN ($placeholders)");
    $stmt->execute($languages);
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $notFound = array_diff($languages, $existing);
    if ($notFound) return 'Следующие языки не поддерживаются: ' . implode(', ', $notFound);
    return null;
}

function validateBiography($bio) {
    if (!empty($bio)) {
        if (strlen($bio) > 10000) return 'Биография не должна превышать 10000 символов';
        if (!preg_match('/^[А-Яа-яЁёA-Za-z0-9\s\.,!?\-:;\(\)\"\'@#$%^&*+=<>]{0,10000}$/u', $bio)) {
            return 'Биография содержит недопустимые символы';
        }
    }
    return null;
}

function validateContract($contract) {
    return $contract == 'on' ? null : 'Необходимо подтвердить ознакомление с контрактом';
}

// ---------- Работа с языками ----------
function getAllLanguages($pdo) {
    $stmt = $pdo->query("SELECT id, name FROM programming_languages ORDER BY name");
    return $stmt->fetchAll();
}

function getUserLanguages($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT pl.name FROM application_languages al JOIN programming_languages pl ON al.language_id = pl.id WHERE al.application_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function updateUserLanguages($pdo, $userId, $languages) {
    $stmtDel = $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?");
    $stmtDel->execute([$userId]);
    $stmtLang = $pdo->prepare("SELECT id FROM programming_languages WHERE name = ?");
    $stmtIns = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
    foreach ($languages as $lang) {
        $stmtLang->execute([$lang]);
        $langId = $stmtLang->fetchColumn();
        if ($langId) $stmtIns->execute([$userId, $langId]);
    }
}

// ---------- HTTP-авторизация администратора ----------
function checkAdminAuth($pdo) {
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        return false;
    }
    $login = $_SERVER['PHP_AUTH_USER'];
    $pass = $_SERVER['PHP_AUTH_PW'];
    $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE login = ?");
    $stmt->execute([$login]);
    $admin = $stmt->fetch();
    return $admin && password_verify($pass, $admin['password_hash']);
}

function requireAdminAuth($pdo) {
    if (!checkAdminAuth($pdo)) {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin area"');
        echo '<h1>Требуется авторизация администратора</h1>';
        exit;
    }
}
?>