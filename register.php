<?php

require_once 'includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Vui lòng điền đầy đủ thông tin.";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else {
        $conn = db_connect();
        
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $error = "Tên người dùng hoặc email đã tồn tại.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt_insert->execute()) {
                $success = "Đăng ký thành công! Bạn có thể <a href='login.php' class='text-blue-500 hover:text-blue-700'>Đăng nhập</a> ngay bây giờ.";
            } else {
                $error = "Lỗi đăng ký: " . $conn->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Đăng Ký</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Đăng Ký Tài Khoản</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Tên người dùng:</label>
                <input type="text" id="username" name="username" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <input type="email" id="email" name="email" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu:</label>
                <input type="password" id="password" name="password" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            
            <button type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Đăng Ký
            </button>
        </form>
        
        <p class="mt-6 text-center text-sm text-gray-600">
            Đã có tài khoản? 
            <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">Đăng nhập</a>
        </p>
    </div>
</body>
</html>