<?php
include 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pesan_sukses = '';
$pesan_error = '';

if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

$instant_methods = [
    [
        'value' => 'QRIS Instan (E-Wallet & Mobile Banking)',
        'title' => 'QRIS Instan',
        'subtitle' => 'Scan QR Semua Aplikasi',
        'logo_img' => 'uploads/logo-qris.jpeg',
        'badge_text' => 'QRIS',
        'badge_class' => 'logo-qris',
        'icon_fallback' => '',
        'active' => true
    ],
    [
        'value' => 'Bayar Tunai / Cash',
        'title' => 'Bayar Tunai / Cash',
        'subtitle' => 'Bayar langsung saat ambil / COD',
        'logo_img' => 'uploads/logo-cash.jpeg',
        'badge_text' => 'CASH',
        'badge_class' => 'logo-cash',
        'icon_fallback' => 'fa-money-bill-wave',
        'active' => false
    ]
];

$metode_categories = [
    'bank' => [
        'label' => 'Transfer Bank (Virtual Account)',
        'sublabel' => 'BCA, Mandiri, BNI, BRI, BSI',
        'icon' => 'fa-building-columns',
        'items' => [
            [
                'value' => 'Virtual Account BCA',
                'title' => 'Virtual Account BCA',
                'subtitle' => 'Transfer Otomatis VA',
                'logo_img' => 'uploads/logo-bca.jpeg',
                'badge_text' => 'BCA',
                'badge_class' => 'logo-bca'
            ],
            [
                'value' => 'Virtual Account Mandiri',
                'title' => 'Virtual Account Mandiri',
                'subtitle' => 'Transfer Otomatis VA',
                'logo_img' => 'uploads/logo-mandiri.jpeg',
                'badge_text' => 'MANDIRI',
                'badge_class' => 'logo-mandiri'
            ],
            [
                'value' => 'Virtual Account BNI',
                'title' => 'Virtual Account BNI',
                'subtitle' => 'Transfer Otomatis VA',
                'logo_img' => 'uploads/logo-bni.jpeg',
                'badge_text' => 'BNI',
                'badge_class' => 'logo-bni'
            ],
            [
                'value' => 'Virtual Account BRI',
                'title' => 'Virtual Account BRI',
                'subtitle' => 'Transfer Otomatis VA',
                'logo_img' => 'uploads/logo-bri.jpeg',
                'badge_text' => 'BRI',
                'badge_class' => 'logo-bri'
            ],
            [
                'value' => 'Virtual Account BSI',
                'title' => 'Virtual Account BSI',
                'subtitle' => 'Transfer Otomatis VA',
                'logo_img' => 'uploads/logo-bsi.jpeg',
                'badge_text' => 'BSI',
                'badge_class' => 'logo-bsi'
            ]
        ]
    ],
    'ewallet' => [
        'label' => 'E-Wallet',
        'sublabel' => 'DANA, ShopeePay, GoPay',
        'icon' => 'fa-wallet',
        'items' => [
            [
                'value' => 'DANA',
                'title' => 'DANA',
                'subtitle' => 'E-Wallet Instan',
                'logo_img' => 'uploads/logo-dana.jpeg',
                'badge_text' => 'DANA',
                'badge_class' => 'logo-dana'
            ],
            [
                'value' => 'ShopeePay',
                'title' => 'ShopeePay',
                'subtitle' => 'E-Wallet Instan',
                'logo_img' => 'uploads/logo-shopeepay.jpeg',
                'badge_text' => 'SPay',
                'badge_class' => 'logo-shopeepay'
            ],
            [
                'value' => 'GoPay',
                'title' => 'GoPay',
                'subtitle' => 'E-Wallet Instan',
                'logo_img' => 'uploads/logo-gopay.jpeg',
                'badge_text' => 'GoPay',
                'badge_class' => 'logo-gopay'
            ]
        ]
    ]
];

$allowed_metode_bayar = [];
foreach ($instant_methods as $inst) {
    $allowed_metode_bayar[] = $inst['value'];
}
foreach ($metode_categories as $cat) {
    foreach ($cat['items'] as $itm) {
        $allowed_metode_bayar[] = $itm['value'];
    }
}

