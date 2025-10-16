<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login/login.php');
    exit;
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

$user_id = $_SESSION['user_id'];

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Пользователь не найден.");
}

$creator_name = trim($user['name'] . ' ' . $user['surname']);
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #0f0f0f;
            color: white;
            padding: 1.5rem;
            min-height: 100vh;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .header h1 {
            color: #4a9eff;
            font-size: 1.8rem;
        }
        .back {
            color: #4a9eff;
            text-decoration: none;
            font-weight: 600;
        }
        .back:hover {
            text-decoration: underline;
        }
        .tabs {
            display: flex;
            gap: 1rem;
            margin: 1rem 0 2rem;
            flex-wrap: wrap;
        }
        .tab-btn {
            padding: 0.6rem 1.2rem;
            background: #2a2a2a;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .tab-btn.active {
            background: #4a6cf7;
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

        /* Стиль для информации о пользователе */
        .user-info-list {
            max-width: 60%;
            margin: 0 auto;
            background: #2a2a2a;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.6rem 0;
            border-bottom: 1px solid #3a3a3a;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-row strong {
            color: #4a9eff;
            min-width: 180px;
        }
        .info-row span {
            text-align: right;
            flex: 1;
            padding-left: 1rem;
            word-break: break-word;
        }

        /* Стили для списков (вакансии, услуги, заявки) */
        ul {
            list-style: none;
            padding: 0;
            max-width: 60%;
            margin: 0 auto;
        }
        li {
            background: #2a2a2a;
            margin: 0.6rem 0;
            padding: 1rem;
            border-radius: 8px;
            line-height: 1.5;
        }
        li h3 {
            color: #4a9eff;
            margin-bottom: 0.4rem;
        }
        small {
            display: block;
            color: #aaa;
            font-size: 0.85rem;
            margin-top: 0.5rem;


}
        .empty {
            color: #888;
            text-align: center;
            padding: 1rem;
            max-width: 60%;
            margin: 1rem auto;
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
    <div class="user-info-list">
        <div class="info-row">
            <strong>📧 Email:</strong>
            <span><?= htmlspecialchars($user['email']) ?></span>
        </div>
        <div class="info-row">
            <strong>📱 Телефон:</strong>
            <span><?= htmlspecialchars($user['phone']) ?></span>
        </div>
        <div class="info-row">
            <strong>🎂 Дата рождения:</strong>
            <span><?= date('d.m.Y', strtotime($user['birth_date'])) ?></span>
        </div>
        <div class="info-row">
            <strong>🌍 Страна:</strong>
            <span><?= htmlspecialchars($user['country']) ?></span>
        </div>
        <div class="info-row">
            <strong>📍 Регион:</strong>
            <span><?= htmlspecialchars($user['region']) ?></span>
        </div>
        <div class="info-row">
            <strong>🏙️ Город:</strong>
            <span><?= htmlspecialchars($user['city']) ?></span>
        </div>
        <div class="info-row">
            <strong>👤 Отчество:</strong>
            <span><?= htmlspecialchars($user['patronymic']) ?></span>
        </div>
    </div>
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
        <p class="empty">Нет вакансий</p>
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
        <p class="empty">Нет услуг</p>
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
        <p class="empty">Нет заявок</p>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            button.classList.add('active');
            const tabId = button.dataset.tab;
            document.getElementById(tabId).classList.add('active');
        });
    });
</script>

</body>
</html>






