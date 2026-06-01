<?php
/**
 * Задание 5-6: регистрационная форма с авторизацией и админ-панелью.
 */

session_start();

header('Content-Type: text/html; charset=UTF-8');

$host = 'localhost';
$dbname = 'u82665';
$username = 'u82665';
$password = '3079533';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// ---------- Функции валидации ----------
function validateFullname($fullname) {
    if (empty($fullname)) return 'ФИО обязательно для заполнения';
    // Абсолютно безопасная проверка длины через регулярное выражение UTF-8
    if (!preg_match('/^.{1,150}$/u', $fullname)) return 'ФИО не должно превышать 150 символов';
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
    
    $invalid = array_filter($languages, fn($l) => !preg_match('/^[a-zA-Z\+\#0-9]+$/', $l));
    if ($invalid) return 'Недопустимые символы в языках: ' . implode(', ', $invalid);
    
    $placeholders = rtrim(str_repeat('?,', count($languages)), ',');
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
    }
    return null;
}

function validateContract($contract) {
    return $contract == 'on' ? null : 'Необходимо подтвердить ознакомление с контрактом';
}

function generateUniqueLogin($pdo, $email) {
    $base = explode('@', $email)[0];
    $base = preg_replace('/[^a-z0-9]/i', '', $base);
    if (strlen($base) < 4) $base = 'user';
    $login = $base;
    $counter = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT 1 FROM applications WHERE login = ?");
        $stmt->execute([$login]);
        if (!$stmt->fetch()) return $login;
        $login = $base . $counter++;
    }
}

function generateRandomPassword($length = 10) {
    $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ123456789';
    return substr(str_shuffle($chars), 0, $length);
}

function getFieldLabel($field) {
    $labels = [
        'fio' => 'ФИО', 'phone' => 'Телефон', 'email' => 'E-mail',
        'birthdate' => 'Дата рождения', 'gender' => 'Пол', 'languages' => 'Языки программирования',
        'bio' => 'Биография', 'contract' => 'Согласие'
    ];
    return $labels[$field] ?? $field;
}

// ---------- Обработка GET-запроса (Рендеринг формы) ----------
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = [];
    $errors = [];
    $values = [];

    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', time() - 3600);
        $messages[] = '<div class="success">✅ Данные успешно сохранены!</div>';
        if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
            $messages[] = sprintf(
                '<div class="success">🔐 Для редактирования используйте логин <strong>%s</strong> и пароль <strong>%s</strong>.<br><a href="login.php" style="text-decoration: underline; color: inherit; font-weight: bold;">Войти в личный кабинет</a></div>',
                htmlspecialchars($_COOKIE['login']),
                htmlspecialchars($_COOKIE['pass'])
            );
            setcookie('login', '', time() - 3600);
            setcookie('pass', '', time() - 3600);
        }
    }

    if (!empty($_COOKIE['save_error'])) {
        setcookie('save_error', '', time() - 3600);
        if (!empty($_COOKIE['save_error_msg'])) {
            $messages[] = '<div class="error">❌ Ошибка сохранения: ' . htmlspecialchars($_COOKIE['save_error_msg']) . '</div>';
            setcookie('save_error_msg', '', time() - 3600);
        }
    }

    $fields = ['fio', 'phone', 'email', 'birthdate', 'gender', 'languages', 'bio', 'contract'];

    foreach ($fields as $field) {
        $errors[$field] = !empty($_COOKIE[$field . '_error']);
        if ($errors[$field] && !empty($_COOKIE[$field . '_error_msg'])) {
            $messages[] = '<div class="error">❌ Ошибка в поле "' . getFieldLabel($field) . '": ' . htmlspecialchars($_COOKIE[$field . '_error_msg']) . '</div>';
            setcookie($field . '_error', '', time() - 3600);
            setcookie($field . '_error_msg', '', time() - 3600);
        }
    }

    foreach ($fields as $field) {
        $cookieName = $field . '_value';
        if (isset($_COOKIE[$cookieName])) {
            if ($field == 'languages') {
                $values[$field] = explode(',', $_COOKIE[$cookieName]);
            } else {
                $values[$field] = strip_tags($_COOKIE[$cookieName]);
            }
        } else {
            $values[$field] = ($field == 'languages') ? [] : '';
        }
    }
    if (empty($values['gender'])) $values['gender'] = 'unspecified';

    $isAuthorized = (!empty($_SESSION['login']) && !empty($_SESSION['uid']));
    if ($isAuthorized) {
        $hasErrors = array_reduce($errors, fn($carry, $err) => $carry || $err, false);
        if (!$hasErrors) {
            $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
            $stmt->execute([$_SESSION['uid']]);
            $userData = $stmt->fetch();
            if ($userData) {
                $values['fio'] = $userData['fullname'];
                $values['phone'] = $userData['phone'];
                $values['email'] = $userData['email'];
                $values['birthdate'] = $userData['birthdate'];
                $values['gender'] = $userData['gender'];
                $values['bio'] = $userData['biography'];
                $values['contract'] = $userData['contract_agreed'] ? 'on' : '';
                
                $langStmt = $pdo->prepare("SELECT pl.name FROM application_languages al JOIN programming_languages pl ON al.language_id = pl.id WHERE al.application_id = ?");
                $langStmt->execute([$_SESSION['uid']]);
                $values['languages'] = $langStmt->fetchAll(PDO::FETCH_COLUMN);
            }
        }
    }

    include('form.php');
}

