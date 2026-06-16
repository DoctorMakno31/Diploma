<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AudioMix Pro - Система аудиомикширования</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            min-height: 100vh;
        }
        .header {
            background: rgba(0,0,0,0.3);
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
        }
        .nav-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #45a049;
        }
        .btn-logout {
            background: #f44336;
        }
        .btn-logout:hover {
            background: #da190b;
        }
        .user-greeting {
            color: #4CAF50;
            font-weight: bold;
            margin-right: 15px;
        }
        .hero {
            text-align: center;
            padding: 100px 20px;
        }
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 20px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding: 50px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .feature-card {
            flex: 1;
            min-width: 220px;
            max-width: 280px;
            background: rgba(255,255,255,0.1);
            padding: 30px 20px;
            border-radius: 10px;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.15);
        }
        .feature-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .feature-card h3 {
            margin-bottom: 10px;
            color: #4CAF50;
        }
        .feature-card p {
            font-size: 14px;
            opacity: 0.8;
            line-height: 1.4;
        }
        @media (max-width: 768px) {
            .hero h1 { font-size: 32px; }
            .hero p { font-size: 16px; }
            .features { gap: 20px; padding: 30px; }
            .feature-card { min-width: 180px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">🎵 AudioMix.Pro</div>
        <div class="nav-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-greeting">👋 Привет, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                <a href="cabinet.php" class="btn">📁 Мои проекты</a>
                <a href="logout.php" class="btn btn-logout">🚪 Выйти</a>
            <?php else: ?>
                <a href="adminstr.php" class="btn">Войти</a>
                <a href="registr.php" class="btn">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero">
        <h1>Профессиональная система аудиомикширования</h1>
        <p>Создавайте, редактируйте и микшируйте аудио проекты онлайн!</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="registr.php" class="btn" style="padding: 15px 30px; font-size: 18px;">Начать работу</a>
        <?php else: ?>
            <a href="cabinet.php" class="btn" style="padding: 15px 30px; font-size: 18px;">Перейти в мои проекты</a>
        <?php endif; ?>
    </div>

    <div class="features">
        <div class="feature-card">
            <div class="feature-icon">🎚️</div>
            <h3>Регулировка громкости</h3>
            <p>Точный контроль уровня громкости для каждого трека</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🎛️</div>
            <h3>Трёхполосный эквалайзер</h3>
            <p>Настройка низких, средних и высоких частот в реальном времени</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">✂️</div>
            <h3>Обрезка аудио</h3>
            <p>Вырезание нужного фрагмента, визуальный выбор начала и конца трека</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Визуализация волны</h3>
            <p>Интерактивная диаграмма для точной навигации по треку</p>
        </div>
    </div>
</body>
</html>