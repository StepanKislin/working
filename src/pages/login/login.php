<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require '../../config/databases/db.php';

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        // Запрашиваем password_hash, а не password!
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Проверяем пароль по полю password_hash
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: ../main/main.php"); // ← сразу на главную
                exit();
            } else {
                $error = 'Неверный email или пароль';
            }
        } else {
            $error = 'Неверный email или пароль';
        }

        $conn->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Working - актуальная и современная платформа для работы, ведения бизнеса и поиска связей. Здесь каждый сможет найти ту работу, которая будет по душе именно тебе">
    <title>Вход</title>
    <link rel="stylesheet" href="../../assets/styles/login.css">
    <link rel="icon" href="../../../assets/images/icon126x126.png">
</head>
<body>
    <main>
        <div class="main_div">

            <div class="main_div_form">
                <?php if ($error): ?>
                    <p style="color:red; text-align:center"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <form action="" method="post">
                    <div class="form_group form_email">
                        <label for="email">email</label>
                        <input type="email" id="email" name="email" class="email" placeholder="working@working.ru" required>
                    </div>
                    <div class="form_group form_password">
                        <label for="email">Пароль</label>
                        <input type="password" id="password" name="password" class="password" placeholder="•••••••••••" required>
                    </div>
                    <br>
                    <div class="form_group from_button">
                        <button type="submit">Войти</button>
                    </div>
                    <div class="form_group" style="margin-top: 3rem;">
                        <p class="login-link">
                            Нет аккаунта? <a href="../registration/registration.php" class="login-link"><strong>Зарегестрироваться</strong></a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>