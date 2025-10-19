<?php
session_start();

// === ВАЛЮТНЫЕ КУРСЫ (на 2025, можно обновлять) ===
$exchangeRates = [
    'USD' => 1.0,
    'RUB' => 0.011,   // ~90 RUB = 1 USD
    'EUR' => 1.07,
    'GBP' => 1.27,
    'CNY' => 0.14,    // ~7.2 CNY = 1 USD
    'TRY' => 0.03,    // ~33 TRY = 1 USD
    'KZT' => 0.0021,  // ~470 KZT = 1 USD
    'UAH' => 0.024,   // ~41 UAH = 1 USD
    'BYN' => 0.30,    // ~3.3 BYN = 1 USD
];

// Функция нормализации цены в USD
function normalizeToUSD($priceStr, $rates) {
    if (empty($priceStr)) return 0.0;

    $priceStr = trim($priceStr);
    $number = (float) preg_replace('/[^\d.,]/', '', str_replace(',', '.', $priceStr));
    if ($number <= 0) return 0.0;

    // Определяем валюту по символу или коду
    if (preg_match('/[₽р]{1,2}/iu', $priceStr)) return $number * $rates['RUB'];
    if (preg_match('/[\$]/', $priceStr)) return $number * $rates['USD'];
    if (preg_match('/[€]/', $priceStr)) return $number * $rates['EUR'];
    if (preg_match('/[£]/', $priceStr)) return $number * $rates['GBP'];
    if (preg_match('/[¥元]/u', $priceStr)) return $number * $rates['CNY'];
    if (preg_match('/[₺TL]/i', $priceStr)) return $number * $rates['TRY'];
    if (preg_match('/тг/i', $priceStr)) return $number * $rates['KZT'];
    if (preg_match('/грн/i', $priceStr)) return $number * $rates['UAH'];
    if (preg_match('/Br/i', $priceStr)) return $number * $rates['BYN'];

    // По умолчанию — рубли
    return $number * $rates['RUB'];
}

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

    // ЗАЯВКИ
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

        /* === ВЫПАДАЮЩЕЕ МЕНЮ ПОЛЬЗОВАТЕЛЯ === */
        .user-menu {
            position: relative;
            display: flex;
            align-items: center;
        }

        .profile-btn {
            background: none;
            border: none;
            color: #4a6cf7;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .profile-btn:hover {
            background: rgba(74, 108, 247, 0.1);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: #222;
            border: 1px solid #444;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            min-width: 180px;
            z-index: 1000;
            flex-direction: column;
        }

        .dropdown-menu a {
            padding: 0.75rem 1rem;
            color: #e0e0e0;
            text-decoration: none;
            font-size: 0.95rem;
            transition: background 0.2s, color 0.2s;
        }

        .dropdown-menu a:hover {
            background: #333;
            color: #4a9eff;
        }

        .dropdown-menu.show {
            display: flex;
        }

        .logout-link {
            color: #ff6b6b !important;
        }

        .logout-link:hover {
            color: #ff4d4d !important;
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
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .theme-header h2 {
            font-size: 2rem;
            color: #e0e0e0;
            margin-bottom: 1rem;
        }
        .theme-header .actions {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }
        .theme-header a {
            padding: 0.6rem 1.5rem;
            border-radius: 30px;
            background-color: #959595;
            text-decoration: none;
            color: white;
            font-size: 1rem;
            white-space: nowrap;
        }
        .theme-header a:hover {
            background-color: #4a9eff;
        }

        /* === СТИЛИ ДЛЯ ФИЛЬТРОВ === */
        .filters {
            display: flex;
            gap: 12px;
            margin: 1rem 0;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }
        .filters label {
            color: #aaa;
            font-size: 0.95rem;
        }
        .filters select {
            padding: 0.65rem 1rem;
            border-radius: 0.7rem;
            background: #222;
            color: white;
            border: 1px solid #444;
            font-size: 0.95rem;
            min-width: 180px;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .filters select:focus {
            outline: none;
            border-color: #4a9eff;
            box-shadow: 0 0 0 2px rgba(74, 158, 255, 0.2);
        }
        .filters select:hover {
            border-color: #666;
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
            transition: transform 0.2s, box-shadow 0.2s;
        }
        li:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
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
        <?php
        $user_name = 'Гость';
        if ($user_id) {
            try {
                $stmt = $pdo->prepare("SELECT name, surname FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $user_name = htmlspecialchars($user['name'] . ' ' . $user['surname'], ENT_QUOTES, 'UTF-8');
                }
            } catch (PDOException $e) {
                // оставить "Гость"
            }
        }
        ?>
        <div class="user-menu">
            <button class="profile-btn"><?= $user_name ?></button>
            <div class="dropdown-menu" id="user-dropdown">
                <a href="../profile/profile.php">Профиль</a>
                <a href="../settings/settings.php">Настройки</a>
                <a href="../news/news.php">Новости</a>
                <a href="../market/market.php">Рынок</a>
                <a href="../login/logout.php" class="logout-link">Выход</a>
            </div>
        </div>
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
            <div class="actions">
                <?php if ($is_logged_in): ?>
                    <a href="../../modules/add/add-vacancies.php">+ Добавить вакансию</a>
                <?php endif; ?>
                <div class="filters">
                    <label for="vacancies-sort">Сортировать:</label>
                    <select id="vacancies-sort">
                        <option value="default">По умолчанию</option>
                        <option value="salary-asc">ЗП: по возрастанию</option>
                        <option value="salary-desc">ЗП: по убыванию</option>
                        <option value="date-new">Сначала новые</option>
                        <option value="date-old">Сначала старые</option>
                    </select>
                </div>
            </div>
        </div>
        <ul>
            <?php if (!empty($vacancies)): ?>
                <?php foreach ($vacancies as $v): ?>
                    <?php $usd = normalizeToUSD($v['salary'] ?? '', $exchangeRates); ?>
                    <li data-salary-usd="<?= number_format($usd, 2, '.', '') ?>"
                        data-date="<?= strtotime($v['created_at'] ?? 'now') ?>">
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
                            <p>📄 Описание:
<?= nl2br(htmlspecialchars($v['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($v['requirements'])): ?>
                            <p>📋 Требования:
<?= nl2br(htmlspecialchars($v['requirements'], ENT_QUOTES, 'UTF-8')) ?></p>
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
            <div class="actions">
                <?php if ($is_logged_in): ?>
                    <a href="../../modules/add/add-services.php">+ Добавить услугу</a>
                <?php endif; ?>
                <div class="filters">
                    <label for="services-sort">Сортировать:</label>
                    <select id="services-sort">
                        <option value="default">По умолчанию</option>
                        <option value="price-asc">Цена: по возрастанию</option>
                        <option value="price-desc">Цена: по убыванию</option>
                        <option value="date-new">Сначала новые</option>
                        <option value="date-old">Сначала старые</option>
                    </select>
                </div>
            </div>
        </div>
        <ul>
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $s): ?>
                    <?php $usd = normalizeToUSD($s['price'] ?? '', $exchangeRates); ?>
                    <li data-price-usd="<?= number_format($usd, 2, '.', '') ?>"
                        data-date="<?= strtotime($s['created_at'] ?? 'now') ?>">
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
                            <p>📄 Описание:
<?= nl2br(htmlspecialchars($s['description'], ENT_QUOTES, 'UTF-8')) ?></p>
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
            <div class="actions">
                <a href="../../modules/add/add-requests.php">+ Добавить заявку</a>
                <div class="filters">
                    <label for="requests-sort">Сортировать:</label>
                    <select id="requests-sort">
                        <option value="default">По умолчанию</option>
                        <option value="price-asc">Бюджет: по возрастанию</option>
                        <option value="price-desc">Бюджет: по убыванию</option>
                        <option value="date-new">Сначала новые</option>
                        <option value="date-old">Сначала старые</option>
                    </select>
                </div>
            </div>
        </div>
        <ul>
            <?php if (!empty($requests)): ?>
                <?php foreach ($requests as $r): ?>
                    <?php $usd = normalizeToUSD($r['price'] ?? '', $exchangeRates); ?>
                    <li data-price-usd="<?= number_format($usd, 2, '.', '') ?>"
                        data-date="<?= strtotime($r['created_at'] ?? 'now') ?>">
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
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.theme-btn');
    const contents = document.querySelectorAll('.theme-content');
    const searchInput = document.getElementById('search-input');

    const originalOrders = {};

    contents.forEach(content => {
        const list = content.querySelector('ul');
        if (list) {
            originalOrders[content.id] = Array.from(list.children);
        }
    });

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const theme = button.dataset.theme;
            buttons.forEach(b => b.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            button.classList.add('active');
            document.getElementById(theme + '-content')?.classList.add('active');
            if (searchInput) {
                searchInput.value = '';
                filterContent();
            }
            const sortSelect = document.getElementById(theme + '-sort');
            if (sortSelect) {
                sortSelect.value = 'default';
                restoreOriginalOrder(theme + '-content');
            }
        });
    });

    function restoreOriginalOrder(contentId) {
        const content = document.getElementById(contentId);
        const list = content.querySelector('ul');
        const original = originalOrders[contentId];
        if (list && original) {
            list.innerHTML = '';
            original.forEach(item => list.appendChild(item.cloneNode(true)));
        }
        originalOrders[contentId] = Array.from(list.children);
        filterContent();
    }

    function filterContent() {
        const query = searchInput?.value.trim().toLowerCase() || '';
        const activeContent = document.querySelector('.theme-content.active');
        if (!activeContent) return;

        const items = activeContent.querySelectorAll('li:not(.not)');
        let visibleCount = 0;

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            const isVisible = text.includes(query);
            item.style.display = isVisible ? 'block' : 'none';
            if (isVisible) visibleCount++;
        });

        const noResults = activeContent.querySelector('li.not');
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    function sortItems(contentId, sortBy) {
        const content = document.getElementById(contentId);
        const list = content.querySelector('ul');
        const items = Array.from(content.querySelectorAll('li:not(.not)'));

        if (sortBy === 'default') {
            restoreOriginalOrder(contentId);
            return;
        }

        const filteredItems = items.filter(item => item.style.display !== 'none');

        filteredItems.sort((a, b) => {
            if (sortBy === 'salary-asc' || sortBy === 'price-asc') {
                const valA = parseFloat(a.dataset.salaryUsd || a.dataset.priceUsd || 0);
                const valB = parseFloat(b.dataset.salaryUsd || b.dataset.priceUsd || 0);
                return valA - valB;
            }
            if (sortBy === 'salary-desc' || sortBy === 'price-desc') {
                const valA = parseFloat(a.dataset.salaryUsd || a.dataset.priceUsd || 0);
                const valB = parseFloat(b.dataset.salaryUsd || b.dataset.priceUsd || 0);
                return valB - valA;
            }
            if (sortBy === 'date-new') {
                return (b.dataset.date || 0) - (a.dataset.date || 0);
            }
            if (sortBy === 'date-old') {
                return (a.dataset.date || 0) - (b.dataset.date || 0);
            }
            return 0;
        });

        const noResults = content.querySelector('li.not');
        list.innerHTML = '';
        filteredItems.forEach(item => list.appendChild(item));
        if (noResults) list.appendChild(noResults);

        originalOrders[contentId] = Array.from(list.children);
    }

    ['vacancies', 'services', 'requests'].forEach(type => {
        const select = document.getElementById(type + '-sort');
        if (select) {
            select.addEventListener('change', (e) => {
                sortItems(type + '-content', e.target.value);
            });
        }
    });

    if (searchInput) {
        searchInput.addEventListener('input', filterContent);
    }

    // Выпадающее меню пользователя
    const profileBtn = document.querySelector('.profile-btn');
    const dropdown = document.getElementById('user-dropdown');

    if (profileBtn && dropdown) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    }

    filterContent();
});
</script>

</body>
</html>