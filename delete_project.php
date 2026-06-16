<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: adminstr.php');
    exit();
}

$connection = mysqli_connect('127.0.0.1', 'root', '', 'MySite');
if (!$connection) {
    die('Ошибка подключения к базе данных');
}

$project_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Проверяем, принадлежит ли проект пользователю
$check = mysqli_query($connection, "SELECT * FROM projects WHERE id = '$project_id' AND user_id = '$user_id'");

if (mysqli_num_rows($check) == 0) {
    header('Location: cabinet.php?error=not_found');
    exit();
}

$project = mysqli_fetch_assoc($check);

// Удаляем файлы проекта, если они есть
if (!empty($project['original_file_path']) && file_exists($project['original_file_path'])) {
    unlink($project['original_file_path']);
}
if (!empty($project['edited_file_path']) && file_exists($project['edited_file_path'])) {
    unlink($project['edited_file_path']);
}

// Удаляем папку проекта, если она пустая
$project_dir = dirname($project['original_file_path']);
if (is_dir($project_dir)) {
    @rmdir($project_dir); // удалит только пустую папку
}

// Удаляем запись из базы данных
mysqli_query($connection, "DELETE FROM projects WHERE id = '$project_id' AND user_id = '$user_id'");

header('Location: cabinet.php?deleted=1');
exit();
?>