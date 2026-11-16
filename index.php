<?php
require_once 'db.php';

if (!isset($_SESSION['mode_tanya'])) {
    $_SESSION['mode_tanya'] = false;
}

if (isset($_GET['sesi'])) {
    if ($_GET['sesi'] === 'mulai') {
        $_SESSION['mode_tanya'] = true;
    } elseif ($_GET['sesi'] === 'tutup') {
        $_SESSION['mode_tanya'] = false;
    } elseif ($_GET['sesi'] === 'reset') {
        $_SESSION['chat_history'] = [];
        $_SESSION['mode_tanya'] = true;
    }
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

if (isset($_POST['beli_obat_id'])) {
    $id_obat = (int)$_POST['beli_obat_id'];
    if ($id_obat > 0) {
        header("Location: apotek.php?auto_cari_obat=" . $id_obat);
        exit;
    }
}

function analisisKeluhanMedis($conn, $keluhan) {
    $keluhan_clean = strtolower(trim($keluhan));
    $keluhan_clean = preg_replace('/[^a-z0-9\s]/', ' ', $keluhan_clean);
    
    $stopwords = ['aku', 'saya', 'lagi', 'sedang', 'merasa', 'rasanya', 'sakit', 'banget', 'sekali', 'sudah', 'dari', 'kemarin', 'tadi', 'pagi', 'malam', 'hari', 'ini', 'ada', 'obat', 'apa', 'ya', 'kak', 'dok', 'tolong', 'buat', 'agar', 'biar', 'cepat', 'sembuh', 'yang', 'di', 'ke', 'dan', 'atau', 'kalau', 'gimana', 'kenapa', 'badan', 'tubuh', 'rasa'];
    
    $words = array_filter(explode(' ', $keluhan_clean), function($w) use ($stopwords) {
        return strlen($w) >= 3 && !in_array($w, $stopwords);
    });

    $frasa_khusus = ['sakit kepala', 'sakit gigi', 'sakit perut', 'sakit mata', 'sakit tenggorokan', 'asam lambung', 'masuk angin', 'kurang darah', 'susah tidur', 'mata merah', 'mata kering', 'batuk berdahak', 'batuk kering', 'nyeri haid', 'nyeri otot', 'panas dalam', 'kutu air', 'luka bakar'];
    $frasa_terdeteksi = [];
    foreach ($frasa_khusus as $frasa) {
        if (strpos($keluhan_clean, $frasa) !== false) {
            $frasa_terdeteksi[] = $frasa;
        }
    }

    $query = mysqli_query($conn, "SELECT * FROM obat WHERE stok > 0");
    $hasil_skor = [];

    while ($row = mysqli_fetch_assoc($query)) {
        $skor = 0;
        $teks_gejala = strtolower($row['gejala_keyword'] . ' ' . $row['indikasi'] . ' ' . $row['kategori']);
        $nama_obat_lower = strtolower($row['nama_obat']);

        foreach ($frasa_terdeteksi as $frasa) {
            if (strpos($teks_gejala, $frasa) !== false) {
                $skor += 8;
            }
        }

        foreach ($words as $kata) {
            if (strpos($row['gejala_keyword'], $kata) !== false) {
                $skor += 4;
            }
            if (strpos(strtolower($row['indikasi']), $kata) !== false) {
                $skor += 2;
            }
            if (strpos($nama_obat_lower, $kata) !== false) {
                $skor += 5;
            }
        }

        if ($skor > 0) {
            $row['skor_medis'] = $skor;
            $hasil_skor[] = $row;
        }
    }

    usort($hasil_skor, function($a, $b) {
        return $b['skor_medis'] <=> $a['skor_medis'];
    });

    return array_slice($hasil_skor, 0, 3);
}

if (isset($_POST['kirim_keluhan']) && !empty(trim($_POST['keluhan']))) {
    $_SESSION['mode_tanya'] = true;
    $keluhan_user = htmlspecialchars(trim($_POST['keluhan']));
    $rekomendasi = analisisKeluhanMedis($conn, $keluhan_user);

    $saran_gabungan = [];
    foreach ($rekomendasi as $rek) {
        if (!in_array($rek['saran_sembuh'], $saran_gabungan)) {
            $saran_gabungan[] = $rek['saran_sembuh'];
        }
    }

    $_SESSION['chat_history'][] = [
        'waktu' => date('H:i'),
        'keluhan' => $keluhan_user,
        'saran' => array_slice($saran_gabungan, 0, 3),
        'obat' => $rekomendasi
    ];
}

$total_keranjang = hitungItemKeranjang();
$mode_tanya = $_SESSION['mode_tanya'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SakuMedika - Layanan Kesehatan & Konsultasi Medis</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --crystal-navy: #0B2545;
            --crystal-blue: #0066FF;
            --crystal-royal: #00529C;
            --crystal-cyan: #00A3E0;
            --glass-bg: rgba(255, 255, 255, 0.68);
            --glass-card: rgba(255, 255, 255, 0.82);
            --glass-border: rgba(255, 255, 255, 0.94);
            --text-dark: #0F2942;
            --text-muted: #526D82;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e0f0ff 0%, #f4faff 50%, #dcf0ff 100%);
            color: var(--text-dark);
            position: relative;
            overflow-x: hidden;
        }

        .bg-wave-top {
            position: fixed; top: -120px; right: -100px; width: 650px; height: 650px;
            background: radial-gradient(circle, rgba(0, 163, 224, 0.2) 0%, rgba(0, 102, 255, 0.05) 70%, transparent 100%);
            border-radius: 50%; z-index: 0; pointer-events: none;
        }
        .bg-wave-bottom {
            position: fixed; bottom: -150px; left: -120px; width: 550px; height: 550px;
            background: radial-gradient(circle, rgba(0, 102, 255, 0.15) 0%, transparent 70%);
            border-radius: 50%; z-index: 0; pointer-events: none;
        }

        .landing-wrapper {
            position: relative; z-index: 10; min-height: 100vh;
            display: flex; flex-direction: column; justify-content: space-between;
            max-width: 1200px; margin: 0 auto; padding: 26px 32px 36px 32px;
        }

        .landing-nav {
            position: relative;
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 32px; background: rgba(255, 255, 255, 0.76);
            backdrop-filter: blur(22px); -webkit-backdrop-filter: blur(22px);
            border: 1.5px solid var(--glass-border); border-radius: 24px;
            box-shadow: 0 12px 32px rgba(0, 82, 156, 0.07);
        }
        .medico-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; z-index: 2; }
        .medico-plus-icon {
            width: 38px; height: 38px; border-radius: 12px 12px 12px 4px;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.3rem; box-shadow: 0 6px 16px rgba(0, 102, 255, 0.28);
        }
        .medico-brand-text {
            font-family: 'Playfair Display', serif; font-size: 1.55rem;
            font-weight: 800; color: var(--crystal-navy);
        }

        .landing-menu {
            position: absolute; left: 50%; transform: translateX(-50%);
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
        .has-dropdown:hover .dropdown-glass {
            opacity: 1; visibility: visible; transform: translateX(-50%) translateY(4px);
        }
        .dropdown-glass li a {
            display: block; padding: 10px 14px; text-decoration: none;
            color: var(--text-dark); font-size: 0.86rem; font-weight: 600; border-radius: 10px;
            transition: all 0.18s;
        }
        .dropdown-glass li a:hover { background: rgba(0, 102, 255, 0.08); color: var(--crystal-blue); }

        .nav-right-spacer { width: 140px; }

        .hero-section {
            display: grid; grid-template-columns: 1.05fr 1fr; gap: 48px;
            align-items: center; margin: auto 0; padding: 36px 6px;
        }
        .hero-left { max-width: 540px; }
        .hero-subtitle {
            display: inline-block; font-size: 0.86rem; font-weight: 700; color: var(--crystal-royal);
            background: rgba(255, 255, 255, 0.78); padding: 7px 16px; border-radius: 999px;
            border: 1px solid var(--glass-border); margin-bottom: 18px;
        }
        .hero-title {
            font-family: 'Playfair Display', serif; font-size: 3.45rem;
            font-weight: 800; color: var(--crystal-navy); line-height: 1.15; margin-bottom: 20px;
        }
        .hero-desc { font-size: 0.98rem; color: var(--text-muted); line-height: 1.75; margin-bottom: 32px; }
        .btn-masuk-utama {
            display: inline-flex; align-items: center; gap: 12px; text-decoration: none;
            padding: 16px 36px; border-radius: 14px;
            background: linear-gradient(135deg, #0066FF 0%, #00A3E0 100%);
            color: #ffffff; font-weight: 700; font-size: 1rem;
            box-shadow: 0 14px 30px rgba(0, 102, 255, 0.32); transition: all 0.25s ease;
        }
        .btn-masuk-utama:hover { transform: translateY(-3px); box-shadow: 0 18px 36px rgba(0, 102, 255, 0.42); }

        .hero-right { position: relative; display: flex; justify-content: center; }
        .real-photo-frame {
            width: 100%; max-width: 500px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.78), rgba(224, 242, 254, 0.58));
            backdrop-filter: blur(24px); border: 2.5px solid #ffffff; border-radius: 32px;
            padding: 16px; box-shadow: 0 24px 55px rgba(0, 82, 156, 0.14); position: relative;
        }
        .real-doctor-img {
            width: 100%; height: 390px; object-fit: cover; object-position: center top;
            border-radius: 22px; display: block;
        }
        .floating-glass-tag {
            position: absolute; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(16px);
            border: 1.5px solid #ffffff; border-radius: 18px; padding: 12px 16px;
            box-shadow: 0 12px 28px rgba(0, 82, 156, 0.14); display: flex; align-items: center; gap: 12px;
        }
        .tag-bottom-left { bottom: -16px; left: -20px; }
        .tag-top-right { top: 24px; right: -18px; }
        .tag-dot-green { width: 12px; height: 12px; border-radius: 50%; background: #10B981; box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.2); }
        .tag-title { font-size: 0.84rem; font-weight: 800; color: var(--crystal-navy); }
        .tag-sub { font-size: 0.72rem; color: var(--text-muted); font-weight: 600; }

        .consult-screen { height: 100vh; display: flex; flex-direction: column; position: relative; z-index: 10; }
        .consult-topbar { display: flex; align-items: center; justify-content: space-between; padding: 16px 28px; }
        .market-corner-btn {
            display: inline-flex; align-items: center; gap: 12px; text-decoration: none;
            padding: 10px 18px 10px 12px; border-radius: 18px;
            background: rgba(255, 255, 255, 0.76); backdrop-filter: blur(20px);
            border: 1.5px solid var(--glass-border); box-shadow: 0 10px 26px rgba(0, 82, 156, 0.12);
            color: var(--crystal-navy); transition: all 0.25s ease;
        }
        .market-corner-btn:hover { background: #ffffff; transform: translateY(-2px); }
        .market-icon-wrap {
            width: 40px; height: 40px; border-radius: 13px;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; display: flex; align-items: center; justify-content: center;
        }
        .market-text { display: flex; flex-direction: column; line-height: 1.2; }
        .market-title { font-weight: 800; font-size: 0.92rem; color: var(--crystal-navy); }
        .market-sub { font-size: 0.73rem; font-weight: 600; color: var(--text-muted); }
        .market-badge {
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; font-size: 0.75rem; font-weight: 800; padding: 4px 10px; border-radius: 999px;
        }
        .top-right-actions { display: flex; align-items: center; gap: 10px; }
        .btn-glass-small {
            text-decoration: none; font-size: 0.82rem; font-weight: 700; color: var(--crystal-navy);
            padding: 9px 16px; border-radius: 14px; background: rgba(255, 255, 255, 0.68);
            backdrop-filter: blur(14px); border: 1.5px solid var(--glass-border); transition: 0.2s;
        }
        .btn-glass-small:hover { background: #ffffff; }

        .chat-scroll-area { flex: 1; overflow-y: auto; padding: 10px 20px 30px 20px; display: flex; flex-direction: column; }
        .chat-center-box { width: 100%; max-width: 780px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px; flex: 1; }
        .welcome-consult-box {
            margin: auto; text-align: center; max-width: 560px; background: rgba(255, 255, 255, 0.68);
            backdrop-filter: blur(22px); border: 1.5px solid var(--glass-border); border-radius: 28px;
            padding: 34px 30px; box-shadow: 0 18px 40px rgba(0, 82, 156, 0.08);
        }
        .steth-circle {
            width: 58px; height: 58px; margin: 0 auto 16px auto; border-radius: 18px;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; display: flex; align-items: center; justify-content: center;
        }
        .welcome-consult-box h2 { font-family: 'Playfair Display', serif; font-size: 1.75rem; color: var(--crystal-navy); margin-bottom: 10px; }
        .welcome-consult-box p { font-size: 0.92rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px; }
        .quick-symptom-list { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
        .symptom-pill {
            background: rgba(255, 255, 255, 0.85); border: 1px solid rgba(0, 102, 255, 0.2);
            padding: 9px 15px; border-radius: 999px; font-size: 0.82rem; font-weight: 600;
            color: var(--crystal-navy); cursor: pointer; transition: 0.2s;
        }
        .symptom-pill:hover { background: var(--crystal-blue); color: #ffffff; }

        .qa-item { display: flex; flex-direction: column; gap: 14px; }
        .question-bubble {
            align-self: flex-end; background: linear-gradient(135deg, var(--crystal-royal), var(--crystal-cyan));
            color: #ffffff; padding: 13px 20px; border-radius: 20px 20px 4px 20px;
            max-width: 78%; font-size: 0.94rem; font-weight: 500; box-shadow: 0 8px 22px rgba(0, 82, 156, 0.16);
        }
        .answer-glass-card {
            align-self: flex-start; width: 100%; background: var(--glass-bg);
            backdrop-filter: blur(22px); border: 1.5px solid var(--glass-border);
            border-radius: 24px; padding: 22px; box-shadow: 0 12px 32px rgba(0, 82, 156, 0.08);
        }
        .sec-label { font-size: 0.82rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: var(--crystal-blue); margin-bottom: 8px; }
        .care-tips-box {
            background: rgba(255, 255, 255, 0.78); border: 1px solid rgba(0, 163, 224, 0.35);
            border-left: 4px solid var(--crystal-blue); border-radius: 14px; padding: 13px 16px;
            margin-bottom: 18px; font-size: 0.88rem; line-height: 1.6;
        }
        .care-tips-box ul { padding-left: 18px; }
        .med-recommend-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 12px; }
        .med-item-card {
            background: var(--glass-card); border: 1px solid #ffffff; border-radius: 16px;
            padding: 14px; display: flex; flex-direction: column; justify-content: space-between;
        }
        .med-item-name { font-weight: 800; font-size: 0.92rem; color: var(--crystal-navy); margin-bottom: 4px; }
        .med-item-price { font-weight: 800; font-size: 0.86rem; color: var(--crystal-blue); margin-bottom: 8px; }
        .med-item-indikasi { font-size: 0.79rem; color: var(--text-muted); line-height: 1.45; margin-bottom: 10px; }
        .med-item-dosis { font-size: 0.76rem; background: rgba(0, 102, 255, 0.06); padding: 7px 9px; border-radius: 9px; margin-bottom: 10px; line-height: 1.45; }
        .btn-beli-obat {
            width: 100%; padding: 9px 12px; border-radius: 10px; border: none;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; font-weight: 700; font-size: 0.8rem; cursor: pointer;
            transition: transform 0.18s ease, filter 0.18s ease;
        }
        .btn-beli-obat:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }
        .bottom-input-bar { padding: 12px 20px 24px 20px; }
        .crystal-input-form {
            max-width: 780px; margin: 0 auto; background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(24px); border: 1.5px solid var(--glass-border); border-radius: 999px;
            padding: 7px 8px 7px 22px; box-shadow: 0 14px 36px rgba(0, 82, 156, 0.14);
            display: flex; align-items: center; gap: 10px;
        }
        .crystal-text-input { flex: 1; border: none; background: transparent; font-size: 0.95rem; color: var(--text-dark); outline: none; padding: 8px 0; }
        .btn-kirim-tanya {
            width: 44px; height: 44px; border-radius: 50%; border: none;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer;
        }

        @media (max-width: 920px) {
            .landing-menu { position: static; transform: none; flex-wrap: wrap; gap: 16px; }
            .nav-right-spacer { display: none; }
            .hero-section { grid-template-columns: 1fr; }
            .hero-title { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

    <div class="bg-wave-top"></div>
    <div class="bg-wave-bottom"></div>

    <?php if (!$mode_tanya): ?>
        <div class="landing-wrapper">
            <nav class="landing-nav">
                <a href="index.php" class="medico-brand">
                    <div class="medico-plus-icon">+</div>
                    <span class="medico-brand-text">SakuMedika</span>
                </a>

                <ul class="landing-menu">
                    <li><a href="index.php" class="active-link">Home</a></li>
                    <li><a href="doctor.php">Doctor</a></li>
                    <li><a href="about_us.php">About Us</a></li>
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

            <section class="hero-section">
                <div class="hero-left">
                    <span class="hero-subtitle">We Are Here For Your Care</span>
                    <h1 class="hero-title">Best Care &amp; Better Doctor</h1>
                    <p class="hero-desc">
                        Konsultasikan keluhan kesehatan Anda dengan nyaman. Dapatkan saran perawatan mandiri agar cepat pulih, rekomendasi obat resmi beserta dosisnya, atau jadwalkan janji temu langsung dengan dokter kami.
                    </p>
                    <a href="index.php?sesi=mulai" class="btn-masuk-utama">
                        <span>Masuk ke Sesi Tanya Jawab</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>

                <div class="hero-right">
                    <div class="real-photo-frame">
                        <img src="https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=900&q=85" alt="Dokter Spesialis SakuMedika" class="real-doctor-img">
                        
                        <div class="floating-glass-tag tag-top-right">
                            <div class="tag-dot-green"></div>
                            <div>
                                <div class="tag-title">Dokter Siaga</div>
                                <div class="tag-sub">100+ Data Obat Terverifikasi</div>
                            </div>
                        </div>

                        <div class="floating-glass-tag tag-bottom-left">
                            <div>
                                <div class="tag-title">Layanan Konsultasi &amp; Janji Temu</div>
                                <div class="tag-sub">Rekomendasi Tepat &bull; Cepat Pulih</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div style="text-align: center; font-size: 0.8rem; color: var(--text-muted);">
                SakuMedika &bull; Layanan Konsultasi Kesehatan &amp; Apotek Digital
            </div>
        </div>

    <?php else: ?>
        <div class="consult-screen">
            <header class="consult-topbar">
                <a href="apotek.php?ganti_apotek=1" class="market-corner-btn" title="Ke Halaman Daftar Apotek">
                    <div class="market-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                    </div>
                    <div class="market-text">
                        <span class="market-title">Apotek Market</span>
                        <span class="market-sub">Beli Obat Resmi</span>
                    </div>
                    <?php if ($total_keranjang > 0): ?>
                        <span class="market-badge"><?php echo $total_keranjang; ?></span>
                    <?php endif; ?>
                </a>

                <div class="top-right-actions">
                    <?php if (!empty($_SESSION['chat_history'])): ?>
                        <a href="index.php?sesi=reset" class="btn-glass-small">Ulangi Sesi</a>
                    <?php endif; ?>
                    <a href="index.php?sesi=tutup" class="btn-glass-small">&larr; Kembali ke Home</a>
                </div>
            </header>

            <main class="chat-scroll-area" id="chatViewport">
                <div class="chat-center-box">
                    <?php if (empty($_SESSION['chat_history'])): ?>
                        <div class="welcome-consult-box">
                            <div class="steth-circle">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                                </svg>
                            </div>
                            <h2>Sesi Tanya Jawab Kesehatan</h2>
                            <p>Silakan tulis keluhan atau gejala sakit yang Anda rasakan pada kolom di bawah, atau pilih keluhan cepat berikut:</p>

                            <form method="POST" action="index.php" class="quick-symptom-list">
                                <input type="hidden" name="kirim_keluhan" value="1">
                                <button type="submit" name="keluhan" value="Badan demam meriang dan kepala pusing" class="symptom-pill">Demam &amp; Sakit Kepala</button>
                                <button type="submit" name="keluhan" value="Batuk berdahak dan tenggorokan gatal" class="symptom-pill">Batuk Berdahak</button>
                                <button type="submit" name="keluhan" value="Perut perih maag asam lambung naik dan mual" class="symptom-pill">Maag &amp; Asam Lambung</button>
                                <button type="submit" name="keluhan" value="Diare mencret dan perut melilit" class="symptom-pill">Diare &amp; Mulas</button>
                                <button type="submit" name="keluhan" value="Sariawan perih dan panas dalam" class="symptom-pill">Sariawan</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <?php foreach ($_SESSION['chat_history'] as $chat): ?>
                            <div class="qa-item">
                                <div class="question-bubble">
                                    <?php echo $chat['keluhan']; ?>
                                </div>

                                <div class="answer-glass-card">
                                    <?php if (!empty($chat['obat'])): ?>
                                        <div class="sec-label">Saran Perawatan Agar Cepat Sembuh</div>
                                        <div class="care-tips-box">
                                            <ul>
                                                <?php foreach ($chat['saran'] as $s): ?>
                                                    <li><?php echo htmlspecialchars($s); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>

                                        <div class="sec-label">Rekomendasi Obat</div>
                                        <div class="med-recommend-grid">
                                            <?php foreach ($chat['obat'] as $obt): ?>
                                                <div class="med-item-card">
                                                    <div>
                                                        <div class="med-item-name"><?php echo htmlspecialchars($obt['nama_obat']); ?></div>
                                                        <div class="med-item-price"><?php echo formatRupiah($obt['harga']); ?></div>
                                                        <div class="med-item-indikasi"><?php echo htmlspecialchars($obt['indikasi']); ?></div>
                                                        <div class="med-item-dosis">
                                                            <b>Dosis:</b> <?php echo htmlspecialchars($obt['dosis']); ?><br>
                                                            <b>Aturan:</b> <?php echo htmlspecialchars($obt['aturan_pakai']); ?>
                                                        </div>
                                                    </div>
                                                    <form method="POST" action="index.php">
                                                        <input type="hidden" name="beli_obat_id" value="<?php echo (int)$obt['id']; ?>">
                                                        <button type="submit" class="btn-beli-obat">+ Masukkan ke Apotek</button>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p style="font-size: 0.9rem; line-height: 1.6;">
                                            Keluhan belum teridentifikasi secara spesifik. Coba tuliskan gejala fisik seperti <b>"demam", "batuk berdahak", "sakit gigi", "mual maag", "diare",</b> atau <b>"gatal alergi"</b>.
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>

            <footer class="bottom-input-bar">
                <form method="POST" action="index.php" class="crystal-input-form">
                    <input type="hidden" name="kirim_keluhan" value="1">
                    <input type="text" name="keluhan" class="crystal-text-input" placeholder="Ceritakan keluhan sakit yang Anda rasakan..." required autocomplete="off" autofocus>
                    <button type="submit" class="btn-kirim-tanya" title="Kirim Keluhan">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </form>
            </footer>
        </div>

        <script>
            const vp = document.getElementById('chatViewport');
            if (vp) { vp.scrollTop = vp.scrollHeight; }
        </script>
    <?php endif; ?>

</body>
</html>