<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: adminstr.php');
    exit();
}

require_once __DIR__ . '/config.php';
$connection = getDBConnection();
$user_id = $_SESSION['user_id'];

$user_result = mysqli_query($connection, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_result);

$projects_result = mysqli_query($connection, "SELECT * FROM projects WHERE user_id = '$user_id' ORDER BY created_at DESC");

function ensureUserDirectories($user_id, $project_id = null) {
    $base_upload_dir = getUploadsPath() . 'users/' . $user_id . '/';
    if (!file_exists($base_upload_dir)) {
        mkdir($base_upload_dir, 0777, true);
    }
    $projects_dir = $base_upload_dir . 'projects/';
    if (!file_exists($projects_dir)) {
        mkdir($projects_dir, 0777, true);
    }
    if ($project_id) {
        $project_dir = $projects_dir . $project_id . '/';
        if (!file_exists($project_dir)) {
            mkdir($project_dir, 0777, true);
        }
        return $project_dir; // абсолютный путь для PHP
    }
    return $projects_dir;
}

ensureUserDirectories($user_id);

$error_message = '';
$success_message = '';

if (isset($_POST['upload_audio']) && isset($_FILES['audio_file'])) {
    $project_id = intval($_POST['project_id']);
    $check_project = mysqli_query($connection, "SELECT * FROM projects WHERE id = '$project_id' AND user_id = '$user_id'");
    
    if (mysqli_num_rows($check_project) > 0) {
        $project_dir_abs = ensureUserDirectories($user_id, $project_id);
        $relative_base = 'uploads/users/' . $user_id . '/projects/' . $project_id . '/';
        $file = $_FILES['audio_file'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['mp3', 'wav', 'ogg', 'm4a', 'aac'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                $filename = 'original_' . time() . '_' . $safe_name . '.' . $file_ext;
                $filepath_abs = $project_dir_abs . $filename;
                $filepath_rel = $relative_base . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath_abs)) {
                    $filepath_escaped = mysqli_real_escape_string($connection, $filepath_rel);
                    $file_name_escaped = mysqli_real_escape_string($connection, $file['name']);
                    mysqli_query($connection, "UPDATE projects SET original_file_path = '$filepath_escaped', file_name = '$file_name_escaped', file_size = '{$file['size']}' WHERE id = '$project_id'");
                    header('Location: cabinet.php?success=1');
                    exit();
                } else {
                    $error_message = "Ошибка перемещения файла";
                }
            } else {
                $error_message = "Неподдерживаемый формат. Разрешены: " . implode(', ', $allowed_ext);
            }
        } else {
            $error_message = "Ошибка загрузки файла";
        }
    } else {
        $error_message = "Проект не найден";
    }
}

if (isset($_GET['success'])) {
    $success_message = 'Файл успешно загружен!';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои проекты - AudioMix Pro</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            min-height: 100vh;
        }
        .header {
            background: rgba(0,0,0,0.3);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .logo-area h1 { font-size: 24px; color: #4CAF50; }
        .nav-area { display: flex; gap: 15px; flex-wrap: wrap; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover { background: #45a049; transform: translateY(-2px); }
        .btn-danger { background: #f44336; }
        .btn-danger:hover { background: #da190b; }
        .btn-outline { background: transparent; border: 1px solid #4CAF50; }
        .btn-outline:hover { background: #4CAF50; }
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .project-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            background: rgba(255,255,255,0.15);
        }
        .project-card h3 { color: #4CAF50; margin-bottom: 10px; font-size: 20px; }
        .project-card p { color: #ddd; line-height: 1.4; margin-bottom: 10px; }
        .file-info {
            background: rgba(0,0,0,0.3);
            padding: 12px;
            border-radius: 10px;
            margin: 15px 0;
            font-size: 13px;
            color: #ccc;
        }
        .upload-form {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }
        .upload-form input[type="file"] {
            margin: 10px 0;
            color: white;
            font-size: 13px;
        }
        .upload-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #4CAF50;
        }
        .upload-form small {
            display: block;
            margin-top: 5px;
            color: #aaa;
            font-size: 12px;
        }
        .project-actions { margin-top: 15px; display: flex; flex-wrap: wrap; gap: 8px; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-edit { background: #2196F3; }
        .btn-download { background: #FF9800; }
        .success-message, .error-message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message { background: #4CAF50; }
        .error-message { background: #f44336; }
        .greeting {
            background: rgba(0,0,0,0.2);
            padding: 10px 15px;
            border-radius: 10px;
            display: inline-block;
        }
        @media (max-width: 768px) {
            .header { flex-direction: column; text-align: center; }
            .projects-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1400px; margin: 0 auto; padding: 20px;">
        <div class="header">
            <div class="logo-area">
                <h1>🎵 Мои проекты аудиомикширования</h1>
                <div class="greeting">👋 Добро пожаловать, <strong><?php echo htmlspecialchars($user['login']); ?></strong>!</div>
            </div>
            <div class="nav-area">
                <a href="create_project.php" class="btn">+ Создать новый проект</a>
                <a href="index.php" class="btn btn-outline">🏠 На главную</a>
                <a href="logout.php" class="btn btn-danger">🚪 Выйти</a>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="success-message">✅ <?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="error-message">❌ <?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="projects-grid">
            <?php while ($project = mysqli_fetch_assoc($projects_result)): ?>
            <div class="project-card">
                <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                <p><?php echo htmlspecialchars($project['description']); ?></p>
                
                <?php if (isset($project['original_file_path']) && $project['original_file_path'] && file_exists(getUploadsPath() . $project['original_file_path'])): ?>
                    <div class="file-info">
                        📁 <strong><?php echo htmlspecialchars($project['file_name']); ?></strong><br>
                        📏 Размер: <?php echo round($project['file_size'] / 1024 / 1024, 2); ?> MB<br>
                        🕒 Загружен: <?php echo date('d.m.Y H:i', strtotime($project['created_at'])); ?>
                    </div>
                    <div class="project-actions">
                        <a href="edit_audio.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-edit">✏️ Редактировать</a>
                        <a href="download.php?type=original&id=<?php echo $project['id']; ?>" class="btn btn-sm btn-download">📥 Скачать оригинал</a>
                        <?php if (isset($project['edited_file_path']) && $project['edited_file_path'] && file_exists(getUploadsPath() . $project['edited_file_path'])): ?>
                            <a href="download.php?type=edited&id=<?php echo $project['id']; ?>" class="btn btn-sm btn-download">💾 Скачать edited</a>
                        <?php endif; ?>
                        <a href="delete_project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить проект?')">🗑️ Удалить</a>
                    </div>
                <?php else: ?>
                    <div class="upload-form">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                            <label>📤 Загрузите аудиофайл:</label>
                            <input type="file" name="audio_file" accept="audio/*" required>
                            <button type="submit" name="upload_audio" class="btn btn-sm">📤 Загрузить</button>
                            <small>Поддерживаемые форматы: MP3, WAV, OGG, M4A, AAC</small>
                        </form>
                    </div>
                    <div class="project-actions">
                        <a href="delete_project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить проект?')">🗑️ Удалить проект</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>