<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['message_id']) && isset($_POST['chat_id'])) {
    $message_id = (int)$_POST['message_id'];
    $chat_id    = (int)$_POST['chat_id'];

    // Kiểm tra tin nhắn có tồn tại và thuộc về user không
    $msg = db_fetch("SELECT * FROM chat_messages WHERE message_id=? AND user_id=?", "ii", [$message_id, $user_id]);

    if ($msg) {
        // Đánh dấu là đã thu hồi
        db_execute("UPDATE chat_messages SET is_revoked=1 WHERE message_id=? AND user_id=?", "ii", [$message_id, $user_id]);

        // Quay lại phòng chat
        header("Location: index.php?chat_id=" . $chat_id);
        exit();
    } else {
        echo "❌ Bạn không có quyền thu hồi tin nhắn này!";
    }
} else {
    echo "❌ Thiếu tham số!";
}
