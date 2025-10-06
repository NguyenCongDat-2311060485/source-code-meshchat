<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$view_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $_SESSION['user_id'];
$user = db_fetch("SELECT * FROM users WHERE user_id=?", "i", [$view_user_id]);

if (!$user) {
    die("❌ Người dùng không tồn tại!");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Hồ sơ người dùng</title>
<style>
body {margin:0; font-family:"Segoe UI", Arial, sans-serif; background:#f5f6fa;}
header {
    background:#007bff; color:#fff; padding:12px 20px;
    display:flex; justify-content:space-between; align-items:center;
    box-shadow:0 2px 5px rgba(0,0,0,0.2);
}
header h1 {margin:0; font-size:20px;}
header a {color:#fff; text-decoration:none;}
header a:hover {text-decoration:underline;}

.container {
    max-width:800px; margin:40px auto; background:#fff; border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,0.15); padding:30px;
}
.profile-header {display:flex; align-items:center; gap:20px; margin-bottom:20px;}
.profile-header img {width:100px; height:100px; border-radius:50%; object-fit:cover; border:3px solid #007bff;}
.profile-header h2 {margin:0;}
.info {margin:20px 0;}
.info p {margin:8px 0; font-size:15px;}
.actions {margin-top:20px; display:flex; gap:10px;}
.actions a {
    background:#007bff; color:#fff; padding:10px 16px; border-radius:6px; text-decoration:none;
}
.actions a:hover {background:#0056b3;}
.actions a.logout {background:#dc3545;}
.actions a.logout:hover {background:#a71d2a;}
</style>
</head>
<body>
<header>
    <h1>👤 Hồ sơ người dùng</h1>
    <a href="index.php">⬅️ Quay lại chat</a>
</header>

<div class="container">
    <div class="profile-header">
        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
        <div>
            <h2><?= htmlspecialchars($user['username']) ?></h2>
            <p><?= htmlspecialchars($user['full_name']) ?></p>
        </div>
    </div>

    <div class="info">
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Quê quán:</strong> <?= htmlspecialchars($user['hometown'] ?? 'Chưa cập nhật') ?></p>
        <p><strong>Ngày sinh:</strong> <?= htmlspecialchars($user['birthdate']) ?></p>
        <p><strong>Ngày tham gia:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
        <p><strong>Lần đăng nhập cuối:</strong> <?= $user['last_login'] ?: 'Chưa từng đăng nhập' ?></p>
    </div>

    <?php if ($view_user_id == $_SESSION['user_id']): ?>
        <div class="actions">
            <a href="edit_profile.php">⚙️ Chỉnh sửa thông tin</a>
            <a href="logout.php" class="logout">🚪 Đăng xuất</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>

