<?php
// File: checkout.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - AIREN Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .payment-method.active {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
        }

        .payment-method {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <a href="index.php">
                        <h2>AIREN Store</h2>
                    </a>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-lg-5">
                        <div id="payment-result" style="display:none;" class="text-center mb-4">
                            <h4 id="payment-result-title">Instruksi Pembayaran</h4>
                            <div id="payment-result-details" class="alert alert-info mt-3"></div>
                        </div>

                        <div id="payment-selection">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h5>Pilih Bank Transfer</h5>
                                    <div id="bank-list" class="list-group">
                                        <div class="list-group-item payment-method" data-bank="bni">BNI Virtual Account
                                        </div>
                                        <div class="list-group-item payment-method" data-bank="bri">BRI Virtual Account
                                        </div>
                                        <div class="list-group-item payment-method" data-bank="mandiri">Mandiri Bill
                                            Payment</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5>Ringkasan Pesanan</h5>
                                    <ul id="order-summary" class="list-group">
                                    </ul>
                                </div>
                            </div>
                            <div class="d-grid mt-4">
                                <button id="pay-button" class="btn btn-primary btn-lg">Bayar Sekarang</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/checkout.js"></script>
</body>

</html>