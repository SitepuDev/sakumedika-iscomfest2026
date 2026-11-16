<?php
require_once 'db.php';

if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}


$daftar_apotek = [
    [
        'id_apotek' => 'APT-01',
        'nama' => 'Apotek Kimia Farma Kaliurang Atas',
        'alamat' => 'Jl. Kaliurang KM 14.5, Umbulmartani, Sleman',
        'jarak_km' => 14.8,
        'jam_buka' => '07.00 - 22.00 WIB',
        'status' => 'Buka • Layanan Antar Jauh',
        'obat_kosong' => []
    ],
    [
        'id_apotek' => 'APT-02',
        'nama' => 'Apotek K-24 Godean Barat',
        'alamat' => 'Jl. Godean KM 10, Sidoagung, Sleman',
        'jarak_km' => 11.2,
        'jam_buka' => 'Buka 24 Jam',
        'status' => 'Buka 24 Jam • Stok Lengkap',
        'obat_kosong' => []
    ],
    [
        'id_apotek' => 'APT-03',
        'nama' => 'Apotek Guardian Ringroad Selatan',
        'alamat' => 'Jl. Ringroad Selatan No. 88, Banguntapan, Bantul',
        'jarak_km' => 8.6,
        'jam_buka' => '08.00 - 22.00 WIB',
        'status' => 'Buka • Tersedia Apoteker',
        'obat_kosong' => [12, 28]
    ],
    [
        'id_apotek' => 'APT-04',
        'nama' => 'Apotek Viva Generik Maguwoharjo',
        'alamat' => 'Jl. Raya Tajem No. 45, Maguwoharjo, Depok',
        'jarak_km' => 6.4,
        'jam_buka' => '07.30 - 21.30 WIB',
        'status' => 'Buka • Ramah Mahasiswa',
        'obat_kosong' => [5, 19]
    ],
    [
        'id_apotek' => 'APT-05',
        'nama' => 'Apotek Century Gejayan',
        'alamat' => 'Jl. Affandi (Gejayan) No. 21, Caturtunggal',
        'jarak_km' => 4.1,
        'jam_buka' => '08.00 - 22.00 WIB',
        'status' => 'Buka • Pengiriman Cepat',
        'obat_kosong' => [3, 17]
    ],
    [
        'id_apotek' => 'APT-06',
        'nama' => 'Apotek K-24 Seturan Raya',
        'alamat' => 'Jl. Seturan Raya No. 112, Depok, Sleman',
        'jarak_km' => 2.3,
        'jam_buka' => 'Buka 24 Jam',
        'status' => 'Buka 24 Jam • Dekat Area Kos',
        'obat_kosong' => [4, 20]
    ],
    [
        'id_apotek' => 'APT-07',
        'nama' => 'Apotek SakuMedika Kampus (Terdekat)',
        'alamat' => 'Jl. Wates / Area Kampus Utama No. 10, Yogyakarta',
        'jarak_km' => 0.4,
        'jam_buka' => 'Buka 24 Jam',
        'status' => 'Apotek Terdekat • Instan 10 Menit',
        'obat_kosong' => [2, 18, 32, 47]
    ]
];

function getRingkasanKeranjang($conn) {
    $items = [];
    $total_harga = 0;
    $total_qty = 0;

    if (!empty($_SESSION['keranjang'])) {
        $valid_ids = [];
        foreach ($_SESSION['keranjang'] as $k => $v) {
            $id_clean = (int)$k;
            $qty_clean = (int)$v;
            if ($id_clean > 0 && $qty_clean > 0) {
                $valid_ids[] = $id_clean;
            }
        }

        if (!empty($valid_ids)) {
            $ids_str = implode(',', $valid_ids);
            $q = mysqli_query($conn, "SELECT * FROM obat WHERE id IN ($ids_str)");
            while ($r = mysqli_fetch_assoc($q)) {
                $id_obt = (int)$r['id'];
                $qty = (int)$_SESSION['keranjang'][$id_obt];
                $sub = (int)$r['harga'] * $qty;
                $total_harga += $sub;
                $total_qty += $qty;

                $items[] = [
                    'id' => $id_obt,
                    'nama_obat' => $r['nama_obat'],
                    'harga' => (int)$r['harga'],
                    'harga_rp' => formatRupiah($r['harga']),
                    'qty' => $qty,
                    'subtotal' => $sub,
                    'subtotal_rp' => formatRupiah($sub)
                ];
            }
        }
    }

    return [
        'items' => $items,
        'total_qty' => $total_qty,
        'total_harga' => $total_harga,
        'total_harga_rp' => formatRupiah($total_harga)
    ];
}


if (isset($_POST['ajax_cek_midtrans']) && !empty($_POST['order_id'])) {
    header('Content-Type: application/json');
    $order_id = trim($_POST['order_id']);
    $status_server = cekStatusMidtrans($order_id);
    echo json_encode(['status_midtrans' => $status_server]);
    exit;
}

// Endpoint AJAX Keranjang (Tanpa Reload Halaman)
if (isset($_POST['ajax_cart'])) {
    header('Content-Type: application/json');
    $act = $_POST['ajax_cart'];
    $id_obat = isset($_POST['id_obat']) ? (int)$_POST['id_obat'] : 0;

    if ($id_obat > 0) {
        if ($act === 'tambah' || $act === 'plus') {
            if (!isset($_SESSION['keranjang'][$id_obat])) {
                $_SESSION['keranjang'][$id_obat] = 1;
            } else {
                $_SESSION['keranjang'][$id_obat] = (int)$_SESSION['keranjang'][$id_obat] + 1;
            }
        } elseif ($act === 'minus') {
            if (isset($_SESSION['keranjang'][$id_obat])) {
                $_SESSION['keranjang'][$id_obat] = (int)$_SESSION['keranjang'][$id_obat] - 1;
                if ($_SESSION['keranjang'][$id_obat] <= 0) {
                    unset($_SESSION['keranjang'][$id_obat]);
                }
            }
        } elseif ($act === 'hapus') {
            unset($_SESSION['keranjang'][$id_obat]);
        }
    } elseif ($act === 'kosongkan') {
        $_SESSION['keranjang'] = [];
    }

    $ringkasan = getRingkasanKeranjang($conn);
    echo json_encode(['status' => 'ok', 'cart' => $ringkasan]);
    exit;
}


