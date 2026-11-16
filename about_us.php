<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - SakuMedika</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --crystal-navy: #0B2545;
            --crystal-blue: #0066FF;
            --crystal-cyan: #00A3E0;
            --glass-card: rgba(255, 255, 255, 0.80);
            --glass-border: rgba(255, 255, 255, 0.94);
            --text-dark: #0F2942;
            --text-muted: #526D82;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e0f0ff 0%, #f4faff 50%, #dcf0ff 100%);
            color: var(--text-dark);
            padding-bottom: 50px;
        }
        .wrapper { max-width: 1200px; margin: 0 auto; padding: 26px 32px; }

        /* NAVBAR DENGAN MENU TEPAT DI TENGAH */
        .landing-nav {
            position: relative;
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 32px; background: rgba(255, 255, 255, 0.76);
            backdrop-filter: blur(22px); -webkit-backdrop-filter: blur(22px);
            border: 1.5px solid var(--glass-border);
            border-radius: 24px; box-shadow: 0 12px 32px rgba(0, 82, 156, 0.07);
            margin-bottom: 36px;
        }
        .medico-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; z-index: 2; }
        .medico-plus-icon {
            width: 38px; height: 38px; border-radius: 12px 12px 12px 4px;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.3rem; box-shadow: 0 6px 16px rgba(0, 102, 255, 0.28);
        }
        .medico-brand-text { font-family: 'Playfair Display', serif; font-size: 1.55rem; font-weight: 800; color: var(--crystal-navy); }
        
        .landing-menu {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            display: flex; align-items: center; gap: 38px; list-style: none;
        }
        .landing-menu > li { position: relative; }
        .landing-menu > li > a {
            text-decoration: none; color: var(--text-dark); font-size: 0.95rem;
            font-weight: 600; padding: 8px 4px; display: inline-flex; align-items: center; gap: 6px;
            transition: color 0.2s;
        }
        .landing-menu > li > a:hover, .landing-menu > li > a.active-link { color: var(--crystal-blue); }

        .dropdown-glass {
            position: absolute; top: 100%; left: 50%; transform: translateX(-50%) translateY(10px);
            min-width: 220px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(22px);
            border: 1.5px solid #ffffff; border-radius: 16px; padding: 10px;
            box-shadow: 0 18px 38px rgba(0, 82, 156, 0.14); list-style: none;
            opacity: 0; visibility: hidden; transition: all 0.22s ease; z-index: 99;
        }
        .has-dropdown:hover .dropdown-glass { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(4px); }
        .dropdown-glass li a { display: block; padding: 10px 14px; text-decoration: none; color: var(--text-dark); font-size: 0.86rem; font-weight: 600; border-radius: 10px; transition: all 0.18s; }
        .dropdown-glass li a:hover { background: rgba(0, 102, 255, 0.08); color: var(--crystal-blue); }

        .nav-right-spacer { width: 140px; }

        .about-hero {
            display: grid; grid-template-columns: 1fr 1.1fr; gap: 40px; align-items: center;
            background: var(--glass-card); backdrop-filter: blur(22px);
            border: 1.5px solid #ffffff; border-radius: 30px; padding: 36px;
            box-shadow: 0 18px 45px rgba(0, 82, 156, 0.09); margin-bottom: 34px;
        }
        .about-img { width: 100%; height: 340px; object-fit: cover; border-radius: 22px; }
        .about-text h1 { font-family: 'Playfair Display', serif; font-size: 2.4rem; color: var(--crystal-navy); margin-bottom: 14px; }
        .about-text p { font-size: 0.95rem; color: var(--text-muted); line-height: 1.75; margin-bottom: 16px; }

        .values-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 22px; }
        .val-card {
            background: var(--glass-card); backdrop-filter: blur(20px);
            border: 1.5px solid #ffffff; border-radius: 24px; padding: 26px;
            box-shadow: 0 12px 30px rgba(0, 82, 156, 0.07);
        }
        .val-badge {
            display: inline-block; font-size: 0.78rem; font-weight: 800; color: var(--crystal-blue);
            background: rgba(0, 102, 255, 0.1); padding: 5px 12px; border-radius: 999px; margin-bottom: 12px;
        }
        .val-card h3 { font-size: 1.15rem; color: var(--crystal-navy); margin-bottom: 8px; }
        .val-card p { font-size: 0.88rem; color: var(--text-muted); line-height: 1.65; }

        @media (max-width: 920px) {
            .landing-menu { position: static; transform: none; flex-wrap: wrap; gap: 16px; }
            .nav-right-spacer { display: none; }
            .about-hero { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <nav class="landing-nav">
            <a href="index.php?sesi=tutup" class="medico-brand">
                <div class="medico-plus-icon">+</div>
                <span class="medico-brand-text">SakuMedika</span>
            </a>

            <ul class="landing-menu">
                <li><a href="index.php?sesi=tutup">Home</a></li>
                <li><a href="doctor.php">Doctor</a></li>
                <li><a href="about_us.php" class="active-link">About Us</a></li>
                <li class="has-dropdown">
                    <a href="our_service.php">Our Service <span style="font-size:0.7rem;">&#9662;</span></a>
                    <ul class="dropdown-glass">
                        <li><a href="our_service.php#konsultasi">Cek Gejala &amp; Saran Pulih</a></li>
                        <li><a href="our_service.php#janji">Janji Temu Spesialis</a></li>
                        <li><a href="our_service.php#farmasi">Tebus Obat Terpadu</a></li>
                    </ul>
                </li>
            </ul>

            <div class="nav-right-spacer"></div>
        </nav>

        <section class="about-hero">
            <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80" alt="Tim Medis SakuMedika" class="about-img">
            <div class="about-text">
                <h1>Mengenal SakuMedika Lebih Dekat</h1>
                <p>
                    <b>SakuMedika</b> hadir sebagai solusi kesehatan digital yang memudahkan masyarakat dan mahasiswa dalam mengenali gejala penyakit sehari-hari secara cepat, tenang, dan terarah.
                </p>
                <p>
                    Kami menggabungkan sesi konsultasi keluhan interaktif, panduan perawatan mandiri di rumah agar lekas sembuh, penjadwalan dokter, serta katalog lebih dari 100 obat resmi dengan informasi takaran dosis yang jelas.
                </p>
            </div>
        </section>

        <section class="values-grid">
            <div class="val-card">
                <span class="val-badge">01 &bull; Edukasi Tepat</span>
                <h3>Saran Pemulihan Mandiri</h3>
                <p>Memberikan langkah pertolongan pertama dan saran istirahat/nutrisi yang mudah dipraktikkan agar tubuh pulih lebih cepat.</p>
            </div>
            <div class="val-card">
                <span class="val-badge">02 &bull; Transparansi Farmasi</span>
                <h3>100+ Database Obat &amp; Dosis</h3>
                <p>Setiap obat dilengkapi informasi indikasi, golongan obat, aturan pakai, hingga peringatan keamanan bagi pasien.</p>
            </div>
            <div class="val-card">
                <span class="val-badge">03 &bull; Akses Terintegrasi</span>
                <h3>Hubungkan Pasien &amp; Dokter</h3>
                <p>Memudahkan pembuatan janji temu dengan dokter umum maupun spesialis apabila gejala memerlukan pemeriksaan medis lanjutan.</p>
            </div>
        </section>
    </div>
</body>
</html>