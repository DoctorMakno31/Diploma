<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$connection = mysqli_connect('127.0.0.1', 'root', '', 'MySite');
if (!$connection) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit();
}

$project_id = intval($_POST['project_id']);
$user_id = $_SESSION['user_id'];

// Проверяем, принадлежит ли проект пользователю
$check = mysqli_query($connection, "SELECT * FROM projects WHERE id = '$project_id' AND user_id = '$user_id'");
if (mysqli_num_rows($check) == 0) {
    echo json_encode(['success' => false, 'message' => 'Project not found']);
    exit();
}

// Сохраняем файл
if (isset($_FILES['saved_audio']) && $_FILES['saved_audio']['error'] === UPLOAD_ERR_OK) {
    $project_dir = 'uploads/users/' . $user_id . '/projects/' . $project_id . '/';
    if (!file_exists($project_dir)) {
        mkdir($project_dir, 0777, true);
    }
    
    $filename = 'edited_' . time() . '.wav';
    $filepath = $project_dir . $filename;
    
    if (move_uploaded_file($_FILES['saved_audio']['tmp_name'], $filepath)) {
        $filepath_escaped = mysqli_real_escape_string($connection, $filepath);
        mysqli_query($connection, "UPDATE projects SET edited_file_path = '$filepath_escaped' WHERE id = '$project_id'");
        echo json_encode(['success' => true]);
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Save failed']);
?>