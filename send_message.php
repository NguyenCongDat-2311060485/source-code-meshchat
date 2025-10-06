<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chat_id = (int)($_POST['chat_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    $image_url = null;

    // Xử lý upload ảnh/gif
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        $allowed = ["jpg", "jpeg", "png", "gif", "webp"];
        if (in_array($fileType, $allowed)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $image_url = $targetFile;

                // Nếu user chỉ chọn ảnh nhưng không gõ gì → xóa tên file khỏi message
                if ($message === $_FILES["image"]["name"]) {
                    $message = '';
                }
            }
        }
    }

    if ($chat_id > 0 && ($message !== '' || $image_url)) {
        db_insert(
            "INSERT INTO chat_messages (chat_id, user_id, message, image_url) VALUES (?, ?, ?, ?)",
            "iiss",
            [$chat_id, $user_id, $message, $image_url]
        );
    }

    header("Location: index.php?chat_id=" . $chat_id);
    exit();
}

header("Location: index.php");
exit();
