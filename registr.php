<?php
session_start();
require_once __DIR__ . '/config.php';
$connection = getDBConnection();

$error_message = '';

if (isset($_POST['registration'])) {
    if (!empty($_POST['login']) && !empty($_POST['password'])) {
        $login = mysqli_real_escape_string($connection, $_POST['login']);
        $password = mysqli_real_escape_string($connection, $_POST['password']);
        
        // ВНИМАНИЕ: исправлено users (строчные) вместо Users
        $check_user = mysqli_query($connection, "SELECT * FROM `users` WHERE `login` = '$login'");
        if (mysqli_num_rows($check_user) > 0) {
            $error_message = 'Такой пользователь уже существует! <a href="registr.php">Назад</a>';
        } else {
            mysqli_query($connection, "INSERT INTO `users`(`login`, `password`) VALUES ('$login', '$password')");
            header('Location: goodjob.php');
            exit();
        }
    } else {
        $error_message = 'Заполните все поля!';
    }
}

if (!isset($_SESSION['user_id'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <style>
        body {
            font-family: Arial;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 300px;
            position: relative;
        }
        .error-message {
            color: white;
            background: #ff6b6b;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        .error-message a {
            color: white;
            text-decoration: underline;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #1877f2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background: #166fe5;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        <form action="registr.php" method="POST">
            <input type="text" name="login" placeholder="Your login">
            <input type="password" name="password" placeholder="Your password">  
            <input type="submit" name="registration" value="Зарегистрироваться">
        </form>
    </div>
</body>
</html>
<?php
} else {
    echo "Вы уже авторизованы!";
}
?>