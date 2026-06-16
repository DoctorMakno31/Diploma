<?php
$connection = mysqli_connect('127.0.0.1', 'root', '');

if (!$connection) {
    die("Ошибка подключения к MySQL: " . mysqli_connect_error());
}

// Создаем БД
mysqli_query($connection, "CREATE DATABASE IF NOT EXISTS MySite");
mysqli_select_db($connection, "MySite");

// Таблица users
mysqli_query($connection, "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Таблица projects
mysqli_query($connection, "
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    bpm INT DEFAULT 120,
    `key` VARCHAR(10) DEFAULT 'C',
    original_file_path VARCHAR(500),
    edited_file_path VARCHAR(500),
    file_name VARCHAR(255),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Перенаправляем на index.php
header('Location: index.php');
exit();
?>