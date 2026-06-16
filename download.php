<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: adminstr.php');
    exit();
}

require_once __DIR__ . '/config.php';
$connection = getDBConnection();

$project_id = intval($_GET['id']);
$type = $_GET['type'];
$user_id = $_SESSION['user_id'];

$result = mysqli_query($connection, "SELECT * FROM projects WHERE id = '$project_id' AND user_id = '$user_id'");
$project = mysqli_fetch_assoc($result);

if ($project) {
    if ($type == 'original' && !empty($project['original_file_path'])) {
        $file_path_rel = $project['original_file_path'];
        $file_ext = pathinfo($file_path_rel, PATHINFO_EXTENSION);
        $file_name = 'original_' . $project['name'] . '.' . $file_ext;
    } elseif ($type == 'edited' && !empty($project['edited_file_path'])) {
        $file_path_rel = $project['edited_file_path'];
        $file_name = 'edited_' . $project['name'] . '.wav';
    } else {
        die('Файл не найден');
    }
    
    $file_path_abs = getUploadsPath() . $file_path_rel;
    
    if (file_exists($file_path_abs)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path_abs);
        finfo_close($finfo);
        
        header('Content-Type: ' . $mime_type);
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . filesize($file_path_abs));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        readfile($file_path_abs);
        exit();
    } else {
        die('Физический файл не найден на сервере: ' . htmlspecialchars($file_path_abs));
    }
} else {
    die('Проект не найден или не принадлежит вам');
}
?>