function resolvePaymentLogo($relPath) {
    $base_no_ext = preg_replace('/\.jpeg$/i', '', $relPath);
    foreach (['.jpeg', '.jpg', '.png'] as $ext_try) {
        if (file_exists(__DIR__ . '/' . $base_no_ext . $ext_try)) {
            return $base_no_ext . $ext_try;
        }
    }
    return '';
}

// Proses update jumlah item di keranjang
if (isset($_POST['update_keranjang'])) {
    if (!empty($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $id_p => $jml) {
            $id_p = (int)$id_p;
            $jml = (int)$jml;
            if ($jml <= 0) {
                unset($_SESSION['keranjang'][$id_p]);
            } else {
                $q_stok = mysqli_query($conn, "SELECT stok FROM produk WHERE id_produk = '$id_p' LIMIT 1");
                $stok_tersedia = 0;
                if ($q_stok && mysqli_num_rows($q_stok) > 0) {
                    $r_stok = mysqli_fetch_assoc($q_stok);
                    $stok_tersedia = (int)($r_stok['stok'] ?? 0);
                }
                $_SESSION['keranjang'][$id_p] = min($jml, max(0, $stok_tersedia));
                if ($_SESSION['keranjang'][$id_p] <= 0) {
                    unset($_SESSION['keranjang'][$id_p]);
                }
            }
        }
    }
    $pesan_sukses = 'Keranjang belanja berhasil diperbarui.';
}

// Proses hapus 1 item dari keranjang
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    unset($_SESSION['keranjang'][$id_hapus]);
    header("Location: keranjang.php");
    exit;
}

// Ambil detail produk yang ada di keranjang
$items_keranjang = [];
$total_belanja = 0;

if (!empty($_SESSION['keranjang'])) {
    $ids = array_map('intval', array_keys($_SESSION['keranjang']));
    $ids_str = implode(',', $ids);
    if ($ids_str !== '') {
        $q_prod = mysqli_query($conn, "SELECT * FROM produk WHERE id_produk IN ($ids_str)");
        if ($q_prod) {
            while ($p = mysqli_fetch_assoc($q_prod)) {
                $id_p = (int)$p['id_produk'];
                $qty = (int)($_SESSION['keranjang'][$id_p] ?? 0);
                $stok_now = (int)($p['stok'] ?? 0);
                if ($qty > 0 && $stok_now > 0) {
                    $qty_final = min($qty, $stok_now);
                    $_SESSION['keranjang'][$id_p] = $qty_final;
                    $subtotal = $qty_final * (int)($p['harga'] ?? 0);
                    $total_belanja += $subtotal;
                    $p['qty_keranjang'] = $qty_final;
                    $p['subtotal'] = $subtotal;
                    $items_keranjang[] = $p;
                } else {
                    unset($_SESSION['keranjang'][$id_p]);
                }
            }
        }
    }
}

// Data pelanggan bila sudah login
$pelanggan_login = [
    'id_pelanggan' => $_SESSION['id_pelanggan'] ?? null,
    'nama' => '',
    'telepon' => '',
    'alamat' => '',
    'email' => ''
];

if (!empty($pelanggan_login['id_pelanggan'])) {
    $id_plg = (int)$pelanggan_login['id_pelanggan'];
    try {
        $q_plg = mysqli_query($conn, "SELECT * FROM pelanggan WHERE id_pelanggan = '$id_plg' LIMIT 1");
        if ($q_plg && mysqli_num_rows($q_plg) > 0) {
            $r_plg = mysqli_fetch_assoc($q_plg);
            $pelanggan_login['nama'] = $r_plg['nama_pelanggan'] ?? $r_plg['nama_lengkap'] ?? $r_plg['nama'] ?? '';
            $pelanggan_login['telepon'] = $r_plg['telepon'] ?? $r_plg['no_hp'] ?? $r_plg['no_whatsapp'] ?? '';
            $pelanggan_login['alamat'] = $r_plg['alamat'] ?? '';
            $pelanggan_login['email'] = $r_plg['email'] ?? '';
        }
    } catch (Throwable $e) {
        // Abaikan jika gagal membaca profil pelanggan
    }
}

