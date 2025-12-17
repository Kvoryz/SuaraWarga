<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/function.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['level'] != 'petugas' && $_SESSION['level'] != 'admin')) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_nama = $_SESSION['nama'];
$user_level = $_SESSION['level'];
$user_email = $_SESSION['email'];

$success = '';
$error = '';

date_default_timezone_set('Asia/Makassar');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_tanggapan'])) {
    $id_pengaduan = mysqli_real_escape_string($conn, $_POST['id_pengaduan']);
    $tanggapan = mysqli_real_escape_string($conn, $_POST['tanggapan']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $tanggal_tanggapan = date('Y-m-d H:i:s');
    
    $sql_status = "UPDATE pengaduan SET status = '$status' WHERE id_pengaduan = $id_pengaduan";
    
    if (mysqli_query($conn, $sql_status)) {
        if (!empty($tanggapan)) {
            $sql_tanggapan = "INSERT INTO tanggapan (id_pengaduan, tanggal_tanggapan, tanggapan, id_petugas) 
                              VALUES ($id_pengaduan, '$tanggal_tanggapan', '$tanggapan', $user_id)";
            
            if (mysqli_query($conn, $sql_tanggapan)) {
                $success = "Status diperbarui dan tanggapan ditambahkan";
            } else {
                $error = "Status diperbarui, tapi gagal menambahkan tanggapan: " . mysqli_error($conn);
            }
        } else {
            $success = "Status pengaduan berhasil diperbarui";
        }
    } else {
        $error = "Gagal memperbarui status: " . mysqli_error($conn);
    }
}

if (isset($_GET['hapus'])) {
    if ($user_level == 'admin' || $user_level == 'petugas') {
        $id_pengaduan = mysqli_real_escape_string($conn, $_GET['hapus']);
        
        $sql_delete_tanggapan = "DELETE FROM tanggapan WHERE id_pengaduan = $id_pengaduan";
        mysqli_query($conn, $sql_delete_tanggapan);
        
        $sql_delete_pengaduan = "DELETE FROM pengaduan WHERE id_pengaduan = $id_pengaduan";
        
        if (mysqli_query($conn, $sql_delete_pengaduan)) {
            $success = "Pengaduan berhasil dihapus";
        } else {
            $error = "Gagal menghapus pengaduan: " . mysqli_error($conn);
        }
    } else {
        $error = "Hanya admin dan petugas yang dapat menghapus pengaduan";
    }

    header("Location: laporan.php?status=$filter_status&date=$filter_date&search=" . urlencode($filter_search));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_delete'])) {
    if ($user_level == 'admin' || $user_level == 'petugas') {
        if (isset($_POST['selected_ids']) && is_array($_POST['selected_ids']) && count($_POST['selected_ids']) > 0) {
            $deleted_count = 0;
            foreach ($_POST['selected_ids'] as $id) {
                $id = mysqli_real_escape_string($conn, $id);
                
                $sql_delete_tanggapan = "DELETE FROM tanggapan WHERE id_pengaduan = $id";
                mysqli_query($conn, $sql_delete_tanggapan);
                
                $sql_delete_pengaduan = "DELETE FROM pengaduan WHERE id_pengaduan = $id";
                if (mysqli_query($conn, $sql_delete_pengaduan)) {
                    $deleted_count++;
                }
            }
            $success = "$deleted_count pengaduan berhasil dihapus";
        } else {
            $error = "Pilih minimal satu pengaduan untuk dihapus";
        }
    } else {
        $error = "Hanya admin dan petugas yang dapat menghapus pengaduan";
    }
}

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$filter_search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT p.*, u.nama as pelapor_nama, u.email as pelapor_email,
          (SELECT COUNT(*) FROM tanggapan t WHERE t.id_pengaduan = p.id_pengaduan) as jumlah_tanggapan
          FROM pengaduan p 
          JOIN users u ON p.email_pelapor = u.email 
          WHERE 1=1";

if ($filter_status && $filter_status != 'semua') {
    $query .= " AND p.status = '$filter_status'";
}

if ($filter_date) {
    $query .= " AND p.tanggal_pengaduan = '$filter_date'";
}

if ($filter_search) {
    $search = mysqli_real_escape_string($conn, $filter_search);
    $query .= " AND (p.isi_laporan LIKE '%$search%' OR u.nama LIKE '%$search%' OR u.email LIKE '%$search%')";
}

$query .= " ORDER BY p.tanggal_pengaduan DESC, p.id_pengaduan DESC";

$result = mysqli_query($conn, $query);

