<?php
session_start();

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

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

$vacancies = [];
$services = [];
$requests = [];

try {
    // Вакансии
    $stmt = $pdo->query("
        SELECT 
            title, 
            description, 
            requirements, 
            salary, 
            location, 
            contacts, 
            creator_name, 
            created_at 
        FROM vacancies 
        ORDER BY id DESC
    ");
    $vacancies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Услуги
    $stmt = $pdo->query("
        SELECT 
            title, 
            description, 
            price, 
            location, 
            contacts, 
            creator_name, 
            created_at 
        FROM services 
        ORDER BY id DESC
    ");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ЗАЯВКИ: все поля
    if ($is_logged_in && $user_id) {
        $stmt = $pdo->prepare("
            SELECT 
                title, 
                description, 
                requirements, 
                price, 
                location, 
                contacts, 
                creator_name, 
                created_at 
            FROM requests 
            WHERE user_id = ?
            ORDER BY id DESC
        ");
        $stmt->execute([$user_id]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Ошибка БД: " . $e->getMessage());
    $vacancies = [];
    $services = [];
    $requests = [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Working — Главная</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #0f0f0f;
            color: white;
            min-height: 100vh;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: #1a1a1a;
            border-bottom: 1px solid #333;
        }
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4a9eff;
            text-decoration: none;
        }
        .search-box {
            flex: 1;
            max-width: 500px;
            margin: 0 2rem;
        }
        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem;
            border-radius: 2rem;
            border: 1px solid #444;
            background: #2a2a2a;
            color: white;
        }
        .profile-link {
            text-decoration: none;
            color: #4a6cf7;
            font-weight: 600;
        }

        .theme-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            padding: 1.5rem;
            flex-wrap: wrap;
            background: #151515;
            border-bottom: 1px solid #333;
        }
        .theme-btn {
            padding: 0.8rem 1.6rem;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 0.8rem;
            background: #2a2a2a;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
        }
        .theme-btn:hover { background: #3a3a3a; }
        .theme-btn.active {
            background: #4a6cf7;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        .theme-btn.vacancies { background: #1e40af; }
        .theme-btn.services { background: #065f46; }
        .theme-btn.requests { background: #9d174d; }
        .theme-btn.vacancies.active { background: #2563eb; }
        .theme-btn.services.active { background: #0d9488; }
        .theme-btn.requests.active { background: #ec4899; }

        .content-area {
            padding: 2rem;
            min-height: calc(100vh - 200px);
        }
        .theme-content {
            display: none;
            text-align: left;
        }
        .theme-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .theme-header {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .theme-header h2 {
            font-size: 2rem;
            color: #e0e0e0;
            margin-bottom: 1rem;
        }
        .theme-header a {
            display: inline-block;
            padding: 0.6rem 1.5rem;
            margin-top: 0.5rem;
            border-radius: 30px;
            background-color: #959595;
            text-decoration: none;
            color: white;
            font-size: 1rem;
        }
        .theme-header a:hover {
            background-color: #4a9eff;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        li {
            background: #2a2a2a;
            margin: 0.8rem 0;
            padding: 1.2rem;
            border-radius: 12px;
            line-height: 1.5;
        }
        li.not {
            background: transparent;
            text-align: center;
            color: #888;
        }
        li h3 {
            margin-bottom: 0.6rem;
            color: #4a9eff;
        }
        li p {
            margin: 0.4rem 0;
        }
        li small {
            display: block;
            color: #aaa;
            font-size: 0.85rem;
            margin-top: 0.6rem;
        }
    </style>
</head>
<body>

<header>
    <a href="main.php" class="logo">Working</a>
    <div class="search-box">
        <input type="text" id="search-input" placeholder="Поиск по сайту...">
    </div>
    <?php if ($is_logged_in): ?>
        <a href="../profile/profile.php" class="profile-link">Профиль</a>
    <?php else: ?>
        <a href="../login/login.php" class="profile-link">Войти</a>
    <?php endif; ?>
</header>

<div class="theme-buttons">
    <button class="theme-btn vacancies active" data-theme="vacancies">Вакансии</button>
    <button class="theme-btn services" data-theme="services">Услуги</button>
    <?php if ($is_logged_in): ?>
        <button class="theme-btn requests" data-theme="requests">Заявки</button>
    <?php endif; ?>
</div>

<div class="content-area">
    <!-- Вакансии -->
    <div class="theme-content active" id="vacancies-content">
        <div class="theme-header">
            <h2>Вакансии</h2>
            <?php if ($is_logged_in): ?>
                <a href="../../modules/add/add-vacancies.php">+ Добавить вакансию</a>
            <?php endif; ?>
        </div>
        <ul>
            <?php if (!empty($vacancies)): ?>
                <?php foreach ($vacancies as $v): ?>
                    <li>
                        <h3><?= htmlspecialchars($v['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if (!empty($v['creator_name'])): ?>
                            <p>👤 Автор: <strong><?= htmlspecialchars($v['creator_name'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($v['salary'])): ?>
                            <p>💰 Зарплата: <?= htmlspecialchars($v['salary'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <?php if (!empty($v['location'])): ?>
                            <p>📍 Местоположение: <?= htmlspecialchars($v['location'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <?php if (!empty($v['description'])): ?>
                            <p>📄 Описание:<br><?= nl2br(htmlspecialchars($v['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($v['requirements'])): ?>
                            <p>📋 Требования:<br><?= nl2br(htmlspecialchars($v['requirements'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($v['contacts'])): ?>
                            <p>📞 Контакты: <strong><?= htmlspecialchars($v['contacts'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($v['created_at'])): ?>
                            <small>Опубликовано: <?= date('d.m.Y в H:i', strtotime($v['created_at'])) ?></small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="not">Нет доступных вакансий</li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Услуги -->
    <div class="theme-content" id="services-content">
        <div class="theme-header">
            <h2>Услуги</h2>
            <?php if ($is_logged_in): ?>
                <a href="../../modules/add/add-services.php">+ Добавить услугу</a>
            <?php endif; ?>
        </div>
        <ul>
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $s): ?>
                    <li>
                        <h3><?= htmlspecialchars($s['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if (!empty($s['creator_name'])): ?>
                            <p>👤 Автор: <strong><?= htmlspecialchars($s['creator_name'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($s['price'])): ?>
                            <p>💰 Цена: <?= htmlspecialchars($s['price'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <?php if (!empty($s['location'])): ?>
                            <p>📍 Местоположение: <?= htmlspecialchars($s['location'], ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <?php if (!empty($s['description'])): ?>
                            <p>📄 Описание:<br><?= nl2br(htmlspecialchars($s['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($s['contacts'])): ?>
                            <p>📞 Контакты: <strong><?= htmlspecialchars($s['contacts'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                        <?php endif; ?>
                        <?php if (!empty($s['created_at'])): ?>
                            <small>Опубликовано: <?= date('d.m.Y в H:i', strtotime($s['created_at'])) ?></small>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="not">Нет доступных услуг</li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Заявки -->
<?php if ($is_logged_in): ?>
<div class="theme-content" id="requests-content">
    <div class="theme-header">
        <h2>Ваши заявки</h2>
        <a href="../../modules/add/add-requests.php">+ Добавить заявку</a>
    </div>
    <ul>
        <?php if (!empty($requests)): ?>
            <?php foreach ($requests as $r): ?>
                <li>
                    <h3><?= htmlspecialchars($r['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
                    <?php if (!empty($r['creator_name'])): ?>
                        <p>👤 Автор: <strong><?= htmlspecialchars($r['creator_name'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <?php endif; ?>
                    <?php if (!empty($r['price'])): ?>
                        <p>💰 Бюджет: <?= htmlspecialchars($r['price'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <?php if (!empty($r['location'])): ?>
                        <p>📍 Местоположение: <?= htmlspecialchars($r['location'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <?php if (!empty($r['description'])): ?>
                        <p>📄 Описание:
<?= nl2br(htmlspecialchars($r['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($r['requirements'])): ?>
                        <p>📋 Требования:
<?= nl2br(htmlspecialchars($r['requirements'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($r['contacts'])): ?>
                        <p>📞 Ваши контакты: <strong><?= htmlspecialchars($r['contacts'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <?php endif; ?>
                    <?php if (!empty($r['created_at'])): ?>
                        <small>Создано: <?= date('d.m.Y в H:i', strtotime($r['created_at'])) ?></small>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="not">У вас пока нет заявок</li>
        <?php endif; ?>
    </ul>
</div>
<?php endif; ?>
</div>

<script>
    const buttons = document.querySelectorAll('.theme-btn');
    const contents = document.querySelectorAll('.theme-content');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const theme = button.dataset.theme;
            buttons.forEach(b => b.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            button.classList.add('active');
            document.getElementById(theme + '-content')?.classList.add('active');
        });
    });
</script>
</body>
</html>