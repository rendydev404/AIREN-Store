<?php
// File: config-midtrans.php

// Pastikan class Midtrans sudah bisa diakses
require_once __DIR__ . '/vendor/autoload.php';

// --- LANGSUNG ATUR KONFIGURASI DI SINI ---
// Ganti dengan kunci API Anda yang sesungguhnya
\Midtrans\Config::$serverKey = 'Mid-server-UJauzkp0W0Y-hmVPCul360dg';
\Midtrans\Config::$clientKey = 'Mid-client-7SKzi6jrsrBHJYIN';

// Ganti ke false jika Anda masih dalam mode Sandbox/Development
\Midtrans\Config::$isProduction = true; 

// Pengaturan tambahan
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;