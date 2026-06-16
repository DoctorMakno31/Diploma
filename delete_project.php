<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: adminstr.php');
    exit();
}

require_once __DIR__ . '/config.php';
$connection = getDBConnection();

$project_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$check = mysqli_query($connection, "SELECT * FROM projects WHERE id = '$project_id' AND user_id = '$user_id'");
if (mysqli_num_rows($check) == 0) {
    header('Location: cabinet.php?error=not_found');
    exit();
}

$project = mysqli_fetch_assoc($check);

// Удаляем файлы по абсолютным путям
if (!empty($project['original_file_path'])) {
    $abs_path = getUploadsPath() . $project['original_file_path'];
    if (file_exists($abs_path)) unlink($abs_path);
}
if (!empty($project['edited_file_path'])) {
    $abs_path = getUploadsPath() . $project['edited_file_path'];
    if (file_exists($abs_path)) unlink($abs_path);
}

// Удаляем папку проекта (если пуста)
if (!empty($project['original_file_path'])) {
    $dir = dirname(getUploadsPath() . $project['original_file_path']);
    if (is_dir($dir)) @rmdir($dir);
}

mysqli_query($connection, "DELETE FROM projects WHERE id = '$project_id' AND user_id = '$user_id'");

header('Location: cabinet.php?deleted=1');
exit();
?>