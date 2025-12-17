<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/function.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit();
}

$user_nama = $_SESSION['nama'];
$user_level = $_SESSION['level'];
$user_email = $_SESSION['email'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah_user'])) {
        $nama = mysqli_real_escape_string($conn, $_POST['nama']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $telp = mysqli_real_escape_string($conn, $_POST['telp']);
        $level = mysqli_real_escape_string($conn, $_POST['level']);
        $password = $_POST['password'];
        
        if (empty($nama) || empty($email) || empty($username) || empty($password)) {
            $error = "Semua field wajib diisi";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid";
        } elseif (strlen($password) < 6) {
            $error = "Password minimal 6 karakter";
        } else {
            $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
            $check_username = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
            
            if (mysqli_num_rows($check_email) > 0) {
                $error = "Email sudah digunakan";
            } elseif (mysqli_num_rows($check_username) > 0) {
                $error = "Username sudah digunakan";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (nama, email, username, telp, password, level) 
                        VALUES ('$nama', '$email', '$username', '$telp', '$hashed_password', '$level')";
                
                if (mysqli_query($conn, $sql)) {
                    $success = "User berhasil ditambahkan";
                } else {
                    $error = "Gagal menambahkan user: " . mysqli_error($conn);
                }
            }
        }
    }
    
    if (isset($_POST['update_user'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $telp = mysqli_real_escape_string($conn, $_POST['telp']);
        $level = mysqli_real_escape_string($conn, $_POST['level']);
        $password = $_POST['password'];
        
        if (empty($nama) || empty($email) || empty($username)) {
            $error = "Nama, email, dan username wajib diisi";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid";
        } else {
            $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND id != $id");
            $check_username = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' AND id != $id");
            
            if (mysqli_num_rows($check_email) > 0) {
                $error = "Email sudah digunakan oleh user lain";
            } elseif (mysqli_num_rows($check_username) > 0) {
                $error = "Username sudah digunakan oleh user lain";
            } else {
                $sql = "UPDATE users SET 
                        nama = '$nama',
                        email = '$email',
                        username = '$username',
                        telp = '$telp',
                        level = '$level'";
                
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $error = "Password minimal 6 karakter";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $sql .= ", password = '$hashed_password'";
                    }
                }
                
                $sql .= " WHERE id = $id";
                
                if (empty($error)) {
                    if (mysqli_query($conn, $sql)) {
                        $success = "User berhasil diperbarui";
                    } else {
                        $error = "Gagal memperbarui user: " . mysqli_error($conn);
                    }
                }
            }
        }
    }
    
    if (isset($_POST['hapus_user'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        
        if ($id == $_SESSION['user_id']) {
            $error = "Tidak dapat menghapus akun sendiri";
        } else {
            $sql = "DELETE FROM users WHERE id = $id";
            
            if (mysqli_query($conn, $sql)) {
                $success = "User berhasil dihapus";
            } else {
                $error = "Gagal menghapus user: " . mysqli_error($conn);
            }
        }
    }
}

$query_petugas = "SELECT * FROM users WHERE level = 'petugas' ORDER BY nama";
$result_petugas = mysqli_query($conn, $query_petugas);

$query_masyarakat = "SELECT * FROM users WHERE level = 'masyarakat' ORDER BY nama";
$result_masyarakat = mysqli_query($conn, $query_masyarakat);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50 min-h-screen">
    
    <?php include 'layout/navbar.php'; ?>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8 flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Kelola User</h1>
                <p class="text-gray-600">Kelola data petugas dan masyarakat</p>
            </div>
            <div class="flex space-x-3">
                <a href="export_user.php" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </a>
                <button onclick="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Tambah User
                </button>
            </div>
        </div>
        
        <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-100 rounded-lg">
            <p class="text-green-700 text-sm flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <?php echo $success; ?>
            </p>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-lg">
            <p class="text-red-700 text-sm flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <?php echo $error; ?>
            </p>
        </div>
        <?php endif; ?>
        

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-8">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Petugas
                    <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                        <?php echo mysqli_num_rows($result_petugas); ?> User
                    </span>
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telepon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($petugas = mysqli_fetch_assoc($result_petugas)): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($petugas['nama']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($petugas['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($petugas['username']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $petugas['telp'] ? htmlspecialchars($petugas['telp']) : '-'; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($petugas['created_at'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="openEditUserModal(
                                    <?php echo $petugas['id']; ?>,
                                    '<?php echo addslashes($petugas['nama']); ?>',
                                    '<?php echo addslashes($petugas['email']); ?>',
                                    '<?php echo addslashes($petugas['username']); ?>',
                                    '<?php echo addslashes($petugas['telp']); ?>',
                                    '<?php echo $petugas['level']; ?>'
                                )" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>

                                <button onclick="openDeleteModal(<?php echo $petugas['id']; ?>)" class="text-red-600 hover:text-red-900">Hapus</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                    </svg>
                    Masyarakat
                    <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                        <?php echo mysqli_num_rows($result_masyarakat); ?> User
                    </span>
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telepon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($masyarakat = mysqli_fetch_assoc($result_masyarakat)): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($masyarakat['nama']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($masyarakat['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($masyarakat['username']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $masyarakat['telp'] ? htmlspecialchars($masyarakat['telp']) : '-'; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($masyarakat['created_at'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="openEditUserModal(
                                    <?php echo $masyarakat['id']; ?>,
                                    '<?php echo addslashes($masyarakat['nama']); ?>',
                                    '<?php echo addslashes($masyarakat['email']); ?>',
                                    '<?php echo addslashes($masyarakat['username']); ?>',
                                    '<?php echo addslashes($masyarakat['telp']); ?>',
                                    '<?php echo $masyarakat['level']; ?>'
                                )" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                <button onclick="openDeleteModal(<?php echo $masyarakat['id']; ?>)" class="text-red-600 hover:text-red-900">Hapus</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah User Baru</h3>
                    <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form method="POST" action="" class="px-6 py-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" name="username" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                        <input type="text" name="telp"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                        <select name="level" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <option value="masyarakat">Masyarakat</option>
                            <option value="petugas">Petugas</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter</p>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeAddModal()"
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit" name="tambah_user"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Tambah User
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Edit User</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form method="POST" action="" class="px-6 py-6">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" id="edit_nama" name="nama" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="edit_email" name="email" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" id="edit_username" name="username" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                        <input type="text" id="edit_telp" name="telp"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                        <select id="edit_level" name="level" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <option value="masyarakat">Masyarakat</option>
                            <option value="petugas">Petugas</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                        <input type="password" name="password"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeEditModal()"
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit" name="update_user"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div id="deleteModal" class="modal">
        <div class="modal-content confirmation-modal">
            <div class="px-6 py-6">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus User</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        Apakah Anda yakin ingin menghapus user ini? Data yang sudah dihapus tidak dapat dikembalikan.
                    </p>
                    
                    <form method="POST" action="" class="space-y-3">
                        <input type="hidden" id="delete_id" name="id">
                        <div class="flex justify-center space-x-3">
                            <button type="button" onclick="closeDeleteModal()"
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit" name="hapus_user"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                                Ya, Hapus
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>