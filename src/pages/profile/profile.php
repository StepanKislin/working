<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Подключение к базе данных
$host = 'localhost';
$dbname = 'working';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

// Получение данных пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Пользователь не найден.");
}

// Подготовка данных для навыков
$user_skills_array = !empty($user['skills']) 
    ? array_map('trim', explode(',', $user['skills'])) 
    : [];

$popular_skills = [
    'Python', 'JavaScript', 'PHP', 'Java', 'C++', 'TypeScript', 'SQL', 'Git',
    'React', 'Vue.js', 'Node.js', 'Docker', 'Kubernetes', 'Figma', 'Adobe Photoshop',
    'UI/UX', 'Data Analysis', 'Machine Learning', 'Cybersecurity', 'HTML', 'CSS'
];

// Обработка сохранения профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $role = trim($_POST['role'] ?? '');
    $direction = trim($_POST['direction'] ?? '');
    $experience_years = (int)($_POST['experience_years'] ?? 0);
    $about = trim($_POST['about'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $telegram = trim($_POST['telegram'] ?? '');
    $github = trim($_POST['github'] ?? '');

    // Обработка навыков
    $selected_skills = $_POST['skills'] ?? [];
    $custom_skills = trim($_POST['custom_skills'] ?? '');
    $all_skills = $selected_skills;

    if (!empty($custom_skills)) {
        $custom = array_map('trim', explode(',', $custom_skills));
        $all_skills = array_merge($all_skills, $custom);
    }

    $skills_str = implode(', ', array_unique(array_filter($all_skills)));

    // Ограничение опыта
    if ($experience_years < 0) $experience_years = 0;
    if ($experience_years > 60) $experience_years = 60;

    // Сохранение в БД
    try {
        $stmt = $pdo->prepare("
            UPDATE users SET 
                role = ?, 
                direction = ?, 
                experience_years = ?, 
                about = ?, 
                skills = ?,
                linkedin = ?,
                telegram = ?,
                github = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $role ?: null,
            $direction ?: null,
            $experience_years ?: null,
            $about ?: null,
            $skills_str ?: null,
            $linkedin ?: null,
            $telegram ?: null,
            $github ?: null,
            $user_id
        ]);

        // Обновляем локальные данные
        $user['role'] = $role;
        $user['direction'] = $direction;
        $user['experience_years'] = $experience_years;
        $user['about'] = $about;
        $user['skills'] = $skills_str;
        $user['linkedin'] = $linkedin;
        $user['telegram'] = $telegram;
        $user['github'] = $github;

        // Перенаправление для предотвращения повторной отправки
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?tab=info");
        exit;
    } catch (PDOException $e) {
        $error = "Ошибка сохранения: " . htmlspecialchars($e->getMessage());
    }
}

