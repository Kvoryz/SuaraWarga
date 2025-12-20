<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/function.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_level = $_SESSION['level'];
$user_nama = $_SESSION['nama'];
$user_email = $_SESSION['email'];

$query_user = "SELECT * FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $query_user);
$user_data = mysqli_fetch_assoc($result_user);
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
<body class="bg-gray-50 min-h-screen">
    
    <?php include 'layout/navbar.php'; ?>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-10 flex flex-col md:flex-row md:justify-between md:items-start gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Selamat datang, <?php echo htmlspecialchars($user_nama); ?></h1>
                <p class="text-gray-600">Dashboard <?php echo htmlspecialchars(ucfirst($user_level)); ?></p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-50 p-2 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <?php 
                        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        $namaHari = $hari[date('w')];
                        $namaBulan = $bulan[date('n') - 1];
                        ?>
                        <p class="text-sm font-medium text-gray-600"><?php echo $namaHari . ', ' . date('d') . ' ' . $namaBulan . ' ' . date('Y'); ?></p>
                        <p class="text-lg font-bold text-gray-900"><span id="jamSekarang"><?php echo date('H:i:s'); ?></span> WITA</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <?php if ($user_level == 'masyarakat'): ?>
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Total Pengaduan</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE email_pelapor = '$user_email'");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Dalam Proses</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE email_pelapor = '$user_email' AND status = 'proses'");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-yellow-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Selesai</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE email_pelapor = '$user_email' AND status = 'selesai'");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-green-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Belum Diproses</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE email_pelapor = '$user_email' AND status = '0'");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($user_level == 'petugas'): ?>
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Total Pengaduan</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Perlu Diproses</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE status = '0'");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-yellow-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Dalam Proses</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE status = 'proses'");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-orange-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Selesai</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE status = 'selesai'");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-green-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($user_level == 'admin'): ?>
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Total Pengguna</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Petugas</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE level = 'petugas'");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-green-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Total Pengaduan</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-purple-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Hari Ini</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php
                                $today = date('Y-m-d');
                                $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE tanggal_pengaduan = '$today'");
                                $data = mysqli_fetch_assoc($query);
                                echo htmlspecialchars($data['total']);
                                ?>
                            </p>
                        </div>
                        <div class="bg-red-50 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-10">
            <div class="border-b border-gray-200 px-8 py-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Profil Pengguna</h2>
                        <p class="text-gray-600 text-sm mt-1">Informasi akun Anda</p>
                    </div>
                    <button onclick="openEditModal()" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                        Edit Profil
                    </button>
                </div>
            </div>
            
            <div class="px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-2">Nama Lengkap</label>
                            <div class="text-gray-900 font-medium"><?php echo htmlspecialchars($user_data['nama']); ?></div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-2">Email</label>
                            <div class="text-gray-900 font-medium"><?php echo htmlspecialchars($user_data['email']); ?></div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-2">Username</label>
                            <div class="text-gray-900 font-medium"><?php echo htmlspecialchars($user_data['username']); ?></div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-2">Nomor Telepon</label>
                            <div class="text-gray-900 font-medium">
                                <?php echo !empty($user_data['telp']) ? htmlspecialchars($user_data['telp']) : '<span class="text-gray-400">Belum diatur</span>'; ?>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-2">Level Akun</label>
                            <div class="inline-block">
                                <span class="px-3 py-1 text-xs font-medium rounded-full 
                                    <?php 
                                    if($user_data['level'] == 'admin') echo 'bg-purple-100 text-purple-800';
                                    elseif($user_data['level'] == 'petugas') echo 'bg-green-100 text-green-800';
                                    else echo 'bg-blue-100 text-blue-800';
                                    ?>">
                                    <?php echo htmlspecialchars(ucfirst($user_data['level'])); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-2">Tanggal Bergabung</label>
                            <div class="text-gray-900 font-medium">
                                <?php echo htmlspecialchars(date('d F Y', strtotime($user_data['created_at']))); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Profil</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form method="POST" action="update_profile.php" class="px-6 py-6">
                <div class="space-y-4">
                    <div>
                        <label for="edit_nama" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap
                        </label>
                        <input type="text" id="edit_nama" name="nama" required
                               value="<?php echo htmlspecialchars($user_data['nama']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input type="email" id="edit_email" name="email" required
                               value="<?php echo htmlspecialchars($user_data['email']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label for="edit_username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username
                        </label>
                        <input type="text" id="edit_username" name="username" required
                               value="<?php echo htmlspecialchars($user_data['username']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label for="edit_telp" class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor Telepon
                        </label>
                        <input type="text" id="edit_telp" name="telp"
                               value="<?php echo htmlspecialchars($user_data['telp']); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div>
                        <label for="edit_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Baru (kosongkan jika tidak ingin mengubah)
                        </label>
                        <input type="password" id="edit_password" name="password"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeEditModal()"
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>