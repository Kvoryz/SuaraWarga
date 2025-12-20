<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/function.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'masyarakat') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'];
$user_nama = $_SESSION['nama'];
$user_level = $_SESSION['level'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajukan_pengaduan'])) {
    
    $isi_laporan = mysqli_real_escape_string($conn, $_POST['isi_laporan']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $tanggal_pengaduan = date('Y-m-d H:i:s');
    
    if (empty($isi_laporan)) {
        $error = "Isi laporan tidak boleh kosong";
    } else {
        $foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0 && $_FILES['foto']['size'] > 0) {
            $upload_result = uploadFoto($_FILES['foto']);
            if ($upload_result['success']) {
                $foto = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        if (empty($error)) {
            $check_user = "SELECT * FROM users WHERE email = '$user_email'";
            $result_user = mysqli_query($conn, $check_user);
            
            if (mysqli_num_rows($result_user) == 1) {
                $sql = "INSERT INTO pengaduan (tanggal_pengaduan, email_pelapor, isi_laporan, lokasi, foto, status) 
                        VALUES ('$tanggal_pengaduan', '$user_email', '$isi_laporan', '$lokasi', '$foto', '0')";
                
                if (mysqli_query($conn, $sql)) {
                    $success = "Pengaduan berhasil diajukan. Tunggu tanggapan dari petugas.";
                    $_POST['isi_laporan'] = '';
                } else {
                    $error = "Gagal mengajukan pengaduan. Silakan coba lagi.";
                }
            } else {
                $error = "User tidak ditemukan dalam database.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <script>if(localStorage.getItem('theme')==='dark'){document.documentElement.setAttribute('data-theme','dark');}</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuaraWarga</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body class="bg-gray-50 min-h-screen">
    
    <?php include 'layout/navbar.php'; ?>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Ajukan Pengaduan Baru</h1>
            <p class="text-gray-600">Laporkan masalah yang Anda temukan di masyarakat</p>
        </div>
        
        <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-100 rounded-lg">
            <p class="text-green-700 text-sm flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <?php echo htmlspecialchars($success); ?>
            </p>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-lg">
            <p class="text-red-700 text-sm flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <?php echo htmlspecialchars($error); ?>
            </p>
        </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-xl border border-gray-200 p-8">
            <form method="POST" action="" enctype="multipart/form-data" id="pengaduanForm">
                <div class="space-y-6">
                    <div>
                        <label for="isi_laporan" class="block text-sm font-medium text-gray-700 mb-2">
                            Isi Laporan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="isi_laporan" name="isi_laporan" rows="8" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                  placeholder="Jelaskan masalah atau keluhan Anda secara detail..."><?php echo isset($_POST['isi_laporan']) ? htmlspecialchars($_POST['isi_laporan']) : ''; ?></textarea>
                    </div>
                    
                    <div>
                        <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-2">
                            Lokasi Kejadian <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="lokasi" name="lokasi" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="Contoh: Jl. Sudirman No. 123, Kelurahan ABC, Kecamatan XYZ"
                               value="<?php echo isset($_POST['lokasi']) ? htmlspecialchars($_POST['lokasi']) : ''; ?>">
                    </div>
                    
                    <div>
                        <label for="foto" class="block text-sm font-medium text-gray-700 mb-2">
                            Foto Bukti (Opsional)
                        </label>
                        <div id="dropZone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition cursor-pointer">
                            <div class="space-y-1 text-center">
                                <svg id="uploadIcon" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div id="uploadText" class="text-sm text-gray-600">
                                    <span>Klik untuk upload gambar</span>
                                    <p class="text-xs mt-1">PNG, JPG, GIF hingga 5MB</p>
                                </div>
                            </div>
                            <input id="foto" name="foto" type="file" class="sr-only" accept="image/*">
                        </div>
                        <div id="previewContainer" class="mt-4 hidden">
                            <p class="text-sm text-gray-600 mb-2 text-center">Preview:</p>
                            <div class="flex justify-center">
                                <div class="relative inline-block">
                                    <img id="previewImage" class="max-w-xs h-auto rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition" onclick="openPreviewModal()">
                                    <button type="button" onclick="removeImage()" 
                                            class="absolute top-2 right-2 bg-red-600 text-white p-1 rounded-full hover:bg-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <div class="absolute bottom-2 left-2 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded">
                                        Klik untuk memperbesar
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-blue-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Pengaduan Anda akan diproses oleh petugas. Anda dapat memantau status pengaduan di halaman "Pengaduan Saya".
                        </p>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-end space-x-3">
                            <a href="dashboard.php" 
                               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Kembali ke Dashboard
                            </a>
                            <button type="submit" name="ajukan_pengaduan"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Ajukan Pengaduan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Image Preview Modal -->
    <div id="previewModal" class="modal hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl max-w-4xl w-auto">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Preview Foto</h3>
                    <button onclick="closePreviewModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6 flex justify-center">
                <img id="modalPreviewImage" src="" alt="Preview Foto" class="max-w-full max-h-[70vh] h-auto rounded-lg">
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
    <script>
        function openPreviewModal() {
            const previewImage = document.getElementById('previewImage');
            const modalImage = document.getElementById('modalPreviewImage');
            modalImage.src = previewImage.src;
            document.getElementById('previewModal').classList.remove('hidden');
            document.getElementById('previewModal').classList.add('active');
            document.body.classList.add('modal-open');
            document.documentElement.classList.add('modal-open');
        }
        
        function closePreviewModal() {
            document.getElementById('previewModal').classList.add('hidden');
            document.getElementById('previewModal').classList.remove('active');
            document.body.classList.remove('modal-open');
            document.documentElement.classList.remove('modal-open');
        }
        
        // Close modal when clicking outside
        document.getElementById('previewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePreviewModal();
            }
        });
    </script>
</body>
</html>