// Имя для поиска вакансий/услуг
$creator_name = htmlspecialchars($user['name'] . ' ' . $user['surname'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль — Working</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        body {
            background: #0f0f0f;
            color: #e0e0e0;
            padding: 1.5rem;
            min-height: 100vh;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .header h1 {
            color: #4a9eff;
            font-size: 1.8rem;
            font-weight: 600;
        }
        .back {
            color: #4a9eff;
            text-decoration: none;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            border: 1px solid #4a9eff;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .back:hover {
            background: rgba(74, 158, 255, 0.1);
        }
        .tabs {
            display: flex;
            gap: 0.8rem;
            margin: 1rem 0 2rem;
            flex-wrap: wrap;
        }
        .tab-btn {
            padding: 0.6rem 1.2rem;
            background: #2a2a2a;
            border: none;
            color: #ccc;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }
        .tab-btn:hover {
            background: #353535;
        }
        .tab-btn.active {
            background: #4a6cf7;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-card {
            max-width: 750px;
            margin: 0 auto;
            background: #1e1e1e;
            border-radius: 16px;
            padding: 1.8rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
            border: 1px solid #333;
        }
        .info-row {
            display: flex;
            margin: 0.8rem 0;
            align-items: flex-start;
        }
        .info-label {
            min-width: 160px;
            color: #6ab0ff;
            font-weight: 500;
        }
        .info-value {
            flex: 1;
            word-break: break-word;
            line-height: 1.5;
        }
        .info-value i {
            color: #777;
        }
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-top: 0.4rem;
        }
        .skill-tag {
            background: #2a3a5a;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #a0c0ff;
        }
        .social-link {
            color: #4a9eff;
            text-decoration: none;
        }
        .social-link:hover {
            text-decoration: underline;
        }

        .form-group {
            margin: 1.2rem 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #6ab0ff;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 0.7rem;
            background: #2a2a2a;
            border: 1px solid #444;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
        }
        .form-control:focus {
            outline: none;
            border-color: #4a9eff;
        }
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        .skills-checklist {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 0.6rem;
            margin-top: 0.5rem;
        }
        .skill-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #ccc;
        }
        .skill-checkbox input {
            accent-color: #4a6cf7;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            justify-content: flex-end;
        }
        .btn {
            padding: 0.6rem 1.4rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary {
            background: #4a6cf7;
            color: white;
            border: none;
        }
        .btn-secondary {
            background: #333;
            color: #ccc;
            border: 1px solid #555;
        }
        .btn-test {
            background: #8a6bff;
            color: white;
            border: none;
            margin-top: 1rem;
            padding: 0.7rem;
            width: 100%;
            font-weight: 600;
        }
        .empty {
            color: #888;
            text-align: center;
            padding: 1.5rem;
            max-width: 600px;
            margin: 1rem auto;
        }
        ul {
            list-style: none;
            padding: 0;
            max-width: 750px;
            margin: 0 auto;
        }
        li {
            background: #1e1e1e;
            margin: 0.8rem 0;
            padding: 1.2rem;
            border-radius: 12px;
            line-height: 1.5;
            border: 1px solid #333;
        }
        li h3 {
            color: #4a9eff;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }
        small {
            display: block;
            color: #888;
            font-size: 0.85rem;
            margin-top: 0.6rem;
        }

        @media (max-width: 768px) {
            .skills-checklist {
                grid-template-columns: 1fr;
            }
            .info-label {
                min-width: 130px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Профиль: <?= htmlspecialchars($user['name'] . ' ' . $user['surname']) ?></h1>
    <a href="../main/main.php" class="back">← На главную</a>
</div>

<div class="tabs">
    <button class="tab-btn active" data-tab="info">Информация</button>
    <button class="tab-btn" data-tab="vacancies">Вакансии</button>
    <button class="tab-btn" data-tab="services">Услуги</button>
    <button class="tab-btn" data-tab="requests">Заявки</button>
</div>

<!-- Информация -->
<div id="info" class="tab-content active">
    <?php if (!empty($_GET['edit']) && $_GET['edit'] === 'profile'): ?>
        <div class="profile-card">
            <?php if (!empty($error)): ?>
                <div style="color: #ff6b6b; margin-bottom: 1rem;"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label>📧 Email</label>
                    <div><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <div class="form-group">
                    <label>📱 Телефон</label>
                    <div><?= htmlspecialchars($user['phone']) ?></div>
                </div>
                <div class="form-group">
                    <label>💼 Должность / Роль</label>
                    <input type="text" name="role" class="form-control" value="<?= htmlspecialchars($user['role'] ?? '') ?>" placeholder="Например: Product Manager">
                </div>
                <div class="form-group">
                    <label>🧭 Направление деятельности</label>
                    <select name="direction" class="form-control">
                        <option value="">— Выберите —</option>
                        <!-- IT и технологии -->
                        <option value="Разработка ПО" <?= ($user['direction'] ?? '') === 'Разработка ПО' ? 'selected' : '' ?>>Разработка ПО</option>
                        <option value="Веб-разработка" <?= ($user['direction'] ?? '') === 'Веб-разработка' ? 'selected' : '' ?>>Веб-разработка</option>
                        <option value="Мобильная разработка" <?= ($user['direction'] ?? '') === 'Мобильная разработка' ? 'selected' : '' ?>>Мобильная разработка</option>
                        <option value="DevOps / SRE" <?= ($user['direction'] ?? '') === 'DevOps / SRE' ? 'selected' : '' ?>>DevOps / SRE</option>
                        <option value="Data Science" <?= ($user['direction'] ?? '') === 'Data Science' ? 'selected' : '' ?>>Data Science</option>
                        <option value="Искусственный интеллект" <?= ($user['direction'] ?? '') === 'Искусственный интеллект' ? 'selected' : '' ?>>Искусственный интеллект</option>
                        <option value="Машинное обучение" <?= ($user['direction'] ?? '') === 'Машинное обучение' ? 'selected' : '' ?>>Машинное обучение</option>
                        <option value="Анализ данных" <?= ($user['direction'] ?? '') === 'Анализ данных' ? 'selected' : '' ?>>Анализ данных</option>
                        <option value="Кибербезопасность" <?= ($user['direction'] ?? '') === 'Кибербезопасность' ? 'selected' : '' ?>>Кибербезопасность</option>
                        <option value="Тестирование ПО (QA)" <?= ($user['direction'] ?? '') === 'Тестирование ПО (QA)' ? 'selected' : '' ?>>Тестирование ПО (QA)</option>
                        <option value="Системное администрирование" <?= ($user['direction'] ?? '') === 'Системное администрирование' ? 'selected' : '' ?>>Системное администрирование</option>
                        <option value="Сетевые технологии" <?= ($user['direction'] ?? '') === 'Сетевые технологии' ? 'selected' : '' ?>>Сетевые технологии</option>
                        <option value="Blockchain / Web3" <?= ($user['direction'] ?? '') === 'Blockchain / Web3' ? 'selected' : '' ?>>Blockchain / Web3</option>
                        <option value="Облачные технологии" <?= ($user['direction'] ?? '') === 'Облачные технологии' ? 'selected' : '' ?>>Облачные технологии</option>
                        <option value="Игровая индустрия" <?= ($user['direction'] ?? '') === 'Игровая индустрия' ? 'selected' : '' ?>>Игровая индустрия</option>
                        <!-- Бизнес и управление -->
                        <option value="Управление продуктом (PM)" <?= ($user['direction'] ?? '') === 'Управление продуктом (PM)' ? 'selected' : '' ?>>Управление продуктом (PM)</option>
                        <option value="Проектный менеджмент" <?= ($user['direction'] ?? '') === 'Проектный менеджмент' ? 'selected' : '' ?>>Проектный менеджмент</option>
                        <option value="Бизнес-аналитика" <?= ($user['direction'] ?? '') === 'Бизнес-аналитика' ? 'selected' : '' ?>>Бизнес-аналитика</option>
                        <option value="Стратегия и консалтинг" <?= ($user['direction'] ?? '') === 'Стратегия и консалтинг' ? 'selected' : '' ?>>Стратегия и консалтинг</option>
                        <option value="Операционное управление" <?= ($user['direction'] ?? '') === 'Операционное управление' ? 'selected' : '' ?>>Операционное управление</option>
                        <option value="Предпринимательство" <?= ($user['direction'] ?? '') === 'Предпринимательство' ? 'selected' : '' ?>>Предпринимательство</option>
                        <!-- Маркетинг и продажи -->
                        <option value="Маркетинг" <?= ($user['direction'] ?? '') === 'Маркетинг' ? 'selected' : '' ?>>Маркетинг</option>
                        <option value="Digital-маркетинг" <?= ($user['direction'] ?? '') === 'Digital-маркетинг' ? 'selected' : '' ?>>Digital-маркетинг</option>
                        <option value="Контент-маркетинг" <?= ($user['direction'] ?? '') === 'Контент-маркетинг' ? 'selected' : '' ?>>Контент-маркетинг</option>
                        <option value="SEO / SMM" <?= ($user['direction'] ?? '') === 'SEO / SMM' ? 'selected' : '' ?>>SEO / SMM</option>
                        <option value="Продажи" <?= ($user['direction'] ?? '') === 'Продажи' ? 'selected' : '' ?>>Продажи</option>
                        <option value="Реклама" <?= ($user['direction'] ?? '') === 'Реклама' ? 'selected' : '' ?>>Реклама</option>
                        <option value="PR" <?= ($user['direction'] ?? '') === 'PR' ? 'selected' : '' ?>>PR</option>
                        <!-- Финансы и юриспруденция -->
                        <option value="Финансы" <?= ($user['direction'] ?? '') === 'Финансы' ? 'selected' : '' ?>>Финансы</option>
                        <option value="Бухгалтерия" <?= ($user['direction'] ?? '') === 'Бухгалтерия' ? 'selected' : '' ?>>Бухгалтерия</option>
                        <option value="Аудит" <?= ($user['direction'] ?? '') === 'Аудит' ? 'selected' : '' ?>>Аудит</option>
                        <option value="Инвестиции" <?= ($user['direction'] ?? '') === 'Инвестиции' ? 'selected' : '' ?>>Инвестиции</option>
                        <option value="Страхование" <?= ($user['direction'] ?? '') === 'Страхование' ? 'selected' : '' ?>>Страхование</option>
                        <option value="Юриспруденция" <?= ($user['direction'] ?? '') === 'Юриспруденция' ? 'selected' : '' ?>>Юриспруденция</option>
                        <option value="Комплаенс" <?= ($user['direction'] ?? '') === 'Комплаенс' ? 'selected' : '' ?>>Комплаенс</option>
                        <option value="Налоговое право" <?= ($user['direction'] ?? '') === 'Налоговое право' ? 'selected' : '' ?>>Налоговое право</option>
                        <!-- Дизайн и медиа -->
                        <option value="UI/UX-дизайн" <?= ($user['direction'] ?? '') === 'UI/UX-дизайн' ? 'selected' : '' ?>>UI/UX-дизайн</option>
                        <option value="Графический дизайн" <?= ($user['direction'] ?? '') === 'Графический дизайн' ? 'selected' : '' ?>>Графический дизайн</option>
                        <option value="Моушн-дизайн" <?= ($user['direction'] ?? '') === 'Моушн-дизайн' ? 'selected' : '' ?>>Моушн-дизайн</option>
                        <option value="Видео и монтаж" <?= ($user['direction'] ?? '') === 'Видео и монтаж' ? 'selected' : '' ?>>Видео и монтаж</option>
                        <option value="Фотография" <?= ($user['direction'] ?? '') === 'Фотография' ? 'selected' : '' ?>>Фотография</option>
                        <option value="Музыка и звук" <?= ($user['direction'] ?? '') === 'Музыка и звук' ? 'selected' : '' ?>>Музыка и звук</option>
                        <option value="Копирайтинг" <?= ($user['direction'] ?? '') === 'Копирайтинг' ? 'selected' : '' ?>>Копирайтинг</option>
                        <option value="Журналистика" <?= ($user['direction'] ?? '') === 'Журналистика' ? 'selected' : '' ?>>Журналистика</option>
                        <!-- Наука и образование -->
                        <option value="Образование" <?= ($user['direction'] ?? '') === 'Образование' ? 'selected' : '' ?>>Образование</option>
                        <option value="Научные исследования" <?= ($user['direction'] ?? '') === 'Научные исследования' ? 'selected' : '' ?>>Научные исследования</option>
                        <option value="Медицина" <?= ($user['direction'] ?? '') === 'Медицина' ? 'selected' : '' ?>>Медицина</option>
                        <option value="Психология" <?= ($user['direction'] ?? '') === 'Психология' ? 'selected' : '' ?>>Психология</option>
                        <option value="Биотехнологии" <?= ($user['direction'] ?? '') === 'Биотехнологии' ? 'selected' : '' ?>>Биотехнологии</option>
                        <!-- Промышленность -->
                        <option value="Инженерия" <?= ($user['direction'] ?? '') === 'Инженерия' ? 'selected' : '' ?>>Инженерия</option>
                        <option value="Строительство" <?= ($user['direction'] ?? '') === 'Строительство' ? 'selected' : '' ?>>Строительство</option>
                        <option value="Архитектура" <?= ($user['direction'] ?? '') === 'Архитектура' ? 'selected' : '' ?>>Архитектура</option>
                        <option value="Логистика" <?= ($user['direction'] ?? '') === 'Логистика' ? 'selected' : '' ?>>Логистика</option>
                        <option value="Сельское хозяйство" <?= ($user['direction'] ?? '') === 'Сельское хозяйство' ? 'selected' : '' ?>>Сельское хозяйство</option>
                        <option value="Энергетика" <?= ($user['direction'] ?? '') === 'Энергетика' ? 'selected' : '' ?>>Энергетика</option>
                        <!-- Сервис и общество -->
                        <option value="HR и рекрутинг" <?= ($user['direction'] ?? '') === 'HR и рекрутинг' ? 'selected' : '' ?>>HR и рекрутинг</option>
                        <option value="Государственная служба" <?= ($user['direction'] ?? '') === 'Государственная служба' ? 'selected' : '' ?>>Государственная служба</option>
                        <option value="НКО и социальные проекты" <?= ($user['direction'] ?? '') === 'НКО и социальные проекты' ? 'selected' : '' ?>>НКО и социальные проекты</option>
                        <option value="Туризм и гостеприимство" <?= ($user['direction'] ?? '') === 'Туризм и гостеприимство' ? 'selected' : '' ?>>Туризм и гостеприимство</option>
                        <option value="Ресторанный бизнес" <?= ($user['direction'] ?? '') === 'Ресторанный бизнес' ? 'selected' : '' ?>>Ресторанный бизнес</option>
                        <option value="Розничная торговля" <?= ($user['direction'] ?? '') === 'Розничная торговля' ? 'selected' : '' ?>>Розничная торговля</option>
                        <option value="Автомобильный бизнес" <?= ($user['direction'] ?? '') === 'Автомобильный бизнес' ? 'selected' : '' ?>>Автомобильный бизнес</option>
                        <option value="Другое" <?= ($user['direction'] ?? '') === 'Другое' ? 'selected' : '' ?>>Другое</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>📅 Опыт работы (лет)</label>
                    <input type="number" name="experience_years" class="form-control" value="<?= (int)($user['experience_years'] ?? 0) ?>" min="0" max="60">
                </div>
                <div class="form-group">
                    <label>📝 О себе</label>
                    <textarea name="about" class="form-control" placeholder="Расскажите о себе..."><?= htmlspecialchars($user['about'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>🛠️ Навыки</label>
                    <p style="color:#888; font-size:0.9rem; margin-bottom:0.6rem;">Выберите популярные навыки и/или добавьте свои:</p>
                    <div class="skills-checklist">
                        <?php foreach ($popular_skills as $skill): ?>
                            <label class="skill-checkbox">
                                <input type="checkbox" name="skills[]" value="<?= htmlspecialchars($skill) ?>" 
                                    <?= in_array($skill, $user_skills_array) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($skill) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="custom_skills" class="form-control" 
                        placeholder="Или введите свои навыки через запятую: Swift, Unity, Blender..."
                        value="">
                </div>
                <div class="form-group">
                    <label>🔗 Профили</label>
                    <input type="url" name="linkedin" class="form-control" value="<?= htmlspecialchars($user['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/...">
                    <input type="text" name="telegram" class="form-control" value="<?= htmlspecialchars($user['telegram'] ?? '') ?>" placeholder="@username">
                    <input type="url" name="github" class="form-control" value="<?= htmlspecialchars($user['github'] ?? '') ?>" placeholder="https://github.com/...">
                </div>
                <div class="form-actions">
                    <button type="submit" name="save_profile" class="btn btn-primary">Сохранить профиль</button>
                    <a href="?tab=info" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="profile-card">
            <div class="info-row">
                <div class="info-label">📧 Email:</div>
                <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">📱 Телефон:</div>
                <div class="info-value"><?= htmlspecialchars($user['phone']) ?></div>
            </div>
            <?php if (!empty($user['role'])): ?>
            <div class="info-row">
                <div class="info-label">💼 Должность:</div>
                <div class="info-value"><?= htmlspecialchars($user['role']) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($user['direction'])): ?>
            <div class="info-row">
                <div class="info-label">🧭 Направление:</div>
                <div class="info-value"><?= htmlspecialchars($user['direction']) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($user['experience_years'])): ?>
            <div class="info-row">
                <div class="info-label">📅 Опыт:</div>
                <div class="info-value"><?= (int)$user['experience_years'] ?> лет</div>
            </div>
            <?php endif; ?>
            <?php if (!empty($user['about'])): ?>
            <div class="info-row">
                <div class="info-label">📝 О себе:</div>
                <div class="info-value"><?= nl2br(htmlspecialchars($user['about'])) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($user['skills'])): ?>
            <div class="info-row">
                <div class="info-label">🛠️ Навыки:</div>
                <div class="info-value">
                    <div class="skills-list">
                        <?php foreach (array_filter(array_map('trim', explode(',', $user['skills']))) as $skill): ?>
                            <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <div class="info-label">🔗 Профили:</div>
                <div class="info-value">
                    <?php if (!empty($user['linkedin'])): ?>
                        <div><a href="<?= htmlspecialchars($user['linkedin']) ?>" target="_blank" class="social-link">LinkedIn</a></div>
                    <?php endif; ?>
                    <?php if (!empty($user['telegram'])): ?>
                        <div><a href="https://t.me/<?= ltrim(htmlspecialchars($user['telegram']), '@') ?>" target="_blank" class="social-link"><?= htmlspecialchars($user['telegram']) ?></a></div>
                    <?php endif; ?>
                    <?php if (!empty($user['github'])): ?>
                        <div><a href="<?= htmlspecialchars($user['github']) ?>" target="_blank" class="social-link">GitHub</a></div>
                    <?php endif; ?>
                    <?php if (empty($user['linkedin']) && empty($user['telegram']) && empty($user['github'])): ?>
                        <i>Не указаны</i>
                    <?php endif; ?>
                </div>
            </div>

            <div style="text-align: center; margin-top: 1.8rem;">
                <a href="../test/test.php" target="_blank" class="btn btn-test">
                    🎯 Пройти тест на профориентацию
                </a>
            </div>

            <div style="text-align: center; margin-top: 1.2rem;">
                <a href="?edit=profile" class="btn btn-secondary" style="padding: 0.5rem 1.2rem;">✏️ Редактировать профиль</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Вакансии -->
<div id="vacancies" class="tab-content">
    <?php
    $stmt = $pdo->prepare("SELECT * FROM vacancies WHERE creator_name = ? ORDER BY created_at DESC");
    $stmt->execute([$creator_name]);
    $items = $stmt->fetchAll();
    if ($items): ?>
        <ul>
            <?php foreach ($items as $v): ?>
                <li>
                    <h3><?= htmlspecialchars($v['title']) ?></h3>
                    <?php if (!empty($v['salary'])): ?>
                        <p>💰 ЗП: <?= htmlspecialchars($v['salary']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($v['location'])): ?>
                        <p>📍 <?= htmlspecialchars($v['location']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($v['description'])): ?>
                        <p>📄 <?= nl2br(htmlspecialchars($v['description'])) ?></p>
                    <?php endif; ?>
                    <small>Опубликовано: <?= date('d.m.Y в H:i', strtotime($v['created_at'])) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="empty">Вы ещё не создали ни одной вакансии.</p>
    <?php endif; ?>
</div>

<!-- Услуги -->
<div id="services" class="tab-content">
    <?php
    $stmt = $pdo->prepare("SELECT * FROM services WHERE creator_name = ? ORDER BY created_at DESC");
    $stmt->execute([$creator_name]);
    $items = $stmt->fetchAll();
    if ($items): ?>
        <ul>
            <?php foreach ($items as $s): ?>
                <li>
                    <h3><?= htmlspecialchars($s['title']) ?></h3>
                    <?php if (!empty($s['price'])): ?>
                        <p>💰 Цена: <?= htmlspecialchars($s['price']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($s['location'])): ?>
                        <p>📍 <?= htmlspecialchars($s['location']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($s['description'])): ?>
                        <p>📄 <?= nl2br(htmlspecialchars($s['description'])) ?></p>
                    <?php endif; ?>
                    <small>Опубликовано: <?= date('d.m.Y в H:i', strtotime($s['created_at'])) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="empty">Вы ещё не добавили ни одной услуги.</p>
    <?php endif; ?>
</div>

<!-- Заявки -->
<div id="requests" class="tab-content">
    <?php
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();
    if ($items): ?>
        <ul>
            <?php foreach ($items as $r): ?>
                <li>
                    <h3><?= htmlspecialchars($r['title']) ?></h3>
                    <?php if (!empty($r['price'])): ?>
                        <p>💰 Бюджет: <?= htmlspecialchars($r['price']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($r['location'])): ?>
                        <p>📍 <?= htmlspecialchars($r['location']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($r['description'])): ?>
                        <p>📄 <?= nl2br(htmlspecialchars($r['description'])) ?></p>
                    <?php endif; ?>
                    <small>Создано: <?= date('d.m.Y в H:i', strtotime($r['created_at'])) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="empty">У вас пока нет заявок.</p>
    <?php endif; ?>
</div>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const tabFromUrl = urlParams.get('tab');
    if (tabFromUrl && document.getElementById(tabFromUrl)) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelector(`.tab-btn[data-tab="${tabFromUrl}"]`)?.classList.add('active');
        document.getElementById(tabFromUrl)?.classList.add('active');
    }

    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            button.classList.add('active');
            const tabId = button.dataset.tab;
            document.getElementById(tabId).classList.add('active');

            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.replaceState(null, '', url);
        });
    });
</script>

</body>
</html>