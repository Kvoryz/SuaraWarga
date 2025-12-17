<?php
session_start();
require_once 'config/function.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $telp = mysqli_real_escape_string($conn, $_POST['telp']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    
    if (empty($nama) || empty($email) || empty($username) || empty($password)) {
        $error = "Semua field wajib diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter";
    } else {
        $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email sudah terdaftar";
        } else {
            $check_username = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
            if (mysqli_num_rows($check_username) > 0) {
                $error = "Username sudah digunakan";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $query = "INSERT INTO users (nama, email, username, telp, password, level) 
                          VALUES ('$nama', '$email', '$username', '$telp', '$hashed_password', 'masyarakat')";
                
                if (mysqli_query($conn, $query)) {
                    $success = "Registrasi berhasil. Silakan login.";
                } else {
                    $error = "Terjadi kesalahan. Silakan coba lagi.";
                }
            }
        }
    }
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
<body class="bg-gray-50 min-h-screen flex flex-col">
    <header class="py-6 px-6 flex items-center">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" class="w-6 h-6 text-gray-900">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M7.5 8.25h9m-9 3h5.25M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25-9 3.694-9 8.25c0 2.09.82 3.995 2.18 5.433L3 20.25l3.07-.82A10.13 10.13 0 0 0 12 20.25Z" />
            </svg>
            <span class="font-semibold text-lg tracking-tight">SuaraWarga</span>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center py-8 px-4">
        <div class="max-w-lg w-full">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Buat Akun Baru</h1>
                <p class="text-gray-600 mt-2">Daftar untuk mengajukan pengaduan</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-100 rounded-lg">
                    <p class="text-green-700 text-sm flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo $success; ?>
                    </p>
                    <a href="login.php" class="text-green-800 font-medium text-sm mt-2 inline-block">
                        ← Kembali ke login
                    </a>
                </div>
                <?php elseif ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-lg">
                    <p class="text-red-700 text-sm flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <?php echo $error; ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="space-y-5">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap
                            </label>
                            <input type="text" id="nama" name="nama" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-gray-900"
                                   placeholder="Masukkan nama lengkap"
                                   value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </label>
                            <input type="email" id="email" name="email" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-gray-900"
                                   placeholder="contoh@email.com"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                Username
                            </label>
                            <input type="text" id="username" name="username" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-gray-900"
                                   placeholder="Pilih username"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                        
                        <div>
                            <label for="telp" class="block text-sm font-medium text-gray-700 mb-2">
                                Nomor Telepon
                            </label>
                            <input type="text" id="telp" name="telp" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-gray-900"
                                   placeholder="08xxxxxxxxxx"
                                   minlength="12"
                                   maxlength="12"
                                   value="<?php echo isset($_POST['telp']) ? htmlspecialchars($_POST['telp']) : ''; ?>">
                        </div>
                        
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>
                            <input type="password" id="password" name="password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-gray-900"
                                   placeholder="Minimal 6 karakter">
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Konfirmasi Password
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-gray-900"
                                   placeholder="Ulangi password">
                        </div>
                        
                        <button type="submit" name="register"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 mt-6">
                            Buat Akun
                        </button>
                    </div>
                </form>
                
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-center text-gray-600 text-sm">
                        Sudah memiliki akun?
                        <a href="login.php" class="text-blue-600 hover:text-blue-800 font-medium ml-1">
                            Masuk di sini
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </main>
    <script src="assets/js/script.js"></script>
</body>
</html>