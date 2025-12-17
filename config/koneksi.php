<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "masyarakat";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

define('UPLOAD_PATH', 'uploads/');

if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}
?>