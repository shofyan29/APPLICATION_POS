<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'supervisor') {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';
$products = mysqli_query($conn, "SELECT * FROM products");

// Ambil tanggal hari ini (format YYYY-MM-DD) untuk rekap harian
$tanggal_hari_ini = date('Y-m-d');

// Query rekap penjualan khusus HARI INI (sesuaikan nama kolom tabel transaksi Anda, misal: tanggal / created_at)
// Contoh diasumsikan kolom tanggal transaksi bernama 'tanggal' atau 'created_at'
$query_transaksi = "SELECT * FROM transactions WHERE DATE(created_at) = '$tanggal_hari_ini' ORDER BY id DESC";
$result_transaksi = mysqli_query($conn,$query_transaksi);

// Hitung total omzet hari ini
$query_omzet = "SELECT SUM(total) as total_omzet FROM transactions WHERE DATE(created_at) = '$tanggal_hari_ini'";
$result_omzet = mysqli_query($conn,$query_omzet);
$data_omzet = mysqli_fetch_assoc($result_omzet);
$omzet_hari_ini =$data_omzet['total_omzet'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard - POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans h-screen flex flex-col overflow-hidden">
    
    <!-- NAVBAR / HEADER SUPERVISOR -->
    <div class="bg-white border-b px-4 py-3 flex justify-between items-center shrink-0 shadow-sm">
        <div class="flex items-center space-x-4">
            <h1 class="text-lg font-bold text-gray-800">Panel Supervisor (<?= $_SESSION['user']['nama_lengkap'] ?? $_SESSION['user']['username'] ?>)</h1>
            <div class="space-x-1">
                <button onclick="switchTab('kasir')" id="tab-btn-kasir" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 text-white transition">Mode Kasir</button>
                <button onclick="switchTab('rekap')" id="tab-btn-rekap" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-200 text-gray-700 hover:bg-gray-300 transition">Rekap Penjualan Hari Ini</button>
            </div>
        </div>
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-xs font-semibold transition">Logout</a>
    </div>

    <!-- KONTEN TAB 1: MODE KASIR (Sama persis seperti index.php) -->
    <div id="content-kasir" class="flex flex-col lg:flex-row flex-1 overflow-hidden">
        <!-- BAGIAN KIRI: MENU & KATEGORI -->
        <div class="w-full lg:w-2/3 p-3 sm:p-4 overflow-y-auto h-1/2 lg:h-full flex flex-col border-b lg:border-b-0 lg:border-r border-gray-200">
            <div class="flex space-x-2 mb-3 overflow-x-auto pb-1 shrink-0">
                <button onclick="filterCategory('All')" id="btn-All" class="px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition bg-blue-600 text-white shadow shrink-0">Semua Menu</button>
                <button onclick="filterCategory('Makan')" id="btn-Makan" class="px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition bg-white text-gray-700 border hover:bg-gray-50 shrink-0">1. Menu Makan</button>
                <button onclick="filterCategory('Minuman')" id="btn-Minuman" class="px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition bg-white text-gray-700 border hover:bg-gray-50 shrink-0">2. Menu Minuman</button>
                <button onclick="filterCategory('Dessert')" id="btn-Dessert" class="px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition bg-white text-gray-700 border hover:bg-gray-50 shrink-0">3. Menu Dessert</button>
            </div>

            <!-- Grid Produk -->
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 pb-4">
                <?php if (mysqli_num_rows($products) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($products)): ?>
                        <div data-category="<?= trim($row['category'] ?? '') ?>" onclick="addToCart(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>', <?=$row['price'] ?>)" class="product-card bg-white rounded-lg shadow-sm border overflow-hidden cursor-pointer hover:shadow-md transition active:scale-95">
                            <img src="<?= $row['image_url'] ?>" alt="<?= $row['name'] ?>" class="w-full h-28 sm:h-32 object-cover">
                            <div class="p-2 sm:p-3">
                                <h3 class="font-bold text-gray-800 text-xs sm:text-sm truncate"><?= $row['name'] ?></h3>
                                <p class="text-blue-600 font-semibold text-xs sm:text-sm mt-1">Rp <?= number_format($row['price'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-10 text-gray-400 text-sm">Belum ada menu di database.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BAGIAN KANAN: KERANJANG -->
        <div class="w-full lg:w-1/3 bg-white p-3 sm:p-4 shadow-lg flex flex-col h-1/2 lg:h-full overflow-y-auto">
            <h2 class="text-base sm:text-xl font-bold mb-2 sm:mb-3 text-gray-800 border-b pb-2 shrink-0">Rincian Pesanan</h2>
            
            <div id="cart-items" class="space-y-2 mb-3 min-h-[80px]">
                <p class="text-gray-400 text-center py-4 text-xs sm:text-sm">Belum ada menu yang dipilih.</p>
            </div>

            <div class="mt-auto pt-2 border-t shrink-0">
                <div class="mb-2">
                    <label class="block text-[10px] sm:text-xs font-bold text-gray-700 mb-1">Metode Pembayaran:</label>
                    <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                        <label class="border rounded-lg p-1.5 text-center cursor-pointer hover:bg-blue-50 flex flex-col items-center text-[11px] sm:text-xs font-semibold">
                            <input type="radio" name="payment_method" value="CASH" checked class="mb-0.5"> Tunai
                        </label>
                        <label class="border rounded-lg p-1.5 text-center cursor-pointer hover:bg-blue-50 flex flex-col items-center text-[11px] sm:text-xs font-semibold">
                            <input type="radio" name="payment_method" value="QRIS" class="mb-0.5"> QRIS
                        </label>
                        <label class="border rounded-lg p-1.5 text-center cursor-pointer hover:bg-blue-50 flex flex-col items-center text-[11px] sm:text-xs font-semibold">
                            <input type="radio" name="payment_method" value="TRANSFER" class="mb-0.5"> Transfer
                        </label>
                    </div>
                </div>

                <div class="space-y-1 text-xs sm:text-sm text-gray-700 mb-2">
                    <div class="flex justify-between"><span>Subtotal:</span><span id="subtotal-val">Rp 0</span></div>
                    <div class="flex justify-between"><span>Pajak (10%):</span><span id="tax-val">Rp 0</span></div>
                    <div class="flex justify-between"><span>Service (5%):</span><span id="service-val">Rp 0</span></div>
                    <div class="flex justify-between text-sm sm:text-lg font-bold text-gray-900 border-t pt-1">
                        <span>Total:</span><span id="total-val" class="text-blue-600">Rp 0</span>
                    </div>
                </div>

                <button onclick="preCheckout()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 sm:py-3 rounded-lg shadow transition flex items-center justify-center space-x-2 text-xs sm:text-sm">
                    <span>Bayar & Cetak Struk</span>
                </button>
            </div>
        </div>
    </div>

    <!-- KONTEN TAB 2: REKAP PENJUALAN HARIAN -->
    <div id="content-rekap" class="hidden flex-1 p-4 overflow-y-auto bg-gray-50">
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-4 bg-white p-4 rounded-xl shadow-sm border">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Laporan Penjualan Hari Ini</h2>
                    <p class="text-xs text-gray-500">Tanggal: <?= date('d-m-Y') ?></p>
                </div>
                <div class="bg-blue-50 border border-blue-200 px-4 py-2 rounded-lg text-right">
                    <span class="block text-xs text-blue-600 font-semibold">Total Omzet Hari Ini</span>
                    <span class="text-lg font-bold text-blue-800">Rp <?= number_format($omzet_hari_ini, 0, ',', '.') ?></span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 text-xs">
                            <th class="p-3 border-b">ID Transaksi</th>
                            <th class="p-3 border-b">Waktu</th>
                            <th class="p-3 border-b">Metode Pembayaran</th>
                            <th class="p-3 border-b text-right">Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_transaksi && mysqli_num_rows($result_transaksi) > 0): ?>
                            <?php while($trx = mysqli_fetch_assoc($result_transaksi)): ?>
                                <tr class="hover:bg-gray-50 text-xs">
                                    <td class="p-3 border-b font-medium">#<?= $trx['id'] ?></td>
                                    <td class="p-3 border-b text-gray-600"><?= $trx['created_at'] ?? '-' ?></td>
                                    <td class="p-3 border-b uppercase font-semibold text-gray-700"><?= $trx['payment_method'] ?? 'CASH' ?></td>
                                    <td class="p-3 border-b text-right font-bold text-green-600">Rp <?= number_format($trx['total'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-8 text-gray-400 text-xs">Belum ada transaksi yang tercatat pada hari ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL PEMBAYARAN KASIR -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white p-5 sm:p-6 rounded-xl shadow-lg max-w-sm w-full mx-auto text-center">
            <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-1">Verifikasi Pembayaran</h3>
            <p id="modal-text" class="text-xs text-gray-600 mb-3">Silakan selesaikan pembayaran.</p>
            <div class="flex space-x-2">
                <button onclick="closeModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 rounded-lg text-xs transition">Batal</button>
                <button onclick="executeCheckout()" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-lg text-xs transition">Ya, Selesai</button>
            </div>
        </div>
    </div>

    <!-- SCRIPT JAVASCRIPT -->
    <script>
        // Fungsi Switch Tab antara Mode Kasir dan Rekap Harian
        function switchTab(tab) {
            const kasirContainer = document.getElementById('content-kasir');
            const rekapContainer = document.getElementById('content-rekap');
            const btnKasir = document.getElementById('tab-btn-kasir');
            const btnRekap = document.getElementById('tab-btn-rekap');

            if (tab === 'kasir') {
                kasirContainer.classList.remove('hidden');
                rekapContainer.classList.add('hidden');
                btnKasir.className = "px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 text-white transition";
                btnRekap.className = "px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-200 text-gray-700 hover:bg-gray-300 transition";
            } else {
                kasirContainer.classList.add('hidden');
                rekapContainer.classList.remove('hidden');
                btnRekap.className = "px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-600 text-white transition";
                btnKasir.className = "px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-200 text-gray-700 hover:bg-gray-300 transition";
            }
        }

        // Script Kasir
        let cart = [];
        let selectedMethod = 'CASH';

        function filterCategory(category) {
            const categories = ['All', 'Makan', 'Minuman', 'Dessert'];
            categories.forEach(cat => {
                const btn = document.getElementById('btn-' + cat);
                if(btn) {
                    btn.className = (cat === category) 
                        ? "px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition bg-blue-600 text-white shadow shrink-0" 
                        : "px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition bg-white text-gray-700 border hover:bg-gray-50 shrink-0";
                }
            });

            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                card.style.display = (category === 'All' || cardCat === category) ? 'block' : 'none';
            });
        }

        function addToCart(id, name, price) {
            const item = cart.find(i => i.id === id);
            if (item) { item.qty++; } else { cart.push({ id, name, price, qty: 1 }); }
            renderCart();
        }

        function changeQty(id, change) {
            const item = cart.find(i => i.id === id);
            if (item) {
                item.qty += change;
                if (item.qty <= 0) { cart = cart.filter(i => i.id !== id); }
            }
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            if (cart.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-center py-4 text-xs sm:text-sm">Belum ada menu yang dipilih.</p>';
                updateTotals(0); return;
            }
            container.innerHTML = '';
            let subtotal = 0;
            cart.forEach(item => {
                let itemTotal = item.price * item.qty;
                subtotal += itemTotal;
                container.innerHTML += `
                    <div class="flex justify-between items-center bg-gray-50 p-2 rounded-lg border">
                        <div class="flex-1 pr-1 truncate">
                            <h4 class="font-bold text-[11px] sm:text-xs text-gray-800 truncate">${item.name}</h4>
                            <span class="text-[10px] text-gray-500">Rp ${item.price.toLocaleString('id-ID')}</span>
                        </div>
                        <div class="flex items-center space-x-1 shrink-0">
                            <button onclick="changeQty(${item.id}, -1)" class="bg-red-500 text-white w-5 h-5 rounded font-bold text-xs">-</button>
                            <span class="text-xs font-bold w-5 text-center">${item.qty}</span>
                            <button onclick="changeQty(${item.id}, 1)" class="bg-blue-500 text-white w-5 h-5 rounded font-bold text-xs">+</button>
                        </div>
                        <div class="text-right pl-2 shrink-0">
                            <span class="font-semibold text-[11px] text-gray-800">Rp ${itemTotal.toLocaleString('id-ID')}</span>
                        </div>
                    </div>`;
            });
            updateTotals(subtotal);
        }

        function updateTotals(subtotal) {
            const tax = subtotal * 0.10;
            const service = subtotal * 0.05;
            const total = subtotal + tax + service;
            document.getElementById('subtotal-val').innerText = `Rp ${subtotal.toLocaleString('id-ID')}`;
            document.getElementById('tax-val').innerText = `Rp ${tax.toLocaleString('id-ID')}`;
            document.getElementById('service-val').innerText = `Rp ${service.toLocaleString('id-ID')}`;
            document.getElementById('total-val').innerText = `Rp ${total.toLocaleString('id-ID')}`;
        }

        function preCheckout() {
            if (cart.length === 0) { alert('Keranjang masih kosong!'); return; }
            selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closeModal() { document.getElementById('paymentModal').classList.add('hidden'); }

        function executeCheckout() {
            closeModal();
            fetch('cetak_struk.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart: cart, payment_method: selectedMethod })
            })
            .then(res => res.text())
            .then(html => {
                const printWindow = window.open('', '_blank', 'width=400,height=600');
                printWindow.document.write(html);
                printWindow.document.close();
                cart = [];
                renderCart();
            })
            .catch(err => alert('Gagal memproses transaksi.'));
        }
    </script>
</body>
</html>