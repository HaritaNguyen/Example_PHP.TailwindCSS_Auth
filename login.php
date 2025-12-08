<?php

require_once 'includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    if (empty($identifier) || empty($password)) {
        $error = "Vui lòng điền đầy đủ thông tin.";
    } else {
        $conn = db_connect();
        
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                
                if ($remember_me) {
                    set_remember_cookie($row['id']);
                }
                
                $stmt->close();
                $conn->close();
                header('Location: index.php');
                exit;
            } else {
                $error = "Tên người dùng/email hoặc mật khẩu không đúng.";
            }
        } else {
            $error = "Tên người dùng/email hoặc mật khẩu không đúng.";
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Đăng Nhập</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Đăng Nhập</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-4">
            <div>
                <label for="identifier" class="block text-sm font-medium text-gray-700">Tên người dùng hoặc Email:</label>
                <input type="text" id="identifier" name="identifier" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu:</label>
                <input type="password" id="password" name="password" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="remember_me" name="remember_me" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                    Ghi nhớ đăng nhập (30 ngày)
                </label>
            </div>
            
            <button type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Đăng Nhập
            </button>
        </form>
        
        <p class="mt-6 text-center text-sm text-gray-600">
            Chưa có tài khoản? 
            <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500">Đăng ký</a>
        </p>
    </div>
</body>
</html>