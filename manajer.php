<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manajer') {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// Ambil filter bulan dari URL jika ada (format: YYYY-MM)
$selected_month = isset($_GET['month']) ? $_GET['month'] : '';

// 1. Query Dasar Statistik & Transaksi dengan Filter Bulan
$where_clause = "";
if (!empty($selected_month)) {
    $safe_month = mysqli_real_escape_string($conn, $selected_month);
    $where_clause = " WHERE DATE_FORMAT(transactions.created_at, '%Y-%m') = '$safe_month'";
}

// Ambil Ringkasan Statistik
$sql_summary = "SELECT COUNT(*) as total_trx, SUM(total) as pendapatan FROM transactions" . $where_clause;
$query_summary = mysqli_query($conn, $sql_summary);
$data_summary = mysqli_fetch_assoc($query_summary);

$total_trx = $data_summary['total_trx'] ?? 0;
$pendapatan = $data_summary['pendapatan'] ?? 0;

// Ambil Riwayat Transaksi Beserta Nama Kasirnya (dengan Filter)
$sql_trx = "SELECT transactions.*, users.nama_lengkap 
            FROM transactions 
            LEFT JOIN users ON transactions.user_id = users.id" 
            . $where_clause . 
            " ORDER BY transactions.created_at DESC";
$result_trx = mysqli_query($conn, $sql_trx);

// Hitung Total Akumulasi (Subtotal, Pajak, Service, Total Akhir) untuk Footer Tabel
$sum_subtotal = 0;
$sum_tax = 0;
$sum_service = 0;
$sum_total = 0;

$transactions_data = [];
while($row = mysqli_fetch_assoc($result_trx)) {
    $transactions_data[] = $row;
    $sum_subtotal += $row['subtotal'];
    $sum_tax += $row['tax'];
    $sum_service += $row['service_charge'];
    $sum_total += $row['total'];
}

// Ambil daftar bulan yang tersedia di database untuk pilihan dropdown filter
$sql_months = "SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') as bulan FROM transactions ORDER BY bulan DESC";
$result_months = mysqli_query($conn, $sql_months);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Manajer - Laporan Penjualan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4 sm:p-6 font-sans">
    <div class="max-w-7xl mx-auto bg-white p-4 sm:p-6 rounded-xl shadow space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b pb-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Laporan Penjualan (Manajer)</h1>
                <p class="text-gray-600 text-xs sm:text-sm mt-1">Selamat datang, <b><?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></b>. Berikut adalah rekapitulasi keuangan dan aktivitas kasir.</p>
            </div>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Logout</a>
        </div>

        <!-- Kartu Statistik & Filter Bulan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Kotak Pendapatan -->
            <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl shadow-sm">
                <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Total Pendapatan <?= !empty($selected_month) ? "(".date('F Y', strtotime($selected_month.'-01')).")" : "" ?></p>
                <h3 class="text-2xl font-bold text-blue-900 mt-1">Rp <?= number_format($pendapatan, 0, ',', '.') ?></h3>
            </div>
            
            <!-- Kotak Transaksi Berhasil -->
            <div class="bg-green-50 border border-green-200 p-4 rounded-xl shadow-sm">
                <p class="text-xs font-semibold text-green-600 uppercase tracking-wider">Total Transaksi Berhasil</p>
                <h3 class="text-2xl font-bold text-green-900 mt-1"><?= number_format($total_trx, 0, ',', '.') ?> Transaksi</h3>
            </div>

            <!-- Kotak Form Filter Bulan -->
            <div class="bg-gray-50 border border-gray-200 p-4 rounded-xl shadow-sm flex flex-col justify-between">
                <label for="month-filter" class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Filter Berdasarkan Bulan:</label>
                <form method="GET" action="" class="flex gap-2">
                    <select name="month" id="month-filter" class="flex-1 border rounded-lg px-3 py-1.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Bulan (Keseluruhan)</option>
                        <?php while($m = mysqli_fetch_assoc($result_months)): ?>
                            <?php 
                                // Format tampilan bulan menjadi contoh: September 2026
                                $formatted_bulan = date('F Y', strtotime($m['bulan'] . '-01'));
                            ?>
                            <option value="<?= $m['bulan'] ?>" <?= ($selected_month == $m['bulan']) ? 'selected' : '' ?>>
                                <?= $formatted_bulan ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-sm font-semibold transition">Filter</button>
                    <?php if(!empty($selected_month)): ?>
                        <a href="?" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-3 py-1.5 rounded-lg text-sm font-semibold transition flex items-center justify-center" title="Reset Filter">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Tabel Riwayat Transaksi -->
        <div>
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-bold text-lg text-gray-800">Riwayat Transaksi Penjualan</h2>
                <?php if(!empty($selected_month)): ?>
                    <span class="text-xs bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-medium">Filter Aktif: <?= date('F Y', strtotime($selected_month.'-01')) ?></span>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse border text-sm">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700 uppercase text-xs">
                            <th class="border p-2.5">ID Trx</th>
                            <th class="border p-2.5">Waktu</th>
                            <th class="border p-2.5">Kasir</th>
                            <th class="border p-2.5 text-right">Subtotal</th>
                            <th class="border p-2.5 text-right">Pajak (10%)</th>
                            <th class="border p-2.5 text-right">Service (5%)</th>
                            <th class="border p-2.5 text-right">Total Akhir</th>
                            <th class="border p-2.5 text-center">Metode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transactions_data) > 0): ?>
                            <?php foreach($transactions_data as $row): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="border p-2.5 font-semibold text-center">#<?= $row['id'] ?></td>
                                <td class="border p-2.5 text-gray-600"><?= $row['created_at'] ?></td>
                                <td class="border p-2.5"><?= htmlspecialchars($row['nama_lengkap'] ?? 'Kasir') ?></td>
                                <td class="border p-2.5 text-right">Rp <?= number_format($row['subtotal'], 0, ',', '.') ?></td>
                                <td class="border p-2.5 text-right">Rp <?= number_format($row['tax'], 0, ',', '.') ?></td>
                                <td class="border p-2.5 text-right">Rp <?= number_format($row['service_charge'], 0, ',', '.') ?></td>
                                <td class="border p-2.5 text-right font-bold text-blue-600">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                <td class="border p-2.5 text-center">
                                    <span class="bg-gray-200 px-2 py-0.5 rounded text-xs font-bold"><?= $row['payment_method'] ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="border p-6 text-center text-gray-400">Belum ada data transaksi penjualan pada periode ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    
                    <!-- FOOTER: BARIS TOTAL KESELURUHAN -->
                    <?php if (count($transactions_data) > 0): ?>
                    <tfoot>
                        <tr class="bg-gray-100 font-bold text-gray-900 border-t-2">
                            <td colspan="3" class="border p-2.5 text-right uppercase text-xs">Total Keseluruhan:</td>
                            <td class="border p-2.5 text-right">Rp <?= number_format($sum_subtotal, 0, ',', '.') ?></td>
                            <td class="border p-2.5 text-right">Rp <?= number_format($sum_tax, 0, ',', '.') ?></td>
                            <td class="border p-2.5 text-right">Rp <?= number_format($sum_service, 0, ',', '.') ?></td>
                            <td class="border p-2.5 text-right text-blue-600 text-base">Rp <?= number_format($sum_total, 0, ',', '.') ?></td>
                            <td class="border p-2.5"></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>