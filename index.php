<?php

require_once 'includes/functions.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$conn = db_connect();
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

$username = $user['username'] ?? 'Người dùng';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trang Chủ</title>
</head>
<body>
    <h2>Xin chào, <?php echo htmlspecialchars($username); ?>!</h2>
    <p>Đây là trang chỉ dành cho người dùng đã đăng nhập.</p>
    <p><a href="logout.php">Đăng Xuất</a></p>
</body>
</html>