# Rose Colection — Modern Retail Management & Point of Sale (POS)

**Rose Colection** adalah aplikasi sistem manajemen toko fashion berbasis web modern yang lengkap dan siap pakai. Aplikasi ini dirancang untuk butik pakaian, hijab, busana muslim, dan aksesoris dengan integrasi penuh ke **PostgreSQL**, penghitungan **Harga Pokok Penjualan (HPP) Moving Weighted Average Cost**, Point of Sale (POS) interaktif, manajemen stok berbasis **buku ledger mutasi**, serta laporan keuangan dan keuntungan kotor real-time.

---

## 1. Fitur Utama

### 🛍️ Katalog & Master Produk
- **Katalog Visual**: Grid produk estetik dengan foto busana, pill kategori, informasi stok, ukuran (S, M, L, XL), warna, dan harga jual.
- **Dukungan Varian**: Setiap produk mendukung multi-varian ukuran dan warna dengan SKU dan barcode unik masing-masing.
- **Integritas Stok & Audit**: Stok tidak dapat diedit sembarangan melalui form edit produk. Seluruh perubahan stok wajib melalui pencatatan mutasi yang sah.
- **Perlindungan Riwayat Transaksi**: Produk atau varian yang telah memiliki riwayat penjualan/pembelian tidak dapat dihapus permanen melainkan diarsipkan (`is_active = false`) guna menjaga akurasi pembukuan historis.

### 💳 Point of Sale (POS) Kasir Cepat
- **Antarmuka Kasir Terpisah (Dual-panel Layout)**:
  - Panel kiri: Pencarian cepat, filter kategori horizontal, kartu produk dengan tombol tambah instan `(+)`.
  - Panel kanan: Keranjang belanja real-time, pengatur jumlah kuantitas (`-` [qty] `+`), diskon transaksi proporsional, pemilihan metode pembayaran (Tunai, Transfer, QRIS), kalkulator uang diterima & kembalian otomatis.
- **Pencegahan Stok Negatif & Concurrency Locking**: Menggunakan `DB::transaction()` dan PostgreSQL row-level locking (`lockForUpdate()`) sehingga transaksi bersamaan tidak akan menyebabkan stok bernilai negatif.
- **Struk Thermal**: Mendukung format cetak struk kasir thermal 58 mm dan 80 mm lengkap dengan identitas toko, rincian barang, waktu transaksi WITA (Asia/Makassar), dan kebijakan retur toko.

### 📊 Perhitungan HPP Moving Weighted Average (Rata-Rata Tertimbang Bergerak)
Setiap kali ada penerimaan stok dari supplier, sistem memperbarui nilai HPP varian dan induk produk secara otomatis menggunakan rumus:

$$\text{HPP Baru} = \frac{(\text{Stok Lama} \times \text{HPP Lama}) + (\text{Qty Masuk} \times \text{Biaya Perolehan Bersih Satuan})}{\text{Stok Lama} + \text{Qty Masuk}}$$

- **Alokasi Biaya Tambahan**: Biaya pengiriman/handling dari supplier dialokasikan secara proporsional ke harga perolehan setiap item.
- **Snapshot HPP Historis**: Saat transaksi kasir selesai, sistem merekam snapshot HPP yang berlaku saat itu pada tabel `sale_items`. Nilai HPP transaksi lampau bersifat permanen dan tidak akan terdistorsi oleh pembelian baru di masa depan.

### 📈 Keuntungan Kotor & Margin Analisis
- Perhitungan Laba Kotor otomatis:
  $$\text{Penjualan Bersih} = \text{Total Penjualan} - \text{Diskon Transaksi}$$
  $$\text{Keuntungan Kotor} = \text{Penjualan Bersih} - \sum(\text{Qty Terjual} \times \text{HPP Snapshot})$$
  $$\text{Margin Kotor (\%)} = \left(\frac{\text{Keuntungan Kotor}}{\text{Penjualan Bersih}}\right) \times 100\%$$
- Diskon transaksi dialokasikan secara proporsional ke setiap item agar analisis keuntungan per produk tetap akurat.