if (isset($_GET['auto_cari_obat'])) {
    $id_cari = (int)$_GET['auto_cari_obat'];
    $q_obt = mysqli_query($conn, "SELECT * FROM obat WHERE id = $id_cari");
    
    if ($r_obt = mysqli_fetch_assoc($q_obt)) {
        $apotek_urut_dekat = $daftar_apotek;
        usort($apotek_urut_dekat, function($a, $b) {
            return $a['jarak_km'] <=> $b['jarak_km'];
        });

        $apotek_ready = null;
        $apotek_terdekat_awal = $apotek_urut_dekat[0];
        $dialihkan = false;

        foreach ($apotek_urut_dekat as $cabang) {
            $sedang_kosong_di_cabang = in_array($id_cari, $cabang['obat_kosong']);
            if (!$sedang_kosong_di_cabang && (int)$r_obt['stok'] > 0) {
                $apotek_ready = $cabang;
                break;
            } else {
                $dialihkan = true;
            }
        }

        if ($apotek_ready === null) {
            $apotek_ready = $apotek_terdekat_awal;
        }

        $_SESSION['apotek_terpilih'] = $apotek_ready;

        if (!isset($_SESSION['keranjang'][$id_cari])) {
            $_SESSION['keranjang'][$id_cari] = 1;
        } else {
            $_SESSION['keranjang'][$id_cari] = (int)$_SESSION['keranjang'][$id_cari] + 1;
        }

        if (!$dialihkan) {
            $_SESSION['info_auto_apotek'] = [
                'tipe' => 'terdekat',
                'pesan' => "Stok <b>" . htmlspecialchars($r_obt['nama_obat']) . "</b> tersedia di apotek terdekat: <b>" . htmlspecialchars($apotek_ready['nama']) . " (" . number_format($apotek_ready['jarak_km'], 1, ',', '.') . " km)</b>. Anda dapat langsung menyelesaikan pemesanan atau menambah produk lainnya."
            ];
        } else {
            $_SESSION['info_auto_apotek'] = [
                'tipe' => 'dialihkan',
                'pesan' => "Stok <b>" . htmlspecialchars($r_obt['nama_obat']) . "</b> di <i>" . htmlspecialchars($apotek_terdekat_awal['nama']) . " (0,4 km)</i> sedang kosong. Sistem secara otomatis mengalihkan pesanan Anda ke cabang terdekat berikutnya yang tersedia: <b>" . htmlspecialchars($apotek_ready['nama']) . " (" . number_format($apotek_ready['jarak_km'], 1, ',', '.') . " km)</b>."
            ];
        }
    }

    header("Location: apotek.php?buka_keranjang=1");
    exit;
}

$info_auto_apotek = null;
if (isset($_SESSION['info_auto_apotek'])) {
    $info_auto_apotek = $_SESSION['info_auto_apotek'];
    unset($_SESSION['info_auto_apotek']);
}

if (isset($_GET['pilih_apotek'])) {
    $kode_pilih = $_GET['pilih_apotek'];
    foreach ($daftar_apotek as $apt) {
        if ($apt['id_apotek'] === $kode_pilih) {
            $_SESSION['apotek_terpilih'] = $apt;
            break;
        }
    }
    header("Location: apotek.php");
    exit;
}

if (isset($_GET['ganti_apotek'])) {
    unset($_SESSION['apotek_terpilih']);
    header("Location: apotek.php");
    exit;
}

$apotek_aktif = isset($_SESSION['apotek_terpilih']) ? $_SESSION['apotek_terpilih'] : null;

$urut_jarak = isset($_GET['urut']) ? $_GET['urut'] : 'terjauh';
$cari_apotek = isset($_GET['cari_apotek']) ? strtolower(trim($_GET['cari_apotek'])) : '';

$list_apotek_tampil = array_filter($daftar_apotek, function($a) use ($cari_apotek) {
    if ($cari_apotek === '') return true;
    return strpos(strtolower($a['nama'] . ' ' . $a['alamat']), $cari_apotek) !== false;
});

usort($list_apotek_tampil, function($a, $b) use ($urut_jarak) {
    if ($urut_jarak === 'terdekat') {
        return $a['jarak_km'] <=> $b['jarak_km'];
    }
    return $b['jarak_km'] <=> $a['jarak_km'];
});


$struk_pesanan = null;
if (isset($_POST['checkout_sekarang']) && !empty($_SESSION['keranjang']) && $apotek_aktif !== null) {
    $nama_pembeli = mysqli_real_escape_string($conn, htmlspecialchars(trim($_POST['nama_pembeli'])));
    $no_hp = mysqli_real_escape_string($conn, htmlspecialchars(trim($_POST['no_hp'])));
    $alamat = mysqli_real_escape_string($conn, htmlspecialchars(trim($_POST['alamat'])));
    $metode = mysqli_real_escape_string($conn, htmlspecialchars(trim($_POST['metode_pengambilan'])));
    $nama_apotek_trx = $apotek_aktif['nama'] . ' (' . $apotek_aktif['jarak_km'] . ' km)';

    $ringkasan_trx = getRingkasanKeranjang($conn);
    $total_bayar = $ringkasan_trx['total_harga'];
    $list_item_teks = [];
    $list_item_struk = [];
    $items_midtrans = [];

    foreach ($ringkasan_trx['items'] as $itm) {
        $id_obt = (int)$itm['id'];
        $qty = (int)$itm['qty'];
        $list_item_teks[] = $itm['nama_obat'] . " (" . $qty . "x)";
        $list_item_struk[] = [
            'nama' => $itm['nama_obat'],
            'qty' => $qty,
            'harga' => $itm['harga'],
            'subtotal' => $itm['subtotal']
        ];
        $items_midtrans[] = [
            'id' => 'OBT-' . $id_obt,
            'price' => (int)$itm['harga'],
            'quantity' => $qty,
            'name' => substr($itm['nama_obat'], 0, 48)
        ];

        mysqli_query($conn, "UPDATE obat SET stok = GREATEST(0, stok - $qty) WHERE id = $id_obt");
    }

    $kode_trx = 'INV-' . date('ymd') . '-' . rand(100, 999);
    $detail_string = mysqli_real_escape_string($conn, '[' . $nama_apotek_trx . '] ' . implode(', ', $list_item_teks));

    $sql_insert = "INSERT INTO pesanan (kode_transaksi, nama_pembeli, no_hp, alamat, metode_pengambilan, detail_item, total_harga)
                   VALUES ('$kode_trx', '$nama_pembeli', '$no_hp', '$alamat', '$metode', '$detail_string', $total_bayar)";
    
    if (mysqli_query($conn, $sql_insert)) {
        $snap_token = getMidtransSnapToken($kode_trx, $total_bayar, $nama_pembeli, $no_hp, $items_midtrans);

        $struk_pesanan = [
            'kode' => $kode_trx,
            'apotek' => $nama_apotek_trx,
            'nama' => $nama_pembeli,
            'no_hp' => $no_hp,
            'alamat' => $alamat,
            'metode' => $metode,
            'items' => $list_item_struk,
            'total' => $total_bayar,
            'waktu' => date('d M Y, H:i'),
            'snap_token' => $snap_token
        ];
        $_SESSION['keranjang'] = [];
    }
}


