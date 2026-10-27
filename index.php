<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'kasir') {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';
$products = mysqli_query($conn, "SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>::: SYSTEM POS :::</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans min-h-screen flex flex-col lg:flex-row lg:h-screen lg:overflow-hidden">
    
    <!-- BAGIAN KIRI: MENU & KATEGORI -->
    <div class="w-full lg:w-2/3 p-3 sm:p-4 lg:overflow-y-auto lg:h-full flex flex-col border-b lg:border-b-0 lg:border-r border-gray-200">
        <div class="flex justify-between items-center mb-3">
            <h1 class="text-lg sm:text-2xl font-bold text-gray-800 truncate">Halaman Kasir (<?= $_SESSION['user']['nama_lengkap'] ?>)</h1>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs sm:text-sm font-semibold transition shrink-0">Logout</a>
        </div>

        <!-- Tombol Filter Kategori -->
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
                    <div data-category="<?= trim($row['category']) ?>" onclick="addToCart(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>', <?= $row['price'] ?>)" class="product-card bg-white rounded-lg shadow-sm border overflow-hidden cursor-pointer hover:shadow-md transition active:scale-95">
                        <img src="<?= $row['image_url'] ?>" alt="<?= $row['name'] ?>" class="w-full h-28 sm:h-32 object-cover">
                        <div class="p-2 sm:p-3">
                            <h3 class="font-bold text-gray-800 text-xs sm:text-sm truncate"><?= $row['name'] ?></h3>
                            <p class="text-blue-600 font-semibold text-xs sm:text-sm mt-1">Rp <?= number_format($row['price'], 0, ',', '.') ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-10 text-gray-400 text-sm">
                    Belum ada menu di database.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BAGIAN KANAN: KERANJANG -->
    <div class="w-full lg:w-1/3 bg-white p-3 sm:p-4 shadow-lg flex flex-col lg:h-full lg:overflow-y-auto pb-6">
        <h2 class="text-base sm:text-xl font-bold mb-2 sm:mb-3 text-gray-800 border-b pb-2 shrink-0">Rincian Pesanan</h2>
        
        <!-- Daftar Item di Keranjang -->
        <div id="cart-items" class="space-y-2 mb-3 min-h-[80px]">
            <p class="text-gray-400 text-center py-4 text-xs sm:text-sm">Belum ada menu yang dipilih.</p>
        </div>

        <div class="mt-auto pt-2 border-t shrink-0">
            <!-- Pilihan Metode Pembayaran -->
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

            <!-- Rincian Harga -->
            <div class="space-y-1 text-xs sm:text-sm text-gray-700 mb-3">
                <div class="flex justify-between"><span>Subtotal:</span><span id="subtotal-val">Rp 0</span></div>
                <div class="flex justify-between"><span>Pajak (10%):</span><span id="tax-val">Rp 0</span></div>
                <div class="flex justify-between"><span>Service (5%):</span><span id="service-val">Rp 0</span></div>
                <div class="flex justify-between text-sm sm:text-lg font-bold text-gray-900 border-t pt-1">
                    <span>Total:</span><span id="total-val" class="text-blue-600">Rp 0</span>
                </div>
            </div>

            <button onclick="preCheckout()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg shadow transition flex items-center justify-center space-x-2 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Bayar & Cetak Struk</span>
            </button>
        </div>
    </div>

    <!-- MODAL KONFIRMASI & QRIS PEMBAYARAN -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white p-5 sm:p-6 rounded-xl shadow-lg max-w-sm w-full mx-auto text-center">
            <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-1">Verifikasi Pembayaran</h3>
            <p id="modal-text" class="text-xs text-gray-600 mb-3">Silakan selesaikan pembayaran.</p>
            
            <div id="qris-container" class="hidden mb-4 flex flex-col items-center">
                <div class="bg-white p-2 border rounded-lg shadow-inner inline-block">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=RETOKU_CAFE_QRIS_PAYMENT" alt="QRIS Code" class="w-36 h-36 sm:w-40 sm:h-40 object-contain mx-auto">
                </div>
                <span class="text-[11px] sm:text-xs font-bold text-blue-600 mt-2">Scan QRIS menggunakan Mobile Banking / E-Wallet</span>
            </div>

            <div id="transfer-container" class="hidden mb-4 bg-gray-50 p-3 rounded-lg border text-left text-xs">
                <p class="font-bold text-gray-700 mb-1">Silakan Transfer ke Rekening:</p>
                <p class="text-gray-600">Bank BCA: <b class="text-gray-900">5770690414</b></p>
                <p class="text-gray-600">A/N: <b class="text-gray-900">WARKOP SHBAHP-30</b></p>
            </div>

            <div class="flex space-x-2">
                <button onclick="closeModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 rounded-lg text-xs transition">Belum</button>
                <button onclick="executeCheckout()" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 rounded-lg text-xs transition">Ya, Sudah Masuk</button>
            </div>
        </div>
    </div>

    <script>
        let cart = [];
        let selectedMethod = 'CASH';

        function filterCategory(category) {
            const categories = ['All', 'Makan', 'Minuman', 'Dessert'];
            categories.forEach(cat => {
                const btn = document.getElementById('btn-' + cat);
                if (cat === category) {
                    btn.className = "px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition bg-blue-600 text-white shadow shrink-0";
                } else {
                    btn.className = "px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition bg-white text-gray-700 border hover:bg-gray-50 shrink-0";
                }
            });

            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                if (category === 'All' || cardCat === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function addToCart(id, name, price) {
            const item = cart.find(i => i.id === id);
            if (item) { 
                item.qty++; 
            } else { 
                cart.push({ id, name, price, qty: 1 }); 
            }
            renderCart();
        }

        function changeQty(id, change) {
            const item = cart.find(i => i.id === id);
            if (item) {
                item.qty += change;
                if (item.qty <= 0) {
                    cart = cart.filter(i => i.id !== id);
                }
            }
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            if (cart.length === 0) {
                container.innerHTML = '<p class="text-gray-400 text-center py-4 text-xs sm:text-sm">Belum ada menu yang dipilih.</p>';
                updateTotals(0); 
                return;
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
                            <span class="text-[10px] sm:text-xs text-gray-500">Rp ${item.price.toLocaleString('id-ID')}</span>
                        </div>
                        <div class="flex items-center space-x-1 shrink-0">
                            <button onclick="changeQty(${item.id}, -1)" class="bg-red-500 hover:bg-red-600 text-white w-5 h-5 sm:w-6 sm:h-6 rounded flex items-center justify-center font-bold text-xs transition">-</button>
                            <span class="text-xs font-bold w-5 text-center">${item.qty}</span>
                            <button onclick="changeQty(${item.id}, 1)" class="bg-blue-500 hover:bg-blue-600 text-white w-5 h-5 sm:w-6 sm:h-6 rounded flex items-center justify-center font-bold text-xs transition">+</button>
                        </div>
                        <div class="text-right pl-2 min-w-[60px] sm:min-w-[70px] shrink-0">
                            <span class="font-semibold text-[11px] sm:text-xs text-gray-800">Rp ${itemTotal.toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                `;
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
            if (cart.length === 0) {
                alert('Keranjang masih kosong!');
                return;
            }

            selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            const modalText = document.getElementById('modal-text');
            const qrisContainer = document.getElementById('qris-container');
            const transferContainer = document.getElementById('transfer-container');

            qrisContainer.classList.add('hidden');
            transferContainer.classList.add('hidden');

            if (selectedMethod === 'QRIS') {
                modalText.innerText = "Silakan minta pelanggan melakukan scan QRIS di bawah ini:";
                qrisContainer.classList.remove('hidden');
            } else if (selectedMethod === 'TRANSFER') {
                modalText.innerText = "Silakan arahkan transfer ke rekening berikut:";
                transferContainer.classList.remove('hidden');
            } else {
                modalText.innerText = "Apakah uang tunai dari pelanggan sudah diterima dengan pas/benar?";
            }

            document.getElementById('paymentModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('paymentModal').classList.add('hidden');
        }

        function executeCheckout() {
            closeModal();

            fetch('cetak_struk.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cart: cart, payment_method: selectedMethod })
            })
            .then(response => response.text())
            .then(html => {
                const printWindow = window.open('', '_blank', 'width=400,height=600');
                printWindow.document.write(html);
                printWindow.document.close();
                
                cart = [];
                renderCart();
            })
            .catch(err => {
                alert('Terjadi kesalahan saat memproses transaksi.');
                console.error(err);
            });
        }
    </script>
</body>
</html>