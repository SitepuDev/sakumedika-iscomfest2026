<?php
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Service - SakuMedika</title>
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

        .page-head { text-align: center; max-width: 660px; margin: 0 auto 36px auto; }
        .page-head h1 { font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--crystal-navy); margin-bottom: 10px; }
        .page-head p { color: var(--text-muted); font-size: 0.95rem; line-height: 1.65; }

        .service-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; }
        .srv-card {
            background: var(--glass-card); backdrop-filter: blur(22px);
            border: 1.5px solid #ffffff; border-radius: 26px; padding: 30px;
            box-shadow: 0 14px 34px rgba(0, 82, 156, 0.08);
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .srv-icon {
            width: 52px; height: 52px; border-radius: 16px;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; display: flex; align-items: center; justify-content: center;
            margin-bottom: 18px; box-shadow: 0 8px 18px rgba(0, 102, 255, 0.25);
        }
        .srv-card h3 { font-size: 1.25rem; color: var(--crystal-navy); margin-bottom: 10px; }
        .srv-card p { font-size: 0.9rem; color: var(--text-muted); line-height: 1.65; margin-bottom: 22px; }
        .btn-srv {
            display: inline-block; text-decoration: none; text-align: center;
            padding: 12px 20px; border-radius: 12px;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; font-weight: 700; font-size: 0.86rem;
        }

        @media (max-width: 920px) {
            .landing-menu { position: static; transform: none; flex-wrap: wrap; gap: 16px; }
            .nav-right-spacer { display: none; }
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
                <li><a href="about_us.php">About Us</a></li>
                <li class="has-dropdown">
                    <a href="our_service.php" class="active-link">Our Service <span style="font-size:0.7rem;">&#9662;</span></a>
                    <ul class="dropdown-glass">
                        <li><a href="our_service.php#konsultasi">Cek Gejala &amp; Saran Pulih</a></li>
                        <li><a href="our_service.php#janji">Janji Temu Spesialis</a></li>
                        <li><a href="our_service.php#farmasi">Tebus Obat Terpadu</a></li>
                    </ul>
                </li>
            </ul>

            <div class="nav-right-spacer"></div>
        </nav>

        <div class="page-head">
            <h1>Layanan Unggulan SakuMedika</h1>
            <p>Kami menghadirkan tiga layanan kesehatan utama yang dirancang untuk mempercepat pemulihan Anda dengan nyaman.</p>
        </div>

        <div class="service-list">
            <div class="srv-card" id="konsultasi">
                <div>
                    <div class="srv-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <h3>Sesi Tanya Jawab Gejala &amp; Saran Pulih</h3>
                    <p>Sampaikan keluhan sakit yang Anda rasakan dengan bahasa sehari-hari. Sistem akan memberikan panduan perawatan mandiri agar cepat sembuh beserta rekomendasi obat yang sesuai.</p>
                </div>
                <a href="index.php?sesi=mulai" class="btn-srv">Mulai Tanya Jawab Sekarang &rarr;</a>
            </div>

            <div class="srv-card" id="janji">
                <div>
                    <div class="srv-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <h3>Janji Temu Dokter &amp; Spesialis</h3>
                    <p>Butuh pemeriksaan fisik lebih lanjut? Pilih jadwal praktik dokter umum maupun dokter spesialis kami dan dapatkan kode antrean instan secara daring.</p>
                </div>
                <a href="doctor.php" class="btn-srv">Buat Janji Dokter &rarr;</a>
            </div>

            <div class="srv-card" id="farmasi">
                <div>
                    <div class="srv-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    </div>
                    <h3>Informasi Dosis &amp; Katalog Obat Resmi</h3>
                    <p>Tersedia lebih dari 100 obat pilihan lengkap dengan penjelasan dosis, aturan konsumsi, serta integrasi pembelian obat langsung setelah sesi konsultasi.</p>
                </div>
                <a href="index.php?sesi=mulai" class="btn-srv">Masuk Konsultasi &amp; Tebus Obat &rarr;</a>
            </div>
        </div>
    </div>
</body>
</html>