### 📦 Manajemen Stok & Buku Ledger Mutasi
- **Buku Ledger Mutasi (`stock_movements`)**: Sumber kebenaran tunggal audit pergerakan barang. Mencatat waktu, jenis pergerakan (`PURCHASE_RECEIPT`, `SALE`, `ADJUSTMENT_DAMAGE`, `ADJUSTMENT_LOST`, `ADJUSTMENT_INTERNAL`, `ADJUSTMENT_CORRECTION`), kuantitas sebelum, perubahan kuantitas, kuantitas sesudah, biaya satuan, petugas, dan referensi transaksi.
- **Penerimaan Barang (PO)**: Penerimaan barang dari supplier dengan alokasi diskon dan biaya pengiriman.
- **Penyesuaian Stok (Stock Opname)**: Perbandingan stok sistem vs fisik, otomatis menghitung selisih dan mencatat alasan (rusak, hilang, koreksi opname).

### 📑 Laporan & Export Data
- Filter fleksibel: Hari Ini, Kemarin, 7 Hari Terakhir, Bulan Ini, Bulan Lalu, Tahun Ini, dan Rentang Kustom.
- Jenis Laporan:
  1. Laporan Penjualan (Sales)
  2. Laporan Barang Masuk (PO)
  3. Laporan Mutasi Stok Ledger
  4. Laporan Nilai Persediaan HPP (Valuasi Aset)
  5. Laporan Keuntungan Kotor
  6. Produk Terlaris (Best Seller)
- Fitur **Export Excel / CSV** (dengan BOM UTF-8) dan **Cetak Laporan Langsung**.

### 🔒 Autentikasi & Otorisasi Peran (Role-Based Access Control)
- **Admin / Owner**: Akses menyeluruh ke seluruh modul, data barang, stok, supplier, pengaturan HPP, keuntungan, laporan finansial, manajemen pengguna, dan pengaturan toko.
- **Kasir**: Khusus transaksi POS kasir, melihat stok produk, melihat riwayat transaksi miliknya, dan mencetak struk. Kasir **tidak memiliki akses** ke HPP, harga beli, laporan keuntungan, maupun pengaturan toko.

---

## 2. Spesifikasi Teknologi

| Komponen | Teknologi |
| :--- | :--- |
| **Backend Framework** | Laravel 13 (PHP 8.3+) |
| **Database** | PostgreSQL |
| **Interactive UI** | Livewire v4 + Alpine.js |
| **Styling & Theme** | Tailwind CSS 4 (Modern Retail: Rose/Burgundy palette) |
| **Icons** | Lucide Icons |
| **Grafik & Visual** | Chart.js |
| **Testing** | PHPUnit (26 tests, 94 assertions) |
| **Format & Waktu** | Rupiah (IDR), Bahasa Indonesia, Asia/Makassar (WITA) |

---

## 3. Akun Demo Pengujian

Saat membuka halaman login di `/login`, tersedia tombol pintas untuk mengisi akun demo berikut:

| Peran | Email | Kata Sandi | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Admin / Owner** | `admin@rosecolection.com` | `password123` | Akses penuh ke seluruh menu toko, HPP, laba, dan pengaturan |
| **Kasir Toko** | `kasir@rosecolection.com` | `password123` | Khusus transaksi Kasir POS & riwayat faktur pribadi |

---

## 4. Panduan Instalasi & Menjalankan Aplikasi

### A. Prasyarat
- PHP 8.3 atau lebih baru (dengan ekstensi `pdo_pgsql`, `pgsql`, `mbstring`, `bcmath`, `curl`)
- Composer 2.x
- Node.js 18+ dan npm
- Server PostgreSQL aktif

### B. Langkah Instalasi

1. **Clone / Buka Repositori**
   ```bash
   cd /Users/user/MuhammadRofik/APPS/rose
   ```

2. **Install Dependensi Composer & Node**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment Database PostgreSQL**
   Salin file `.env.example` menjadi `.env` jika belum ada:
   ```bash
   cp .env.example .env
   ```
   Pastikan pengaturan PostgreSQL pada `.env`:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=rose_colection
   DB_USERNAME=user
   DB_PASSWORD=
   ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Jalankan Migrasi Database & Seeder Data Awal**
   Perintah ini akan membuat seluruh tabel PostgreSQL, check constraint stok non-negatif, pengaturan toko, supplier, kategori busana, 16 produk dengan multi-varian, stok awal melalui PO, dan sampel transaksi kasir:
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Build Asset Frontend (Tailwind CSS 4 & Chart.js)**
   ```bash
   npm run build
   ```

7. **Jalankan Web Server Lokal**
   ```bash
   php artisan serve
   ```
   Akses aplikasi pada peramban melalui alamat: **`http://localhost:8000`**

---

## 5. Menjalankan Automated Tests

