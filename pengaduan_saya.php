<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/function.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'masyarakat') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_pengaduan'])) {
    $id_pengaduan = mysqli_real_escape_string($conn, $_POST['id_pengaduan']);
    $user_email = $_SESSION['email'];
    
    $check_query = "SELECT * FROM pengaduan WHERE id_pengaduan = '$id_pengaduan' AND email_pelapor = '$user_email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $pengaduan_data = mysqli_fetch_assoc($check_result);
        $foto = $pengaduan_data['foto'];
        
        $delete_tanggapan_query = "DELETE FROM tanggapan WHERE id_pengaduan = '$id_pengaduan'";
        mysqli_query($conn, $delete_tanggapan_query);
        
        $delete_query = "DELETE FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'";
        if (mysqli_query($conn, $delete_query)) {
            if (!empty($foto) && file_exists("uploads/" . $foto)) {
                unlink("uploads/" . $foto);
            }
            
            $_SESSION['success'] = "Pengaduan berhasil dihapus.";
            header("Location: pengaduan_saya.php");
            exit();
        } else {
            $_SESSION['error'] = "Gagal menghapus pengaduan: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Pengaduan tidak ditemukan atau Anda tidak memiliki akses.";
    }
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'];
$user_nama = $_SESSION['nama'];
$user_level = $_SESSION['level'];

$query = "SELECT p.*, 
          (SELECT COUNT(*) FROM tanggapan t WHERE t.id_pengaduan = p.id_pengaduan) as jumlah_tanggapan
          FROM pengaduan p 
          WHERE p.email_pelapor = '$user_email' 
          ORDER BY p.id_pengaduan ASC";
$result = mysqli_query($conn, $query);

$pengaduan_list = [];
$nomor = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $row['nomor_urut'] = $nomor++;
    $pengaduan_list[] = $row;
}

$pengaduan_list = array_reverse($pengaduan_list);
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-800"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-800"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Pengaduan Saya</h1>
            <p class="text-gray-600">Daftar pengaduan yang telah Anda ajukan</p>
        </div>
        
        <?php if (count($pengaduan_list) > 0): ?>
        <div class="space-y-6">
            <?php foreach ($pengaduan_list as $pengaduan): 
                $id_pengaduan = $pengaduan['id_pengaduan'];
                $nomor_pengaduan = $pengaduan['nomor_urut'];
                $query_tanggapan = "SELECT t.*, u.nama as petugas_nama 
                                    FROM tanggapan t 
                                    JOIN users u ON t.id_petugas = u.id 
                                    WHERE t.id_pengaduan = $id_pengaduan 
                                    ORDER BY t.tanggal_tanggapan DESC";
                $result_tanggapan = mysqli_query($conn, $query_tanggapan);
                $jumlah_tanggapan = mysqli_num_rows($result_tanggapan);
                
                $query_instansi = "SELECT i.nama_instansi, i.ikon 
                                   FROM pengaduan_instansi pi 
                                   JOIN instansi i ON pi.id_instansi = i.id_instansi 
                                   WHERE pi.id_pengaduan = $id_pengaduan";
                $result_instansi = mysqli_query($conn, $query_instansi);
                $instansi_list = [];
                while ($inst = mysqli_fetch_assoc($result_instansi)) {
                    $instansi_list[] = $inst;
                }
            ?>
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Pengaduan #<?php echo $nomor_pengaduan; ?></h3>
                            <p class="text-sm text-gray-500">Diajukan pada <?php echo date('d F Y', strtotime($pengaduan['tanggal_pengaduan'])); ?></p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <?php
                            $status_class = '';
                            $status_text = '';
                            switch ($pengaduan['status']) {
                                case '0':
                                    $status_class = 'bg-yellow-100 text-yellow-800';
                                    $status_text = 'Belum Diproses';
                                    break;
                                case 'proses':
                                    $status_class = 'bg-orange-100 text-orange-800';
                                    $status_text = 'Dalam Proses';
                                    break;
                                case 'selesai':
                                    $status_class = 'bg-green-100 text-green-800';
                                    $status_text = 'Selesai';
                                    break;
                            }
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                            
                            <?php if ($pengaduan['status'] == '0' || $pengaduan['status'] == 'proses'): ?>
                                <button onclick="showDeleteModal(<?php echo $pengaduan['id_pengaduan']; ?>)" 
                                        class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50 transition-colors"
                                        title="Hapus Pengaduan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4">
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Isi Laporan:</h4>
                        <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($pengaduan['isi_laporan'])); ?></p>
                    </div>
                    
                    <?php if (!empty($pengaduan['lokasi'])): ?>
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Lokasi Kejadian:</h4>
                        <p class="text-gray-900 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <?php echo htmlspecialchars($pengaduan['lokasi']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($pengaduan['foto'])): ?>
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Foto Bukti:</h4>
                        <img src="uploads/<?php echo htmlspecialchars($pengaduan['foto']); ?>" 
                             alt="Foto Pengaduan" 
                             class="max-w-md h-auto rounded-lg border border-gray-200 cursor-pointer mx-auto block"
                             onclick="openImageModal('<?php echo htmlspecialchars($pengaduan['foto']); ?>')">
                    </div>
                    <?php endif; ?>
                    
                    <?php if (count($instansi_list) > 0): ?>
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Ditangani oleh:
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($instansi_list as $inst): ?>
                            <span class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium border border-blue-200">
                                <span class="mr-1"><?php echo $inst['ikon']; ?></span>
                                <?php echo htmlspecialchars($inst['nama_instansi']); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">
                            Tanggapan (<?php echo $jumlah_tanggapan; ?>)
                        </h4>
                        
                        <?php if ($jumlah_tanggapan > 0): ?>
                            <div class="space-y-4">
                                <?php while ($tanggapan = mysqli_fetch_assoc($result_tanggapan)): ?>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($tanggapan['petugas_nama']); ?></p>
                                            <p class="text-xs text-gray-500">Petugas</p>
                                        </div>
                                        <p class="text-sm text-gray-500"><?php echo date('d F Y', strtotime($tanggapan['tanggal_tanggapan'])); ?></p>
                                    </div>
                                    <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($tanggapan['tanggapan'])); ?></p>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Belum ada tanggapan dari petugas.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Belum ada pengaduan</h3>
            <p class="mt-2 text-sm text-gray-500">Anda belum mengajukan pengaduan apapun.</p>
            <div class="mt-6">
                <a href="pengaduan_baru.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajukan Pengaduan Pertama
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div id="imageModal" class="modal hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl max-w-4xl w-auto">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Foto Pengaduan</h3>
                    <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6 flex justify-center">
                <img id="modalImage" src="" alt="Foto Pengaduan" class="max-w-full max-h-[70vh] h-auto rounded-lg">
            </div>
        </div>
    </div>
    
    <div id="deleteModal" class="modal hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center mb-6">
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-center text-gray-700 mb-6">Apakah Anda yakin ingin menghapus pengaduan ini? Tindakan ini tidak dapat dibatalkan.</p>
                <form id="deleteForm" method="POST" action="">
                    <input type="hidden" name="id_pengaduan" id="deletePengaduanId">
                    <input type="hidden" name="delete_pengaduan" value="1">
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideDeleteModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>