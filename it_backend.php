<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'it_backend') {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// 1. Handle Tambah Menu
if (isset($_POST['tambah_menu'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = floatval($_POST['harga']);
    $kategori_id = intval($_POST['kategori_id']);
    
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    $target_file = $target_dir . basename($_FILES["foto"]["name"]);
    move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
    
    mysqli_query($conn, "INSERT INTO products (category_id, name, price, image_url) VALUES ('$kategori_id', '$nama', '$harga', '$target_file')");
    header("Location: it_backend.php");
    exit;
}

// 2. Handle Edit Menu
if (isset($_POST['edit_menu'])) {
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = floatval($_POST['harga']);
    $kategori_id = intval($_POST['kategori_id']);

    if (!empty($_FILES["foto"]["name"])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["foto"]["name"]);
        move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
        
        mysqli_query($conn, "UPDATE products SET category_id='$kategori_id', name='$nama', price='$harga', image_url='$target_file' WHERE id='$id'");
    } else {
        mysqli_query($conn, "UPDATE products SET category_id='$kategori_id', name='$nama', price='$harga' WHERE id='$id'");
    }
    header("Location: it_backend.php");
    exit;
}

// 3. Handle Tambah User Baru
if (isset($_POST['tambah_user'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); // Jika sistem Anda pakai md5/password_hash, sesuaikan di sini
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')");
    header("Location: it_backend.php");
    exit;
}

// Ambil data produk & data users
$products = mysqli_query($conn, "SELECT products.*, categories.nama_kategori FROM products LEFT JOIN categories ON products.category_id = categories.id");
$users = mysqli_query($conn, "SELECT * FROM users");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>IT Backend - Kelola Menu & User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-xl shadow mb-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Panel IT Backend (Kelola Menu & Pengguna)</h1>
            <a href="logout.php" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">Logout</a>
        </div>

        <!-- Form Tambah Menu -->
        <form method="POST" enctype="multipart/form-data" class="bg-gray-50 p-4 rounded-lg mb-6 border">
            <h2 class="font-bold mb-3 text-gray-700">Tambah Menu Baru</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                <input type="text" name="nama" placeholder="Nama Menu" required class="p-2 border rounded text-sm">
                <input type="number" name="harga" placeholder="Harga (Rp)" required class="p-2 border rounded text-sm">
                <select name="kategori_id" class="p-2 border rounded text-sm">
                    <option value="1">Makanan</option>
                    <option value="2">Minuman</option>
                    <option value="3">Dessert</option>
                </select>
                <input type="file" name="foto" required class="p-1 border rounded bg-white text-sm">
            </div>
            <button type="submit" name="tambah_menu" class="bg-blue-600 text-white px-4 py-2 rounded font-semibold hover:bg-blue-700 text-sm">Simpan Menu</button>
        </form>

        <!-- Form Tambah Pengguna Baru (Kasir, Supervisor, Manajer) -->
        <form method="POST" class="bg-blue-50 p-4 rounded-lg mb-6 border border-blue-200">
            <h2 class="font-bold mb-3 text-blue-800">Tambah Pengguna Baru (Kasir / Supervisor / Manajer)</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-3">
                <input type="text" name="username" placeholder="Username" required class="p-2 border rounded text-sm bg-white">
                <input type="password" name="password" placeholder="Password" required class="p-2 border rounded text-sm bg-white">
                <select name="role" class="p-2 border rounded text-sm bg-white">
                    <option value="kasir">Kasir</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="manajer">Manajer</option>
                    <option value="it_backend">IT Backend</option>
                </select>
            </div>
            <button type="submit" name="tambah_user" class="bg-green-600 text-white px-4 py-2 rounded font-semibold hover:bg-green-700 text-sm">Tambah Pengguna</button>
        </form>

        <!-- Daftar Menu Saat Ini -->
        <h2 class="font-bold mb-3 text-gray-700">Daftar Menu Saat Ini</h2>
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left border-collapse border text-sm">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="border p-2">Gambar</th>
                        <th class="border p-2">Nama</th>
                        <th class="border p-2">Kategori</th>
                        <th class="border p-2">Harga</th>
                        <th class="border p-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($products)): ?>
                    <tr>
                        <td class="border p-2 text-center">
                            <img src="<?= $row['image_url'] ?>" alt="" class="w-12 h-12 object-cover mx-auto rounded">
                        </td>
                        <td class="border p-2 font-medium"><?= $row['name'] ?></td>
                        <td class="border p-2"><?= $row['nama_kategori'] ?></td>
                        <td class="border p-2">Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                        <td class="border p-2 text-center">
                            <button onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs font-semibold">Edit</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Daftar Pengguna Sistem -->
        <h2 class="font-bold mb-3 text-gray-700">Daftar Pengguna Sistem</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse border text-sm">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="border p-2">ID</th>
                        <th class="border p-2">Username</th>
                        <th class="border p-2">Role / Hak Akses</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($usr = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td class="border p-2"><?= $usr['id'] ?></td>
                        <td class="border p-2 font-medium"><?= $usr['username'] ?></td>
                        <td class="border p-2 uppercase text-xs font-bold text-blue-600"><?= $usr['role'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL EDIT MENU -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl p-6 max-w-md w-full shadow-lg">
            <h2 class="font-bold text-lg mb-4 text-gray-800">Edit Menu</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit-id">
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Menu</label>
                    <input type="text" name="nama" id="edit-nama" required class="w-full p-2 border rounded text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Harga (Rp)</label>
                    <input type="number" name="harga" id="edit-harga" required class="w-full p-2 border rounded text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                    <select name="kategori_id" id="edit-kategori" class="w-full p-2 border rounded text-sm">
                        <option value="1">Makanan</option>
                        <option value="2">Minuman</option>
                        <option value="3">Dessert</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Ganti Foto (Opsional)</label>
                    <input type="file" name="foto" class="w-full p-1 border rounded bg-white text-sm">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded text-sm">Batal</button>
                    <button type="submit" name="edit_menu" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-semibold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(product) {
            document.getElementById('edit-id').value = product.id;
            document.getElementById('edit-nama').value = product.name;
            document.getElementById('edit-harga').value = product.price;
            document.getElementById('edit-kategori').value = product.category_id;
            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</body>
</html>