<?php
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require '../../config/databases/db.php'; // Убедитесь, что путь верный!

    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $patronymic = trim($_POST['patronymic'] ?? '');
    $birth_date = $_POST['age'] ?? ''; // Это дата рождения, не возраст
    $country = trim($_POST['country'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $phone = trim($_POST['number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Валидация
    if (empty($name) || empty($surname) || empty($patronymic) || empty($birth_date) ||
        empty($country) || empty($region) || empty($city) || empty($phone) ||
        empty($email) || empty($password) || empty($password_confirm)) {
        $error = 'Заполните все поля';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Неверный email';
    } elseif (strlen($password) < 8) {
        $error = 'Пароль должен быть не короче 8 символов';
    } else {
        // Проверка email на уникальность
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$check) {
            $error = 'Ошибка подготовки запроса: ' . $conn->error;
        } else {
            $check->bind_param("s", $email);
            $check->execute();
            $result = $check->get_result();
            if ($result->num_rows > 0) {
                $error = 'Email уже используется';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    INSERT INTO users (name, surname, patronymic, birth_date, country, region, city, phone, email, password)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if (!$stmt) {
                    $error = 'Ошибка подготовки INSERT: ' . $conn->error;
                } else {
                    $stmt->bind_param("ssssssssss", 
                        $name, $surname, $patronymic, $birth_date, 
                        $country, $region, $city, $phone, $email, $hash
                    );

                    if ($stmt->execute()) {
                        // Успешная регистрация — можно сразу авторизовать
                        session_start();
                        // Получаем ID нового пользователя
                        $user_id = $conn->insert_id;
                        $_SESSION['user_id'] = $user_id;
                        
                        header("Location: ../main/main.php");
                        exit();
                    } else {
                        $error = 'Ошибка при сохранении данных: ' . $stmt->error;
                    }
                }
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Working - актуальная и современная платформа для работы, ведения бизнеса и поиска связей. Здесь каждый сможет найти ту работу, которая будет по душе именно тебе">
    <title>Регистрация</title>
    <link rel="stylesheet" href="../../assets/styles/registration.css">
    <link rel="icon" href="../../../assets/images/icon126x126.png">
    <style>
        html, body {
            background-color: #00BCD5;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .main_div_form {
            box-shadow: none;
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
        }
        .form_group {
            margin-bottom: 1rem;
        }
        .form_group label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: bold;
            color: #333;
        }
        .form_group input, .form_group select {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }
        button {
            background: #1e40af;
            color: white;
            padding: 0.8rem;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #2563eb;
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #1e40af;
        }
        .login-link a {
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<main>
    <div class="main_div">
        <div class="main_div_form">

            <?php if ($error): ?>
                <p style="color:red; text-align:center; margin-bottom: 1rem;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="" method="post">

                <div class="form_group form_name">
                    <label for="name">Имя *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form_group form_surname">
                    <label for="surname">Фамилия *</label>
                    <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($_POST['surname'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form_group form_patronymic">
                    <label for="patronymic">Отчество *</label>
                    <input type="text" id="patronymic" name="patronymic" value="<?= htmlspecialchars($_POST['patronymic'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form_group form_age">
                    <label for="age">Дата рождения *</label>
                    <input type="date" id="age" name="age" value="<?= htmlspecialchars($_POST['age'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form_group form_country">
                    <label for="country">Страна *</label>
                    <select id="country" name="country" required>
                        <option value="">-- Выберите страну --</option>
                        <option value="Россия" <?= (($_POST['country'] ?? '') === 'Россия') ? 'selected' : '' ?>>Россия</option>
                        <option value="Украина" <?= (($_POST['country'] ?? '') === 'Украина') ? 'selected' : '' ?>>Украина</option>
                        <option value="Казахстан" <?= (($_POST['country'] ?? '') === 'Казахстан') ? 'selected' : '' ?>>Казахстан</option>
                        <!-- Остальные страны можно оставить или сократить для примера -->
                        <option value="США">США</option>
                        <option value="Германия">Германия</option>
                        <!-- ...остальные опции... -->
                    </select>
                </div>
                <div class="form_group form_region">
                    <label for="region">Регион *</label>
                    <input type="text" id="region" name="region" value="<?= htmlspecialchars($_POST['region'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form_group form_city">
                    <label for="city">Город *</label>
                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($_POST['city'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form_group form_number">
                    <label for="number">Номер телефона *</label>
                    <input type="tel" id="number" name="number" value="<?= htmlspecialchars($_POST['number'] ?? '', ENT_QUOTES) ?>" placeholder="+7 (000) 000-00-00" required>
                </div>

                <script>
                    document.getElementById('number').addEventListener('input', function (e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 0 && !['7', '8'].includes(value[0])) {
                            value = '7' + value;
                        }
                        if (value.startsWith('8')) {
                            value = '7' + value.slice(1);
                        }
                        value = value.slice(0, 11);
                        let formatted = '';
                        if (value.length > 0) formatted = '+7';
                        if (value.length > 1) formatted += ' (' + value.slice(1, 4);
                        if (value.length >= 4) formatted += ') ' + value.slice(4, 7);
                        if (value.length >= 7) formatted += '-' + value.slice(7, 9);
                        if (value.length >= 9) formatted += '-' + value.slice(9, 11);
                        e.target.value = formatted;
                    });
                </script>

                <div class="form_group form_email">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form_group form_password">
                    <label for="password">Пароль *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form_group form_password_confirm">
                    <label for="password_confirm">Подтвердите пароль *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>

                <br>
                <div class="form_group from_button">
                    <button type="submit">Зарегистрироваться</button>
                </div>
                <div class="form_group" style="margin-top: 2rem; text-align: center;">
                    <p class="login-link">
                        Уже есть аккаунт? <a href="../login/login.php"><strong>Войти</strong></a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</main>
</body>
</html>