// ---------- Обработка POST-запроса (Запись / Модификация) ----------
else {
    $has_errors = false;

    $fio_err = validateFullname($_POST['fio'] ?? '');
    if ($fio_err) { setcookie('fio_error', '1', time()+86400); setcookie('fio_error_msg', $fio_err, time()+86400); $has_errors = true; }
    setcookie('fio_value', $_POST['fio'] ?? '', time()+365*86400);

    $phone_err = validatePhone($_POST['phone'] ?? '');
    if ($phone_err) { setcookie('phone_error', '1', time()+86400); setcookie('phone_error_msg', $phone_err, time()+86400); $has_errors = true; }
    setcookie('phone_value', $_POST['phone'] ?? '', time()+365*86400);

    $email_err = validateEmail($_POST['email'] ?? '');
    if ($email_err) { setcookie('email_error', '1', time()+86400); setcookie('email_error_msg', $email_err, time()+86400); $has_errors = true; }
    setcookie('email_value', $_POST['email'] ?? '', time()+365*86400);

    $birth_err = validateBirthdate($_POST['birthdate'] ?? '');
    if ($birth_err) { setcookie('birthdate_error', '1', time()+86400); setcookie('birthdate_error_msg', $birth_err, time()+86400); $has_errors = true; }
    setcookie('birthdate_value', $_POST['birthdate'] ?? '', time()+365*86400);

    $gender = $_POST['gender'] ?? 'unspecified';
    $gender_err = validateGender($gender);
    if ($gender_err) { setcookie('gender_error', '1', time()+86400); setcookie('gender_error_msg', $gender_err, time()+86400); $has_errors = true; }
    setcookie('gender_value', $gender, time()+365*86400);

    $languages = $_POST['fav_langs'] ?? [];
    $lang_err = validateLanguages($languages, $pdo);
    if ($lang_err) { setcookie('languages_error', '1', time()+86400); setcookie('languages_error_msg', $lang_err, time()+86400); $has_errors = true; }
    setcookie('languages_value', implode(',', $languages), time()+365*86400);

    $bio_err = validateBiography($_POST['bio'] ?? '');
    if ($bio_err) { setcookie('bio_error', '1', time()+86400); setcookie('bio_error_msg', $bio_err, time()+86400); $has_errors = true; }
    setcookie('bio_value', $_POST['bio'] ?? '', time()+365*86400);

    $contract = $_POST['contract_agreed'] ?? '';
    $contract_err = validateContract($contract);
    if ($contract_err) { setcookie('contract_error', '1', time()+86400); setcookie('contract_error_msg', $contract_err, time()+86400); $has_errors = true; }
    setcookie('contract_value', $contract, time()+365*86400);

    if ($has_errors) {
        header('Location: index.php');
        exit();
    }

    foreach (['fio','phone','email','birthdate','gender','languages','bio','contract'] as $field) {
        setcookie($field.'_error', '', time() - 3600);
        setcookie($field.'_error_msg', '', time() - 3600);
    }

    $isAuthorized = (!empty($_SESSION['login']) && !empty($_SESSION['uid']));

    try {
        if ($isAuthorized) {
            $userId = $_SESSION['uid'];
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE applications SET fullname=?, phone=?, email=?, birthdate=?, gender=?, biography=?, contract_agreed=? WHERE id=?");
            $stmt->execute([
                $_POST['fio'],
                !empty($_POST['phone']) ? $_POST['phone'] : null,
                $_POST['email'],
                !empty($_POST['birthdate']) ? $_POST['birthdate'] : null,
                $gender,
                !empty($_POST['bio']) ? $_POST['bio'] : null,
                $contract == 'on' ? 1 : 0,
                $userId
            ]);
            
            $delStmt = $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?");
            $delStmt->execute([$userId]);
            
            $langStmt = $pdo->prepare("SELECT id FROM programming_languages WHERE name = ?");
            $insStmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($languages as $lang) {
                $langStmt->execute([$lang]);
                $langId = $langStmt->fetchColumn();
                if ($langId) $insStmt->execute([$userId, $langId]);
            }
            $pdo->commit();
            setcookie('save', '1', time()+86400);

            // КРИТИЧЕСКОЕ ОБНОВЛЕНИЕ ДЛЯ ЗАДАНИЯ 6: Если правил админ — выходим в админку
            if (!empty($_SESSION['admin_mode'])) {
                unset($_SESSION['admin_mode']);
                unset($_SESSION['login']);
                unset($_SESSION['uid']);
                header('Location: admin.php?success=updated');
                exit();
            }
        } else {
            $plainPassword = generateRandomPassword();
            $login = generateUniqueLogin($pdo, $_POST['email']);
            $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO applications (fullname, phone, email, birthdate, gender, biography, contract_agreed, login, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['fio'],
                !empty($_POST['phone']) ? $_POST['phone'] : null,
                $_POST['email'],
                !empty($_POST['birthdate']) ? $_POST['birthdate'] : null,
                $gender,
                !empty($_POST['bio']) ? $_POST['bio'] : null,
                $contract == 'on' ? 1 : 0,
                $login,
                $passwordHash
            ]);
            $newId = $pdo->lastInsertId();
            
            $langStmt = $pdo->prepare("SELECT id FROM programming_languages WHERE name = ?");
            $insStmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($languages as $lang) {
                $langStmt->execute([$lang]);
                $langId = $langStmt->fetchColumn();
                if ($langId) $insStmt->execute([$newId, $langId]);
            }
            $pdo->commit();

            setcookie('login', $login, time()+86400);
            setcookie('pass', $plainPassword, time()+86400);
            setcookie('save', '1', time()+86400);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        setcookie('save_error', '1', time()+86400);
        setcookie('save_error_msg', 'Ошибка БД: ' . $e->getMessage(), time()+86400);
    }

    header('Location: index.php');
    exit();
}
?>