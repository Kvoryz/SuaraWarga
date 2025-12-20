<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/function.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_nama = $_SESSION['nama'];
$user_level = $_SESSION['level'];
$user_email = $_SESSION['email'];

$filter_type = isset($_GET['type']) ? $_GET['type'] : 'semua';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

$query_pengaduan = "SELECT 
    p.id_pengaduan,
    p.tanggal_pengaduan,
    p.isi_laporan,
    p.status,
    u.nama as pelapor_nama,
    'pengaduan' as tipe,
    p.tanggal_pengaduan as waktu
    FROM pengaduan p 
    JOIN users u ON p.email_pelapor = u.email";

if ($filter_date) {
    $query_pengaduan .= " WHERE p.tanggal_pengaduan = '$filter_date'";
}

$query_users = "SELECT 
    id,
    nama,
    email,
    level,
    created_at,
    'user_baru' as tipe,
    created_at as waktu
    FROM users";

if ($filter_date) {
    $query_users .= " WHERE DATE(created_at) = '$filter_date'";
}

$query_tanggapan = "SELECT 
    t.id_tanggapan,
    t.tanggal_tanggapan,
    t.tanggapan,
    t.id_pengaduan,
    u.nama as petugas_nama,
    'tanggapan' as tipe,
    t.tanggal_tanggapan as waktu
    FROM tanggapan t 
    JOIN users u ON t.id_petugas = u.id";

if ($filter_date) {
    $query_tanggapan .= " WHERE t.tanggal_tanggapan = '$filter_date'";
}

$result_pengaduan = mysqli_query($conn, $query_pengaduan);
$result_users = mysqli_query($conn, $query_users);
$result_tanggapan = mysqli_query($conn, $query_tanggapan);

$activities = [];

while ($row = mysqli_fetch_assoc($result_pengaduan)) {
    $activities[] = [
        'tipe' => 'pengaduan',
        'waktu' => $row['tanggal_pengaduan'],
        'judul' => 'Pengaduan Baru #' . $row['id_pengaduan'],
        'deskripsi' => substr($row['isi_laporan'], 0, 100) . (strlen($row['isi_laporan']) > 100 ? '...' : ''),
        'user' => $row['pelapor_nama'],
        'status' => $row['status'],
        'icon' => 'pengaduan',
        'link' => 'laporan.php'
    ];
}

while ($row = mysqli_fetch_assoc($result_users)) {
    $activities[] = [
        'tipe' => 'user_baru',
        'waktu' => $row['created_at'],
        'judul' => 'User Baru Terdaftar',
        'deskripsi' => $row['nama'] . ' (' . $row['email'] . ')',
        'user' => $row['nama'],
        'level' => $row['level'],
        'icon' => 'user',
        'link' => 'kelola_user.php'
    ];
}

while ($row = mysqli_fetch_assoc($result_tanggapan)) {
    $activities[] = [
        'tipe' => 'tanggapan',
        'waktu' => $row['tanggal_tanggapan'],
        'judul' => 'Tanggapan pada Pengaduan #' . $row['id_pengaduan'],
        'deskripsi' => substr($row['tanggapan'], 0, 100) . (strlen($row['tanggapan']) > 100 ? '...' : ''),
        'user' => $row['petugas_nama'],
        'icon' => 'tanggapan',
        'link' => 'laporan.php'
    ];
}

if ($filter_type != 'semua') {
    $activities = array_filter($activities, function($item) use ($filter_type) {
        return $item['tipe'] == $filter_type;
    });
}

usort($activities, function($a, $b) {
    return strtotime($b['waktu']) - strtotime($a['waktu']);
});

$total_pengaduan = mysqli_num_rows(mysqli_query($conn, "SELECT id_pengaduan FROM pengaduan"));
$total_users = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));
$total_tanggapan = mysqli_num_rows(mysqli_query($conn, "SELECT id_tanggapan FROM tanggapan"));

