SakuMedika — Sistem Konsultasi Gejala Pintar & E-Apotek Terpadu

SakuMedika adalah aplikasi berbasis web yang dikembangkan untuk Lomba Programming ISCOMFEST 2026 (Universitas Mercu Buana Yogyakarta). Website ini membantu masyarakat dan mahasiswa mengenali gejala penyakit harian, mendapatkan panduan perawatan mandiri (self-care) agar cepat pulih, membuat janji temu dokter (Doctor Appointment), serta memesan obat dari apotek terdekat yang terintegrasi dengan Midtrans Payment Gateway (Sandbox).

Fitur Utama

Sesi Tanya Jawab Gejala & Rekomendasi Obat (index.php)

Menganalisis keluhan kesehatan yang diketikkan pengguna menggunakan Natural Language Keyword Scoring.

Memberikan Saran Perawatan Agar Cepat Sembuh beserta rekomendasi obat yang relevan dari 105 data obat resmi (lengkap dengan indikasi, dosis, dan aturan pakai).

Smart Pharmacy Locator & Auto-Fallback Stok (apotek.php)

Menampilkan daftar cabang apotek mitra berdasarkan urutan jarak (Terjauh ke Terdekat maupun Terdekat ke Terjauh).

Prioritas Apotek Terdekat: Saat menekan tombol "+ Masukkan ke Apotek" dari ruang tanya jawab, sistem otomatis memilihkan Apotek Terdekat (0,4 km) yang memiliki stok obat tersebut.

Pengalihan Otomatis: Jika stok obat di apotek terdekat sedang habis, sistem otomatis mengalihkan pesanan ke cabang apotek terdekat berikutnya yang Ready Stok.

Katalog Apotek Market & Keranjang AJAX (apotek.php)

Katalog 105 obat dan alat kesehatan dengan filter kategori, kolom pencarian, dan Pagination (12 produk per halaman) dalam tata letak 4 kolom yang rapi.

Penambahan item ke keranjang dan pengaturan jumlah (+ / -) berjalan secara real-time menggunakan Fetch AJAX tanpa memuat ulang (reload) halaman.

Pembayaran Digital Midtrans Snap Sandbox (apotek.php & db.php)

Terintegrasi dengan Midtrans Snap API (Sandbox) untuk pembayaran menggunakan QRIS, GoPay, ShopeePay, dan Virtual Account (BCA, BRI, BNI, Mandiri).

Dilengkapi pengecekan status pembayaran otomatis (Auto-Polling setiap 4 detik) ke server Midtrans.

Jadwal Dokter & Buat Janji Temu (doctor.php)

Menampilkan daftar dokter beserta formulir pembuatan janji temu (Make an Appointment) yang menghasilkan kode booking instan.

Profil & Informasi Layanan (about_us.php & our_service.php)

Menyajikan informasi visi layanan, standar farmasi, dan rincian layanan kesehatan SakuMedika.

Teknologi yang Digunakan

Backend: PHP 8.x (Native)

Database: MySQL / MariaDB (db_apotek_ai)

Frontend: HTML5, CSS3 (Crystal Blue Glassmorphism UI, CSS Grid & Flexbox), JavaScript (Fetch API AJAX)

Payment Gateway: Midtrans Snap API (Environment: Sandbox)

Web Server: Laragon / XAMPP

📂 Struktur File

sakumedika/
├── database.sql # File SQL database, tabel, dan 105 data obat
├── db.php # Konfigurasi koneksi MySQL, Session Keranjang, & API Midtrans
├── index.php # Halaman Utama (Landing Page) & Sesi Tanya Jawab Gejala
├── apotek.php # Pemilihan Cabang Apotek, Katalog Obat, & Checkout Midtrans
├── doctor.php # Daftar Dokter & Formulir Buat Janji Temu (Appointment)
├── about_us.php # Halaman Tentang Kami
├── our_service.php # Halaman Layanan Kesehatan SakuMedika
└── README.md # Dokumentasi panduan menjalankan website

Cara Menjalankan Website (Localhost)

Nyalakan Web Server:
Buka Laragon atau XAMPP, lalu jalankan modul Apache dan MySQL.

Salin Folder Proyek:
Letakkan folder proyek di dalam C:\laragon\www\sakumedika (Laragon) atau C:\xampp\htdocs\sakumedika (XAMPP).

Import Database:

Buka http://localhost/phpmyadmin di browser.

Buka tab SQL atau Import, lalu jalankan file database.sql.

Database db_apotek_ai beserta tabel obat (105 data) dan pesanan akan otomatis terbuat.

Akses Website:
Buka browser dan kunjungi alamat:

http://localhost/sakumedika/index.php

Panduan Simulasi Pembayaran Midtrans Sandbox

Pilih obat melalui Sesi Tanya Jawab (index.php) atau langsung dari katalog Apotek Market (apotek.php).

Buka Keranjang, lengkapi data penerima, lalu klik "Lanjutkan ke Pembayaran".

Pada popup Midtrans Snap, pilih metode pembayaran (contoh: BCA Virtual Account) lalu salin (Copy) nomor Virtual Account yang muncul.

Klik tombol "Payment Simulator" pada kartu tagihan (atau buka https://simulator.sandbox.midtrans.com/), tempelkan nomor Virtual Account, lalu klik Inquire dan Pay.

Kembali ke tab website SakuMedika, status pesanan akan otomatis berubah menjadi "PEMBAYARAN TERVERIFIKASI • LUNAS".

Sumber Aset Terbuka (Open Source)

Google Fonts: Plus Jakarta Sans & Playfair Display (fonts.google.com)

Unsplash: Foto ilustrasi medis & dokter (unsplash.com)

Midtrans Sandbox API: Gerbang simulasi pembayaran (midtrans.com)
