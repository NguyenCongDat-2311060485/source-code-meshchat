<?php
session_start();
require_once "db.php";

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Tìm user theo username hoặc email
    $user = db_fetch("SELECT * FROM users WHERE username=? OR email=?", "ss", [$username, $username]);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Lưu session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        // Cập nhật last_login
        db_execute("UPDATE users SET last_login = NOW() WHERE user_id=?", "i", [$user['user_id']]);

        // Chuyển hướng
        header("Location: index.php");
        exit();
    } else {
        $msg = "❌ Sai tài khoản hoặc mật khẩu!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Đăng nhập</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f0f2f5;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}
form {
    background: #fff;
    padding: 24px;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.2);
    width: 320px;
    text-align: center;
}
h2 {
    margin-bottom: 16px;
    color: #333;
}
input, button {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
button {
    background: #28a745;
    color: #fff;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}
button:hover {
    background: #218838;
}
p {
    color: red;
    margin: 6px 0;
}
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<form method="post">
  <h2>Đăng nhập</h2>
  <input type="text" name="username" placeholder="Tên đăng nhập hoặc Email" required>
  <input type="password" name="password" placeholder="Mật khẩu" required>
  <button type="submit">Đăng nhập</button>
  <p><?= $msg ?></p>
  <div>Chưa có tài khoản? <a href="register.php">Đăng ký</a></div>
</form>
</body>
</html>