Aplikasi dilengkapi 26 automated feature test suites terisolasi menggunakan database PostgreSQL `rose_colection_test`.

Jalankan pengujian dengan:
```bash
php artisan test
```

### Lingkup Pengujian yang Dicakup:
- ✅ Otentikasi login berhasil dan penolakan kredensial salah
- ✅ Penolakan login akun yang dinonaktifkan
- ✅ Hak akses kasir vs admin (RBAC protection pada controller dan route)
- ✅ CRUD produk dan varian multi-ukuran/warna
- ✅ Validasi keunikan SKU produk dan SKU varian
- ✅ Perlindungan produk bertransaksi agar tidak terhapus permanen
- ✅ Perhitungan Moving Weighted Average HPP sesuai rumus standar akuntansi
- ✅ Penentuan HPP baru jika stok sebelumnya nol
- ✅ Alokasi biaya pengiriman/ongkir ke harga pokok bersih satuan
- ✅ Kekekalan snapshot HPP transaksi masa lalu terhadap pembelian baru
- ✅ Pengurangan stok otomatis dan pencatatan ledger mutasi `SALE`
- ✅ Penolakan transaksi penjualan jika stok tidak mencukupi
- ✅ Penolakan transaksi penjualan jika nominal pembayaran kurang dari tagihan
- ✅ Alokasi diskon transaksi proporsional dan perhitungan laba kotor
- ✅ Concurrency locking untuk mencegah penarikan stok negatif
- ✅ Rollback transaksi database (`DB::transaction`) saat checkout gagal
- ✅ Penyesuaian stok fisik (Stock Opname) dan pencatatan selisih
- ✅ Agregasi data laporan penjualan dan valuasi aset persediaan
- ✅ Streaming ekspor laporan ke format CSV / Excel

---

## 6. Struktur Menu Aplikasi

```
Dashboard
├── Ringkasan Penjualan & Order
├── Valuasi Persediaan & HPP
├── Grafik Tren Pendapatan 6 Bulan (WITA)
├── Distribusi Stok per Kategori
├── Peringatan Stok Menipis (Needs Attention)
├── 5 Transaksi Kasir Terbaru
└── 5 Produk Terlaris

Katalog Barang
├── Pencarian & Filter Kategori Horizontal
├── Filter Status Ketersediaan (Aman / Menipis / Habis)
├── Pengurutan Harga & Nama
└── Detail Produk & Varian

Data Barang (Admin)
├── Daftar Produk & Multi-Varian
├── Tambah Produk & Varian Dinamis
├── Edit Produk & Arsipkan
├── Kategori Produk (CRUD)
└── Supplier & Mitra Konveksi (CRUD)

Kasir (POS)
├── Transaksi Kasir Cepat (Interactive Dual-Panel)
├── Keranjang Belanja Dinamis & Stepper Kuantitas
├── Diskon Nominal Transaksi
├── Metode Bayar: Tunai, Transfer Bank, QRIS
├── Tombol Cepat Uang Diterima & Kembalian
└── Cetak Struk Thermal (Ukuran 58 mm & 80 mm)

Riwayat Penjualan
├── Daftar Faktur Kasir & Pencarian Invoice
├── Rincian Faktur & Snapshot HPP Transaksi
└── Cetak Struk Ulang

Manajemen Stok (Admin)
├── Stok Fisik & Valuasi per Varian
├── Penerimaan Barang Masuk (PO) & Pembaruan HPP
├── Penyesuaian Stok (Stock Opname Fisik)
└── Buku Ledger Mutasi Stok (Audit Trail)

HPP & Keuntungan (Admin)
├── Analisis Keuntungan Kotor & Margin (%)
├── Keuntungan per Produk Terjual
├── Kontribusi Laba per Kategori
└── HPP Berjalan Moving Average per Varian

Laporan Toko (Admin)
├── Laporan Penjualan
├── Laporan Barang Masuk (PO)
├── Laporan Mutasi Stok Ledger
├── Laporan Valuasi Nilai Persediaan
├── Laporan Keuntungan Kotor
├── Laporan Produk Terlaris
└── Unduh Ekspor CSV / Excel

Pengaturan (Admin)
├── Profil Toko (Nama, Alamat, Kontak)
├── Footer Struk Kasir & Zona Waktu WITA
└── Manajemen Pengguna & Hak Akses
```

---

Didesain dan dibangun dengan standar arsitektur profesional untuk **Rose Colection** — Boutique & Modern Fashion.