$today = date('Y-m-d');
$pengaduan_hari_ini = mysqli_num_rows(mysqli_query($conn, "SELECT id_pengaduan FROM pengaduan WHERE tanggal_pengaduan = '$today'"));
$user_hari_ini = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE DATE(created_at) = '$today'"));
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
        <div class="mb-8 flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Activity Logs</h1>
                <p class="text-gray-600">Pantau semua aktivitas terbaru di sistem</p>
            </div>
            <a href="export/export_logs.php?type=<?php echo $filter_type; ?>&date=<?php echo $filter_date; ?>" 
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Pengaduan</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_pengaduan; ?></p>
                        <p class="text-xs text-green-600 mt-1">+<?php echo $pengaduan_hari_ini; ?> hari ini</p>
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
                        <p class="text-sm text-gray-500 mb-1">Total Pengguna</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_users; ?></p>
                        <p class="text-xs text-green-600 mt-1">+<?php echo $user_hari_ini; ?> hari ini</p>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Tanggapan</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $total_tanggapan; ?></p>
                    </div>
                    <div class="bg-purple-50 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Aktivitas</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($activities); ?></p>
                    </div>
                    <div class="bg-orange-50 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Aktivitas</h2>
            <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Aktivitas</label>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <option value="semua" <?php echo $filter_type == 'semua' ? 'selected' : ''; ?>>Semua Aktivitas</option>
                        <option value="pengaduan" <?php echo $filter_type == 'pengaduan' ? 'selected' : ''; ?>>Pengaduan Baru</option>
                        <option value="user_baru" <?php echo $filter_type == 'user_baru' ? 'selected' : ''; ?>>User Baru</option>
                        <option value="tanggapan" <?php echo $filter_type == 'tanggapan' ? 'selected' : ''; ?>>Tanggapan</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="date" value="<?php echo $filter_date; ?>"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
                
                <div class="flex items-end">
                    <div class="flex space-x-2 w-full">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition w-full">
                            Terapkan Filter
                        </button>
                        <a href="logs.php"
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
                    <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
                    <div class="text-sm text-gray-500">
                        <?php echo count($activities); ?> aktivitas
                    </div>
                </div>
            </div>
            
            <?php if (count($activities) > 0): ?>
            <div class="divide-y divide-gray-200">
                <?php 
                $count = 0;
                foreach ($activities as $activity): 
                    if ($count >= 50) break; 
                    $count++;
                    
                    $icon_bg = '';
                    $icon_color = '';
                    $icon_svg = '';
                    
                    switch ($activity['tipe']) {
                        case 'pengaduan':
                            $icon_bg = 'bg-blue-100';
                            $icon_color = 'text-blue-600';
                            $icon_svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>';
                            break;
                        case 'user_baru':
                            $icon_bg = 'bg-green-100';
                            $icon_color = 'text-green-600';
                            $icon_svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>';
                            break;
                        case 'tanggapan':
                            $icon_bg = 'bg-purple-100';
                            $icon_color = 'text-purple-600';
                            $icon_svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>';
                            break;
                    }
                    
                    
                    $waktu = strtotime($activity['waktu']);
                    $waktu_formatted = date('d M Y, H:i', $waktu);
                    $waktu_relative = '';
                    
                    $now = time();
                    $diff = $now - $waktu;
                    
                    if ($diff < 0) {
                        $waktu_relative = $waktu_formatted;
                    } elseif ($diff < 60) {
                        $waktu_relative = 'Baru saja';
                    } elseif ($diff < 3600) {
                        $waktu_relative = floor($diff / 60) . ' menit lalu';
                    } elseif ($diff < 86400) {
                        $waktu_relative = floor($diff / 3600) . ' jam lalu';
                    } elseif ($diff < 604800) {
                        $waktu_relative = floor($diff / 86400) . ' hari lalu';
                    } else {
                        $waktu_relative = $waktu_formatted;
                    }
                ?>
                <div class="px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-start space-x-4">
                        <div class="<?php echo $icon_bg; ?> p-2 rounded-lg flex-shrink-0">
                            <svg class="w-5 h-5 <?php echo $icon_color; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?php echo $icon_svg; ?>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($activity['judul']); ?></p>
                                <span class="text-xs text-gray-500"><?php echo $waktu_relative; ?></span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($activity['deskripsi']); ?></p>
                            <div class="flex items-center mt-2 space-x-3">
                                <span class="text-xs text-gray-500">
                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <?php echo htmlspecialchars($activity['user']); ?>
                                </span>
                                <?php if (isset($activity['status'])): ?>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($activity['status']) {
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
                                    <span class="px-2 py-0.5 text-xs rounded-full <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                <?php endif; ?>
                                <?php if (isset($activity['level'])): ?>
                                    <?php
                                    $level_class = '';
                                    switch ($activity['level']) {
                                        case 'admin':
                                            $level_class = 'bg-purple-100 text-purple-800';
                                            break;
                                        case 'petugas':
                                            $level_class = 'bg-blue-100 text-blue-800';
                                            break;
                                        default:
                                            $level_class = 'bg-gray-100 text-gray-800';
                                    }
                                    ?>
                                    <span class="px-2 py-0.5 text-xs rounded-full <?php echo $level_class; ?>"><?php echo ucfirst($activity['level']); ?></span>
                                <?php endif; ?>
                                <a href="<?php echo $activity['link']; ?>" class="text-xs text-blue-600 hover:text-blue-800">Lihat Detail →</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada aktivitas</h3>
                <p class="mt-1 text-sm text-gray-500">
                    <?php echo $filter_type != 'semua' || $filter_date ? 'Coba ubah filter pencarian Anda.' : 'Belum ada aktivitas yang tercatat.'; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
