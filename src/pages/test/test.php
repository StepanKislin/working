<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// Подключение к БД
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

$result_direction = null;

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['answer'])) {
    $answers = $_POST['answer']; // [question_id => option_id]

    // Собираем все direction_hint из выбранных вариантов
    $directions = [];
    foreach ($answers as $question_id => $option_id) {
        $stmt = $pdo->prepare("SELECT direction_hint FROM career_test_options WHERE id = ? AND question_id = ?");
        $stmt->execute([(int)$option_id, (int)$question_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $directions[] = $row['direction_hint'];
        }
    }

    if (!empty($directions)) {
        // Находим направление, которое встречается чаще всего
        $counts = array_count_values($directions);
        arsort($counts);
        $result_direction = key($counts);

        // Сохраняем результат
        $stmt = $pdo->prepare("INSERT INTO career_test_results (user_id, result_direction) VALUES (?, ?)");
        $stmt->execute([$user_id, $result_direction]);

        // Опционально: обновляем направление в профиле
        $stmt = $pdo->prepare("UPDATE users SET direction = ? WHERE id = ?");
        $stmt->execute([$result_direction, $user_id]);
    }
}

// Загрузка вопросов и вариантов (если тест ещё не пройден)
$questions = [];
if (!$result_direction) {
    $stmt = $pdo->query("SELECT * FROM career_test_questions ORDER BY order_num, id");
    $questions_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($questions_raw as $q) {
        $stmt = $pdo->prepare("SELECT * FROM career_test_options WHERE question_id = ? ORDER BY id");
        $stmt->execute([$q['id']]);
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $questions[] = ['id' => $q['id'], 'question_text' => $q['question_text'], 'options' => $options];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест на профориентацию</title>
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
            padding: 2rem;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            color: #4a9eff;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        .question {
            background: #1e1e1e;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 12px;
            border: 1px solid #333;
        }
        .question h3 {
            color: #6ab0ff;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        .option {
            display: block;
            padding: 0.8rem;
            margin: 0.5rem 0;
            background: #2a2a2a;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .option:hover {
            background: #333;
        }
        .option input {
            margin-right: 0.8rem;
            accent-color: #4a6cf7;
        }
        .btn {
            background: #4a6cf7;
            color: white;
            border: none;
            padding: 0.8rem 1.8rem;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 1rem;
        }
        .result-box {
            background: #1e1e1e;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #8a6bff;
            margin-top: 2rem;
        }
        .result-box h2 {
            color: #8a6bff;
            margin-bottom: 1rem;
        }
        .result-box p {
            font-size: 1.3rem;
            color: #6ab0ff;
            margin: 1rem 0;
        }
        a.back-link {
            color: #4a9eff;
            text-decoration: none;
            display: inline-block;
            margin-top: 1.5rem;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($result_direction): ?>
            <div class="result-box">
                <h2>🎯 Ваше профессиональное направление:</h2>
                <p><?= htmlspecialchars($result_direction) ?></p>
                <p>Направление автоматически сохранено в вашем профиле!</p>
                <a href="../profile/profile.php" class="btn" style="background:#8a6bff; text-decoration:none; display: inline-block;">Перейти в профиль</a>
                <br><br>
                <a href="test.php" class="back-link">Пройти тест ещё раз</a>
            </div>
        <?php else: ?>
            <h1>Тест на профориентацию</h1>
            <p>Ответьте на все вопросы, чтобы определить подходящую сферу деятельности.</p>

            <form method="POST">
                <?php if (empty($questions)): ?>
                    <p style="color: #ff6b6b;">Тест пока не настроен. Обратитесь к администратору.</p>
                <?php else: ?>
                    <?php foreach ($questions as $q): ?>
                        <div class="question">
                            <h3><?= htmlspecialchars($q['question_text']) ?></h3>
                            <?php foreach ($q['options'] as $opt): ?>
                                <label class="option">
                                    <input type="radio" name="answer[<?= (int)$q['id'] ?>]" value="<?= (int)$opt['id'] ?>" required>
                                    <?= htmlspecialchars($opt['option_text']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn">Получить рекомендацию</button>
                <?php endif; ?>
            </form>

            <a href="../profile/profile.php" class="back-link">← Вернуться в профиль</a>
        <?php endif; ?>
    </div>
</body>
</html>