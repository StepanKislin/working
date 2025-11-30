<?php
session_start();

// Подключение к базе данных Working
try {
    $pdo = new PDO("mysql:host=localhost;dbname=Working;charset=utf8mb4", 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Ошибка подключения к базе данных.");
}

// Обработка AJAX-запроса от ИИ-ассистента
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    header('Content-Type: application/json; charset=utf8');
    $user_question = trim($_POST['question']);

    // Валидация: минимум 3 символа и хотя бы одна буква
    if (mb_strlen($user_question, 'UTF-8') < 3 || !preg_match('/[а-яёa-z]/iu', $user_question)) {
        echo json_encode([
            'error' => true,
            'answer' => 'Пожалуйста, задайте осмысленный вопрос (минимум 3 символа с буквами). Например: «Как добавить вакансию?»'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Получаем все шаблоны из таблицы help_qa
    $stmt = $pdo->query("SELECT question_patterns, answer FROM help_qa");
    $qa_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $best_answer = null;
    $max_matches = 0;
    $clean_input = mb_strtolower($user_question, 'UTF-8');

    foreach ($qa_list as $row) {
        $patterns = json_decode($row['question_patterns'], true);
        if (!$patterns || !is_array($patterns)) continue;

        $matches = 0;
        foreach ($patterns as $pattern) {
            $pattern_clean = mb_strtolower(trim($pattern), 'UTF-8');
            if ($pattern_clean !== '' && mb_strpos($clean_input, $pattern_clean) !== false) {
                $matches++;
            }
        }
        if ($matches > $max_matches) {
            $max_matches = $matches;
            $best_answer = $row['answer'];
        }
    }

    if ($best_answer && $max_matches > 0) {
        echo json_encode(['error' => false, 'answer' => $best_answer], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'error' => true,
            'answer' => "К сожалению, я не нашёл точного ответа. Попробуйте:\n— Как зарегистрироваться?\n— Как добавить вакансию?\n— Как пройти карьерный тест?\n— Можно ли работать удалённо?\n— Как пожаловаться на спам?"
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Загрузка данных пользователя для меню
$is_logged_in = isset($_SESSION['user_id']);
$user_name = 'Гость';
if ($is_logged_in && !empty($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT name, surname FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $user_name = htmlspecialchars($user['name'] . ' ' . $user['surname'], ENT_QUOTES, 'UTF-8');
        }
    } catch (Exception $e) {
        // Оставляем "Гость"
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Working — Помощь</title>
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
        .content-area {
            padding: 2rem;
            min-height: calc(100vh - 120px);
            max-width: 800px;
            margin: 0 auto;
        }
        .help-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .help-header h1 {
            font-size: 2.2rem;
            color: #4a9eff;
            margin-bottom: 0.5rem;
        }
        .help-header p {
            color: #aaa;
            font-size: 1.1rem;
        }
        .ai-chat {
            background: #1a1a1a;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            position: relative;
        }
        .ai-message {
            background: #2a2a2a;
            padding: 1.2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            opacity: 0;
            animation: fadeIn 0.4s forwards;
        }
        .ai-message strong {
            color: #4a9eff;
            display: block;
            margin-bottom: 0.4rem;
        }
        .thinking {
            background: #2a2a2a;
            padding: 1.2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            color: #aaa;
        }
        .thinking span {
            display: inline-block;
            animation: blink 1.2s infinite;
        }
        .thinking span:nth-child(2) { animation-delay: 0.2s; }
        .thinking span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes blink {
            0%, 60%, 100% { opacity: 0.2; }
            30% { opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .question-input {
            display: flex;
            gap: 0.8rem;
        }
        .question-input input {
            flex: 1;
            padding: 0.8rem 1.2rem;
            border-radius: 30px;
            border: 1px solid #444;
            background: #2a2a2a;
            color: white;
            font-size: 1rem;
        }
        .question-input button {
            padding: 0.8rem 1.5rem;
            border-radius: 30px;
            background: #4a6cf7;
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .question-input button:hover {
            background: #5a7cff;
        }
        .examples {
            margin-top: 1.5rem;
            color: #aaa;
            font-size: 0.95rem;
        }
        .examples p {
            margin-bottom: 0.4rem;
        }
        .examples span {
            color: #4a9eff;
            cursor: pointer;
            text-decoration: underline;
        }
        .examples span:hover {
            color: #6aa9ff;
        }
    </style>
</head>
<body>

<header>
    <a href="main.php" class="logo">Working</a>
    <div class="search-box">
        <input type="text" placeholder="Поиск по сайту...">
    </div>
    <?php if ($is_logged_in): ?>
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

<div class="content-area">
    <div class="help-header">
        <h1>Помощь от ИИ-ассистента</h1>
        <p>Задайте любой вопрос — я подскажу, как пользоваться сайтом Working</p>
    </div>

    <div class="ai-chat" id="ai-chat">
        <div class="ai-message" style="animation:none;opacity:1">
            <strong>Привет! 👋 Я — виртуальный помощник Working.</strong>
            Могу помочь с регистрацией, публикацией вакансий, услуг, заявок, профориентацией и многим другим.
        </div>

        <div class="question-input">
            <input type="text" id="user-question" placeholder="Например: Как добавить вакансию?" onkeypress="if(event.key==='Enter') askAI()">
            <button onclick="askAI()">Отправить</button>
        </div>

        <div class="examples">
            <p>Попробуйте спросить:</p>
            <p>— <span onclick="fillQuestion('Как зарегистрироваться?')">Как зарегистрироваться?</span></p>
            <p>— <span onclick="fillQuestion('Как добавить вакансию?')">Как добавить вакансию?</span></p>
            <p>— <span onclick="fillQuestion('Как пройти карьерный тест?')">Как пройти карьерный тест?</span></p>
            <p>— <span onclick="fillQuestion('Можно ли работать удалённо?')">Можно ли работать удалённо?</span></p>
            <p>— <span onclick="fillQuestion('Как пожаловаться на спам?')">Как пожаловаться на спам?</span></p>
        </div>
    </div>
</div>

<script>
function askAI() {
    const input = document.getElementById('user-question');
    const question = input.value.trim();
    if (!question) return;

    const aiChat = document.getElementById('ai-chat');

    // Показать вопрос пользователя
    const userMsg = document.createElement('div');
    userMsg.className = 'ai-message';
    userMsg.innerHTML = `<strong>Вы:</strong> ${question}`;
    aiChat.insertBefore(userMsg, aiChat.querySelector('.question-input'));

    // Анимация "думает..."
    const thinking = document.createElement('div');
    thinking.className = 'thinking';
    thinking.innerHTML = '<span>.</span><span>.</span><span>.</span>';
    aiChat.insertBefore(thinking, aiChat.querySelector('.question-input'));

    // Отправка AJAX-запроса
    fetch('help.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'question=' + encodeURIComponent(question)
    })
    .then(res => res.json())
    .then(data => {
        thinking.remove();
        const aiMsg = document.createElement('div');
        aiMsg.className = 'ai-message';
        aiMsg.innerHTML = '<strong>ИИ-ассистент:</strong> <span id="typing-text"></span>';
        aiChat.insertBefore(aiMsg, aiChat.querySelector('.question-input'));

        // Анимация "печатания"
        const span = aiMsg.querySelector('#typing-text');
        let i = 0;
        const type = () => {
            if (i < data.answer.length) {
                span.textContent += data.answer.charAt(i++);
                setTimeout(type, 12);
            }
        };
        type();
    })
    .catch(() => {
        thinking.remove();
        const aiMsg = document.createElement('div');
        aiMsg.className = 'ai-message';
        aiMsg.innerHTML = '<strong>ИИ-ассистент:</strong> Произошла ошибка. Попробуйте обновить страницу.';
        aiChat.insertBefore(aiMsg, aiChat.querySelector('.question-input'));
    });

    input.value = '';
}

function fillQuestion(text) {
    document.getElementById('user-question').value = text;
}

// Выпадающее меню пользователя
document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('user-dropdown');
    const btn = document.querySelector('.profile-btn');
    if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});

document.querySelector('.profile-btn')?.addEventListener('click', (e) => {
    e.stopPropagation();
    document.getElementById('user-dropdown')?.classList.toggle('show');
});
</script>

</body>
</html>