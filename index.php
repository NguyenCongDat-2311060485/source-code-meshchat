<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user = db_fetch("SELECT * FROM users WHERE user_id=?", "i", [$_SESSION['user_id']]);
$user_id = $current_user['user_id'];

// Lấy danh sách phòng
$chats = db_fetch_all("
    SELECT c.*, u.username AS creator_name 
    FROM chats c
    JOIN users u ON c.created_by = u.user_id
    ORDER BY c.created_at DESC
");

$current_chat_id = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : null;
$messages = [];
$chat_info = null;

if ($current_chat_id) {
    $chat_info = db_fetch("SELECT * FROM chats WHERE chat_id=?", "i", [$current_chat_id]);

    // Kiểm tra mật khẩu phòng
    if ($chat_info && $chat_info['password_hash']) {
        if (!isset($_SESSION['chat_access'][$current_chat_id])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_password'])) {
                if (password_verify($_POST['room_password'], $chat_info['password_hash'])) {
                    $_SESSION['chat_access'][$current_chat_id] = true;
                } else {
                    $error = "❌ Sai mật khẩu phòng!";
                }
            }
        }
    }

    // Nếu vào được phòng → lấy tin nhắn
    if ($chat_info && (!$chat_info['password_hash'] || isset($_SESSION['chat_access'][$current_chat_id]))) {
        $messages = db_fetch_all("
            SELECT m.*, u.username, u.avatar 
            FROM chat_messages m
            JOIN users u ON m.user_id = u.user_id
            WHERE m.chat_id=? 
            ORDER BY m.created_at ASC
        ", "i", [$current_chat_id]);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Mesh Chat</title>
<style>
body {margin:0; font-family:"Segoe UI", Arial, sans-serif; background:#f5f6fa;}
header {
    background:#007bff; color:#fff; padding:12px 20px;
    display:flex; justify-content:space-between; align-items:center;
    box-shadow:0 2px 5px rgba(0,0,0,0.2);
}
header h1 {margin:0; font-size:20px;}
header .user {display:flex; align-items:center; gap:10px; position:relative;}
header .user img {width:32px; height:32px; border-radius:50%; cursor:pointer;}
header .user a.username {color:#fff; font-weight:bold; text-decoration:none;}
header .user-menu {
    position:absolute; top:42px; right:0;
    background:#fff; border:1px solid #ddd; border-radius:6px;
    box-shadow:0 2px 5px rgba(0,0,0,0.2);
    display:none; flex-direction:column; min-width:160px; z-index:100;
}
header .user-menu a {
    padding:10px; color:#333; text-decoration:none; font-size:14px;
}
header .user-menu a:hover {background:#f1f1f1;}
header .user:hover .user-menu {display:flex;}

.container {display:flex; height:calc(100vh - 56px);}
.sidebar {width:260px; background:#fff; border-right:1px solid #ddd; display:flex; flex-direction:column;}
.sidebar-header {display:flex; justify-content:space-between; align-items:center; padding:12px 15px; border-bottom:1px solid #ddd;}
.sidebar-header h3 {margin:0; font-size:16px;}
.sidebar-header button {
    background:#007bff; border:none; color:#fff; width:28px; height:28px; border-radius:50%; cursor:pointer;
    font-size:18px; line-height:0;
}
.sidebar-header button:hover {background:#0056b3;}
.chat-list {flex:1; overflow-y:auto; padding:10px;}
.chat-item {padding:10px; border-radius:8px; cursor:pointer; background:#f1f1f1; margin-bottom:8px;}
.chat-item:hover {background:#e2e6ea;}
.chat-item.active {background:#007bff; color:#fff;}
.chat-item small {color:#666; font-size:12px;}
.chat-item .lock {float:right;}

.chat-box {flex:1; display:flex; flex-direction:column; background:#fafafa;}
.chat-header {padding:12px 15px; border-bottom:1px solid #ddd; background:#fff; font-weight:bold; font-size:16px;}
.messages {flex:1; overflow-y:auto; padding:15px;}
.message {display:flex; gap:10px; margin-bottom:12px; max-width:70%;}
.message.you {margin-left:auto; flex-direction:row-reverse;}
.message img.avatar {width:32px; height:32px; border-radius:50%;}
.msg-content {background:#f1f1f1; padding:8px 12px; border-radius:12px; word-wrap:break-word; font-size:14px; max-width:300px;}
.you .msg-content {background:#007bff; color:#fff;}
.msg-content img {max-width:180px; border-radius:8px; margin-top:5px;}
.msg-meta {font-size:11px; color:#666; margin-top:4px; text-align:right;}
.you .msg-meta {color:#ddd;}

.send-box {display:flex; align-items:center; gap:6px; border-top:1px solid #ddd; padding:8px; background:#fff;}
.send-box input[type="text"] {flex:1; padding:10px; border:1px solid #ccc; border-radius:20px;}
.send-box input[type="file"] {display:none;}
.send-box label.file-btn {
    background:#eee; padding:8px 12px; border-radius:20px; cursor:pointer; font-size:14px;
}
.send-box button {background:#007bff; border:none; color:#fff; padding:10px 16px; border-radius:20px; cursor:pointer;}
.send-box button:hover {background:#0056b3;}

#img-preview img {max-height:50px; border-radius:6px; margin-left:8px;}

.icon-picker {padding:6px 12px; border-top:1px solid #eee; background:#fff;}
.icon-picker button {background:none; border:none; font-size:20px; cursor:pointer; margin:4px;}
.icon-picker button:hover {transform:scale(1.2);}

.password-form {
    margin:auto; text-align:center; background:#fff; padding:20px; border:1px solid #ddd; border-radius:8px;
}
.password-form input[type=password] {
    padding:10px; width:200px; border:1px solid #ccc; border-radius:6px;
}
.password-form button {
    padding:10px 16px; border:none; background:#007bff; color:#fff; border-radius:6px; cursor:pointer;
}
.password-form button:hover {background:#0056b3;}
</style>
<script>
function addIcon(icon){
    const input=document.getElementById("msg-input");
    input.value+= " " + icon;
    input.focus();
}
function previewImage(input){
    const preview=document.getElementById("img-preview");
    preview.innerHTML="";
    if(input.files && input.files[0]){
        const reader=new FileReader();
        reader.onload=function(e){
            const img=document.createElement("img");
            img.src=e.target.result;
            preview.appendChild(img);
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</head>
<body>
<header>
    <h1>💬 MeshChat</h1>
    <div class="user">
        <a href="profile.php?user_id=<?= $current_user['user_id'] ?>" class="username">
            <img src="<?= htmlspecialchars($current_user['avatar']) ?>" alt="avatar">
            <?= htmlspecialchars($current_user['username']) ?>
        </a>
        <div class="user-menu">
            <a href="edit_profile.php">⚙️ Chỉnh sửa thông tin</a>
            <a href="logout.php">🚪 Đăng xuất</a>
        </div>
    </div>
</header>
<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Phòng Chat</h3>
            <button onclick="location.href='create_room.php'">+</button>
        </div>
        <div class="chat-list">
            <?php foreach($chats as $c): ?>
                <div class="chat-item <?= ($c['chat_id']==$current_chat_id)?'active':'' ?>"
                     onclick="location.href='?chat_id=<?= $c['chat_id'] ?>'">
                    <strong><?= htmlspecialchars($c['title']) ?></strong>
                    <?php if ($c['password_hash']): ?><span class="lock">🔒</span><?php endif; ?><br>
                    <small>Tạo bởi <?= htmlspecialchars($c['creator_name']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Chat box -->
    <div class="chat-box">
        <?php if ($current_chat_id && $chat_info): ?>
            <?php if ($chat_info['password_hash'] && !isset($_SESSION['chat_access'][$current_chat_id])): ?>
                <div class="password-form">
                    <h3>🔒 Phòng này có mật khẩu</h3>
                    <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
                    <form method="post">
                        <input type="password" name="room_password" placeholder="Nhập mật khẩu..." required>
                        <button type="submit">Vào phòng</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="chat-header">
                    <?= htmlspecialchars($chat_info['title']) ?>
                </div>
                <div class="messages">
                    <?php foreach($messages as $m): ?>
                        <div class="message <?= $m['user_id']==$user_id?'you':'other' ?>">
                            <img src="<?= htmlspecialchars($m['avatar']) ?>" class="avatar">
                            <div>
                                <div class="msg-content">
                                    <?php if ($m['is_revoked']): ?>
                                        <em style="color:#999;">Tin nhắn đã bị thu hồi</em>
                                    <?php else: ?>
                                        <?php if ($m['message']): ?>
                                            <?= nl2br(htmlspecialchars($m['message'])) ?>
                                        <?php endif; ?>
                                        <?php if ($m['image_url']): ?>
                                            <img src="<?= htmlspecialchars($m['image_url']) ?>" alt="img">
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="msg-meta">
                                    <?= $m['username'] ?> - <?= $m['created_at'] ?>
                                    <?php if ($m['user_id']==$user_id && !$m['is_revoked']): ?>
                                        <form method="post" action="revoke_message.php" style="display:inline;">
                                        <input type="hidden" name="chat_id" value="<?= $current_chat_id ?>">
                                        <input type="hidden" name="message_id" value="<?= $m['message_id'] ?>">
                                        <button type="submit" style="color:red; font-size:11px; background:none; border:none; cursor:pointer;">Thu hồi</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form class="send-box" method="post" action="send_message.php" enctype="multipart/form-data">
                    <input type="hidden" name="chat_id" value="<?= $current_chat_id ?>">
                    <input type="text" id="msg-input" name="message" placeholder="Nhập tin nhắn...">
                    <label class="file-btn">
                        📎
                        <input type="file" name="image" accept="image/*,.gif" onchange="previewImage(this)">
                    </label>
                    <div id="img-preview"></div>
                    <button type="submit">Gửi</button>
                </form>
                <div class="icon-picker">
                    <?php foreach(["😊","😂","❤️","👍","😢","🔥","🎉","✨","🎶","🎂"] as $icon): ?>
                        <button type="button" onclick="addIcon('<?= $icon ?>')"><?= $icon ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p style="margin:auto; font-size:18px; color:#555">👉 Chọn một phòng chat để bắt đầu.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

