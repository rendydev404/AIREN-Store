<?php
$host = 'localhost';       // biasanya localhost
$db   = 'parfum_shop';     // nama database
$user = 'root';            // user default XAMPP/WAMP
$pass = '';                // password default XAMPP biasanya kosong

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // set error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>
