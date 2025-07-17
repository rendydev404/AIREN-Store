<?php
// File: process_core_payment.php (Mode Debug + Fix)

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    session_start();
    header('Content-Type: application/json');

    require_once __DIR__ . '/config-midtrans.php';
    require_once __DIR__ . '/config/db.php';

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Anda harus login untuk melanjutkan.']);
        exit;
    }

    $request_data = json_decode(file_get_contents('php://input'), true);
    
    // Debug: Log request data
    error_log('Payment request: ' . print_r($request_data, true));

    if (!$request_data || empty($request_data['items']) || empty($request_data['payment_method'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Permintaan tidak valid, data tidak lengkap.']);
        exit;
    }

    // Validasi dan sanitasi data
    $items = [];
    $total_calculated = 0;
    
    foreach ($request_data['items'] as $item) {
        $price = isset($item['price']) ? (float)$item['price'] : 0;
        $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
        
        if ($price <= 0) {
            http_response_code(400);
            echo json_encode(['error' => "Harga produk {$item['name']} tidak valid"]);
            exit;
        }
        
        $items[] = [
            'id' => $item['id'],
            'price' => (int)$price,
            'quantity' => $quantity,
            'name' => substr($item['name'], 0, 50) // Limit nama produk
        ];
        
        $total_calculated += $price * $quantity;
    }
    
    // Validasi total
    $request_total = (float)($request_data['total'] ?? 0);
    if (abs($total_calculated - $request_total) > 1) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Total tidak sesuai', 
            'expected' => $total_calculated,
            'received' => $request_total
        ]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT username, email, nomor FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        throw new Exception("Data customer tidak ditemukan untuk user_id: " . $_SESSION['user_id']);
    }

    $order_id = 'AIREN-' . time() . '-' . $_SESSION['user_id'];
    $name_parts = explode(' ', $customer['username'], 2);

    $params = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => (int)$total_calculated
        ],
        'item_details' => $items,
        'customer_details' => [
            'first_name' => $name_parts[0],
            'last_name' => isset($name_parts[1]) ? $name_parts[1] : '',
            'email' => $customer['email'],
            'phone' => $customer['nomor'],
        ],
    ];

    $payment_method = $request_data['payment_method'];
    // Tambahkan ini sebelum switch-case
error_log("Received payment_method: " . $payment_method);
   // 🔁 GANTI bagian switch-case menjadi:
switch ($payment_method) {
    case 'bni':                    // ← sesuai data-bank di HTML
        $params['payment_type'] = 'bank_transfer';
        $params['bank_transfer'] = ['bank' => 'bni'];
        break;

    case 'bri':                    // ← sesuai data-bank di HTML
        $params['payment_type'] = 'bank_transfer';
        $params['bank_transfer'] = ['bank' => 'bri'];
        break;

    case 'mandiri':                // ← sesuai data-bank di HTML
        $params['payment_type'] = 'echannel';
        $params['echannel'] = [
            'bill_info1' => 'Pembayaran untuk:',
            'bill_info2' => 'AIREN Store'
        ];
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => "Metode pembayaran '$payment_method' tidak dikenal. Gunakan: bni, bri, mandiri"]);
        exit;
}

    
    // Debug: Log params
    error_log('Midtrans params: ' . print_r($params, true));
    
    $response = \Midtrans\CoreApi::charge($params);
    
    // Debug: Log response
    error_log('Midtrans response: ' . print_r($response, true));
    
    echo json_encode($response);

} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Terjadi error fatal di server.',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
}
?>