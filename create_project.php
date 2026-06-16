<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: adminstr.php');
    exit();
}

$connection = mysqli_connect('127.0.0.1', 'root', '', 'MySite');
if ($connection == false) {
    echo 'ERROR!<br>';
    exit();
}

function ensureUserDirectories($user_id, $project_id = null) {
    $base_upload_dir = 'uploads/users/' . $user_id . '/';
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
        return $project_dir;
    }
    return $projects_dir;
}

ensureUserDirectories($_SESSION['user_id']);

$error_message = '';

if (isset($_POST['create_project'])) {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);
    $user_id = $_SESSION['user_id'];
    
    $insert_query = "INSERT INTO projects (user_id, name, description) VALUES ('$user_id', '$name', '$description')";
    
    if (mysqli_query($connection, $insert_query)) {
        $project_id = mysqli_insert_id($connection);
        $project_dir = ensureUserDirectories($user_id, $project_id);
        
        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['audio_file'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['mp3', 'wav', 'ogg', 'm4a', 'aac'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                $filename = 'original_' . time() . '_' . $safe_name . '.' . $file_ext;
                $filepath = $project_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $filepath_escaped = mysqli_real_escape_string($connection, $filepath);
                    $file_name_escaped = mysqli_real_escape_string($connection, $file['name']);
                    mysqli_query($connection, "UPDATE projects SET original_file_path = '$filepath_escaped', file_name = '$file_name_escaped', file_size = '{$file['size']}' WHERE id = '$project_id'");
                    header('Location: cabinet.php?success=1');
                    exit();
                } else {
                    $error_message = "Ошибка загрузки файла";
                }
            } else {
                $error_message = "Неподдерживаемый формат. Разрешены: " . implode(', ', $allowed_ext);
            }
        } else {
            header('Location: cabinet.php?created=1');
            exit();
        }
    } else {
        $error_message = "Ошибка создания проекта: " . mysqli_error($connection);
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создать проект - AudioMix Pro</title>
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
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #4CAF50;
            font-size: 28px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #ddd;
        }
        input, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            box-sizing: border-box;
            background: rgba(0,0,0,0.3);
            color: white;
            font-size: 14px;
            transition: all 0.3s;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #4CAF50;
            background: rgba(0,0,0,0.5);
        }
        input[type="file"] {
            padding: 10px;
            background: rgba(0,0,0,0.3);
            border: 1px dashed rgba(255,255,255,0.3);
        }
        input[type="file"]::-webkit-file-upload-button {
            background: #4CAF50;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            margin-right: 15px;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.3);
            transform: translateX(-3px);
        }
        .error-message {
            background: #f44336;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .file-hint {
            font-size: 12px;
            color: #aaa;
            margin-top: 5px;
        }
        ::placeholder {
            color: rgba(255,255,255,0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="cabinet.php" class="btn-back">← Назад в личный кабинет</a>
        
        <div class="form-card">
            <h1>🎵 Создать новый проект</h1>
            
            <?php if ($error_message): ?>
                <div class="error-message">❌ <?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>📝 Название проекта</label>
                    <input type="text" name="name" placeholder="Например: Мой первый микс" required>
                </div>
                <div class="form-group">
                    <label>📄 Описание</label>
                    <textarea name="description" rows="4" placeholder="Краткое описание проекта..."></textarea>
                </div>
                <div class="form-group">
                    <label>🎵 Аудиофайл</label>
                    <input type="file" name="audio_file" accept="audio/*">
                    <div class="file-hint">Поддерживаемые форматы: MP3, WAV, OGG, M4A, AAC. Можно загрузить позже.</div>
                </div>
                <button type="submit" name="create_project" class="btn-submit">✨ Создать проект</button>
            </form>
        </div>
    </div>
</body>
</html>