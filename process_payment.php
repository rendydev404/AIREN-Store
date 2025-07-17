<?php
// TES 2: Memeriksa file config-midtrans.php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/config-midtrans.php';
    echo json_encode(['status' => 'OK', 'test' => 2, 'message' => 'File config-midtrans.php berhasil dimuat.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'GAGAL', 'test' => 2, 'message' => $e->getMessage()]);
}
?>