// Proses checkout pembeli online (otomatis terdata ke pelanggan & transaksi)
if (isset($_POST['proses_checkout'])) {
    if (empty($items_keranjang)) {
        $pesan_error = 'Keranjang belanja masih kosong.';
    } else {
        $nama_penerima = trim($_POST['nama_penerima'] ?? $pelanggan_login['nama']);
        $telepon_penerima = trim($_POST['telepon_penerima'] ?? $pelanggan_login['telepon']);
        $alamat_penerima = trim($_POST['alamat_penerima'] ?? $pelanggan_login['alamat']);
        $metode_bayar = trim($_POST['metode_bayar'] ?? $instant_methods[0]['value']);
        $catatan = trim($_POST['catatan'] ?? '');

        if (!in_array($metode_bayar, $allowed_metode_bayar, true)) {
            $pesan_error = 'Metode pembayaran tidak valid. Silakan pilih kembali metode yang tersedia.';
        } elseif ($nama_penerima === '' || $telepon_penerima === '' || $alamat_penerima === '') {
            $pesan_error = 'Silakan lengkapi nama penerima, nomor telepon/WA, dan alamat pengiriman.';
        } else {
            mysqli_begin_transaction($conn);
            try {
                $id_pelanggan_fix = !empty($pelanggan_login['id_pelanggan']) ? (int)$pelanggan_login['id_pelanggan'] : 0;

                $cols_plg = [];
                $q_c_plg = mysqli_query($conn, "SHOW COLUMNS FROM pelanggan");
                if ($q_c_plg) {
                    while ($c = mysqli_fetch_assoc($q_c_plg)) {
                        $cols_plg[] = $c['Field'];
                    }
                }

                $col_nama_plg = in_array('nama_pelanggan', $cols_plg) ? 'nama_pelanggan' : (in_array('nama_lengkap', $cols_plg) ? 'nama_lengkap' : 'nama');
                $col_telp_plg = in_array('telepon', $cols_plg) ? 'telepon' : (in_array('no_hp', $cols_plg) ? 'no_hp' : 'no_whatsapp');

                if ($id_pelanggan_fix > 0) {
                    $upd_parts = [];
                    if (in_array($col_nama_plg, $cols_plg)) {
                        $upd_parts[] = "`$col_nama_plg` = '" . mysqli_real_escape_string($conn, $nama_penerima) . "'";
                    }
                    if (in_array($col_telp_plg, $cols_plg)) {
                        $upd_parts[] = "`$col_telp_plg` = '" . mysqli_real_escape_string($conn, $telepon_penerima) . "'";
                    }
                    if (in_array('alamat', $cols_plg)) {
                        $upd_parts[] = "`alamat` = '" . mysqli_real_escape_string($conn, $alamat_penerima) . "'";
                    }
                    if (!empty($upd_parts)) {
                        mysqli_query($conn, "UPDATE pelanggan SET " . implode(', ', $upd_parts) . " WHERE id_pelanggan = '$id_pelanggan_fix'");
                    }
                } else {
                    $ins_cols = [];
                    $ins_vals = [];
                    if (in_array($col_nama_plg, $cols_plg)) {
                        $ins_cols[] = "`$col_nama_plg`";
                        $ins_vals[] = "'" . mysqli_real_escape_string($conn, $nama_penerima) . "'";
                    }
                    if (in_array($col_telp_plg, $cols_plg)) {
                        $ins_cols[] = "`$col_telp_plg`";
                        $ins_vals[] = "'" . mysqli_real_escape_string($conn, $telepon_penerima) . "'";
                    }
                    if (in_array('alamat', $cols_plg)) {
                        $ins_cols[] = "`alamat`";
                        $ins_vals[] = "'" . mysqli_real_escape_string($conn, $alamat_penerima) . "'";
                    }
                    if (!empty($ins_cols)) {
                        mysqli_query($conn, "INSERT INTO pelanggan (" . implode(', ', $ins_cols) . ") VALUES (" . implode(', ', $ins_vals) . ")");
                        $id_pelanggan_fix = (int)mysqli_insert_id($conn);
                    }
                }

                $cols_trx = [];
                $q_c_trx = mysqli_query($conn, "SHOW COLUMNS FROM transaksi");
                if ($q_c_trx) {
                    while ($c = mysqli_fetch_assoc($q_c_trx)) {
                        $cols_trx[] = $c['Field'];
                    }
                }

                $kode_ref = 'KC-' . date('YmdHis') . '-' . rand(100, 999);
                $tgl_now = date('Y-m-d H:i:s');
                $status_bayar_awal = ($metode_bayar === 'Bayar Tunai / Cash') ? 'Bayar di Tempat / Tunai' : 'Menunggu Pembayaran';

                $trx_data = [
                    'id_pelanggan' => $id_pelanggan_fix,
                    'kode_referensi' => $kode_ref,
                    'tanggal_transaksi' => $tgl_now,
                    'total_pembelian' => (int)$total_belanja,
                    'metode_bayar' => $metode_bayar,
                    'status_transaksi' => 'Diproses',
                    'status_bayar' => $status_bayar_awal,
                    'tipe_order' => 'Online',
                    'catatan' => $catatan
                ];

                $t_cols = [];
                $t_vals = [];
                foreach ($trx_data as $k => $v) {
                    if (in_array($k, $cols_trx)) {
                        $t_cols[] = "`$k`";
                        $t_vals[] = is_int($v) ? (string)$v : "'" . mysqli_real_escape_string($conn, (string)$v) . "'";
                    }
                }

                mysqli_query($conn, "INSERT INTO transaksi (" . implode(', ', $t_cols) . ") VALUES (" . implode(', ', $t_vals) . ")");
                $id_transaksi_baru = (int)mysqli_insert_id($conn);

                foreach ($items_keranjang as $item) {
                    $id_p = (int)$item['id_produk'];
                    $qty_p = (int)$item['qty_keranjang'];
                    $harga_p = (int)$item['harga'];
                    $sub_p = (int)$item['subtotal'];

                    mysqli_query($conn, "INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga_satuan, subtotal)
                        VALUES ('$id_transaksi_baru', '$id_p', '$qty_p', '$harga_p', '$sub_p')");

                    mysqli_query($conn, "UPDATE produk SET stok = GREATEST(0, stok - $qty_p) WHERE id_produk = '$id_p'");
                }

                mysqli_commit($conn);
                $_SESSION['keranjang'] = [];
                $items_keranjang = [];
                $total_belanja = 0;
                $pesan_sukses = "Pesanan Online berhasil dibuat! Kode Referensi: <strong>$kode_ref</strong> &bull; Metode: <strong>" . htmlspecialchars($metode_bayar) . "</strong>";
            } catch (Throwable $e) {
                mysqli_rollback($conn);
                $pesan_error = 'Gagal memproses pesanan: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang & Checkout - Kembang Cerita Flowers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --feminine-pink: #d9778c;
            --feminine-dark: #4a2e35;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff9fb;
            color: var(--feminine-dark);
        }
        .card-soft {
            border: none;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 8px 25px rgba(217, 119, 140, 0.08);
        }
        .btn-feminine {
            background-color: var(--feminine-pink);
            color: white;
            border: none;
            border-radius: 50px;
            transition: all 0.2s;
        }
        .btn-feminine:hover {
            background-color: #c2657a;
            color: white;
        }
        .payment-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .payment-group-box {
            border: 1.5px solid #f0d5dc;
            border-radius: 16px;
            background: #ffffff;
            overflow: hidden;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .payment-group-box.has-selected {
            border-color: var(--feminine-pink);
            box-shadow: 0 5px 14px rgba(217, 119, 140, 0.12);
        }
        .payment-group-header {
            width: 100%;
            border: none;
            background: #ffffff;
            padding: 13px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .payment-group-header:hover {
            background: #fff7f9;
        }
        .payment-group-header .chevron-icon {
            color: #9e6b78;
            transition: transform 0.25s ease;
        }
        .payment-group-header[aria-expanded="true"] .chevron-icon {
            transform: rotate(180deg);
            color: var(--feminine-pink);
        }
        .payment-children {
            padding: 0 14px 14px;
            border-top: 1px dashed #f3dde3;
            background: #fffcfd;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(195px, 1fr));
            gap: 10px;
            padding-top: 12px;
        }
        .payment-card {
            border: 1.5px solid #f0d5dc;
            border-radius: 14px;
            padding: 10px 12px;
            background: #ffffff;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 11px;
            transition: all 0.2s ease;
            user-select: none;
        }
        .payment-card:hover {
            border-color: var(--feminine-pink);
            background: #fff7f9;
            transform: translateY(-1px);
        }
        .payment-card.active {
            border-color: var(--feminine-pink);
            background: #fff0f4;
            box-shadow: 0 5px 14px rgba(217, 119, 140, 0.18);
        }
        .payment-logo-box {
            width: 72px;
            height: 46px;
            padding: 2px;
            border-radius: 10px;
            border: 1px solid #f2dbe1;
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .payment-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transform: scale(1.16);
            display: block;
        }
        .payment-badge-fallback {
            width: 100%;
            height: 100%;
            display: none;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 10px;
            border-radius: 8px;
            color: #ffffff;
        }
        .logo-qris { background: linear-gradient(135deg, #c8102e, #8b0000); }
        .logo-cash { background: linear-gradient(135deg, #2e7d32, #1b5e20); }
        .logo-bca { background: linear-gradient(135deg, #005eb8, #003d7a); }
        .logo-mandiri { background: linear-gradient(135deg, #003d79, #ffb700); }
        .logo-bni { background: linear-gradient(135deg, #006666, #ff6600); }
        .logo-bri { background: linear-gradient(135deg, #00529c, #0077c8); }
        .logo-bsi { background: linear-gradient(135deg, #00a39d, #f5a623); }
        .logo-dana { background: linear-gradient(135deg, #108ee9, #0066cc); }
        .logo-shopeepay { background: linear-gradient(135deg, #ee4d2d, #ff7337); }
        .logo-gopay { background: linear-gradient(135deg, #00aa13, #00880e); }
        .selected-method-pill {
            font-size: 11.5px;
            background: #fbebee;
            color: var(--feminine-pink);
            border-radius: 50px;
            padding: 4px 10px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar shadow-sm bg-white py-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand fw-bold" href="index.php" style="color: var(--feminine-pink);">
                <i class="fa fa-arrow-left me-2"></i> Kembang Cerita Flowers
            </a>
            <span class="badge rounded-pill px-3 py-2" style="background-color: #fbebee; color: var(--feminine-pink);">
                <i class="fa fa-shopping-bag me-1"></i> Keranjang Belanja
            </span>
        </div>
    </nav>

    <div class="container pb-5">
        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success rounded-4 shadow-sm"><?php echo $pesan_sukses; ?></div>
        <?php endif; ?>
        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger rounded-4 shadow-sm"><?php echo htmlspecialchars($pesan_error); ?></div>
        <?php endif; ?>

        <?php if (!empty($items_keranjang)): ?>
            <div class="row g-4">
                <!-- Daftar Item Keranjang -->
                <div class="col-lg-7">
                    <div class="card card-soft p-4">
                        <h4 class="fw-bold mb-3">Daftar Buket di Keranjang</h4>
                        <form method="POST">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Harga</th>
                                            <th style="width: 105px;">Jumlah</th>
                                            <th>Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items_keranjang as $item): ?>
                                        <?php $id_p = (int)$item['id_produk']; ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="uploads/<?php echo htmlspecialchars($item['foto'] ?: 'default.jpg'); ?>" width="54" height="54" class="rounded-3 object-fit-cover" alt="">
                                                    <div>
                                                        <div class="fw-semibold small"><?php echo htmlspecialchars($item['nama_produk']); ?></div>
                                                        <small class="text-muted">Stok: <?php echo (int)$item['stok']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="small">Rp <?php echo number_format((int)$item['harga'], 0, ',', '.'); ?></td>
                                            <td>
                                                <input type="number" name="qty[<?php echo $id_p; ?>]" value="<?php echo (int)$item['qty_keranjang']; ?>" min="0" max="<?php echo (int)$item['stok']; ?>" class="form-control form-control-sm rounded-pill text-center">
                                            </td>
                                            <td class="fw-semibold small" style="color: var(--feminine-pink);">
                                                Rp <?php echo number_format((int)$item['subtotal'], 0, ',', '.'); ?>
                                            </td>
                                            <td>
                                                <a href="keranjang.php?hapus=<?php echo $id_p; ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Hapus item ini?')">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2">
                                <a href="index.php#katalog" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Lanjut Belanja</a>
                                <button type="submit" name="update_keranjang" class="btn btn-outline-dark btn-sm rounded-pill px-4">
                                    <i class="fa fa-rotate me-1"></i> Perbarui Jumlah
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Form Checkout & Metode Payment Gateway Ala Shopee -->
                <div class="col-lg-5">
                    <div class="card card-soft p-4">
                        <h4 class="fw-bold mb-3">Data Penerima & Pembayaran</h4>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Nama Penerima</label>
                                <input type="text" name="nama_penerima" class="form-control rounded-pill px-3" value="<?php echo htmlspecialchars($pelanggan_login['nama']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">No. WhatsApp / Telepon</label>
                                <input type="text" name="telepon_penerima" class="form-control rounded-pill px-3" value="<?php echo htmlspecialchars($pelanggan_login['telepon']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Alamat Pengiriman</label>
                                <textarea name="alamat_penerima" rows="2" class="form-control rounded-4 px-3" required><?php echo htmlspecialchars($pelanggan_login['alamat']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label small fw-semibold mb-0">Metode Pembayaran</label>
                                    <span class="selected-method-pill" id="selectedMethodBadge"><?php echo htmlspecialchars($instant_methods[0]['title']); ?></span>
                                </div>
                                <input type="hidden" name="metode_bayar" id="metodeBayarInput" value="<?php echo htmlspecialchars($instant_methods[0]['value']); ?>" required>

                                <div class="payment-stack" id="paymentMethodAccordion">
                                    <!-- 1. Opsi Instan (QRIS Instan & Bayar Tunai / Cash) -->
                                    <?php foreach ($instant_methods as $inst): ?>
                                    <?php $inst_img = resolvePaymentLogo($inst['logo_img']); ?>
                                    <div class="payment-group-box <?php echo $inst['active'] ? 'has-selected' : ''; ?>">
                                        <div class="payment-card border-0 rounded-4 <?php echo $inst['active'] ? 'active' : ''; ?>"
                                             data-value="<?php echo htmlspecialchars($inst['value']); ?>"
                                             data-label="<?php echo htmlspecialchars($inst['title']); ?>"
                                             onclick="pilihMetodeBayar(this)">
                                            <span class="payment-logo-box">
                                                <?php if ($inst_img !== ''): ?>
                                                    <img src="<?php echo htmlspecialchars($inst_img); ?>"
                                                         alt="<?php echo htmlspecialchars($inst['title']); ?>"
                                                         class="payment-logo-img"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <span class="payment-badge-fallback <?php echo htmlspecialchars($inst['badge_class']); ?>">
                                                        <?php if (!empty($inst['icon_fallback'])): ?><i class="fa <?php echo htmlspecialchars($inst['icon_fallback']); ?> me-1"></i><?php endif; ?>
                                                        <?php echo htmlspecialchars($inst['badge_text']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="payment-badge-fallback <?php echo htmlspecialchars($inst['badge_class']); ?>" style="display:flex;">
                                                        <?php if (!empty($inst['icon_fallback'])): ?><i class="fa <?php echo htmlspecialchars($inst['icon_fallback']); ?> me-1"></i><?php endif; ?>
                                                        <?php echo htmlspecialchars($inst['badge_text']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </span>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold small"><?php echo htmlspecialchars($inst['title']); ?></div>
                                                <div class="text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($inst['subtitle']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>

                                    <!-- 2. Kategori Collapse (Transfer Bank & E-Wallet) -->
                                    <?php foreach ($metode_categories as $catKey => $cat): ?>
                                    <?php $collapseId = 'collapse_' . $catKey; ?>
                                    <div class="payment-group-box" data-group="<?php echo htmlspecialchars($catKey); ?>">
                                        <button type="button"
                                                class="payment-group-header"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#<?php echo $collapseId; ?>"
                                                aria-expanded="false"
                                                aria-controls="<?php echo $collapseId; ?>">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="d-inline-flex align-items-center justify-content-center rounded-3"
                                                      style="width: 38px; height: 38px; background: #fbebee; color: var(--feminine-pink);">
                                                    <i class="fa <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                                                </span>
                                                <div>
                                                    <div class="fw-semibold small"><?php echo htmlspecialchars($cat['label']); ?></div>
                                                    <div class="text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($cat['sublabel']); ?></div>
                                                </div>
                                            </div>
                                            <i class="fa fa-chevron-down chevron-icon small"></i>
                                        </button>

                                        <div class="collapse" id="<?php echo $collapseId; ?>" data-bs-parent="#paymentMethodAccordion">
                                            <div class="payment-children">
                                                <?php foreach ($cat['items'] as $m): ?>
                                                <?php $resolved_img = resolvePaymentLogo($m['logo_img']); ?>
                                                <div class="payment-card"
                                                     data-value="<?php echo htmlspecialchars($m['value']); ?>"
                                                     data-label="<?php echo htmlspecialchars($m['title']); ?>"
                                                     onclick="pilihMetodeBayar(this)">
                                                    <span class="payment-logo-box">
                                                        <?php if ($resolved_img !== ''): ?>
                                                            <img src="<?php echo htmlspecialchars($resolved_img); ?>"
                                                                 alt="<?php echo htmlspecialchars($m['title']); ?>"
                                                                 class="payment-logo-img"
                                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                            <span class="payment-badge-fallback <?php echo htmlspecialchars($m['badge_class']); ?>">
                                                                <?php echo htmlspecialchars($m['badge_text']); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="payment-badge-fallback <?php echo htmlspecialchars($m['badge_class']); ?>" style="display:flex;">
                                                                <?php echo htmlspecialchars($m['badge_text']); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <div>
                                                        <div class="fw-semibold small"><?php echo htmlspecialchars($m['title']); ?></div>
                                                        <div class="text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($m['subtitle']); ?></div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Catatan / Kartu Ucapan (Opsional)</label>
                                <input type="text" name="catatan" class="form-control rounded-pill px-3" placeholder="Contoh: Happy Graduation, kirim jam 14.00">
                            </div>

                            <hr>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-semibold">Total Pembayaran</span>
                                <span class="fw-bold fs-4" style="color: var(--feminine-pink);">Rp <?php echo number_format((int)$total_belanja, 0, ',', '.'); ?></span>
                            </div>

                            <button type="submit" name="proses_checkout" class="btn btn-feminine w-100 py-2.5 fw-semibold shadow-sm">
                                <i class="fa fa-lock me-1"></i> Buat Pesanan Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card card-soft p-5 text-center">
                <h5 class="fw-bold mb-2">Keranjang Belanja Kosong</h5>
                <p class="text-muted small mb-4">Yuk pilih buket bunga favoritmu di katalog terlebih dahulu.</p>
                <div>
                    <a href="index.php#katalog" class="btn btn-feminine px-5 py-2 fw-medium">Lihat Katalog Buket</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function pilihMetodeBayar(cardEl) {
            document.querySelectorAll('#paymentMethodAccordion .payment-card').forEach(function(item) {
                item.classList.remove('active');
            });
            document.querySelectorAll('#paymentMethodAccordion .payment-group-box').forEach(function(box) {
                box.classList.remove('has-selected');
            });

            cardEl.classList.add('active');
            const parentGroup = cardEl.closest('.payment-group-box');
            if (parentGroup) {
                parentGroup.classList.add('has-selected');
            }

            const hiddenInput = document.getElementById('metodeBayarInput');
            if (hiddenInput) {
                hiddenInput.value = cardEl.getAttribute('data-value');
            }

            const badgeEl = document.getElementById('selectedMethodBadge');
            if (badgeEl) {
                badgeEl.textContent = cardEl.getAttribute('data-label') || cardEl.getAttribute('data-value');
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>