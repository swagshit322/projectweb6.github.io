<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета разработчика | Soft Vision</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: radial-gradient(circle at 10% 30%, #f5f0e7, #e3d9cd);
            font-family: 'Inter', 'Segoe UI', 'Manrope', system-ui, sans-serif;
            padding: 2rem 1.5rem;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .form-container {
            max-width: 980px;
            width: 100%;
            background: #fefdf9;
            border-radius: 2.5rem;
            box-shadow: 0 30px 50px -20px rgba(0, 0, 0, 0.4);
            overflow: hidden;
        }
        
        .form-header {
            background: linear-gradient(115deg, #2c2b28 0%, #1e1d1b 100%);
            padding: 2rem 2.8rem;
            color: #faf6eb;
            border-bottom: 1px solid #ffdd99;
        }
        
        .form-header h1 {
            font-weight: 650;
            font-size: 2rem;
            margin-bottom: 0.4rem;
        }
        
        .form-header p {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .form-body {
            padding: 2rem 2.5rem;
            background: #ffffff;
        }
        
        .field-group {
            margin-bottom: 1.9rem;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 0.9rem;
        }
        
        .field-group label {
            width: 170px;
            font-weight: 600;
            color: #2d3e50;
            font-size: 0.9rem;
            padding-top: 0.7rem;
            flex-shrink: 0;
        }
        
        .field-group .input-wrapper {
            flex: 1;
            min-width: 230px;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 0.8rem 1.1rem;
            border: 1.8px solid #ece3d8;
            border-radius: 1.2rem;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s;
            outline: none;
            background-color: #ffffff;
            color: #1e2a36;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #c9772e;
            box-shadow: 0 0 0 4px rgba(201, 119, 46, 0.15);
        }
        
        input.error-input, select.error-input, textarea.error-input {
            border-color: #dc2626 !important;
            background-color: #fef2f2 !important;
        }
        
        #messages {
            margin-bottom: 1.5rem;
        }
        
        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid #dc2626;
        }
        
        .success {
            background: #dcfce7;
            color: #166534;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid #22c55e;
        }
        
        .radio-group {
            display: flex;
            gap: 1.6rem;
            align-items: center;
            flex-wrap: wrap;
            background: #fefaf4;
            padding: 0.5rem 1.2rem;
            border-radius: 2rem;
            border: 1.5px solid #f1e6da;
        }
        
        .radio-group.error-group {
            border-color: #dc2626 !important;
            background-color: #fef2f2 !important;
        }
        
        .radio-group label {
            width: auto;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding-top: 0;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            background: #fefaf4;
            padding: 0.7rem 1.3rem;
            border-radius: 2rem;
            border: 1.5px solid #f1e6da;
        }
        
        .checkbox-wrapper.error-wrapper {
            border-color: #dc2626 !important;
            background-color: #fef2f2 !important;
        }
        
        .checkbox-wrapper input {
            width: 20px;
            height: 20px;
            accent-color: #c9772e;
        }
        
        .checkbox-wrapper label {
            font-weight: 500;
            cursor: pointer;
        }
        
        select[multiple] {
            min-height: 150px;
        }
        
        select[multiple] option:checked {
            background: #d9a066 linear-gradient(0deg, #d17e3b 0%, #c4692a 100%);
            color: white;
        }
        
        .action-buttons {
            margin-top: 2.3rem;
            text-align: right;
            border-top: 2px dashed #f0e4d4;
            padding-top: 1.8rem;
        }
        
        .save-btn {
            background: linear-gradient(105deg, #2c3e2f, #1f2e22);
            border: none;
            padding: 0.85rem 2.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 3rem;
            color: #fef6e8;
            cursor: pointer;
            transition: all 0.25s;
        }
        
        .save-btn:hover {
            transform: translateY(-3px);
            background: linear-gradient(105deg, #3e5a41, #2b4230);
            box-shadow: 0 18px 28px -14px rgba(0, 0, 0, 0.35);
        }
        
        .hint-text {
            font-size: 0.7rem;
            color: #a18f78;
            margin-top: 0.4rem;
            padding-left: 0.3rem;
        }
        
        @media (max-width: 720px) {
            .form-body { padding: 1.5rem; }
            .field-group { flex-direction: column; }
            .field-group label { width: 100%; }
            .form-header { padding: 1.4rem 1.6rem; }
        }
    </style>
</head>
<body>
<div class="form-container">
    <div class="form-header">
        <h1>📄 Регистрационная анкета</h1>
        <p>Заполните данные о себе — все поля проверяются на сервере</p>
    </div>
    
    <div class="form-body">
        <?php if (!empty($messages)): ?>
            <div id="messages">
                <?php foreach ($messages as $message): ?>
                    <?php echo $message; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Блок для авторизованного пользователя (задание 5) -->
        <?php if (!empty($_COOKIE[session_name()])): 
            session_start();
            if (!empty($_SESSION['login'])): ?>
                <div style="text-align: right; margin-bottom: 1rem;">
                    <span>👋 Вы вошли как <strong><?= htmlspecialchars($_SESSION['login']) ?></strong> | </span>
                    <a href="logout.php" style="background:#c9772e; color:white; padding:0.3rem 0.8rem; border-radius:1.5rem; text-decoration:none; font-size:0.8rem;">Выйти</a>
                </div>
        <?php endif; endif; ?>
        
        <form action="" method="POST">
            <!-- ФИО -->
            <div class="field-group">
                <label for="fullname">👤 ФИО *</label>
                <div class="input-wrapper">
                    <input type="text" id="fullname" name="fio" 
                           <?php if (!empty($errors['fio'])) echo 'class="error-input"'; ?> 
                           value="<?php echo isset($values['fio']) ? htmlspecialchars($values['fio']) : ''; ?>" 
                           placeholder="Иванов Иван Иванович" required>
                </div>
            </div>
            
            <!-- Телефон -->
            <div class="field-group">
                <label for="phone">📞 Телефон</label>
                <div class="input-wrapper">
                    <input type="tel" id="phone" name="phone" 
                           <?php if (!empty($errors['phone'])) echo 'class="error-input"'; ?> 
                           value="<?php echo isset($values['phone']) ? htmlspecialchars($values['phone']) : ''; ?>" 
                           placeholder="+7 (900) 123-45-67">
                </div>
            </div>
            
            <!-- Email -->
            <div class="field-group">
                <label for="email">✉️ E-mail *</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" 
                           <?php if (!empty($errors['email'])) echo 'class="error-input"'; ?> 
                           value="<?php echo isset($values['email']) ? htmlspecialchars($values['email']) : ''; ?>" 
                           placeholder="dev@example.com" required>
                </div>
            </div>
            
            <!-- Дата рождения -->
            <div class="field-group">
                <label for="birthdate">🎂 Дата рождения</label>
                <div class="input-wrapper">
                    <input type="date" id="birthdate" name="birthdate" 
                           <?php if (!empty($errors['birthdate'])) echo 'class="error-input"'; ?> 
                           value="<?php echo isset($values['birthdate']) ? htmlspecialchars($values['birthdate']) : ''; ?>">
                </div>
            </div>
            
            <!-- Пол -->
            <div class="field-group">
                <label>⚥ Пол</label>
                <div class="input-wrapper radio-group <?php if (!empty($errors['gender'])) echo 'error-group'; ?>">
                    <label><input type="radio" name="gender" value="male" <?php echo (isset($values['gender']) && $values['gender'] == 'male') ? 'checked' : ''; ?>> Мужской</label>
                    <label><input type="radio" name="gender" value="female" <?php echo (isset($values['gender']) && $values['gender'] == 'female') ? 'checked' : ''; ?>> Женский</label>
                    <label><input type="radio" name="gender" value="other" <?php echo (isset($values['gender']) && $values['gender'] == 'other') ? 'checked' : ''; ?>> Другой</label>
                    <label><input type="radio" name="gender" value="unspecified" <?php echo (!isset($values['gender']) || $values['gender'] == 'unspecified' || empty($values['gender'])) ? 'checked' : ''; ?>> Не указан</label>
                </div>
            </div>
            
            <!-- Языки программирования -->
            <div class="field-group">
                <label>💻 Любимые языки *</label>
                <div class="input-wrapper">
                    <select name="fav_langs[]" id="fav_langs" multiple size="6" 
                            <?php if (!empty($errors['languages'])) echo 'class="error-input"'; ?>>
                        <option value="Pascal" <?php echo (isset($values['languages']) && in_array('Pascal', $values['languages'])) ? 'selected' : ''; ?>>Pascal</option>
                        <option value="C" <?php echo (isset($values['languages']) && in_array('C', $values['languages'])) ? 'selected' : ''; ?>>C</option>
                        <option value="C++" <?php echo (isset($values['languages']) && in_array('C++', $values['languages'])) ? 'selected' : ''; ?>>C++</option>
                        <option value="JavaScript" <?php echo (isset($values['languages']) && in_array('JavaScript', $values['languages'])) ? 'selected' : ''; ?>>JavaScript</option>
                        <option value="PHP" <?php echo (isset($values['languages']) && in_array('PHP', $values['languages'])) ? 'selected' : ''; ?>>PHP</option>
                        <option value="Python" <?php echo (isset($values['languages']) && in_array('Python', $values['languages'])) ? 'selected' : ''; ?>>Python</option>
                        <option value="Java" <?php echo (isset($values['languages']) && in_array('Java', $values['languages'])) ? 'selected' : ''; ?>>Java</option>
                        <option value="Haskell" <?php echo (isset($values['languages']) && in_array('Haskell', $values['languages'])) ? 'selected' : ''; ?>>Haskell</option>
                        <option value="Clojure" <?php echo (isset($values['languages']) && in_array('Clojure', $values['languages'])) ? 'selected' : ''; ?>>Clojure</option>
                        <option value="Prolog" <?php echo (isset($values['languages']) && in_array('Prolog', $values['languages'])) ? 'selected' : ''; ?>>Prolog</option>
                        <option value="Scala" <?php echo (isset($values['languages']) && in_array('Scala', $values['languages'])) ? 'selected' : ''; ?>>Scala</option>
                        <option value="Go" <?php echo (isset($values['languages']) && in_array('Go', $values['languages'])) ? 'selected' : ''; ?>>Go</option>
                    </select>
                    <div class="hint-text">⌘ Удерживайте Ctrl (Cmd) для выбора нескольких языков</div>
                </div>
            </div>
            
            <!-- Биография -->
            <div class="field-group">
                <label for="bio">📝 Биография</label>
                <div class="input-wrapper">
                    <textarea id="bio" name="bio" rows="4" 
                              <?php if (!empty($errors['bio'])) echo 'class="error-input"'; ?> 
                              placeholder="Расскажите о своем опыте в разработке, увлечениях, проектах..."><?php echo isset($values['bio']) ? htmlspecialchars($values['bio']) : ''; ?></textarea>
                </div>
            </div>
            
            <!-- Контракт -->
            <div class="field-group">
                <label>📑 Согласие</label>
                <div class="input-wrapper checkbox-wrapper <?php if (!empty($errors['contract'])) echo 'error-wrapper'; ?>">
                    <input type="checkbox" id="contractCheck" name="contract_agreed" <?php echo (isset($values['contract']) && $values['contract'] == 'on') ? 'checked' : ''; ?> required>
                    <label for="contractCheck">Я ознакомлен(а) с условиями пользовательского соглашения *</label>
                </div>
            </div>
            
            <!-- Кнопка сохранения -->
            <div class="action-buttons">
                <button type="submit" class="save-btn">💾 Сохранить</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>