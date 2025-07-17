<?php
// File: notification_handler.php

// Pastikan Anda sudah menjalankan 'composer require midtrans/midtrans-php'
require_once dirname(__FILE__) . '/vendor/autoload.php';

// Pastikan file konfigurasi ini ada.
require_once dirname(__FILE__) . '/config-midtrans.php';

// Mengatur konfigurasi Midtrans
\Midtrans\Config::$serverKey = MIDTRANS_SERVER_KEY;
\Midtrans\Config::$isProduction = MIDTRANS_IS_PRODUCTION;

// Membuat objek notifikasi dari Midtrans
try {
    $notif = new \Midtrans\Notification();
} catch (Exception $e) {
    // Jika gagal membuat objek notifikasi (misalnya karena data tidak valid)
    error_log("Gagal membuat objek notifikasi Midtrans: " . $e->getMessage());
    http_response_code(400); // Bad Request
    exit('Invalid notification');
}

// Mengambil detail dari notifikasi
$order_id = $notif->order_id;
$transaction_status = $notif->transaction_status;
$payment_type = $notif->payment_type;
$fraud_status = $notif->fraud_status;

// Membuat log untuk debugging. Ini akan membuat file 'payment_log.txt'
// di folder yang sama untuk mencatat setiap notifikasi yang masuk.
$log_message = "Notifikasi Diterima | Order ID: $order_id | Status: $transaction_status | Tipe Pembayaran: $payment_type | Status Fraud: $fraud_status\n";
file_put_contents('payment_log.txt', $log_message, FILE_APPEND);

// Logika untuk menangani status transaksi
// =======================================
// Di sinilah Anda akan melakukan UPDATE ke database Anda.

if ($transaction_status == 'capture') {
    // Khusus untuk transaksi kartu kredit.
    if ($payment_type == 'credit_card') {
        if ($fraud_status == 'challenge') {
            // TODO: Set status transaksi di database Anda menjadi 'CHALLENGE'.
            // Contoh: UPDATE orders SET status = 'CHALLENGE' WHERE id = '$order_id';
            file_put_contents('payment_log.txt', "-> Aksi: Transaksi $order_id berstatus 'challenge'.\n", FILE_APPEND);
        } else if ($fraud_status == 'accept') {
            // TODO: Set status transaksi di database Anda menjadi 'SUCCESS' atau 'LUNAS'.
            // Contoh: UPDATE orders SET status = 'SUCCESS' WHERE id = '$order_id';
            // TODO: Kurangi stok produk di database.
            file_put_contents('payment_log.txt', "-> Aksi: Transaksi $order_id berhasil (accept).\n", FILE_APPEND);
        }
    }
} else if ($transaction_status == 'settlement') {
    // Status ini menandakan pembayaran telah berhasil dan dana telah masuk.
    // Ini adalah status yang paling umum untuk menandai pesanan 'LUNAS'.
    
    // TODO: Set status transaksi di database Anda menjadi 'SUCCESS' atau 'LUNAS'.
    // Contoh: UPDATE orders SET status = 'SUCCESS' WHERE id = '$order_id';
    
    // TODO: Kurangi stok produk di database.
    // Anda perlu mengambil detail item dari pesanan ini dan mengurangi stoknya.
    file_put_contents('payment_log.txt', "-> Aksi: Transaksi $order_id lunas (settlement).\n", FILE_APPEND);

} else if ($transaction_status == 'pending') {
    // TODO: Set status transaksi di database Anda menjadi 'PENDING'.
    // Contoh: UPDATE orders SET status = 'PENDING' WHERE id = '$order_id';
    file_put_contents('payment_log.txt', "-> Aksi: Transaksi $order_id menunggu pembayaran.\n", FILE_APPEND);
} else if ($transaction_status == 'deny') {
    // TODO: Set status transaksi di database Anda menjadi 'DENIED' atau 'GAGAL'.
    // Contoh: UPDATE orders SET status = 'DENIED' WHERE id = '$order_id';
    file_put_contents('payment_log.txt', "-> Aksi: Transaksi $order_id ditolak.\n", FILE_APPEND);
} else if ($transaction_status == 'expire') {
    // TODO: Set status transaksi di database Anda menjadi 'EXPIRED' atau 'KADALUWARSA'.
    // Contoh: UPDATE orders SET status = 'EXPIRED' WHERE id = '$order_id';
    file_put_contents('payment_log.txt', "-> Aksi: Transaksi $order_id kadaluwarsa.\n", FILE_APPEND);
} else if ($transaction_status == 'cancel') {
    // TODO: Set status transaksi di database Anda menjadi 'CANCELLED' atau 'DIBATALKAN'.
    // Contoh: UPDATE orders SET status = 'CANCELLED' WHERE id = '$order_id';
    file_put_contents('payment_log.txt', "-> Aksi: Transaksi $order_id dibatalkan.\n", FILE_APPEND);
}

// Memberi respons '200 OK' ke Midtrans untuk menandakan notifikasi berhasil diterima.
http_response_code(200);
