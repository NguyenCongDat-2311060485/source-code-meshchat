<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$user = db_fetch("SELECT * FROM users WHERE user_id = ?", "i", [$user_id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $hometown  = trim($_POST['hometown'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $avatar    = $user['avatar']; // giữ avatar cũ nếu không đổi

    // Upload avatar mới (nếu có)
    if (!empty($_FILES['avatar']['name'])) {
        $targetDir = "uploads/avatars/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . "_" . basename($_FILES["avatar"]["name"]);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "gif", "webp"];

        if (in_array($fileType, $allowed)) {
            if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile)) {
                $avatar = $targetFile;
            }
        }
    }

    // Nếu có mật khẩu mới thì hash
    if ($password !== '') {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        db_execute(
            "UPDATE users SET full_name=?, birthdate=?, hometown=?, email=?, password_hash=?, avatar=? WHERE user_id=?",
            "ssssssi",
            [$full_name, $birthdate, $hometown, $email, $password_hash, $avatar, $user_id]
        );
    } else {
        db_execute(
            "UPDATE users SET full_name=?, birthdate=?, hometown=?, email=?, avatar=? WHERE user_id=?",
            "sssssi",
            [$full_name, $birthdate, $hometown, $email, $avatar, $user_id]
        );
    }

    header("Location: index.php?msg=updated");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa thông tin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .profile-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .profile-card h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-card img {
            display: block;
            margin: 0 auto 15px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile-card input, .profile-card button {
            width: 100%;
            padding: 10px;
            margin: 6px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .profile-card button {
            background: #007bff;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
        .profile-card button:hover {
            background: #0056b3;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>
<div class="profile-card">
    <h2>Chỉnh sửa thông tin</h2>
    <form method="post" enctype="multipart/form-data">
        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
        <input type="file" name="avatar">

        <input type="text" name="full_name" placeholder="Họ và tên" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        <input type="date" name="birthdate" value="<?= htmlspecialchars($user['birthdate']) ?>" required>
        <input type="text" name="hometown" placeholder="Quê quán" value="<?= htmlspecialchars($user['hometown']) ?>">
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>" required>
        <input type="password" name="password" placeholder="Mật khẩu mới (bỏ trống nếu giữ nguyên)">
        <button type="submit">Cập nhật</button>
    </form>
    <a class="back-link" href="index.php">← Quay lại</a>
</div>
</body>
</html>