$query_stats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = '0' THEN 1 ELSE 0 END) as belum_diproses,
                SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as dalam_proses,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
                FROM pengaduan";

$stats_result = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50 min-h-screen">
    
    <?php include 'layout/navbar.php'; ?>
    
    <div id="confirmationModal" class="modal">
        <div class="modal-content confirmation-modal">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
                    <button onclick="closeConfirmationModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-6">
                <div class="flex items-center mb-4">
                    <div class="bg-red-100 p-3 rounded-full mr-3">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">Apakah Anda yakin?</h4>
                        <p class="text-sm text-gray-500 mt-1">Pengaduan dan semua tanggapannya akan dihapus permanen.</p>
                    </div>
                </div>
                
                <div class="text-sm text-gray-600 mb-4 p-3 bg-gray-50 rounded-lg">
                    <p><strong>ID:</strong> <span id="delete_id"></span></p>
                    <p><strong>Pelapor:</strong> <span id="delete_pelapor"></span></p>
                    <p><strong>Tanggal:</strong> <span id="delete_tanggal"></span></p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeConfirmationModal()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <a id="confirmDeleteBtn" href="#"
                       class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                        Ya, Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Laporan Pengaduan</h1>
                <p class="text-gray-600">Kelola dan tanggapi semua pengaduan masyarakat</p>
            </div>
                <?php if ($user_level == 'admin'): ?>
                    <a href="export_laporan.php?status=<?php echo urlencode($filter_status); ?>&date=<?php echo urlencode($filter_date); ?>&search=<?php echo urlencode($filter_search); ?>" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export CSV
                    </a>
                <?php endif; ?>
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
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Pengaduan</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total']; ?></p>
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
                        <p class="text-sm text-gray-500 mb-1">Belum Diproses</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['belum_diproses']; ?></p>
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
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['dalam_proses']; ?></p>
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
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['selesai']; ?></p>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Laporan</h2>
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <option value="semua">Semua Status</option>
                        <option value="0" <?php echo $filter_status === '0' ? 'selected' : ''; ?>>Belum Diproses</option>
                        <option value="proses" <?php echo $filter_status === 'proses' ? 'selected' : ''; ?>>Dalam Proses</option>
                        <option value="selesai" <?php echo $filter_status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="date" value="<?php echo $filter_date; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($filter_search); ?>"
                           placeholder="Cari laporan atau pelapor..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
                
                <div class="flex items-end">
                    <div class="flex space-x-2 w-full">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition w-full">
                            Terapkan Filter
                        </button>
                        <a href="laporan.php"
                           class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <h3 class="text-lg font-semibold text-gray-900">Daftar Pengaduan</h3>
                        <span id="selectedCount" class="text-sm text-gray-500 hidden">
                            (<span id="selectedNum">0</span> dipilih)
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" form="bulkDeleteForm" name="bulk_delete" id="btnBulkDelete"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition hidden items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus Terpilih
                        </button>
                        <div class="text-sm text-gray-500">
                            <?php echo mysqli_num_rows($result); ?> pengaduan ditemukan
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (mysqli_num_rows($result) > 0): ?>
            <form id="bulkDeleteForm" method="POST" action="">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelapor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laporan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggapan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            $id_pengaduan = $row['id_pengaduan'];
                            $query_tanggapan = "SELECT t.*, u.nama as petugas_nama 
                                                FROM tanggapan t 
                                                JOIN users u ON t.id_petugas = u.id 
                                                WHERE t.id_pengaduan = $id_pengaduan 
                                                ORDER BY t.tanggal_tanggapan DESC 
                                                LIMIT 1";
                            $result_tanggapan = mysqli_query($conn, $query_tanggapan);
                            $tanggapan_terakhir = mysqli_fetch_assoc($result_tanggapan);
                        ?>
                        <tr>
                            <td class="px-4 py-4">
                                <input type="checkbox" name="selected_ids[]" value="<?php echo $row['id_pengaduan']; ?>" 
                                       class="row-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">#<?php echo $row['id_pengaduan']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($row['tanggal_pengaduan'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['pelapor_nama']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['pelapor_email']); ?></div>
                            </td>
                            <td class="px-6 py-4" style="max-width: 250px;">
                                <div class="text-sm text-gray-900 overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; word-break: break-word;"><?php echo htmlspecialchars(substr($row['isi_laporan'], 0, 150)); ?><?php echo strlen($row['isi_laporan']) > 150 ? '...' : ''; ?></div>
                            </td>
                            <td class="px-6 py-4" style="max-width: 150px;">
                                <div class="text-sm text-gray-900 overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; word-break: break-word;">
                                    <?php echo !empty($row['lokasi']) ? htmlspecialchars($row['lokasi']) : '<span class="text-gray-400">-</span>'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $status_class = '';
                                $status_text = '';
                                switch ($row['status']) {
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
                            </td>
                            <td class="px-6 py-4" style="max-width: 200px; min-width: 150px;">
                                <?php if ($row['jumlah_tanggapan'] > 0 && $tanggapan_terakhir): ?>
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900 mb-1"><?php echo htmlspecialchars($tanggapan_terakhir['petugas_nama']); ?></div>
                                        <div class="text-gray-500" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: break-word;"><?php echo htmlspecialchars(substr($tanggapan_terakhir['tanggapan'], 0, 100)); ?><?php echo strlen($tanggapan_terakhir['tanggapan']) > 100 ? '...' : ''; ?></div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-sm text-gray-500">Belum ada tanggapan</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button" onclick="openDetailModal(
                                    <?php echo $row['id_pengaduan']; ?>,
                                    '<?php echo date('d F Y', strtotime($row['tanggal_pengaduan'])); ?>',
                                    '<?php echo addslashes($row['pelapor_nama']); ?>',
                                    '<?php echo addslashes($row['pelapor_email']); ?>',
                                    '<?php echo addslashes($row['isi_laporan']); ?>',
                                    '<?php echo $row['status']; ?>',
                                    '<?php echo $row['foto'] ? addslashes($row['foto']) : ''; ?>',
                                    '<?php echo addslashes($row['lokasi'] ?? ''); ?>'
                                )" class="text-blue-600 hover:text-blue-900 mr-3">Detail</button>
                                
                                <button type="button" onclick="openTanggapanModal(<?php echo $row['id_pengaduan']; ?>, '<?php echo $row['status']; ?>')" 
                                        class="text-green-600 hover:text-green-900 mr-3">Tanggapi</button>
                                
                                <?php if ($user_level == 'admin' || $user_level == 'petugas'): ?>
                                <button type="button" onclick="confirmDeletePengaduan(
                                    <?php echo $row['id_pengaduan']; ?>,
                                    '<?php echo addslashes($row['pelapor_nama']); ?>',
                                    '<?php echo date('d F Y', strtotime($row['tanggal_pengaduan'])); ?>'
                                )" 
                                        class="text-red-600 hover:text-red-900">Hapus</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            </form>
            <?php else: ?>
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada pengaduan</h3>
                <p class="mt-1 text-sm text-gray-500">
                    <?php echo $filter_status || $filter_date || $filter_search ? 'Coba ubah filter pencarian Anda.' : 'Belum ada pengaduan yang diajukan.'; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Detail Modal -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Pengaduan</h3>
                    <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">ID Pengaduan</label>
                        <div class="text-sm font-medium text-gray-900" id="detail_id"></div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal</label>
                        <div class="text-sm text-gray-900" id="detail_tanggal"></div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Pelapor</label>
                        <div class="text-sm font-medium text-gray-900" id="detail_pelapor"></div>
                        <div class="text-sm text-gray-500" id="detail_email"></div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                        <div class="text-sm">
                            <span id="detail_status_badge" class="status-badge"></span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Isi Laporan</label>
                        <div class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg whitespace-pre-line" id="detail_laporan"></div>
                    </div>
                    
                    <div id="detail_lokasi_container">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Lokasi Kejadian</label>
                        <div class="text-sm text-gray-900 flex items-center" id="detail_lokasi">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span id="detail_lokasi_text"></span>
                        </div>
                    </div>
                    
                    <div id="detail_foto_container" style="display: none;">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Foto</label>
                        <div class="mt-2">
                            <img id="detail_foto" src="" alt="Foto Pengaduan" class="max-w-full h-auto rounded-lg border border-gray-200">
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closeDetailModal()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tanggapan Modal -->
    <div id="tanggapanModal" class="modal">
        <div class="modal-content">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Berikan Tanggapan</h3>
                    <button onclick="closeTanggapanModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form method="POST" action="">
                <div class="px-6 py-6">
                    <input type="hidden" name="id_pengaduan" id="tanggapan_id_pengaduan">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Pengaduan</label>
                        <select name="status" id="tanggapan_status" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <option value="0">Belum Diproses</option>
                            <option value="proses">Dalam Proses</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggapan Anda (Opsional)</label>
                        <textarea name="tanggapan" id="tanggapan_text" rows="6"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none"
                                  placeholder="Tuliskan tanggapan atau tindakan yang telah diambil (opsional)..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeTanggapanModal()"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit" name="tambah_tanggapan"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            Simpan Tanggapan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>