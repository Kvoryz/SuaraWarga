<?php
session_start();
require_once 'config/function.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $telp = mysqli_real_escape_string($conn, $_POST['telp']);
    $password = $_POST['password'];
    
    if (empty($nama) || empty($email) || empty($username)) {
        $error = "Nama, email, dan username wajib diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid";
    } else {
        $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND id != $user_id");
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email sudah digunakan oleh pengguna lain";
        } else {
            $check_username = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' AND id != $user_id");
            if (mysqli_num_rows($check_username) > 0) {
                $error = "Username sudah digunakan oleh pengguna lain";
            } else {
                $sql = "UPDATE users SET 
                        nama = '$nama',
                        email = '$email',
                        username = '$username',
                        telp = '$telp'";
                
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $error = "Password minimal 6 karakter";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $sql .= ", password = '$hashed_password'";
                    }
                }
                
                $sql .= " WHERE id = $user_id";
                
                if (empty($error)) {
                    if (mysqli_query($conn, $sql)) {
                        $_SESSION['nama'] = $nama;
                        $_SESSION['email'] = $email;
                        $_SESSION['username'] = $username;
                        
                        $success = "Profil berhasil diperbarui";
                        $_SESSION['success_message'] = $success;
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = "Terjadi kesalahan: " . mysqli_error($conn);
                    }
                }
            }
        }
    }
    
    if (!empty($error)) {
        $_SESSION['error_message'] = $error;
        header("Location: dashboard.php");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>