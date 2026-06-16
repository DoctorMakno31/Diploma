<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: adminstr.php');
    exit();
}

$connection = mysqli_connect('127.0.0.1', 'root', '', 'MySite');
if (!$connection) {
    die('Ошибка подключения к БД');
}

$project_id = intval($_GET['id']);
$type = $_GET['type']; // original or edited
$user_id = $_SESSION['user_id'];

$result = mysqli_query($connection, "SELECT * FROM projects WHERE id = '$project_id' AND user_id = '$user_id'");
$project = mysqli_fetch_assoc($result);

if ($project) {
    if ($type == 'original' && !empty($project['original_file_path'])) {
        $file_path = $project['original_file_path'];
        // Определяем расширение файла
        $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
        $file_name = 'original_' . $project['name'] . '.' . $file_ext;
    } elseif ($type == 'edited' && !empty($project['edited_file_path'])) {
        $file_path = $project['edited_file_path'];
        $file_name = 'edited_' . $project['name'] . '.wav';
    } else {
        die('Файл не найден');
    }
    
    if (file_exists($file_path)) {
        // Определяем MIME тип файла
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);
        
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Читаем файл и отправляем пользователю
        readfile($file_path);
        exit();
    } else {
        die('Физический файл не найден на сервере: ' . htmlspecialchars($file_path));
    }
} else {
    die('Проект не найден или не принадлежит вам');
}
?>