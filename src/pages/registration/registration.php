<?php
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require '../../config/databases/db.php';

    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $patronymic = trim($_POST['patronymic'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $country = trim($_POST['country'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

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
                        session_start();
                        $_SESSION['user_id'] = $conn->insert_id;
                        header("Location: ../main/main.php");
                        exit();
                    } else {
                        $error = 'Ошибка при сохранении данных: ' . $stmt->error;
                    }
                }
            }
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Working - актуальная и современная платформа для работы, ведения бизнеса и поиска связей. Здесь каждый сможет найти ту работу, которая будет по душе именно тебе">
    <title>Регистрация</title>
    <!-- Подключаем ВНЕШНИЙ CSS, как в login.php -->
    <link rel="stylesheet" href="../../assets/styles/registration.css">
    <link rel="icon" href="../../../assets/images/icon126x126.png">
    <style>
        html, body {
        background-color: #00BCD5;
        margin: 0;
        padding: 0;
        min-height: 100%;
        font-family: Arial, sans-serif;
        }
        strong {
            color: white;
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
                <div class="form_group form_birth_date">
                    <label for="birth_date">Дата рождения *</label>
                    <input type="date" id="birth_date" name="birth_date" value="<?= htmlspecialchars($_POST['birth_date'] ?? '', ENT_QUOTES) ?>" required>
                </div>
                <div class="form_group form_country">
                    <label for="country">Страна *</label>
                    <select id="country" name="country" required>
                        <option value="">-- Выберите страну --</option>
                        <?php
                        $countries = [
                            "Австралия", "Австрия", "Азербайджан", "Албания", "Алжир", "Американское Самоа", "Ангола", "Ангилья", "Антарктида", "Антигуа и Барбуда",
                            "Аргентина", "Армения", "Аруба", "Афганистан", "Багамские острова", "Бангладеш", "Барбадос", "Бахрейн", "Беларусь", "Белиз",
                            "Бельгия", "Бенин", "Бермудские острова", "Болгария", "Боливия", "Босния и Герцеговина", "Ботсвана", "Бразилия", "Британская территория в Индийском океане",
                            "Бруней", "Буркина-Фасо", "Бурунди", "Бутан", "Вануату", "Ватикан", "Великобритания", "Венгрия", "Венесуэла", "Виргинские острова (Британские)",
                            "Виргинские острова (США)", "Вьетнам", "Габон", "Гаити", "Гайана", "Гамбия", "Гана", "Гваделупа", "Гватемала", "Гвинея", "Гвинея-Бисау",
                            "Германия", "Гибралтар", "Гондурас", "Гонконг", "Гренада", "Гренландия", "Греция", "Грузия", "Дания", "Джибути", "Доминика",
                            "Доминиканская Республика", "Египет", "Замбия", "Западная Сахара", "Зимбабве", "Израиль", "Индия", "Индонезия", "Иордания", "Ирак",
                            "Иран", "Ирландия", "Исландия", "Испания", "Италия", "Йемен", "Кабо-Верде", "Казахстан", "Камбоджа", "Камерун", "Канада",
                            "Катар", "Кения", "Кипр", "Киргизия", "Кирибати", "Китай", "Кокосовые острова", "Колумбия", "Коморы", "Конго", "Конго (ДРК)",
                            "Корея (КНДР)", "Корея (Республика)", "Коста-Рика", "Кот-д’Ивуар", "Куба", "Кувейт", "Кюрасао", "Лаос", "Латвия", "Лесото",
                            "Либерия", "Ливан", "Ливия", "Литва", "Лихтенштейн", "Люксембург", "Маврикий", "Мавритания", "Мадагаскар", "Майотта", "Макао",
                            "Малави", "Малайзия", "Мали", "Мальдивы", "Мальта", "Марокко", "Мартиника", "Маршалловы острова", "Мексика", "Микронезия",
                            "Мозамбик", "Молдова", "Монако", "Монголия", "Монтсеррат", "Мьянма", "Намибия", "Науру", "Непал", "Нигер", "Нигерия",
                            "Нидерланды", "Никарагуа", "Ниуэ", "Новая Зеландия", "Новая Каледония", "Норвегия", "Объединённые Арабские Эмираты", "Оман",
                            "Пакистан", "Палау", "Палестина", "Панама", "Папуа — Новая Гвинея", "Парагвай", "Перу", "Питкэрн", "Польша", "Португалия",
                            "Пуэрто-Рико", "Реюньон", "Россия", "Руанда", "Румыния", "Самоа", "Сан-Марино", "Сан-Томе и Принсипи", "Саудовская Аравия",
                            "Свазиленд", "Северная Македония", "Северные Марианские острова", "Сейшельские острова", "Сенегал", "Сент-Винсент и Гренадины",
                            "Сент-Китс и Невис", "Сент-Люсия", "Сент-Пьер и Микелон", "Сербия", "Сингапур", "Сирия", "Словакия", "Словения", "Соединённые Штаты Америки",
                            "Соломоновы острова", "Сомали", "Судан", "Суринам", "Сьерра-Леоне", "Таджикистан", "Таиланд", "Тайвань", "Танзания", "Тёркс и Кайкос",
                            "Того", "Токелау", "Тонга", "Тринидад и Тобаго", "Тувалу", "Тунис", "Туркменистан", "Турция", "Уганда", "Узбекистан", "Украина",
                            "Уоллис и Футуна", "Уругвай", "Фарерские острова", "Фиджи", "Филиппины", "Финляндия", "Фолклендские острова", "Франция",
                            "Французская Гвиана", "Французская Полинезия", "Французские Южные и Антарктические территории", "Хорватия", "Центральноафриканская Республика",
                            "Чад", "Черногория", "Чехия", "Чили", "Швейцария", "Швеция", "Шри-Ланка", "Эквадор", "Экваториальная Гвинея", "Эландские острова",
                            "Эль-Сальвадор", "Эритрея", "Эстония", "Эфиопия", "Южная Африка", "Южная Георгия и Южные Сандвичевы острова", "Южная Корея",
                            "Южный Судан", "Ямайка", "Япония"
                        ];
                        foreach ($countries as $country) {
                            $selected = (($_POST['country'] ?? '') === $country) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($country, ENT_QUOTES, 'UTF-8') . "\" $selected>$country</option>\n";
                        }
                        ?>
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
                <div class="form_group form_phone">
                    <label for="phone">Номер телефона *</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES) ?>" placeholder="+7 (000) 000-00-00" required>
                </div>

                <script>
                    document.getElementById('phone').addEventListener('input', function (e) {
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