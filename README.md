# -Aplikasi_POS_CAFE

Aplikasi manajemen jual beli dan kasir serbaguna yang dibangun menggunakan **PHP Native (PHP Murni tanpa framework)**. Sistem ini menggunakan arsitektur modular sederhana dengan pembagian hak akses pengguna berbasis berkas skrip terpisah untuk memastikan manajemen operasional toko berjalan langsung dan efisien.

---

## 📄 Lisensi (Copyright Notice)

**© 2026 Shofyan Hadi Barta.**
 
* Proyek ini dikembangkan secara mandiri sebagai solusi sistem kasir toko berbasis PHP Native.

---

## 🛠️ Fitur Utama & Struktur Hak Akses

Aplikasi membagi alur kerja kasir dan manajemen langsung melalui pemisahan file halaman operasional:

* **Gerbang Utama & Autentikasi (`index.php`, `login.php`, `logout.php`):** Mengatur sesi masuk dan keluar pengguna untuk mengamankan data transaksi toko.
* **Hak Akses Tingkat Manajer (`manajer.php`):** Dashboard khusus untuk meninjau rangkuman performa toko dan laporan keuangan global.
* **Hak Akses Tingkat Supervisor (`supervisor.php`):** Halaman kontrol operasional untuk memantau aktivitas kasir di lapangan secara langsung.
* **Modul Cetak Struk (`cetak_struk.php`):** Komponen inti kasir untuk memproses dan mencetak nota fisik belanja konsumen setelah transaksi selesai.
* **Pemrosesan Backend (`it_backend.php`):** Mengatur alur logika data, manipulasi stok, dan menjembatani proses data dari antarmuka ke database.

---

## 📦 Prototype & Struktur File Aktual

Struktur direktori riil dari proyek kasir PHP Native Anda diatur sebagai berikut:

```text
kasir/
├── assets/                     # Media aset gambar statis bawaan sistem
│   ├── bubur_roti.jpeg
│   └── es_krim.jpg
├── uploads/                    # Gambar produk hasil unggahan dinamis
│   ├── bubur_roti.jpeg
│   └── es_krim.jpg
├── db_kasir_pos.sql            # Master skema database MySQL
├── koneksi.php                 # Skrip koneksi database MySQLi / PDO
├── index.php                   # Halaman beranda / pengecekan sesi login
├── login.php                   # Form login pengguna
├── logout.php                  # Penghapus sesi login pengguna
├── it_backend.php              # Pengolah logika query dan data backend
├── cetak_struk.php             # Tampilan format cetak nota belanja
├── manajer.php                 # Modul halaman khusus pengguna level Manajer
└── supervisor.php              # Modul halaman khusus pengguna level Supervisor
```

---

## 🚀 Panduan Instalasi & Menjalankan Proyek

Karena proyek ini menggunakan PHP Native, Anda tidak memerlukan Composer atau tool CLI tambahan. Ikuti langkah-langkah berikut:

### Langkah 1: Penempatan File Proyek
1. Pastikan Anda sudah menginstal local server seperti **XAMPP**, **Laragon**, atau **AppServ**.
2. Ekstrak folder proyek `kasir` dan pindahkan ke dalam direktori server lokal Anda (misal: `C:\xampp\htdocs\kasir\`).

### Langkah 2: Impor Database SQL
1. Buka browser dan akses **http://localhost/phpmyadmin/**.
2. Buat database baru dengan nama `db_kasir_pos`.
3. Pilih database tersebut, masuk ke tab **Import**, lalu pilih berkas `db_kasir_pos.sql` untuk mengunggah struktur tabelnya.

### Langkah 3: Konfigurasi Hubungan Database (`koneksi.php`)
Buka file `kasir/koneksi.php` menggunakan teks editor, lalu sesuaikan kredensial server MySQL lokal Anda.

### Langkah 4: Uji Coba Aplikasi
1. Aktifkan modul *Apache* dan *MySQL* pada control panel server lokal Anda.
2. Buka web browser, lalu akses alamat: **http://localhost/kasir/**.
