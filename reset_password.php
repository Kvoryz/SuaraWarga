<?php
session_start();
require_once 'config/function.php';

if (!isset($_SESSION['reset_user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $user_id = $_SESSION['reset_user_id'];
    
    if ($new_password !== $confirm_password) {
        $error = "Password baru dan konfirmasi password tidak sama";
    } elseif (strlen($new_password) < 6) {
        $error = "Password minimal 6 karakter";
    } else {
        $query = "SELECT password FROM users WHERE id = '$user_id'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $old_hashed_password = $user['password'];
            
            if (password_verify($new_password, $old_hashed_password)) {
                $error = "Password baru tidak boleh sama dengan password sebelumnya";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $update_query = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
                
                if (mysqli_query($conn, $update_query)) {
                    unset($_SESSION['reset_user_id']);
                    unset($_SESSION['reset_email']);
                    
                    $success = "Password berhasil direset! Silakan login dengan password baru Anda.";
                } else {
                    $error = "Terjadi kesalahan saat mereset password";
                }
            }
        } else {
            $error = "Data pengguna tidak ditemukan";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Reset Password</h1>
            <p class="text-gray-600 mt-2">Buat password baru untuk akun Anda</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <?php if ($error): ?>
            <div class="mb-6 p-3 bg-red-50 border border-red-100 rounded-lg">
                <p class="text-red-700 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <?php echo $error; ?>
                </p>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="mb-6 p-3 bg-green-50 border border-green-100 rounded-lg">
                <p class="text-green-700 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <?php echo $success; ?>
                </p>
                <div class="mt-4 text-center">
                    <a href="login.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                        Login Sekarang
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <div class="mb-6 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                <p class="text-blue-700 text-sm">
                    <strong>Reset Password untuk:</strong> <?php echo $_SESSION['reset_email']; ?>
                </p>
            </div>
            
            <form method="POST" action="">
                <div class="space-y-6">
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Baru
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input type="password" id="new_password" name="new_password" required minlength="6"
                                   class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                   placeholder="Password baru minimal 6 karakter">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Minimal 6 karakter, tidak boleh sama dengan password sebelumnya</p>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password Baru
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6"
                                   class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                   placeholder="Ulangi password baru">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Harus sama dengan password di atas</p>
                    </div>
                    
                    <div class="pt-4">
                        <button type="submit" name="reset_password"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Reset Password
                        </button>
                    </div>
                </div>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-center text-gray-600 text-sm">
                    <a href="login.php" class="text-blue-600 hover:text-blue-800 font-medium">
                        Kembali ke halaman login
                    </a>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>