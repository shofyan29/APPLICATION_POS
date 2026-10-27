<?php
session_start();
// Izinkan role 'kasir' ATAU 'supervisor' untuk melakukan checkout
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'kasir' && $_SESSION['user']['role'] !== 'supervisor')) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data['cart'])) {
    echo json_encode(["status" => "error", "message" => "Keranjang kosong!"]);
    exit;
}

$user_id = $_SESSION['user']['id'];
$cart = $data['cart'];
$payment_method = $data['payment_method'] ?? 'CASH'; // Menangkap pilihan metode pembayaran

$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

$tax = $subtotal * 0.10;
$service = $subtotal * 0.05;
$total = $subtotal + $tax + $service;

// 1. Simpan ke tabel transactions dengan metode pembayaran yang dipilih
$query_trx = "INSERT INTO transactions (user_id, subtotal, tax, service_charge, total, payment_method) 
              VALUES ('$user_id', '$subtotal', '$tax', '$service', '$total', '$payment_method')";
if (mysqli_query($conn, $query_trx)) {
    $transaction_id = mysqli_insert_id($conn);

    // 2. Simpan ke tabel transaction_items
    foreach ($cart as $item) {
        $product_id = $item['id'];
        $qty = $item['qty'];
        $price = $item['price'];
        $subtotal_item = $price * $qty;

        mysqli_query($conn, "INSERT INTO transaction_items (transaction_id, product_id, quantity, price_at_sale, subtotal_item) 
                             VALUES ('$transaction_id', '$product_id', '$qty', '$price', '$subtotal_item')");
    }

    // Output HTML Struk
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Struk Pembayaran #<?= $transaction_id ?></title>
        <style>
            body { font-family: 'Courier New', Courier, monospace; width: 300px; margin: 0 auto; padding: 10px; color: #000; }
            .text-center { text-align: center; }
            .flex { display: flex; justify-content: space-between; }
            .border-dash { border-bottom: 1px dashed #000; margin: 8px 0; }
            table { width: 100%; font-size: 12px; border-collapse: collapse; }
            th, td { text-align: left; padding: 3px 0; }
            .total-section { font-weight: bold; margin-top: 5px; }
            @media print {
                body { width: 100%; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body onload="window.print();">
        <div class="text-center">
            <h3 style="margin: 0;">RETOKU CAFE / KASIR</h3>
            <p style="font-size: 11px; margin: 2px 0;">Jl. Contoh Alamat No. 123</p>
            <p style="font-size: 11px; margin: 2px 0;">Telp: 08123456789</p>
        </div>
        <div class="border-dash"></div>
        <div style="font-size: 11px;">
            <div>No. Transaksi : #<?= $transaction_id ?></div>
            <div>Kasir         : <?= $_SESSION['user']['nama_lengkap'] ?></div>
            <div>Metode Bayar  : <b><?= $payment_method ?></b></div>
            <div>Waktu         : <?= date('d-m-Y H:i:s') ?></div>
        </div>
        <div class="border-dash"></div>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Sub</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $item): ?>
                <tr>
                    <td><?= $item['name'] ?></td>
                    <td style="text-align: center;"><?= $item['qty'] ?></td>
                    <td style="text-align: right;"><?= number_format($item['price'] * $item['qty'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="border-dash"></div>
        <div style="font-size: 12px;">
            <div class="flex"><span>Subtotal</span><span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span></div>
            <div class="flex"><span>Pajak (10%)</span><span>Rp <?= number_format($tax, 0, ',', '.') ?></span></div>
            <div class="flex"><span>Service (5%)</span><span>Rp <?= number_format($service, 0, ',', '.') ?></span></div>
            <div class="border-dash"></div>
            <div class="flex total-section" style="font-size: 14px;"><span>TOTAL</span><span>Rp <?= number_format($total, 0, ',', '.') ?></span></div>
        </div>
        <div class="border-dash"></div>
        <div class="text-center" style="font-size: 11px; margin-top: 10px;">
            <p>Terima Kasih Atas Kunjungan Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat ditukar.</p>
        </div>
        <div class="text-center no-print" style="margin-top: 15px;">
            <button onclick="window.print()" style="padding: 8px 15px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Cetak Ulang</button>
            <button onclick="window.location.href='index.php'" style="padding: 8px 15px; background: #6b7280; color: #fff; border: none; border-radius: 4px; cursor: pointer; margin-left: 5px;">Kembali</button>
        </div>
    </body>
    </html>
    <?php
} else {
    echo json_encode(["status" => "error", "message" => "Gagal menyimpan transaksi!"]);
}
?>