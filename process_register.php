<?php
session_start();
require_once 'config/db.php'; // Pastikan path ini benar

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan bersihkan input
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // 1. Validasi Input Dasar
    if (empty($email) || empty($username) || empty($phone) || empty($password) || empty($password_confirm)) {
        $_SESSION['error_message'] = "Semua kolom wajib diisi.";
        header('Location: register.php');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Format email tidak valid.";
        header('Location: register.php');
        exit;
    }
    $pattern = '/^(\+62|62|0)8[0-9]{8,15}$/';
    if (!preg_match($pattern, $phone)) {
        $_SESSION['error_message'] = "Format nomor telepon tidak valid. Gunakan format 08..., +628..., atau 628...";
        header('Location: register.php');
        exit;
    }
    if (strlen($password) < 6) {
        $_SESSION['error_message'] = "Password minimal harus 6 karakter.";
        header('Location: register.php');
        exit;
    }
    if ($password !== $password_confirm) {
        $_SESSION['error_message'] = "Konfirmasi password tidak cocok.";
        header('Location: register.php');
        exit;
    }

    try {
        // 2. Cek duplikasi (dibuat lebih spesifik untuk pesan error yang lebih baik)
        // Cek email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Email sudah terdaftar. Silakan gunakan email lain.";
            header('Location: register.php');
            exit;
        }

        // Cek username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Username sudah digunakan. Silakan pilih username lain.";
            header('Location: register.php');
            exit;
        }

        // PERBAIKAN: Cek nomor telepon jika perlu unik juga
        $stmt = $pdo->prepare("SELECT id FROM users WHERE nomor = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Nomor telepon sudah terdaftar.";
            header('Location: register.php');
            exit;
        }

        // 3. Hash Password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // 4. Simpan ke Database
        // PERBAIKAN KRITIS: Sesuaikan kolom, jumlah placeholder (?), dan data yang dieksekusi
        $stmt = $pdo->prepare("INSERT INTO users (email, username, nomor, password, role) VALUES (?, ?, ?, ?, 'customer')");
        
        // PERBAIKAN KRITIS: Urutan dan jumlah data harus sesuai dengan placeholder di atas
        if ($stmt->execute([$email, $username, $phone, $hashed_password])) {
            $_SESSION['success_message'] = "Pendaftaran berhasil! Silakan login dengan akun Anda.";
            header('Location: login.php');
            exit;
        } else {
            $_SESSION['error_message'] = "Terjadi kesalahan saat menyimpan data.";
            header('Location: register.php');
            exit;
        }
    } catch (PDOException $e) {
        // error_log("Registration error: " . $e->getMessage()); // Untuk debugging
        $_SESSION['error_message'] = "Terjadi kesalahan pada database. Silakan coba lagi.";
        header('Location: register.php');
        exit;
    }
} else {
    header('Location: register.php');
    exit;
}
?>