<?php
require_once 'db.php';

if (!isset($_SESSION['janji_dokter'])) {
    $_SESSION['janji_dokter'] = [];
}

$notif_janji = '';
if (isset($_POST['buat_janji'])) {
    $nama_pasien = htmlspecialchars(trim($_POST['nama_pasien']));
    $email_pasien = htmlspecialchars(trim($_POST['email_pasien']));
    $layanan = htmlspecialchars(trim($_POST['layanan']));
    $jam_janji = htmlspecialchars(trim($_POST['jam_janji']));
    $catatan = htmlspecialchars(trim($_POST['catatan']));

    if ($nama_pasien && $email_pasien) {
        $kode_booking = 'APT-' . rand(1000, 9999);
        $_SESSION['janji_dokter'][] = [
            'kode' => $kode_booking,
            'nama' => $nama_pasien,
            'email' => $email_pasien,
            'layanan' => $layanan,
            'jam' => $jam_janji,
            'catatan' => $catatan
        ];
        $notif_janji = "Appointment berhasil dibuat! Kode Booking Anda: $kode_booking ($layanan - $jam_janji).";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors - SakuMedika</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --crystal-navy: #0B2545;
            --crystal-blue: #0066FF;
            --crystal-cyan: #00A3E0;
            --glass-border: rgba(255, 255, 255, 0.94);
            --text-dark: #0F2942;
            --text-muted: #64748B;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background: #ffffff;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        .top-nav-container {
            background: linear-gradient(135deg, #e0f0ff 0%, #f4faff 100%);
            padding: 22px 32px;
        }
        .landing-nav {
            position: relative;
            max-width: 1160px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 32px; background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(22px); -webkit-backdrop-filter: blur(22px);
            border: 1.5px solid var(--glass-border); border-radius: 24px;
            box-shadow: 0 10px 28px rgba(0, 82, 156, 0.07);
        }
        .medico-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; z-index: 2; }
        .medico-plus-icon {
            width: 38px; height: 38px; border-radius: 12px 12px 12px 4px;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.3rem;
        }
        .medico-brand-text { font-family: 'Playfair Display', serif; font-size: 1.55rem; font-weight: 800; color: var(--crystal-navy); }

        .landing-menu {
            position: absolute; left: 50%; transform: translateX(-50%);
            display: flex; align-items: center; gap: 38px; list-style: none;
        }
        .landing-menu > li { position: relative; }
        .landing-menu > li > a { text-decoration: none; color: var(--text-dark); font-size: 0.95rem; font-weight: 600; padding: 8px 4px; }
        .landing-menu > li > a:hover, .landing-menu > li > a.active-link { color: var(--crystal-blue); }

        .dropdown-glass {
            position: absolute; top: 100%; left: 50%; transform: translateX(-50%) translateY(10px);
            min-width: 220px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(22px);
            border: 1.5px solid #ffffff; border-radius: 16px; padding: 10px;
            box-shadow: 0 18px 38px rgba(0, 82, 156, 0.14); list-style: none;
            opacity: 0; visibility: hidden; transition: all 0.22s ease; z-index: 99;
        }
        .has-dropdown:hover .dropdown-glass { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(4px); }
        .dropdown-glass li a { display: block; padding: 10px 14px; text-decoration: none; color: var(--text-dark); font-size: 0.86rem; font-weight: 600; border-radius: 10px; }
        .dropdown-glass li a:hover { background: rgba(0, 102, 255, 0.08); color: var(--crystal-blue); }
        .nav-right-spacer { width: 140px; }

        .doctors-hero-banner {
            background: repeating-linear-gradient(
                -55deg,
                #0066FF,
                #0066FF 48px,
                #0b72ff 48px,
                #0b72ff 96px
            );
            padding: 58px 24px;
            text-align: center;
            color: #ffffff;
        }
        .doctors-hero-banner h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.3rem;
            font-weight: 800;
            letter-spacing: 0.4px;
        }

        .our-doctors-section {
            max-width: 1060px;
            margin: 0 auto;
            padding: 68px 24px 78px 24px;
        }
        .our-doctors-header {
            text-align: center;
            margin-bottom: 48px;
        }
        .our-doctors-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: #111827;
            margin-bottom: 8px;
        }
        .our-doctors-header p {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .doctors-four-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 26px;
        }
        .doc-clean-item {
            cursor: pointer;
            transition: transform 0.22s ease;
        }
        .doc-clean-item:hover {
            transform: translateY(-5px);
        }
        .doc-clean-photo {
            width: 100%;
            height: 260px;
            object-fit: cover;
            object-position: top center;
            display: block;
            margin-bottom: 14px;
        }
        .doc-clean-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
            transition: color 0.2s;
        }
        .doc-clean-item:hover .doc-clean-name,
        .doc-clean-item.highlight .doc-clean-name {
            color: var(--crystal-blue);
        }
        .doc-clean-role {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

       
        .appointment-blue-section {
            background: linear-gradient(135deg, #1E2296 0%, #252B9E 55%, #1A1F7A 100%);
            padding: 76px 24px;
            color: #ffffff;
        }
        .appointment-inner {
            max-width: 1020px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 48px;
            align-items: center;
        }
        .appt-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.65rem;
            font-weight: 700;
            margin-bottom: 26px;
            color: #ffffff;
        }

        .appt-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }
        .appt-field label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 7px;
            color: rgba(255, 255, 255, 0.92);
        }
        .appt-input, .appt-select, .appt-textarea {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1.2px solid rgba(255, 255, 255, 0.65);
            color: #ffffff;
            padding: 11px 13px;
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .appt-input::placeholder, .appt-textarea::placeholder {
            color: rgba(255, 255, 255, 0.55);
        }
        .appt-select option {
            color: #0F2942;
            background: #ffffff;
        }
        .appt-input:focus, .appt-select:focus, .appt-textarea:focus {
            border-color: #38BDF8;
            background: rgba(255, 255, 255, 0.1);
        }
        .appt-textarea {
            height: 115px;
            resize: vertical;
            margin-bottom: 20px;
        }

        .btn-make-appt {
            background: #ffffff;
            color: #1E2296;
            border: none;
            padding: 13px 28px;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-make-appt:hover {
            background: #E0F2FE;
            transform: translateY(-2px);
        }

        .appt-right-image-wrap {
            display: flex;
            justify-content: center;
        }
        .appt-right-img {
            width: 100%;
            max-width: 390px;
            height: 330px;
            object-fit: cover;
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.28);
        }

        .notif-banner {
            background: rgba(56, 189, 248, 0.2);
            border: 1px solid #38BDF8;
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.86rem;
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .landing-menu { position: static; transform: none; flex-wrap: wrap; gap: 16px; }
            .nav-right-spacer { display: none; }
            .doctors-four-grid { grid-template-columns: repeat(2, 1fr); }
            .appointment-inner { grid-template-columns: 1fr; }
        }
        @media (max-width: 540px) {
            .doctors-four-grid { grid-template-columns: 1fr; }
            .appt-grid-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="top-nav-container">
        <nav class="landing-nav">
            <a href="index.php?sesi=tutup" class="medico-brand">
                <div class="medico-plus-icon">+</div>
                <span class="medico-brand-text">SakuMedika</span>
            </a>

            <ul class="landing-menu">
                <li><a href="index.php?sesi=tutup">Home</a></li>
                <li><a href="doctor.php" class="active-link">Doctor</a></li>
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
    </div>

    <section class="doctors-hero-banner">
        <h1>Doctors</h1>
    </section>

    <section class="our-doctors-section">
        <div class="our-doctors-header">
            <h2>Our doctors</h2>
            <p>Experienced, Friendly Clinicians Who Take The Time To Explain.</p>
        </div>

        <div class="doctors-four-grid">
            <div class="doc-clean-item" onclick="pilihLayanan('Family medicine - Dr. Tomas Verhoeven')">
                <img src="https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=600&q=80" alt="Dr. Tomas Verhoeven" class="doc-clean-photo">
                <div class="doc-clean-name">Dr. Tomas Verhoeven</div>
                <div class="doc-clean-role">Family medicine</div>
            </div>

            <div class="doc-clean-item" onclick="pilihLayanan('Senior physician - Dr. Henry Lindqvist')">
                <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=600&q=80" alt="Dr. Henry Lindqvist" class="doc-clean-photo">
                <div class="doc-clean-name">Dr. Henry Lindqvist</div>
                <div class="doc-clean-role">Senior physician</div>
            </div>

            <div class="doc-clean-item highlight" onclick="pilihLayanan('General practice - Dr. Priya Raman')">
                <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=600&q=80" alt="Dr. Priya Raman" class="doc-clean-photo">
                <div class="doc-clean-name">Dr. Priya Raman</div>
                <div class="doc-clean-role">General practice</div>
            </div>

            <div class="doc-clean-item" onclick="pilihLayanan('Nurse practitioner - Elena Moretti, RN')">
                <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=600&q=80" alt="Elena Moretti, RN" class="doc-clean-photo">
                <div class="doc-clean-name">Elena Moretti, RN</div>
                <div class="doc-clean-role">Nurse practitioner</div>
            </div>
        </div>
    </section>

    <section class="appointment-blue-section" id="formAppointment">
        <div class="appointment-inner">
            <div>
                <h3 class="appt-title">Make an appointment</h3>

                <?php if (!empty($notif_janji)): ?>
                    <div class="notif-banner"><?php echo $notif_janji; ?></div>
                <?php endif; ?>

                <form method="POST" action="doctor.php#formAppointment">
                    <input type="hidden" name="buat_janji" value="1">
                    
                    <div class="appt-grid-2">
                        <div class="appt-field">
                            <label>Name</label>
                            <input type="text" name="nama_pasien" class="appt-input" required>
                        </div>
                        <div class="appt-field">
                            <label>Email address</label>
                            <input type="email" name="email_pasien" class="appt-input" required>
                        </div>
                    </div>

                    <div class="appt-grid-2">
                        <div class="appt-field">
                            <label>Service</label>
                            <select name="layanan" id="selectLayanan" class="appt-select">
                                <option value="Family check-up">Family check-up</option>
                                <option value="General consultation">General consultation</option>
                                <option value="Family medicine - Dr. Tomas Verhoeven">Family medicine - Dr. Tomas Verhoeven</option>
                                <option value="Senior physician - Dr. Henry Lindqvist">Senior physician - Dr. Henry Lindqvist</option>
                                <option value="General practice - Dr. Priya Raman">General practice - Dr. Priya Raman</option>
                                <option value="Nurse practitioner - Elena Moretti, RN">Nurse practitioner - Elena Moretti, RN</option>
                            </select>
                        </div>
                        <div class="appt-field">
                            <label>Preferred time</label>
                            <select name="jam_janji" class="appt-select">
                                <option value="8 to 10 AM">8 to 10 AM</option>
                                <option value="10 to 12 AM">10 to 12 AM</option>
                                <option value="1 to 3 PM">1 to 3 PM</option>
                                <option value="3 to 5 PM">3 to 5 PM</option>
                            </select>
                        </div>
                    </div>

                    <div class="appt-field">
                        <label>Your note</label>
                        <textarea name="catatan" class="appt-textarea" required></textarea>
                    </div>

                    <button type="submit" class="btn-make-appt">MAKE AN APPOINTMENT</button>
                </form>
            </div>

            <div class="appt-right-image-wrap">
                <img src="https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=700&q=80" alt="Pemeriksaan Dokter" class="appt-right-img">
            </div>
        </div>
    </section>

    <script>
        function pilihLayanan(namaLayanan) {
            const select = document.getElementById('selectLayanan');
            if (select) {
                select.value = namaLayanan;
                document.getElementById('formAppointment').scrollIntoView({ behavior: 'smooth' });
            }
        }
    </script>
</body>
</html>