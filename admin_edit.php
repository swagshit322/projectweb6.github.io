<?php
require_once 'common.php';
requireAdminAuth($pdo);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: admin.php');
    exit;
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    header('Location: admin.php');
    exit;
}

$userLanguages = getUserLanguages($pdo, $id);
$allLanguages = getAllLanguages($pdo);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация
    if ($err = validateFullname($_POST['fio'])) $errors['fio'] = $err;
    if ($err = validatePhone($_POST['phone'])) $errors['phone'] = $err;
    if ($err = validateEmail($_POST['email'])) $errors['email'] = $err;
    if ($err = validateBirthdate($_POST['birthdate'])) $errors['birthdate'] = $err;
    $gender = $_POST['gender'] ?? 'unspecified';
    if ($err = validateGender($gender)) $errors['gender'] = $err;
    $languages = $_POST['fav_langs'] ?? [];
    if ($err = validateLanguages($languages, $pdo)) $errors['languages'] = $err;
    if ($err = validateBiography($_POST['bio'])) $errors['bio'] = $err;
    $contract = $_POST['contract_agreed'] ?? '';
    if ($err = validateContract($contract)) $errors['contract'] = $err;

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $updateStmt = $pdo->prepare("UPDATE applications SET fullname=?, phone=?, email=?, birthdate=?, gender=?, biography=?, contract_agreed=? WHERE id=?");
            $updateStmt->execute([
                $_POST['fio'],
                $_POST['phone'] ?: null,
                $_POST['email'],
                $_POST['birthdate'] ?: null,
                $gender,
                $_POST['bio'] ?: null,
                $contract == 'on' ? 1 : 0,
                $id
            ]);
            updateUserLanguages($pdo, $id, $languages);
            $pdo->commit();
            header('Location: admin.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['general'] = 'Ошибка сохранения: ' . $e->getMessage();
        }
    }
} else {
    // Заполнение формы текущими данными
    $_POST = [
        'fio' => $user['fullname'],
        'phone' => $user['phone'],
        'email' => $user['email'],
        'birthdate' => $user['birthdate'],
        'gender' => $user['gender'] ?: 'unspecified',
        'fav_langs' => $userLanguages,
        'bio' => $user['biography'],
        'contract_agreed' => $user['contract_agreed'] ? 'on' : ''
    ];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование анкеты</title>
    <style>
        body { background: #f0ebe3; font-family: system-ui; padding: 2rem; }
        .form-card { max-width: 800px; margin: 0 auto; background: white; border-radius: 1.5rem; padding: 2rem; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .field-group { margin-bottom: 1.2rem; }
        label { display: inline-block; width: 180px; font-weight: 600; vertical-align: top; padding-top: 0.5rem; }
        input, select, textarea { width: 100%; max-width: 400px; padding: 0.5rem; border-radius: 0.75rem; border: 1px solid #ccc; font-family: inherit; }
        .radio-group { display: inline-block; }
        .radio-group label { width: auto; font-weight: normal; margin-right: 1rem; padding-top: 0; }
        .error { color: #dc2626; margin-left: 180px; font-size: 0.8rem; margin-top: 0.2rem; }
        button { background: #2c3e2f; color: white; padding: 0.6rem 1.8rem; border: none; border-radius: 2rem; cursor: pointer; font-size: 1rem; margin-right: 1rem; }
        .btn-cancel { background: #6c757d; text-decoration: none; color: white; padding: 0.6rem 1.8rem; border-radius: 2rem; display: inline-block; }
        h1 { color: #2c3e2f; }
    </style>
</head>
<body>
<div class="form-card">
    <h1>✏ Редактирование данных пользователя #<?= $id ?></h1>
    <?php if (isset($errors['general'])): ?>
        <div class="error" style="margin-left:0; background:#fee; padding:0.5rem;"><?= htmlspecialchars($errors['general']) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="field-group">
            <label>ФИО *</label>
            <input type="text" name="fio" value="<?= htmlspecialchars($_POST['fio']) ?>">
            <?php if (isset($errors['fio'])) echo '<div class="error">' . htmlspecialchars($errors['fio']) . '</div>'; ?>
        </div>
        <div class="field-group">
            <label>Телефон</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($_POST['phone']) ?>">
            <?php if (isset($errors['phone'])) echo '<div class="error">' . htmlspecialchars($errors['phone']) . '</div>'; ?>
        </div>
        <div class="field-group">
            <label>Email *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email']) ?>">
            <?php if (isset($errors['email'])) echo '<div class="error">' . htmlspecialchars($errors['email']) . '</div>'; ?>
        </div>
        <div class="field-group">
            <label>Дата рождения</label>
            <input type="date" name="birthdate" value="<?= htmlspecialchars($_POST['birthdate']) ?>">
            <?php if (isset($errors['birthdate'])) echo '<div class="error">' . htmlspecialchars($errors['birthdate']) . '</div>'; ?>
        </div>
        <div class="field-group">
            <label>Пол</label>
            <div class="radio-group">
                <label><input type="radio" name="gender" value="male" <?= $_POST['gender'] == 'male' ? 'checked' : '' ?>> Мужской</label>
                <label><input type="radio" name="gender" value="female" <?= $_POST['gender'] == 'female' ? 'checked' : '' ?>> Женский</label>
                <label><input type="radio" name="gender" value="other" <?= $_POST['gender'] == 'other' ? 'checked' : '' ?>> Другой</label>
                <label><input type="radio" name="gender" value="unspecified" <?= $_POST['gender'] == 'unspecified' ? 'checked' : '' ?>> Не указан</label>
            </div>
            <?php if (isset($errors['gender'])) echo '<div class="error">' . htmlspecialchars($errors['gender']) . '</div>'; ?>
        </div>
        <div class="field-group">
            <label>Языки программирования *</label>
            <select name="fav_langs[]" multiple size="6" style="max-width:400px;">
                <?php foreach ($allLanguages as $lang): ?>
                    <option value="<?= htmlspecialchars($lang['name']) ?>" 
                        <?= in_array($lang['name'], $_POST['fav_langs']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['languages'])) echo '<div class="error">' . htmlspecialchars($errors['languages']) . '</div>'; ?>
            <div class="hint" style="margin-left:180px; font-size:0.7rem; color:#666;">Удерживайте Ctrl (Cmd) для выбора нескольких</div>
        </div>
        <div class="field-group">
            <label>Биография</label>
            <textarea name="bio" rows="4"><?= htmlspecialchars($_POST['bio']) ?></textarea>
            <?php if (isset($errors['bio'])) echo '<div class="error">' . htmlspecialchars($errors['bio']) . '</div>'; ?>
        </div>
        <div class="field-group">
            <label>Согласие *</label>
            <input type="checkbox" name="contract_agreed" <?= $_POST['contract_agreed'] == 'on' ? 'checked' : '' ?>>
            <span>Я ознакомлен(а) с условиями</span>
            <?php if (isset($errors['contract'])) echo '<div class="error">' . htmlspecialchars($errors['contract']) . '</div>'; ?>
        </div>
        <div class="field-group">
            <button type="submit">💾 Сохранить изменения</button>
            <a href="admin.php" class="btn-cancel">Отмена</a>
        </div>
    </form>
</div>
</body>
</html>