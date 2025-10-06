<?php
session_start();
require_once "db.php";

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname  = trim($_POST['fullname']);
    $birthdate = $_POST['birthdate']; // yyyy-mm-dd
    $hometown  = trim($_POST['hometown']);
    $email     = trim($_POST['email']);
    $username  = trim($_POST['username']);
    $password  = $_POST['password'];

    // Mã hoá mật khẩu
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    if ($fullname && $birthdate && $email && $username && $password) {
        // Kiểm tra username hoặc email tồn tại
        $exist = db_fetch("SELECT * FROM users WHERE username=? OR email=?", "ss", [$username, $email]);
        if ($exist) {
            $msg = "⚠️ Tài khoản hoặc email đã tồn tại!";
        } else {
            // Thêm user mới
            db_insert(
                "INSERT INTO users (full_name, birthdate, hometown, email, username, password_hash) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                "ssssss",
                [$fullname, $birthdate, $hometown, $email, $username, $password_hash]
            );

            $msg = "✅ Đăng ký thành công. <a href='login.php'>Đăng nhập ngay</a>";
        }
    } else {
        $msg = "⚠️ Vui lòng điền đầy đủ thông tin!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Đăng ký</title>
<style>
body{font-family:Arial;background:#f0f2f5;display:flex;justify-content:center;align-items:center;height:100vh}
form{background:#fff;padding:20px;border-radius:8px;box-shadow:0 0 8px rgba(0,0,0,.2);width:320px}
input,button{width:100%;padding:8px;margin:6px 0;border:1px solid #ccc;border-radius:6px}
button{background:#007bff;color:#fff;cursor:pointer}
p{color:red}
</style>
</head>
<body>
<form method="post">
  <h2>Đăng ký</h2>
  <input type="text" name="fullname" placeholder="Họ tên" required>
  <input type="date" name="birthdate" required> <!-- thay cho tuổi -->
  <input type="text" name="hometown" placeholder="Quê quán">
  <input type="email" name="email" placeholder="Email" required>
  <input type="text" name="username" placeholder="Tên đăng nhập" required>
  <input type="password" name="password" placeholder="Mật khẩu" required>
  <button type="submit">Đăng ký</button>
  <p><?= $msg ?></p>
  <div>Đã có tài khoản? <a href="login.php">Đăng nhập</a></div>
</form>
</body>
</html>
