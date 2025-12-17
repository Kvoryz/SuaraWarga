<?php
require_once 'koneksi.php';

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

function uploadFoto($file) {
    if ($file['error'] != 0) {
        return ['success' => false, 'message' => 'Error upload file.'];
    }
    
    $target_dir = UPLOAD_PATH;
    
    $filename = uniqid() . '_' . basename($file["name"]);
    $target_file = $target_dir . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'File bukan gambar.'];
    }
    
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar (max 5MB).'];
    }
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'message' => 'Hanya file JPG, JPEG, PNG & GIF yang diizinkan.'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'message' => 'Terjadi kesalahan saat upload.'];
    }
}
?>