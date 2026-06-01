<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета разработчика | Soft Vision</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: radial-gradient(circle at 10% 30%, #f5f0e7, #e3d9cd);
            font-family: 'Inter', 'Segoe UI', 'Manrope', system-ui, sans-serif;
            padding: 2rem 1.5rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .top-bar { width: 100%; max-width: 980px; text-align: right; margin-bottom: 1rem; }
        .auth-btn {
            background: #2c3e2f; color: #faf6eb; padding: 0.6rem 1.4rem; border-radius: 2rem;
            text-decoration: none; font-weight: 600; font-size: 0.9rem; display: inline-block;
            transition: all 0.2s; border: 1px solid transparent;
        }
        .auth-btn:hover { background: #3d5641; transform: translateY(-1px); }
        .logout-btn { background: #c9772e; color: white; }
        .logout-btn:hover { background: #b06522; }
        .form-container { max-width: 980px; width: 100%; background: #fefdf9; border-radius: 2.5rem; box-shadow: 0 30px 50px -20px rgba(0, 0, 0, 0.4); overflow: hidden; }
        .form-header { background: linear-gradient(115deg, #2c2b28 0%, #1e1d1b 100%); padding: 2rem 2.8rem; color: #faf6eb; border-bottom: 1px solid #ffdd99; }
        .form-header h1 { font-weight: 650; font-size: 2rem; margin-bottom: 0.4rem; }
        .form-header p { font-size: 0.9rem; opacity: 0.8; }
        .form-body { padding: 2rem 2.5rem; background: #ffffff; }
        .field-group { margin-bottom: 1.9rem; display: flex; flex-wrap: wrap; align-items: flex-start; gap: 0.9rem; }
        .field-group label { width: 170px; font-weight: 600; color: #2d3e50; font-size: 0.9rem; padding-top: 0.7rem; flex-shrink: 0; }
        .field-group .input-wrapper { flex: 1; min-width: 230px; }
        input, select, textarea {
            width: 100%; padding: 0.8rem 1.1rem; border: 1.8px solid #ece3d8; border-radius: 1.2rem;
            font-size: 0.95rem; font-family: inherit; transition: all 0.2s; outline: none; background-color: #ffffff; color: #1e2a36;
        }
        input:focus, select:focus, textarea:focus { border-color: #c9772e; box-shadow: 0 0 0 4px rgba(201, 119, 46, 0.15); }
        input.error-input, select.error-input, textarea.error-input { border-color: #dc2626 !important; background-color: #fef2f2 !important; }
        #messages { margin-bottom: 1.5rem; }
        .error { background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 0.75rem; margin-bottom: 0.5rem; border-left: 4px solid #dc2626; }
        .success { background: #dcfce7; color: #166534; padding: 0.75rem 1rem; border-radius: 0.75rem; margin-bottom: 0.5rem; border-left: 4px solid #22c55e; }
        .radio-group { display: flex; gap: 1.6rem; align-items: center; flex-wrap: wrap; background: #fefaf4; padding: 0.5rem 1.2rem; border-radius: 2rem; border: 1.5px solid #f1e6da; }
        .radio-group.error-group { border-color: #dc2626 !important; background-color: #fef2f2 !important; }
        .radio-group label { width: auto; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; padding-top: 0; }
        .checkbox-wrapper { display: flex; align-items: center; gap: 0.85rem; background: #fefaf4; padding: 0.7rem 1.3rem; border-radius: 2rem; border: 1.5px solid #f1e6da; }
        .checkbox-wrapper.error-wrapper { border-color: #dc2626 !important; background-color: #fef2f2 !important; }
        .checkbox-wrapper input { width: 20px; height: 20px; accent-color: #c9772e; }
        .checkbox-wrapper label { font-weight: 500; cursor: pointer; }
        select[multiple] { min-height: 150px; }
        select[multiple] option:checked { background: #d9a066 linear-gradient(0deg, #d17e3b 0%, #c4692a 100%); color: white; }
        .action-buttons { margin-top: 2.3rem; text-align: right; border-top: 2px dashed #f0e4d4; padding-top: 1.8rem; }
        .save-btn { background: linear-gradient(105deg, #2c3e2f, #1f2e22); border: none; padding: 0.85rem 2.5rem; font-size: 1rem; font-weight: 600; border-radius: 3rem; color: #fef6e8; cursor: pointer; transition: all 0.25s; }
        .save-btn:hover { transform: translateY(-3px); background: linear-gradient(105deg, #3e5a41, #2b4230); box-shadow: 0 18px 28px -14px rgba(0, 0, 0, 0.35); }
        .hint-text { font-size: 0.7rem; color: #a18f78; margin-top: 0.4rem; padding-left: 0.3rem; }
        @media (max-width: 720px) { .form-body { padding: 1.5rem; } .field-group { flex-direction: column; } .field-group label { width: 100%; } .form-header { padding: 1.4rem 1.6rem; } }
    </style>
</head>
<body>

<div class="top-bar">
    <?php if (!empty($_SESSION['admin_mode'])): ?>
        <a href="admin.php" class="auth-btn" style="background: #c9772e; margin-right: 8px;">← Назад в админку</a>
    <?php endif; ?>

    <?php if (!empty($_SESSION['login'])): ?>
        <a href="logout.php" class="auth-btn logout-btn">🚪 Выйти (<?php echo htmlspecialchars($_SESSION['login']); ?>)</a>
    <?php else: ?>
        <a href="login.php" class="auth-btn">🔐 Войти в личный кабинет</a>
    <?php endif; ?>
</div>

<div class="form-container">
    <div class="form-header">
        <h1>📄 Регистрационная анкета</h1>
        <p>Заполните данные о себе или войдите для редактирования прошлых ответов</p>
    </div>
    
    <div class="form-body">
        <?php if (!empty($messages)): ?>
            <div id="messages">
                <?php foreach ($messages as $message): echo $message; endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="field-group">
                <label for="fullname">👤 ФИО *</label>
                <div class="input-wrapper">
                    <input type="text" id="fullname" name="fio" 
                           class="<?php echo !empty($errors['fio']) ? 'error-input' : ''; ?>" 
                           value="<?php echo isset($values['fio']) ? htmlspecialchars($values['fio']) : ''; ?>" placeholder="Иванов Иван Иванович" required>
                </div>
            </div>
            
            <div class="field-group">
                <label for="phone">📞 Телефон</label>
                <div class="input-wrapper">
                    <input type="tel" id="phone" name="phone" 
                           class="<?php echo !empty($errors['phone']) ? 'error-input' : ''; ?>" 
                           value="<?php echo isset($values['phone']) ? htmlspecialchars($values['phone']) : ''; ?>" placeholder="+7 (900) 123-45-67">
                </div>
            </div>
            
            <div class="field-group">
                <label for="email">✉️ E-mail *</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" 
                           class="<?php echo !empty($errors['email']) ? 'error-input' : ''; ?>" 
                           value="<?php echo isset($values['email']) ? htmlspecialchars($values['email']) : ''; ?>" placeholder="dev@example.com" required>
                </div>
            </div>
            
            <div class="field-group">
                <label for="birthdate">🎂 Дата рождения</label>
                <div class="input-wrapper">
                    <input type="date" id="birthdate" name="birthdate" 
                           class="<?php echo !empty($errors['birthdate']) ? 'error-input' : ''; ?>" 
                           value="<?php echo isset($values['birthdate']) ? htmlspecialchars($values['birthdate']) : ''; ?>">
                </div>
            </div>
            
            <div class="field-group">
                <label>⚥ Пол</label>
                <div class="input-wrapper radio-group <?php echo !empty($errors['gender']) ? 'error-group' : ''; ?>">
                    <label><input type="radio" name="gender" value="male" <?php echo ($values['gender'] == 'male') ? 'checked' : ''; ?>> Мужской</label>
                    <label><input type="radio" name="gender" value="female" <?php echo ($values['gender'] == 'female') ? 'checked' : ''; ?>> Женский</label>
                    <label><input type="radio" name="gender" value="other" <?php echo ($values['gender'] == 'other') ? 'checked' : ''; ?>> Другой</label>
                    <label><input type="radio" name="gender" value="unspecified" <?php echo ($values['gender'] == 'unspecified') ? 'checked' : ''; ?>> Не указан</label>
                </div>
            </div>
            
            <div class="field-group">
                <label>💻 Любимые языки *</label>
                <div class="input-wrapper">
                    <select name="fav_langs[]" id="fav_langs" multiple size="6" class="<?php echo !empty($errors['languages']) ? 'error-input' : ''; ?>">
                        <?php
                        $langs_list = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                        foreach ($langs_list as $l):
                            $selected = in_array($l, $values['languages']) ? 'selected' : '';
                            echo "<option value=\"$l\" $selected>$l</option>";
                        endforeach;
                        ?>
                    </select>
                    <div class="hint-text">⌘ Удерживайте Ctrl (Cmd) для выбора нескольких языков</div>
                </div>
            </div>
            
            <div class="field-group">
                <label for="bio">📝 Биография</label>
                <div class="input-wrapper">
                    <textarea id="bio" name="bio" rows="4" 
                              class="<?php echo !empty($errors['bio']) ? 'error-input' : ''; ?>" placeholder="Расскажите о своем опыте..."><?php echo isset($values['bio']) ? htmlspecialchars($values['bio']) : ''; ?></textarea>
                </div>
            </div>
            
            <div class="field-group">
                <label>📑 Согласие</label>
                <div class="input-wrapper checkbox-wrapper <?php echo !empty($errors['contract']) ? 'error-wrapper' : ''; ?>">
                    <input type="checkbox" id="contractCheck" name="contract_agreed" <?php echo ($values['contract'] == 'on') ? 'checked' : ''; ?> required>
                    <label for="contractCheck">Я ознакомлен(а) с условиями пользовательского соглашения *</label>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="submit" class="save-btn">💾 Сохранить</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>