$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$kategori_aktif = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

$where_clauses = [];
if ($cari !== '') {
    $cari_safe = mysqli_real_escape_string($conn, $cari);
    $where_clauses[] = "(nama_obat LIKE '%$cari_safe%' OR gejala_keyword LIKE '%$cari_safe%' OR indikasi LIKE '%$cari_safe%' OR kategori LIKE '%$cari_safe%')";
}
if ($kategori_aktif !== '') {
    $kat_safe = mysqli_real_escape_string($conn, $kategori_aktif);
    $where_clauses[] = "kategori = '$kat_safe'";
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

$q_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM obat" . $where_sql);
$row_count = mysqli_fetch_assoc($q_count);
$total_data_obat = (int)$row_count['total'];

$limit_per_halaman = 12;
$total_halaman = max(1, ceil($total_data_obat / $limit_per_halaman));
$halaman_sekarang = isset($_GET['hal']) ? (int)$_GET['hal'] : 1;
if ($halaman_sekarang < 1) $halaman_sekarang = 1;
if ($halaman_sekarang > $total_halaman) $halaman_sekarang = $total_halaman;

$offset = ($halaman_sekarang - 1) * $limit_per_halaman;

$sql_obat = "SELECT * FROM obat" . $where_sql . " ORDER BY id ASC LIMIT $limit_per_halaman OFFSET $offset";
$res_obat = mysqli_query($conn, $sql_obat);

function buatUrlHalaman($hal_tujuan, $kategori_aktif, $cari) {
    $params = [];
    if ($kategori_aktif !== '') $params['kategori'] = $kategori_aktif;
    if ($cari !== '') $params['cari'] = $cari;
    $params['hal'] = $hal_tujuan;
    return 'apotek.php?' . http_build_query($params);
}

$res_kategori = mysqli_query($conn, "SELECT DISTINCT kategori FROM obat ORDER BY kategori ASC");
$daftar_kategori = [];
while ($rk = mysqli_fetch_assoc($res_kategori)) {
    $daftar_kategori[] = $rk['kategori'];
}

$data_keranjang_awal = getRingkasanKeranjang($conn);
$buka_drawer = isset($_GET['buka_keranjang']) ? true : false;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apotek Market - SakuMedika</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?php echo MIDTRANS_SNAP_JS; ?>" data-client-key="<?php echo htmlspecialchars(MIDTRANS_CLIENT_KEY); ?>"></script>
    <style>
        :root {
            --crystal-navy: #0B2545;
            --crystal-blue: #0066FF;
            --crystal-royal: #00529C;
            --crystal-cyan: #00A3E0;
            --glass-bg: rgba(255, 255, 255, 0.82);
            --glass-card: #FFFFFF;
            --glass-border: rgba(255, 255, 255, 0.96);
            --text-dark: #0F2942;
            --text-muted: #526D82;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        html, body {
            width: 100%;
            min-height: 100vh;
            background: linear-gradient(145deg, #e2f1ff 0%, #f4faff 50%, #dff1ff 100%);
            color: var(--text-dark);
            overflow-x: hidden;
        }

        body {
            padding-bottom: 70px;
        }

  
        .market-header {
            position: sticky;
            top: 0;
            z-index: 80;
            width: 100%;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
            border-bottom: 1.5px solid #E2E8F0;
            box-shadow: 0 8px 26px rgba(0, 82, 156, 0.06);
        }
        .market-header-inner {
            width: 100%;
            max-width: 1240px;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .left-brand-group {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-shrink: 0;
        }
        .btn-back-consult {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 12px;
            background: #EFF6FF;
            color: var(--crystal-blue);
            font-weight: 700;
            font-size: 0.84rem;
            border: 1px solid #BFDBFE;
            transition: 0.2s;
            white-space: nowrap;
        }
        .btn-back-consult:hover {
            background: var(--crystal-blue);
            color: #ffffff;
        }
        .market-brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--crystal-navy);
            white-space: nowrap;
        }

        .search-market-form {
            flex: 1;
            max-width: 580px;
            display: flex;
            align-items: center;
            background: #F8FAFC;
            border: 1.5px solid #CBD5E1;
            border-radius: 999px;
            padding: 4px 5px 4px 18px;
            transition: 0.2s;
        }
        .search-market-form:focus-within {
            background: #FFFFFF;
            border-color: var(--crystal-blue);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.12);
        }
        .search-market-input {
            flex: 1;
            width: 100%;
            min-width: 0;
            border: none;
            background: transparent;
            font-size: 0.88rem;
            color: var(--text-dark);
            outline: none;
            padding: 6px 0;
        }
        .btn-search-submit {
            padding: 9px 20px;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff;
            font-weight: 700;
            font-size: 0.83rem;
            cursor: pointer;
            flex-shrink: 0;
        }

        .btn-cart-trigger {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, var(--crystal-blue) 0%, var(--crystal-cyan) 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0, 102, 255, 0.24);
            white-space: nowrap;
            flex-shrink: 0;
        }
        .cart-count-bubble {
            background: #ffffff;
            color: var(--crystal-blue);
            font-weight: 800;
            font-size: 0.78rem;
            padding: 2px 9px;
            border-radius: 999px;
        }

        
        .market-container {
            width: 100%;
            max-width: 1240px;
            margin: 26px auto 0 auto;
            padding: 0 24px;
            display: block;
        }

        .toast-added {
            position: fixed; bottom: 26px; right: 26px; z-index: 120;
            background: rgba(11, 37, 69, 0.95); backdrop-filter: blur(14px);
            color: #ffffff; padding: 13px 20px; border-radius: 16px;
            font-size: 0.86rem; font-weight: 700;
            box-shadow: 0 14px 32px rgba(0, 38, 77, 0.25);
            border: 1px solid rgba(56, 189, 248, 0.4);
            display: flex; align-items: center; gap: 12px;
            opacity: 0; pointer-events: none; transform: translateY(15px); transition: all 0.25s ease;
        }
        .toast-added.show { opacity: 1; pointer-events: auto; transform: translateY(0); }
        .toast-added button {
            background: var(--crystal-cyan); color: #fff; border: none;
            padding: 6px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 800; cursor: pointer;
        }

        .auto-locator-alert {
            width: 100%;
            background: #FFFFFF;
            border: 1.5px solid #38BDF8;
            border-left: 6px solid #059669;
            border-radius: 18px;
            padding: 16px 22px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: var(--crystal-navy);
            line-height: 1.55;
            box-shadow: 0 8px 24px rgba(0, 82, 156, 0.06);
        }
        .auto-locator-alert.dialihkan { border-left-color: #F59E0B; }

      
        .locator-hero {
            width: 100%;
            background: linear-gradient(135deg, #00529C 0%, #00A3E0 100%);
            border-radius: 24px;
            padding: 30px 34px;
            color: #ffffff;
            margin-bottom: 24px;
            box-shadow: 0 16px 38px rgba(0, 82, 156, 0.16);
        }
        .locator-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.05rem; margin-bottom: 8px; }
        .locator-hero p { font-size: 0.93rem; color: rgba(240, 249, 255, 0.94); max-width: 720px; line-height: 1.6; }

        .sort-filter-bar {
            width: 100%;
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; margin-bottom: 22px; flex-wrap: wrap;
        }
        .sort-pills { display: flex; gap: 10px; align-items: center; }
        .sort-btn {
            text-decoration: none; padding: 10px 18px; border-radius: 12px;
            font-size: 0.84rem; font-weight: 700; background: #FFFFFF;
            color: var(--crystal-navy); border: 1px solid #CBD5E1; transition: 0.2s;
        }
        .sort-btn.active, .sort-btn:hover {
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; border-color: transparent; box-shadow: 0 8px 20px rgba(0, 102, 255, 0.22);
        }

        .pharmacy-list {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }
        .pharmacy-card {
            background: #FFFFFF;
            border: 1.5px solid #E2E8F0;
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 8px 24px rgba(0, 82, 156, 0.05);
            display: flex; flex-direction: column; justify-content: space-between;
            transition: all 0.22s ease;
        }
        .pharmacy-card:hover { transform: translateY(-4px); border-color: #60A5FA; box-shadow: 0 14px 30px rgba(0, 102, 255, 0.12); }
        .pharmacy-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .distance-badge {
            display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px;
            border-radius: 999px; font-size: 0.78rem; font-weight: 800;
            background: #EFF6FF; color: var(--crystal-blue);
        }
        .distance-badge.nearest { background: linear-gradient(135deg, #059669, #10B981); color: #ffffff; }
        .step-num { font-size: 0.75rem; font-weight: 800; color: var(--text-muted); }
        .pharmacy-name { font-size: 1.1rem; font-weight: 800; color: var(--crystal-navy); margin-bottom: 6px; }
        .pharmacy-address { font-size: 0.84rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 12px; }
        .pharmacy-meta {
            background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 9px 12px;
            font-size: 0.76rem; font-weight: 600; color: var(--crystal-royal); margin-bottom: 16px;
            display: flex; justify-content: space-between; gap: 8px;
        }
        .btn-pilih-apotek {
            display: block; text-align: center; text-decoration: none; padding: 12px 18px;
            border-radius: 12px; background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; font-weight: 700; font-size: 0.86rem; box-shadow: 0 6px 16px rgba(0, 102, 255, 0.22);
        }

        .selected-pharmacy-banner {
            width: 100%;
            background: #FFFFFF;
            border: 1.5px solid #BFDBFE;
            border-left: 6px solid var(--crystal-blue);
            border-radius: 18px;
            padding: 18px 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: 0 8px 22px rgba(0, 82, 156, 0.05);
        }
        .btn-ganti-apotek {
            text-decoration: none; padding: 10px 18px; border-radius: 12px;
            background: #EFF6FF; color: var(--crystal-blue);
            font-weight: 700; font-size: 0.83rem; border: 1px solid #BFDBFE; transition: 0.2s;
        }
        .btn-ganti-apotek:hover { background: var(--crystal-blue); color: #ffffff; }

        
        .catalog-filter-wrapper {
            width: 100%;
            background: #FFFFFF;
            border: 1.5px solid #E2E8F0;
            border-radius: 18px;
            padding: 18px 22px;
            margin-bottom: 24px;
            box-shadow: 0 8px 22px rgba(0, 82, 156, 0.04);
        }
        .category-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .cat-pill {
            text-decoration: none;
            padding: 9px 18px;
            border-radius: 999px;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            color: var(--crystal-navy);
            font-size: 0.83rem;
            font-weight: 700;
            transition: all 0.18s ease;
        }
        .cat-pill:hover, .cat-pill.active {
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 6px 14px rgba(0, 102, 255, 0.22);
        }
        .catalog-summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 12px;
            border-top: 1px solid #F1F5F9;
            font-size: 0.83rem;
            color: var(--text-muted);
            font-weight: 600;
        }

    
        .product-grid {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
            align-items: stretch;
        }
        .product-card {
            width: 100%;
            min-width: 0;
            background: #FFFFFF;
            border: 1.5px solid #E2E8F0;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(0, 82, 156, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-4px);
            border-color: #60A5FA;
            box-shadow: 0 14px 28px rgba(0, 102, 255, 0.12);
        }
        .product-top-badges {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 12px;
        }
        .badge-kategori {
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--crystal-blue);
            background: #EFF6FF;
            padding: 5px 10px;
            border-radius: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .badge-stok {
            font-size: 0.72rem;
            font-weight: 700;
            color: #059669;
            background: #ECFDF5;
            padding: 5px 10px;
            border-radius: 8px;
            flex-shrink: 0;
        }
        .badge-stok.habis { color: #DC2626; background: #FEF2F2; }

        .product-title {
            font-size: 0.98rem;
            font-weight: 800;
            color: var(--crystal-navy);
            margin-bottom: 8px;
            line-height: 1.4;
            height: 2.8em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }
        .product-indikasi {
            font-size: 0.81rem;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 14px;
            height: 3.1em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }
        .product-dosis-mini {
            width: 100%;
            min-width: 0;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 0.76rem;
            color: #334155;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        .dosis-line {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-all;
        }

        .product-bottom-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-top: 14px;
            border-top: 1px solid #F1F5F9;
            margin-top: auto;
        }
        .product-price-label {
            display: flex;
            flex-direction: column;
        }
        .price-caption {
            font-size: 0.68rem;
            color: var(--text-muted);
            font-weight: 600;
        }
        .product-price {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--crystal-royal);
        }
        .btn-add-market {
            padding: 10px 18px;
            border-radius: 11px;
            border: none;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff;
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.2);
            transition: all 0.18s ease;
            flex-shrink: 0;
        }
        .btn-add-market:hover { filter: brightness(1.07); transform: translateY(-1px); }

    
        .pagination-wrapper {
            width: 100%;
            margin-top: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .page-link {
            min-width: 42px;
            height: 42px;
            padding: 0 14px;
            border-radius: 12px;
            background: #FFFFFF;
            border: 1.5px solid #CBD5E1;
            color: var(--crystal-navy);
            font-weight: 700;
            font-size: 0.86rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .page-link:hover {
            border-color: var(--crystal-blue);
            color: var(--crystal-blue);
            transform: translateY(-2px);
        }
        .page-link.active {
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 8px 18px rgba(0, 102, 255, 0.28);
        }
        .page-link.disabled { opacity: 0.45; pointer-events: none; }

      
        .receipt-card {
            width: 100%;
            background: #FFFFFF;
            border: 1.5px solid #93C5FD;
            border-radius: 22px;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: 0 16px 38px rgba(0, 82, 156, 0.08);
        }
        .badge-status-pay {
            font-weight: 800; font-size: 0.76rem; letter-spacing: 0.4px;
            padding: 6px 14px; border-radius: 999px;
            display: inline-flex; align-items: center; gap: 7px;
        }
        .badge-status-pay.pending { background: #FEF3C7; color: #B45309; border: 1px solid #FCD34D; }
        .badge-status-pay.paid { background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; }

        .payment-footer-bar {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 16px; padding-top: 16px; border-top: 1px solid #E2E8F0;
        }
        .payment-info-text {
            display: flex; align-items: center; gap: 10px;
            font-size: 0.86rem; color: var(--text-muted); font-weight: 500;
        }
        .status-dot-pulse {
            width: 9px; height: 9px; border-radius: 50%;
            background: #F59E0B; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2); flex-shrink: 0;
        }
        .btn-pay-primary {
            padding: 11px 20px; border-radius: 12px; border: none;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; font-weight: 700; font-size: 0.84rem; cursor: pointer;
            box-shadow: 0 8px 20px rgba(0, 102, 255, 0.24);
        }
        .btn-pay-secondary {
            padding: 11px 16px; border-radius: 12px; border: 1px solid #CBD5E1;
            background: #F8FAFC; color: var(--crystal-navy); font-weight: 700; font-size: 0.83rem;
            cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-pay-secondary:hover { background: #EFF6FF; border-color: var(--crystal-blue); color: var(--crystal-blue); }


        .cart-overlay {
            position: fixed; inset: 0; background: rgba(11, 37, 69, 0.45);
            backdrop-filter: blur(6px); z-index: 100; display: none; justify-content: flex-end;
        }
        .cart-overlay.open { display: flex; }
        .cart-drawer {
            width: 100%; max-width: 460px; background: #FFFFFF;
            height: 100vh; padding: 24px; display: flex; flex-direction: column; overflow-y: auto;
        }
        .drawer-head {
            display: flex; align-items: center; justify-content: space-between;
            padding-bottom: 14px; border-bottom: 1.5px solid #E2E8F0; margin-bottom: 16px;
        }
        .btn-close-drawer {
            background: #F1F5F9; border: none; width: 34px; height: 34px;
            border-radius: 10px; font-weight: 800; cursor: pointer;
        }
        .cart-item-row {
            background: #F8FAFC; border: 1px solid #E2E8F0;
            border-radius: 14px; padding: 12px 14px; display: flex;
            align-items: center; justify-content: space-between; margin-bottom: 10px;
        }
        .qty-controls { display: flex; align-items: center; gap: 7px; }
        .qty-btn {
            width: 28px; height: 28px; border-radius: 8px;
            background: #ffffff; border: 1px solid #CBD5E1;
            color: var(--crystal-navy); display: flex; align-items: center; justify-content: center;
            font-weight: 800; cursor: pointer;
        }
        .cart-total-box {
            background: #EFF6FF; border-radius: 14px; padding: 14px 16px;
            display: flex; justify-content: space-between; margin: 14px 0; font-weight: 800;
        }
        .checkout-input {
            width: 100%; padding: 10px 13px; border-radius: 11px;
            border: 1px solid #CBD5E1; margin-bottom: 12px; font-size: 0.85rem;
        }
        .btn-checkout-pay {
            width: 100%; padding: 14px; border-radius: 14px; border: none;
            background: linear-gradient(135deg, var(--crystal-blue), var(--crystal-cyan));
            color: #ffffff; font-weight: 800; cursor: pointer;
        }

        @media (max-width: 1120px) {
            .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .pharmacy-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 820px) {
            .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .pharmacy-list { grid-template-columns: 1fr; }
            .market-header-inner { flex-wrap: wrap; }
        }
        @media (max-width: 540px) {
            .product-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div id="toastAdded" class="toast-added">
        <span id="toastText">Obat ditambahkan ke keranjang!</span>
        <button type="button" onclick="toggleKeranjang(true)">Lihat Keranjang</button>
    </div>

    <header class="market-header">
        <div class="market-header-inner">
            <div class="left-brand-group">
                <a href="index.php?sesi=mulai" class="btn-back-consult">&larr; Sesi Tanya Jawab</a>
                <span class="market-brand-title">Apotek</span>
            </div>

            <?php if ($apotek_aktif !== null): ?>
                <form method="GET" action="apotek.php" class="search-market-form">
                    <?php if ($kategori_aktif !== ''): ?>
                        <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($kategori_aktif); ?>">
                    <?php endif; ?>
                    <input type="text" name="cari" class="search-market-input" placeholder="Cari nama obat atau keluhan di <?php echo htmlspecialchars($apotek_aktif['nama']); ?>..." value="<?php echo htmlspecialchars($cari); ?>">
                    <button type="submit" class="btn-search-submit">Cari Obat</button>
                </form>

                <button type="button" class="btn-cart-trigger" onclick="toggleKeranjang(true)">
                    <span>Keranjang</span>
                    <span class="cart-count-bubble" id="headerCartCount"><?php echo $data_keranjang_awal['total_qty']; ?></span>
                </button>
            <?php else: ?>
                <form method="GET" action="apotek.php" class="search-market-form">
                    <input type="hidden" name="urut" value="<?php echo htmlspecialchars($urut_jarak); ?>">
                    <input type="text" name="cari_apotek" class="search-market-input" placeholder="Cari nama apotek atau lokasi jalan..." value="<?php echo htmlspecialchars($cari_apotek); ?>">
                    <button type="submit" class="btn-search-submit">Cari Lokasi</button>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <main class="market-container">

        <?php if ($apotek_aktif === null): ?>
         >
            <section class="locator-hero">
                <h1>Pilih Lokasi Apotek Terlebih Dahulu</h1>
                <p>Daftar apotek diurutkan mulai dari jangkauan <b>terjauh hingga yang paling dekat</b>. Produk kesehatan yang sudah Anda pilih tetap tersimpan aman di keranjang belanja.</p>
            </section>

            <div class="sort-filter-bar">
                <div class="sort-pills">
                    <a href="apotek.php?urut=terjauh" class="sort-btn <?php echo $urut_jarak === 'terjauh' ? 'active' : ''; ?>">
                        Urutan: Terjauh &rarr; Terdekat (Default)
                    </a>
                    <a href="apotek.php?urut=terdekat" class="sort-btn <?php echo $urut_jarak === 'terdekat' ? 'active' : ''; ?>">
                        Urutan: Terdekat &rarr; Terjauh
                    </a>
                </div>
                <span style="font-size:0.86rem; font-weight:700; color:var(--crystal-navy);">
                    Tersedia <?php echo count($list_apotek_tampil); ?> Cabang Apotek Siap Melayani
                </span>
            </div>

            <div class="pharmacy-list">
                <?php 
                $no = 1;
                foreach ($list_apotek_tampil as $apt): 
                    $is_nearest = ($apt['jarak_km'] <= 1.0);
                ?>
                    <div class="pharmacy-card">
                        <div>
                            <div class="pharmacy-top">
                                <span class="distance-badge <?php echo $is_nearest ? 'nearest' : ''; ?>">
                                    Jarak: <?php echo number_format($apt['jarak_km'], 1, ',', '.'); ?> km
                                    <?php echo $is_nearest ? ' (Terdekat)' : ''; ?>
                                </span>
                                <span class="step-num">Cabang #<?php echo $no++; ?></span>
                            </div>
                            <div class="pharmacy-name"><?php echo htmlspecialchars($apt['nama']); ?></div>
                            <div class="pharmacy-address"><?php echo htmlspecialchars($apt['alamat']); ?></div>
                            <div class="pharmacy-meta">
                                <span><?php echo htmlspecialchars($apt['jam_buka']); ?></span>
                                <span><?php echo htmlspecialchars($apt['status']); ?></span>
                            </div>
                        </div>

                        <a href="apotek.php?pilih_apotek=<?php echo urlencode($apt['id_apotek']); ?>" class="btn-pilih-apotek">
                            Pilih Apotek &amp; Belanja Obat &rarr;
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            
            <?php if ($info_auto_apotek !== null): ?>
                <div class="auto-locator-alert <?php echo $info_auto_apotek['tipe'] === 'dialihkan' ? 'dialihkan' : ''; ?>">
                    <?php echo $info_auto_apotek['pesan']; ?>
                </div>
            <?php endif; ?>

            <div class="selected-pharmacy-banner">
                <div>
                    <div style="font-size:0.74rem; font-weight:800; color:var(--crystal-blue); text-transform:uppercase; letter-spacing:0.4px;">
                        Apotek Mitra Terpilih:
                    </div>
                    <div style="font-size:1.14rem; font-weight:800; color:var(--crystal-navy);">
                        <?php echo htmlspecialchars($apotek_aktif['nama']); ?> 
                        <span style="font-size:0.86rem; color:#059669;">(Jarak <?php echo number_format($apotek_aktif['jarak_km'], 1, ',', '.'); ?> km)</span>
                    </div>
                    <div style="font-size:0.83rem; color:var(--text-muted);">
                        <?php echo htmlspecialchars($apotek_aktif['alamat']); ?> &bull; <?php echo htmlspecialchars($apotek_aktif['jam_buka']); ?>
                    </div>
                </div>
                <a href="apotek.php?ganti_apotek=1" class="btn-ganti-apotek">&larr; Pilih Cabang Lain</a>
            </div>

            
            <?php if ($struk_pesanan !== null): ?>
                <div class="receipt-card" id="receiptCard">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
                        <span id="statusBadgeMidtrans" class="badge-status-pay pending">
                            MENUNGGU PEMBAYARAN
                        </span>
                        <span style="font-size:0.82rem; color:var(--text-muted); font-weight:600;"><?php echo $struk_pesanan['waktu']; ?> WIB</span>
                    </div>

                    <h2 style="font-size:1.35rem; color:var(--crystal-navy); margin-bottom:4px;">
                        Nomor Pesanan: <?php echo htmlspecialchars($struk_pesanan['kode']); ?>
                    </h2>
                    <p style="font-size:0.88rem; color:var(--crystal-blue); font-weight:700; margin-bottom:6px;">
                        Apotek Penyedia: <?php echo htmlspecialchars($struk_pesanan['apotek']); ?>
                    </p>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:18px;">
                        Penerima: <b><?php echo htmlspecialchars($struk_pesanan['nama']); ?></b> (<?php echo htmlspecialchars($struk_pesanan['no_hp']); ?>) &bull; <?php echo htmlspecialchars($struk_pesanan['metode']); ?>
                    </p>

                    <div style="background:#f8fbff; border: 1px solid #E2E8F0; border-radius:16px; padding:16px 18px; font-size:0.86rem; margin-bottom:18px;">
                        <?php foreach ($struk_pesanan['items'] as $it): ?>
                            <div style="display:flex; justify-content:space-between; padding:5px 0;">
                                <span><?php echo htmlspecialchars($it['nama']); ?> &times; <?php echo $it['qty']; ?></span>
                                <b><?php echo formatRupiah($it['subtotal']); ?></b>
                            </div>
                        <?php endforeach; ?>
                        <div style="display:flex; justify-content:space-between; padding-top:12px; margin-top:8px; border-top:1px dashed #cbd5e1; font-size:1.05rem; color:var(--crystal-blue);">
                            <b>Total Tagihan</b>
                            <b><?php echo formatRupiah($struk_pesanan['total']); ?></b>
                        </div>
                    </div>

                    <div id="midtransActionArea" class="payment-footer-bar">
                        <div class="payment-info-text">
                            <span class="status-dot-pulse" id="statusDot"></span>
                            <span id="teksStatusDetail">Silakan selesaikan pembayaran sesuai metode yang Anda pilih.</span>
                        </div>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <a href="https://simulator.sandbox.midtrans.com/" target="_blank" class="btn-pay-secondary" title="Buka Portal Midtrans Sandbox Payment Simulator">
                                <span>Payment Simulator</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" y1="14" x2="21" y2="3"></line>
                                </svg>
                            </a>
                            <button type="button" class="btn-pay-secondary" id="btnCekStatus" onclick="cekStatusAsliMidtrans(true)">
                                Cek Status
                            </button>
                            <button type="button" class="btn-pay-primary" onclick="bukaPopupMidtrans()">
                                Rincian Pembayaran &rarr;
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    const snapTokenServer = <?php echo json_encode($struk_pesanan['snap_token']); ?>;
                    const kodeOrderAktif = <?php echo json_encode($struk_pesanan['kode']); ?>;
                    let pollerInterval = null;

                    function setTampilanLunas() {
                        if (pollerInterval) clearInterval(pollerInterval);
                        const badge = document.getElementById('statusBadgeMidtrans');
                        const actionArea = document.getElementById('midtransActionArea');
                        if (badge) {
                            badge.className = 'badge-status-pay paid';
                            badge.innerText = 'PEMBAYARAN TERVERIFIKASI • LUNAS';
                        }
                        if (actionArea) {
                            actionArea.innerHTML = `
                                <div style="display:flex; align-items:center; gap:10px; color:#15803D; font-weight:700; font-size:0.9rem;">
                                    <span style="width:10px; height:10px; border-radius:50%; background:#16A34A; display:inline-block;"></span>
                                    <span>Pembayaran telah berhasil diverifikasi. Pesanan obat Anda sedang disiapkan oleh pihak apotek.</span>
                                </div>
                            `;
                        }
                    }

                    function setTampilanPending(infoMetode) {
                        const badge = document.getElementById('statusBadgeMidtrans');
                        const teksDetail = document.getElementById('teksStatusDetail');
                        if (badge) {
                            badge.className = 'badge-status-pay pending';
                            badge.innerText = 'MENUNGGU PEMBAYARAN • ' + infoMetode;
                        }
                        if (teksDetail) {
                            teksDetail.innerHTML = `Menunggu konfirmasi pembayaran otomatis. Halaman ini akan diperbarui secara langsung setelah transaksi selesai.`;
                        }
                    }

                    function cekStatusAsliMidtrans(manualClick = false) {
                        const btn = document.getElementById('btnCekStatus');
                        if (manualClick && btn) btn.innerText = 'Memeriksa...';

                        const fd = new FormData();
                        fd.append('ajax_cek_midtrans', '1');
                        fd.append('order_id', kodeOrderAktif);

                        fetch('apotek.php', { method: 'POST', body: fd })
                        .then(r => r.json())
                        .then(res => {
                            if (manualClick && btn) btn.innerText = 'Cek Status';
                            const st = res.status_midtrans;
                            if (st === 'settlement' || st === 'capture') {
                                setTampilanLunas();
                            } else if (manualClick && st === 'pending') {
                                setTampilanPending('DALAM PROSES');
                            }
                        })
                        .catch(() => {
                            if (manualClick && btn) btn.innerText = 'Cek Status';
                        });
                    }

                    function bukaPopupMidtrans() {
                        if (snapTokenServer && typeof window.snap !== 'undefined') {
                            window.snap.pay(snapTokenServer, {
                                onSuccess: function() { setTampilanLunas(); },
                                onPending: function(result) {
                                    setTampilanPending(result.payment_type ? result.payment_type.replace('_', ' ').toUpperCase() : 'VIRTUAL ACCOUNT');
                                },
                                onError: function() {
                                    alert('Proses pembayaran tidak dapat dilanjutkan. Silakan coba kembali.');
                                }
                            });
                        }
                    }

                    document.addEventListener('DOMContentLoaded', () => {
                        setTimeout(() => { bukaPopupMidtrans(); }, 400);
                        pollerInterval = setInterval(() => { cekStatusAsliMidtrans(false); }, 4000);
                    });
                </script>
            <?php endif; ?>

            <!-- Panel Filter Kategori Lebar Penuh 100% Sejajar -->
            <div class="catalog-filter-wrapper">
                <div class="category-bar">
                    <a href="apotek.php" class="cat-pill <?php echo $kategori_aktif === '' ? 'active' : ''; ?>">Semua Kategori</a>
                    <?php foreach ($daftar_kategori as $kat): ?>
                        <a href="apotek.php?kategori=<?php echo urlencode($kat); ?><?php echo $cari !== '' ? '&cari=' . urlencode($cari) : ''; ?>"
                           class="cat-pill <?php echo $kategori_aktif === $kat ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($kat); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="catalog-summary-row">
                    <span>Menampilkan halaman <b><?php echo $halaman_sekarang; ?></b> dari <b><?php echo $total_halaman; ?></b> (Total <b><?php echo $total_data_obat; ?></b> produk obat)</span>
                    <?php if ($cari !== '' || $kategori_aktif !== ''): ?>
                        <a href="apotek.php" style="color:var(--crystal-blue); text-decoration:none; font-weight:700;">Reset Filter &times;</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Katalog Obat (4 Kolom Sejajar Penuh 100%) -->
            <div class="product-grid">
                <?php 
                $obat_kosong_cabang = isset($apotek_aktif['obat_kosong']) ? $apotek_aktif['obat_kosong'] : [];
                while ($obat = mysqli_fetch_assoc($res_obat)): 
                    $id_o = (int)$obat['id'];
                    $kosong_di_sini = in_array($id_o, $obat_kosong_cabang) || (int)$obat['stok'] <= 0;
                    $nama_js = htmlspecialchars(addslashes($obat['nama_obat']), ENT_QUOTES);
                ?>
                    <div class="product-card">
                        <div>
                            <div class="product-top-badges">
                                <span class="badge-kategori"><?php echo htmlspecialchars($obat['kategori']); ?></span>
                                <?php if ($kosong_di_sini): ?>
                                    <span class="badge-stok habis">Stok Habis</span>
                                <?php else: ?>
                                    <span class="badge-stok">Stok: <?php echo (int)$obat['stok']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="product-title" title="<?php echo htmlspecialchars($obat['nama_obat']); ?>">
                                <?php echo htmlspecialchars($obat['nama_obat']); ?>
                            </div>
                            <div class="product-indikasi" title="<?php echo htmlspecialchars($obat['indikasi']); ?>">
                                <?php echo htmlspecialchars($obat['indikasi']); ?>
                            </div>
                            <div class="product-dosis-mini">
                                <div class="dosis-line" title="<?php echo htmlspecialchars($obat['dosis']); ?>"><b>Dosis:</b> <?php echo htmlspecialchars($obat['dosis']); ?></div>
                                <div class="dosis-line" title="<?php echo htmlspecialchars($obat['aturan_pakai']); ?>"><b>Aturan:</b> <?php echo htmlspecialchars($obat['aturan_pakai']); ?></div>
                            </div>
                        </div>

                        <div class="product-bottom-row">
                            <div class="product-price-label">
                                <span class="price-caption">Harga Resmi</span>
                                <span class="product-price"><?php echo formatRupiah($obat['harga']); ?></span>
                            </div>
                            <?php if ($kosong_di_sini): ?>
                                <a href="apotek.php?auto_cari_obat=<?php echo $id_o; ?>" class="btn-add-market" style="text-decoration:none; background:#F59E0B;">
                                    Cabang Lain &rarr;
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn-add-market" onclick="updateKeranjangAjax('tambah', <?php echo $id_o; ?>, '<?php echo $nama_js; ?>')">
                                    + Beli
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Navigasi Nomor Halaman (Tetap Tampil Sebagai Indikator Halaman) -->
            <nav class="pagination-wrapper" aria-label="Navigasi Halaman Obat">
                <a href="<?php echo buatUrlHalaman(max(1, $halaman_sekarang - 1), $kategori_aktif, $cari); ?>"
                   class="page-link <?php echo $halaman_sekarang <= 1 ? 'disabled' : ''; ?>">
                    &laquo; Sebelumnya
                </a>

                <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                    <a href="<?php echo buatUrlHalaman($i, $kategori_aktif, $cari); ?>"
                       class="page-link <?php echo $i === $halaman_sekarang ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <a href="<?php echo buatUrlHalaman(min($total_halaman, $halaman_sekarang + 1), $kategori_aktif, $cari); ?>"
                   class="page-link <?php echo $halaman_sekarang >= $total_halaman ? 'disabled' : ''; ?>">
                    Berikutnya &raquo;
                </a>
            </nav>

        <?php endif; ?>

    </main>

    <?php if ($apotek_aktif !== null): ?>
        <div class="cart-overlay <?php echo $buka_drawer ? 'open' : ''; ?>" id="cartOverlay">
            <div class="cart-drawer">
                <div class="drawer-head">
                    <div>
                        <h3 style="font-size:1.1rem; color:var(--crystal-navy);">
                            Keranjang Belanja (<span id="drawerCartCount"><?php echo $data_keranjang_awal['total_qty']; ?></span>)
                        </h3>
                        <small style="color:var(--crystal-blue); font-weight:700;">
                            <?php echo htmlspecialchars($apotek_aktif['nama']); ?> (<?php echo number_format($apotek_aktif['jarak_km'], 1, ',', '.'); ?> km)
                        </small>
                    </div>
                    <button type="button" class="btn-close-drawer" onclick="toggleKeranjang(false)">&times;</button>
                </div>

                <div id="cartDynamicContainer" style="display:flex; flex-direction:column; flex:1;"></div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        let currentCartData = <?php echo json_encode($data_keranjang_awal); ?>;
        const namaApotekAktif = <?php echo json_encode($apotek_aktif ? $apotek_aktif['nama'] : ''); ?>;
        let toastTimer = null;

        function toggleKeranjang(buka) {
            const overlay = document.getElementById('cartOverlay');
            if (overlay) {
                if (buka) overlay.classList.add('open');
                else overlay.classList.remove('open');
            }
        }

        function tampilkanToast(pesan) {
            const toast = document.getElementById('toastAdded');
            const text = document.getElementById('toastText');
            if (!toast || !text) return;
            text.innerText = pesan;
            toast.classList.add('show');
            if (toastTimer) clearTimeout(toastTimer);
            toastTimer = setTimeout(() => {
                toast.classList.remove('show');
            }, 2800);
        }

        function updateKeranjangAjax(aksi, idObat = 0, namaObat = '') {
            const formData = new FormData();
            formData.append('ajax_cart', aksi);
            formData.append('id_obat', idObat);

            fetch('apotek.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    currentCartData = data.cart;
                    renderIsiKeranjang();

                    if (aksi === 'tambah' && namaObat) {
                        tampilkanToast('+ ' + namaObat + ' ditambahkan ke keranjang');
                    }
                }
            })
            .catch(err => console.error('Gagal memperbarui keranjang:', err));
        }

        function renderIsiKeranjang() {
            const headerCount = document.getElementById('headerCartCount');
            const drawerCount = document.getElementById('drawerCartCount');
            const container = document.getElementById('cartDynamicContainer');

            if (headerCount) headerCount.innerText = currentCartData.total_qty;
            if (drawerCount) drawerCount.innerText = currentCartData.total_qty;
            if (!container) return;

            if (!currentCartData.items || currentCartData.items.length === 0) {
                container.innerHTML = `
                    <div style="margin:auto; text-align:center; color:var(--text-muted); padding:30px 10px;">
                        <p style="font-weight:700; color:var(--crystal-navy); margin-bottom:6px;">Keranjang Belanja Kosong</p>
                        <p style="font-size:0.84rem;">Pilih produk obat di katalog untuk mulai memesan dari ${namaApotekAktif}.</p>
                    </div>
                `;
                return;
            }

            let itemsHtml = '<div style="margin-bottom:8px;">';
            currentCartData.items.forEach(item => {
                itemsHtml += `
                    <div class="cart-item-row">
                        <div style="flex:1; padding-right:8px;">
                            <div style="font-size:0.86rem; font-weight:800; color:var(--crystal-navy);">${item.nama_obat}</div>
                            <div style="font-size:0.78rem; color:var(--crystal-blue); font-weight:700;">${item.harga_rp} &bull; Sub: ${item.subtotal_rp}</div>
                        </div>
                        <div class="qty-controls">
                            <button type="button" class="qty-btn" onclick="updateKeranjangAjax('minus', ${item.id})">&minus;</button>
                            <span style="font-size:0.85rem; font-weight:800; min-width:22px; text-align:center;">${item.qty}</span>
                            <button type="button" class="qty-btn" onclick="updateKeranjangAjax('plus', ${item.id})">+</button>
                        </div>
                    </div>
                `;
            });
            itemsHtml += '</div>';

            itemsHtml += `
                <div style="text-align:right; margin-bottom:6px;">
                    <button type="button" onclick="updateKeranjangAjax('kosongkan', 0)" style="background:none; border:none; font-size:0.76rem; color:#DC2626; font-weight:700; cursor:pointer;">
                        Kosongkan Keranjang
                    </button>
                </div>
                <div class="cart-total-box">
                    <span>Total Tagihan</span>
                    <span style="color:var(--crystal-blue);">${currentCartData.total_harga_rp}</span>
                </div>
                <form method="POST" action="apotek.php">
                    <input type="hidden" name="checkout_sekarang" value="1">
                    <input type="text" name="nama_pembeli" class="checkout-input" placeholder="Nama lengkap penerima" required>
                    <input type="text" name="no_hp" class="checkout-input" placeholder="Nomor WhatsApp aktif" required>
                    <select name="metode_pengambilan" class="checkout-input">
                        <option value="Layanan Antar Instan ke Alamat Tujuan">Layanan Antar Instan ke Alamat Tujuan</option>
                        <option value="Ambil Langsung di Cabang Apotek">Ambil Langsung di Cabang Apotek</option>
                    </select>
                    <input type="text" name="alamat" class="checkout-input" placeholder="Alamat lengkap pengiriman" required>
                    <button type="submit" class="btn-checkout-pay">Lanjutkan ke Pembayaran &rarr;</button>
                </form>
            `;

            container.innerHTML = itemsHtml;
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderIsiKeranjang();
        });
    </script>
</body>
</html>
