<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = trim($_POST['message'] ?? "");
$image_url = null;

// Nếu có upload ảnh
if (!empty($_FILES['image']['name'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $filename = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $image_url = $target_file;
    }
}

// Lưu vào database
if ($message || $image_url) {
    db_execute(
        "INSERT INTO chat_messages (user_id, message, image_url, created_at) VALUES (?,?,?,NOW())",
        "iss",
        [$user_id, $message, $image_url]
    );
}

// Quay lại index.php
header("Location: index.php");
exit();
