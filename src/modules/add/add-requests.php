<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../pages/login/login.php');
    exit;
}

// Получаем имя пользователя
try {
    $pdo = new PDO("mysql:host=localhost;dbname=working;charset=utf8", 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT name, surname FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $creator_name = $user ? ($user['name'] . ' ' . $user['surname']) : 'Аноним';
} catch (PDOException $e) {
    $creator_name = 'Аноним';
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $contacts = trim($_POST['contacts'] ?? '');

    if (empty($title)) {
        $error = "Название заявки обязательно.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO requests (user_id, title, description, requirements, price, location, contacts, creator_name)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $title,
                $description,
                $requirements,
                $price,
                $location,
                $contacts,
                $creator_name
            ]);
            header('Location: ../../pages/main/main.php');
            exit;
        } catch (PDOException $e) {
            $error = "Ошибка сохранения: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление заявки</title>
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #0f0f0f;
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2rem;
            overflow-y: auto;
        }
        .form-box {
            background: #1a1a1a;
            padding: 2.5rem;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .form-box h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #4a9eff;
        }
        .form-box input[type="text"],
        .form-box textarea {
            width: 100%;
            padding: 0.9rem;
            margin: 0.6rem 0;
            border: none;
            border-radius: 10px;
            background: #2a2a2a;
            color: white;
            font-size: 1rem;
        }
        .form-box textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-box button {
            width: 100%;
            padding: 0.9rem;
            margin: 1rem 0 0.5rem;
            border: none;
            border-radius: 10px;
            background: #9d174d;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .form-box button:hover {
            background: #ec4899;
        }
        .form-box a {
            display: block;
            text-align: center;
            color: #959595;
            text-decoration: none;
            margin-top: 0.5rem;
        }
        .form-box a:hover {
            color: #4a9eff;
        }
        .error {
            color: #ff6b6b;
            text-align: center;


margin-bottom: 1rem;
        }
        label {
            display: block;
            margin: 0.4rem 0 0.2rem;
            font-size: 0.95rem;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>➕ Новая заявка</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="title">Название заявки *</label>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES) ?>" required>

            <label for="description">Описание</label>
            <textarea name="description" id="description" placeholder="Что именно вы ищете?"><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES) ?></textarea>

            <label for="requirements">Требования</label>
            <textarea name="requirements" id="requirements" placeholder="Навыки, опыт, оборудование и т.д."><?= htmlspecialchars($_POST['requirements'] ?? '', ENT_QUOTES) ?></textarea>

            <label for="price">Бюджет / Вознаграждение</label>
            <input type="text" name="price" id="price" placeholder="Например: до 10 000 ₽, по договорённости" value="<?= htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES) ?>">

            <label for="location">Местоположение</label>
            <input type="text" name="location" id="location" placeholder="Город или удалённо" value="<?= htmlspecialchars($_POST['location'] ?? '', ENT_QUOTES) ?>">

            <label for="contacts">Ваши контакты</label>
            <input type="text" name="contacts" id="contacts" placeholder="Телефон, email, Telegram" value="<?= htmlspecialchars($_POST['contacts'] ?? '', ENT_QUOTES) ?>">

            <button type="submit">Создать заявку</button>
            <a href="../../pages/main/main.php">← Отмена</a>
        </form>
    </div>
</body>
</html>