<?php
session_start();
require_once 'config/function.php';

if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: index.php");
    exit();
}

$query = "SELECT * FROM users ORDER BY level, nama";
$result = mysqli_query($conn, $query);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=users_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, array('ID', 'Nama', 'Email', 'Username', 'Telepon', 'Level', 'Tanggal Bergabung'));

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, array(
        $row['id'],
        $row['nama'],
        $row['email'],
        $row['username'],
        $row['telp'],
        $row['level'],
        date('d/m/Y H:i', strtotime($row['created_at']))
    ));
}

fclose($output);
exit();
?>