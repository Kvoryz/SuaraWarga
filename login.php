<?php
session_start();
require_once 'config/function.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$forgot_error = '';
$forgot_success = '';
$show_forgot_modal = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $query = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['level'] = $user['level'];
            $_SESSION['username'] = $user['username'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password salah";
        }
    } else {
        $error = "Username atau email tidak ditemukan";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_forgot'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telp = mysqli_real_escape_string($conn, $_POST['telp']);
    
    $query = "SELECT * FROM users WHERE email = '$email' AND telp = '$telp'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        $_SESSION['reset_user_id'] = $user['id'];
        $_SESSION['reset_email'] = $user['email'];
        
        header("Location: reset_password.php");
        exit();
    } else {
        $forgot_error = "Email dan nomor telepon tidak sesuai dengan data yang terdaftar";
        $show_forgot_modal = true;
    }
}

if (isset($_GET['forgot_password'])) {
    $show_forgot_modal = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuaraWarga</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <header class="absolute top-6 left-6 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
        viewBox="0 0 24 24" stroke-width="1.5"
        stroke="currentColor" class="w-6 h-6 text-gray-900">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M7.5 8.25h9m-9 3h5.25M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25-9 3.694-9 8.25c0 2.09.82 3.995 2.18 5.433L3 20.25l3.07-.82A10.13 10.13 0 0 0 12 20.25Z" />
        </svg>
        <span class="font-semibold text-lg tracking-tight">SuaraWarga</span>
    </header>
    
    <div class="max-w-md w-full mx-4">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7.5 8.25h9m-9 3h5.25M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25-9 3.694-9 8.25c0 2.09.82 3.995 2.18 5.433L3 20.25l3.07-.82A10.13 10.13 0 0 0 12 20.25Z" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Masuk</h1>
            <p class="text-gray-600 mt-2">Akses sistem pengaduan masyarakat</p>
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
            
            <form method="POST" action="" id="loginForm">
                <div class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username atau Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input type="text" id="username" name="username" required
                                   class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-gray-900"
                                   placeholder="Masukkan username atau email"
                                   autocomplete="username"
                                   autofocus>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Password
                            </label>
                            <button type="button" onclick="showForgotModal()" 
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Lupa Password?
                            </button>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required
                                   class="pl-10 pr-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-gray-900"
                                   placeholder="Masukkan password"
                                   autocomplete="current-password">
                            <span class="password-toggle" onclick="togglePasswordVisibility()">
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                    
                    <button type="submit" name="login"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Masuk ke Sistem
                    </button>
                </div>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-center text-gray-600 text-sm">
                    Belum memiliki akun?
                    <a href="register.php" class="text-blue-600 hover:text-blue-800 font-medium ml-1">
                        Buat akun baru
                    </a>
                </p>
            </div>
        </div>
    </div>
    
    <div class="modal-overlay <?php echo $show_forgot_modal ? 'active' : ''; ?>" id="forgotModal">
        <div class="bg-white rounded-xl shadow-lg max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-8 pt-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Lupa Password</h2>
                        <p class="text-gray-600 text-sm mt-1">Verifikasi Identitas Anda</p>
                    </div>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <?php if ($forgot_error): ?>
                <div class="mb-6 p-3 bg-red-50 border border-red-100 rounded-lg">
                    <p class="text-red-700 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo $forgot_error; ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="px-8 pb-8">
                <form method="POST" action="" id="forgotForm">
                    <div class="space-y-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Terdaftar
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="email" id="email" name="email" required
                                       class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                       placeholder="Masukkan email terdaftar"
                                       autocomplete="email">
                            </div>
                        </div>
                        
                        <div>
                            <label for="telp" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Telepon Terdaftar
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <input type="text" id="telp" name="telp" required
                                       class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                       placeholder="Masukkan nomor telepon terdaftar"
                                       autocomplete="tel">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Pastikan nomor telepon sesuai dengan yang terdaftar di sistem</p>
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" name="verify_forgot"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Verifikasi Identitas
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-center text-gray-600 text-sm">
                        Ingat password Anda?
                        <button onclick="closeModal()" class="text-blue-600 hover:text-blue-800 font-medium ml-1">
                            Kembali login
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>