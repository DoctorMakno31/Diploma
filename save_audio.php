<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/config.php';
$connection = getDBConnection();

$project_id = intval($_POST['project_id']);
$user_id = $_SESSION['user_id'];

$check = mysqli_query($connection, "SELECT * FROM projects WHERE id = '$project_id' AND user_id = '$user_id'");
if (mysqli_num_rows($check) == 0) {
    echo json_encode(['success' => false, 'message' => 'Project not found']);
    exit();
}

if (isset($_FILES['saved_audio']) && $_FILES['saved_audio']['error'] === UPLOAD_ERR_OK) {
    $relative_dir = 'uploads/users/' . $user_id . '/projects/' . $project_id . '/';
    $abs_dir = getUploadsPath() . $relative_dir;
    if (!file_exists($abs_dir)) {
        mkdir($abs_dir, 0777, true);
    }
    
    $filename = 'edited_' . time() . '.wav';
    $filepath_abs = $abs_dir . $filename;
    $filepath_rel = $relative_dir . $filename;
    
    if (move_uploaded_file($_FILES['saved_audio']['tmp_name'], $filepath_abs)) {
        $filepath_escaped = mysqli_real_escape_string($connection, $filepath_rel);
        mysqli_query($connection, "UPDATE projects SET edited_file_path = '$filepath_escaped' WHERE id = '$project_id'");
        echo json_encode(['success' => true]);
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Save failed']);
?>