<?php
// Selalu mulai session di awal
session_start();

// Hapus semua data yang tersimpan dalam session
session_unset();

// Hancurkan session
session_destroy();

// Setelah logout berhasil, alihkan pengguna kembali ke halaman utama
header("location: login.php");
exit(); // Pastikan tidak ada kode lain yang dieksekusi setelah redirect
?>