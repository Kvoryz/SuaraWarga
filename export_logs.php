<?php
session_start();
require_once 'config/function.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

date_default_timezone_set('Asia/Makassar');

$filter_type = isset($_GET['type']) ? $_GET['type'] : 'semua';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

$query_pengaduan = "SELECT 
    p.id_pengaduan,
    p.tanggal_pengaduan,
    p.isi_laporan,
    p.status,
    u.nama as pelapor_nama,
    'Pengaduan Baru' as tipe,
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
    'User Baru' as tipe,
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
    'Tanggapan' as tipe,
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
        'tipe' => 'Pengaduan Baru',
        'waktu' => $row['tanggal_pengaduan'],
        'deskripsi' => 'Pengaduan #' . $row['id_pengaduan'] . ' - ' . substr($row['isi_laporan'], 0, 100),
        'user' => $row['pelapor_nama'],
        'status' => $row['status']
    ];
}

while ($row = mysqli_fetch_assoc($result_users)) {
    $activities[] = [
        'tipe' => 'User Baru',
        'waktu' => $row['created_at'],
        'deskripsi' => $row['nama'] . ' (' . $row['email'] . ')',
        'user' => $row['nama'],
        'status' => ucfirst($row['level'])
    ];
}

while ($row = mysqli_fetch_assoc($result_tanggapan)) {
    $activities[] = [
        'tipe' => 'Tanggapan',
        'waktu' => $row['tanggal_tanggapan'],
        'deskripsi' => 'Tanggapan pada Pengaduan #' . $row['id_pengaduan'] . ' - ' . substr($row['tanggapan'], 0, 100),
        'user' => $row['petugas_nama'],
        'status' => '-'
    ];
}

if ($filter_type != 'semua') {
    $type_map = [
        'pengaduan' => 'Pengaduan Baru',
        'user_baru' => 'User Baru',
        'tanggapan' => 'Tanggapan'
    ];
    $filter_label = $type_map[$filter_type] ?? '';
    $activities = array_filter($activities, function($item) use ($filter_label) {
        return $item['tipe'] == $filter_label;
    });
}

usort($activities, function($a, $b) {
    return strtotime($b['waktu']) - strtotime($a['waktu']);
});

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=activity_logs_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

fputcsv($output, array('No', 'Tipe Aktivitas', 'Tanggal/Waktu', 'Deskripsi', 'User', 'Status'));

$no = 1;
foreach ($activities as $activity) {
    fputcsv($output, array(
        $no++,
        $activity['tipe'],
        date('d/m/Y H:i', strtotime($activity['waktu'])),
        $activity['deskripsi'],
        $activity['user'],
        $activity['status']
    ));
}

fclose($output);
exit();
?>
