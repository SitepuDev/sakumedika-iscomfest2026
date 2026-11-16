<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; 
$db_name = 'db_apotek_ai';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-CYRZme5kWORXe8DXKFmqmfHy');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-T1b0LJ8aS3c0RU8Q');
define('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/v1/transactions');
define('MIDTRANS_SNAP_JS', 'https://app.sandbox.midtrans.com/snap/snap.js');

if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

function formatRupiah($angka) {
    return 'Rp ' . number_format((int)$angka, 0, ',', '.');
}

function hitungItemKeranjang() {
    $total = 0;
    if (!empty($_SESSION['keranjang'])) {
        foreach ($_SESSION['keranjang'] as $qty) {
            $total += (int)$qty;
        }
    }
    return $total;
}


function getMidtransSnapToken($kode_trx, $total_harga, $nama_pembeli, $no_hp, $items_midtrans) {
    if (!function_exists('curl_init')) {
        return null;
    }

    $payload = [
        'transaction_details' => [
            'order_id' => $kode_trx,
            'gross_amount' => (int)$total_harga
        ],
        'customer_details' => [
            'first_name' => $nama_pembeli,
            'phone' => $no_hp
        ],
        'item_details' => $items_midtrans
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, MIDTRANS_SNAP_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
    ]);

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 201 && $result) {
        $data = json_decode($result, true);
        return isset($data['token']) ? $data['token'] : null;
    }
    return null;
}

function cekStatusMidtrans($order_id) {
    if (!function_exists('curl_init')) return 'pending';

    $url = "https://api.sandbox.midtrans.com/v2/" . urlencode($order_id) . "/status";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
    ]);

    $result = curl_exec($ch);
    curl_close($ch);

    if ($result) {
        $data = json_decode($result, true);
        return isset($data['transaction_status']) ? $data['transaction_status'] : 'pending';
    }
    return 'pending';
}
?>