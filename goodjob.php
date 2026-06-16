<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Успешная регистрация</title>
    <style>
        body {
            font-family: Arial;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { 
            color: green; 
            font-size: 24px; 
            margin-bottom: 20px; 
        }
        .btn { 
            display: inline-block; 
            padding: 10px 20px; 
            background: #007bff; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            margin: 5px;
        }
        .btn-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success">✅ Регистрация прошла успешно!</div>
        <p>Пользователь успешно зарегистрирован в системе</p>
        <div class="btn-container">
            <a href="index.php" class="btn">На главную страницу</a>
            <a href="cabinet.php" class="btn">В личный кабинет</a>
        </div>
    </div>
</body>
</html>