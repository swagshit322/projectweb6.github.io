<?php
/**
 * index.php - Публичная форма регистрации (задания 4 и 5)
 * Поддерживает неавторизованных пользователей (Cookies) и авторизованных (сессии)
 */

require_once 'common.php';

// Функции генерации логина и пароля (только для новых записей)
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

// Вспомогательная функция для подписи полей в сообщениях
function getFieldLabel($field) {
    $labels = [
        'fio' => 'ФИО', 'phone' => 'Телефон', 'email' => 'E-mail',
        'birthdate' => 'Дата рождения', 'gender' => 'Пол', 'languages' => 'Языки программирования',
        'bio' => 'Биография', 'contract' => 'Согласие'
    ];
    return $labels[$field] ?? $field;
}

// Обработка GET-запроса (показ формы)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = [];
    $errors = [];
    $values = [];

    // Сообщение об успешном сохранении (из куки)
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = '<div class="success">✅ Данные успешно сохранены!</div>';
        if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
            $messages[] = sprintf(
                '<div class="success">🔐 Для редактирования используйте логин <strong>%s</strong> и пароль <strong>%s</strong>.<br><a href="login.php">Войти</a></div>',
                htmlspecialchars($_COOKIE['login']),
                htmlspecialchars($_COOKIE['pass'])
            );
            setcookie('login', '', 100000);
            setcookie('pass', '', 100000);
        }
    }

    // Список всех полей
    $fields = ['fio', 'phone', 'email', 'birthdate', 'gender', 'languages', 'bio', 'contract'];

    // Считываем флаги ошибок и сообщения из кук
    foreach ($fields as $field) {
        $errors[$field] = !empty($_COOKIE[$field . '_error']);
        if ($errors[$field] && !empty($_COOKIE[$field . '_error_msg'])) {
            $messages[] = '<div class="error">❌ Ошибка в поле "' . getFieldLabel($field) . '": ' . htmlspecialchars($_COOKIE[$field . '_error_msg']) . '</div>';
            setcookie($field . '_error', '', 100000);
            setcookie($field . '_error_msg', '', 100000);
        }
    }

    // Загружаем значения из кук (предыдущий ввод)
    foreach ($fields as $field) {
        $cookieName = $field . '_value';
        if (!empty($_COOKIE[$cookieName])) {
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

    // Если пользователь авторизован и нет активных ошибок – загружаем данные из БД
    $isAuthorized = false;
    if (!empty($_COOKIE[session_name()])) {
        session_start();
        if (!empty($_SESSION['login']) && !empty($_SESSION['uid'])) {
            $isAuthorized = true;
            // Проверяем, есть ли ошибки (если есть – оставляем значения из кук)
            $hasErrors = array_reduce($errors, fn($carry, $err) => $carry || $err, false);
            if (!$hasErrors) {
                $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
                $stmt->execute([$_SESSION['uid']]);
                $userData = $stmt->fetch();
                if ($userData) {
                    $values['fio'] = htmlspecialchars($userData['fullname']);
                    $values['phone'] = htmlspecialchars($userData['phone']);
                    $values['email'] = htmlspecialchars($userData['email']);
                    $values['birthdate'] = htmlspecialchars($userData['birthdate']);
                    $values['gender'] = htmlspecialchars($userData['gender']);
                    $values['bio'] = htmlspecialchars($userData['biography']);
                    $values['contract'] = $userData['contract_agreed'] ? 'on' : '';
                    // Загружаем языки
                    $langStmt = $pdo->prepare("SELECT pl.name FROM application_languages al JOIN programming_languages pl ON al.language_id = pl.id WHERE al.application_id = ?");
                    $langStmt->execute([$_SESSION['uid']]);
                    $values['languages'] = $langStmt->fetchAll(PDO::FETCH_COLUMN);
                }
            }
        }
    }

    // Включаем HTML-форму
    include('form.php');
}
// Обработка POST-запроса (валидация и сохранение)
else {
    $errors = false;

    // ---- Валидация каждого поля с сохранением в куки ----
    $fio_err = validateFullname($_POST['fio']);
    if ($fio_err) { setcookie('fio_error', '1', time()+86400); setcookie('fio_error_msg', $fio_err, time()+86400); $errors = true; }
    setcookie('fio_value', $_POST['fio'], time()+365*86400);

    $phone_err = validatePhone($_POST['phone']);
    if ($phone_err) { setcookie('phone_error', '1', time()+86400); setcookie('phone_error_msg', $phone_err, time()+86400); $errors = true; }
    setcookie('phone_value', $_POST['phone'], time()+365*86400);

    $email_err = validateEmail($_POST['email']);
    if ($email_err) { setcookie('email_error', '1', time()+86400); setcookie('email_error_msg', $email_err, time()+86400); $errors = true; }
    setcookie('email_value', $_POST['email'], time()+365*86400);

    $birth_err = validateBirthdate($_POST['birthdate']);
    if ($birth_err) { setcookie('birthdate_error', '1', time()+86400); setcookie('birthdate_error_msg', $birth_err, time()+86400); $errors = true; }
    setcookie('birthdate_value', $_POST['birthdate'], time()+365*86400);

    $gender = $_POST['gender'] ?? 'unspecified';
    $gender_err = validateGender($gender);
    if ($gender_err) { setcookie('gender_error', '1', time()+86400); setcookie('gender_error_msg', $gender_err, time()+86400); $errors = true; }
    setcookie('gender_value', $gender, time()+365*86400);

    $languages = $_POST['fav_langs'] ?? [];
    $lang_err = validateLanguages($languages, $pdo);
    if ($lang_err) { setcookie('languages_error', '1', time()+86400); setcookie('languages_error_msg', $lang_err, time()+86400); $errors = true; }
    setcookie('languages_value', implode(',', $languages), time()+365*86400);

    $bio_err = validateBiography($_POST['bio']);
    if ($bio_err) { setcookie('bio_error', '1', time()+86400); setcookie('bio_error_msg', $bio_err, time()+86400); $errors = true; }
    setcookie('bio_value', $_POST['bio'], time()+365*86400);

    $contract = $_POST['contract_agreed'] ?? '';
    $contract_err = validateContract($contract);
    if ($contract_err) { setcookie('contract_error', '1', time()+86400); setcookie('contract_error_msg', $contract_err, time()+86400); $errors = true; }
    setcookie('contract_value', $contract, time()+365*86400);

    if ($errors) {
        header('Location: index.php');
        exit();
    }

    // Удаляем куки ошибок (все прошли валидацию)
    foreach (['fio','phone','email','birthdate','gender','languages','bio','contract'] as $field) {
        setcookie($field.'_error', '', 100000);
        setcookie($field.'_error_msg', '', 100000);
    }

    // ---------- Сохранение в БД (новое или обновление) ----------
    $isAuthorized = false;
    $userId = null;

    if (!empty($_COOKIE[session_name()])) {
        session_start();
        if (!empty($_SESSION['login']) && !empty($_SESSION['uid'])) {
            $isAuthorized = true;
            $userId = $_SESSION['uid'];
        }
    }

    try {
        if ($isAuthorized) {
            // Обновление существующей записи
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE applications SET fullname=?, phone=?, email=?, birthdate=?, gender=?, biography=?, contract_agreed=? WHERE id=?");
            $stmt->execute([
                $_POST['fio'],
                $_POST['phone'] ?: null,
                $_POST['email'],
                $_POST['birthdate'] ?: null,
                $gender,
                $_POST['bio'] ?: null,
                $contract == 'on' ? 1 : 0,
                $userId
            ]);
            updateUserLanguages($pdo, $userId, $languages);
            $pdo->commit();
            setcookie('save', '1', time()+86400);
        } else {
            // Новая запись – генерируем логин/пароль
            $plainPassword = generateRandomPassword();
            $login = generateUniqueLogin($pdo, $_POST['email']);
            $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO applications (fullname, phone, email, birthdate, gender, biography, contract_agreed, login, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['fio'],
                $_POST['phone'] ?: null,
                $_POST['email'],
                $_POST['birthdate'] ?: null,
                $gender,
                $_POST['bio'] ?: null,
                $contract == 'on' ? 1 : 0,
                $login,
                $passwordHash
            ]);
            $newId = $pdo->lastInsertId();
            // Сохраняем языки
            $langStmt = $pdo->prepare("SELECT id FROM programming_languages WHERE name = ?");
            $insStmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($languages as $lang) {
                $langStmt->execute([$lang]);
                $langId = $langStmt->fetchColumn();
                if ($langId) $insStmt->execute([$newId, $langId]);
            }
            $pdo->commit();

            // Сохраняем логин и пароль в куки для однократного отображения
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