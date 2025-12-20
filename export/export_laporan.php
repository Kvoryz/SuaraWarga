<?php
session_start();
require_once '../config/function.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
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

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

fputcsv($output, array('No', 'ID', 'Tanggal', 'Pelapor', 'Email Pelapor', 'Isi Laporan', 'Lokasi', 'Status', 'Instansi', 'Jumlah Tanggapan', 'Foto'));

$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $status_text = '';
    switch ($row['status']) {
        case '0':
            $status_text = 'Belum Diproses';
            break;
        case 'proses':
            $status_text = 'Dalam Proses';
            break;
        case 'selesai':
            $status_text = 'Selesai';
            break;
        default:
            $status_text = $row['status'];
    }
    
    $isi_laporan = strip_tags($row['isi_laporan']);
    if (strlen($isi_laporan) > 200) {
        $isi_laporan = substr($isi_laporan, 0, 200) . '...';
    }
    
    $id_pengaduan = $row['id_pengaduan'];
    $query_instansi = "SELECT i.nama_instansi 
                       FROM pengaduan_instansi pi 
                       JOIN instansi i ON pi.id_instansi = i.id_instansi 
                       WHERE pi.id_pengaduan = $id_pengaduan";
    $result_instansi = mysqli_query($conn, $query_instansi);
    $instansi_names = [];
    while ($inst = mysqli_fetch_assoc($result_instansi)) {
        $instansi_names[] = $inst['nama_instansi'];
    }
    $instansi_text = count($instansi_names) > 0 ? implode(', ', $instansi_names) : '-';
    
    fputcsv($output, array(
        $no++,
        $row['id_pengaduan'],
        date('d/m/Y', strtotime($row['tanggal_pengaduan'])),
        $row['pelapor_nama'],
        $row['pelapor_email'],
        $isi_laporan,
        $row['lokasi'] ?? '-',
        $status_text,
        $instansi_text,
        $row['jumlah_tanggapan'],
        $row['foto'] ?: '-'
    ));
}

fclose($output);
exit();
?>