<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($title !== '') {
        $password_hash = $password ? password_hash($password, PASSWORD_BCRYPT) : null;
        $chat_id = db_insert(
            "INSERT INTO chats (created_by, title, password_hash) VALUES (?, ?, ?)",
            "iss",
            [$user_id, $title, $password_hash]
        );
        header("Location: index.php?chat_id=" . $chat_id);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Tạo phòng chat</title>
<style>
body {margin:0; font-family:Arial,sans-serif; background:#f5f6fa;}
.form-box {
    max-width:400px; margin:80px auto; background:#fff; padding:20px;
    border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.15);
}
.form-box h2 {margin-top:0; text-align:center;}
.form-box input, .form-box button {
    width:100%; padding:10px; margin-top:12px;
    border:1px solid #ccc; border-radius:6px;
}
.form-box button {
    background:#007bff; border:none; color:#fff; cursor:pointer;
}
.form-box button:hover {background:#0056b3;}
</style>
</head>
<body>
<div class="form-box">
    <h2>➕ Tạo phòng chat mới</h2>
    <form method="post">
        <input type="text" name="title" placeholder="Tên phòng..." required>
        <input type="password" name="password" placeholder="Mật khẩu phòng (tùy chọn)">
        <button type="submit">Tạo phòng</button>
    </form>
    <p style="text-align:center; margin-top:10px;">
        <a href="index.php">← Quay lại</a>
    </p>
</div>
</body>
</html>

