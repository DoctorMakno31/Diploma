<?php
require_once __DIR__ . '/config.php';
$connection = getDBConnection();

// Создание таблиц (БД уже должна существовать)
mysqli_query($connection, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

mysqli_query($connection, "CREATE TABLE IF NOT EXISTS projects (
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

header('Location: index.php');
exit();
?>