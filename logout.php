<?php
session_start();
session_unset();
session_destroy();

// Quay về trang đăng nhập
header("Location: login.php");
exit();
