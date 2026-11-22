<?php
include 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nama_toko = 'Kembang Cerita Flowers';
$foto_logo = 'logo.jpg';

try {
    $q_toko = mysqli_query($conn, "SELECT * FROM profil_toko WHERE id_profil = 1 LIMIT 1");
    if ($q_toko && mysqli_num_rows($q_toko) > 0) {
        $r_toko = mysqli_fetch_assoc($q_toko);
        if (!empty($r_toko['nama_toko'])) $nama_toko = $r_toko['nama_toko'];
        if (!empty($r_toko['foto_logo'])) $foto_logo = $r_toko['foto_logo'];
    }
} catch (Throwable $e) {
    // Gunakan nilai default jika tabel belum tersedia
}

function resolveLoginTokoLogo($fotoLogoDb) {
    $baseDir = __DIR__ . '/uploads/';
    $bersih = basename((string)$fotoLogoDb);
    if ($bersih === '') {
        $bersih = 'logo.jpg';
    }
    if (file_exists($baseDir . $bersih)) {
        return 'uploads/' . $bersih;
    }

    $tanpaExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $bersih);
    foreach (['.jpeg', '.jpg', '.png', '.webp'] as $ext) {
        if (file_exists($baseDir . $tanpaExt . $ext)) {
            return 'uploads/' . $tanpaExt . $ext;
        }
    }

    foreach (['logo.jpeg', 'logo.jpg', 'logo.png', 'logo.webp'] as $cadangan) {
        if (file_exists($baseDir . $cadangan)) {
            return 'uploads/' . $cadangan;
        }
    }

    return 'uploads/' . $bersih;
}

$logo_login_url = resolveLoginTokoLogo($foto_logo);

$pesan_error = '';
$pesan_sukses = '';

$redirect_after_auth = 'index.php';
if (!empty($_GET['redirect']) && is_string($_GET['redirect'])) {
    $cand = $_GET['redirect'];
    if (strpos($cand, 'http') !== 0 && strpos($cand, '//') !== 0) {
        $redirect_after_auth = $cand;
    }
}

$cols_plg = [];
try {
    $q_cols = mysqli_query($conn, "SHOW COLUMNS FROM pelanggan");
    if ($q_cols) {
        while ($c = mysqli_fetch_assoc($q_cols)) {
            $cols_plg[] = $c['Field'];
        }
    }
} catch (Throwable $e) {
    $cols_plg = [];
}

$col_nama = in_array('nama_pelanggan', $cols_plg) ? 'nama_pelanggan' : (in_array('nama_lengkap', $cols_plg) ? 'nama_lengkap' : 'nama');
$col_telp = in_array('telepon', $cols_plg) ? 'telepon' : (in_array('no_hp', $cols_plg) ? 'no_hp' : 'no_whatsapp');
$col_pass = in_array('password_hash', $cols_plg) ? 'password_hash' : 'password';

if (isset($_POST['login_pelanggan'])) {
    $identitas = trim($_POST['identitas'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($identitas === '' || $password === '') {
        $pesan_error = 'Silakan masukkan email/no. HP dan kata sandi.';
    } else {
        $id_esc = mysqli_real_escape_string($conn, $identitas);
        $where_parts = [];
        if (in_array('email', $cols_plg)) $where_parts[] = "`email` = '$id_esc'";
        if (in_array($col_telp, $cols_plg)) $where_parts[] = "`$col_telp` = '$id_esc'";
        if (in_array($col_nama, $cols_plg)) $where_parts[] = "`$col_nama` = '$id_esc'";

        $sql_plg = !empty($where_parts)
            ? "SELECT * FROM pelanggan WHERE (" . implode(' OR ', $where_parts) . ") LIMIT 1"
            : "SELECT * FROM pelanggan LIMIT 1";

        $q_login = mysqli_query($conn, $sql_plg);
        if ($q_login && mysqli_num_rows($q_login) > 0) {
            $row_plg = mysqli_fetch_assoc($q_login);
            $hash_db = (string)($row_plg[$col_pass] ?? $row_plg['password_hash'] ?? $row_plg['password'] ?? '');

            $cocok = false;
            if ($hash_db !== '') {
                if (password_verify($password, $hash_db) || $password === $hash_db || md5($password) === $hash_db) {
                    $cocok = true;
                }
            }

            if ($cocok) {
                $_SESSION['id_pelanggan'] = $row_plg['id_pelanggan'] ?? 1;
                $_SESSION['nama_pelanggan'] = $row_plg[$col_nama] ?? $row_plg['nama_pelanggan'] ?? $row_plg['nama'] ?? 'Pelanggan';
                header("Location: " . $redirect_after_auth);
                exit;
            } else {
                $pesan_error = 'Kata sandi akun pelanggan salah.';
            }
        } else {
            $pesan_error = 'Akun pelanggan tidak ditemukan. Silakan daftar terlebih dahulu.';
        }
    }
}

if (isset($_POST['daftar_pelanggan'])) {
    $nama_baru = trim($_POST['nama_baru'] ?? '');
    $email_baru = trim($_POST['email_baru'] ?? '');
    $telp_baru = trim($_POST['telp_baru'] ?? '');
    $alamat_baru = trim($_POST['alamat_baru'] ?? '');
    $pass_baru = (string)($_POST['pass_baru'] ?? '');

    if ($nama_baru === '' || $telp_baru === '' || $pass_baru === '') {
        $pesan_error = 'Nama lengkap, nomor WhatsApp/telepon, dan kata sandi wajib diisi.';
    } else {
        $ins_cols = [];
        $ins_vals = [];

        if (in_array($col_nama, $cols_plg)) {
            $ins_cols[] = "`$col_nama`";
            $ins_vals[] = "'" . mysqli_real_escape_string($conn, $nama_baru) . "'";
        }
        if ($email_baru !== '' && in_array('email', $cols_plg)) {
            $ins_cols[] = "`email`";
            $ins_vals[] = "'" . mysqli_real_escape_string($conn, $email_baru) . "'";
        }
        if (in_array($col_telp, $cols_plg)) {
            $ins_cols[] = "`$col_telp`";
            $ins_vals[] = "'" . mysqli_real_escape_string($conn, $telp_baru) . "'";
        }
        if ($alamat_baru !== '' && in_array('alamat', $cols_plg)) {
            $ins_cols[] = "`alamat`";
            $ins_vals[] = "'" . mysqli_real_escape_string($conn, $alamat_baru) . "'";
        }
        if (in_array($col_pass, $cols_plg)) {
            $ins_cols[] = "`$col_pass`";
            $ins_vals[] = "'" . mysqli_real_escape_string($conn, password_hash($pass_baru, PASSWORD_DEFAULT)) . "'";
        }

        if (!empty($ins_cols)) {
            $ok_ins = mysqli_query($conn, "INSERT INTO pelanggan (" . implode(', ', $ins_cols) . ") VALUES (" . implode(', ', $ins_vals) . ")");
            if ($ok_ins) {
                $id_baru = (int)mysqli_insert_id($conn);
                $_SESSION['id_pelanggan'] = $id_baru;
                $_SESSION['nama_pelanggan'] = $nama_baru;
                header("Location: " . $redirect_after_auth);
                exit;
            } else {
                $pesan_error = 'Gagal mendaftarkan akun: ' . mysqli_error($conn);
            }
        } else {
            $pesan_error = 'Struktur kolom tabel pelanggan belum lengkap.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Akun - <?php echo htmlspecialchars($nama_toko); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --wine-dark: #451524;
            --wine-mid: #5a2033;
            --rose-cta: #c86d82;
            --rose-cta-hover: #b4586e;
            --ink-main: #4a2e35;
        }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            background: radial-gradient(circle at 12% 18%, #f8dce4 0%, #f6e7ec 48%, #f2d8e0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--ink-main);
        }
        .split-auth-wrapper {
            width: 100%;
            max-width: 980px;
            min-height: 540px;
            border-radius: 26px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 44% 56%;
            box-shadow: 0 24px 60px rgba(69, 21, 36, 0.16);
            background: #ffffff;
        }
        .left-brand-panel {
            position: relative;
            background: linear-gradient(165deg, #4b1828 0%, #3b101e 100%);
            color: #ffffff;
            padding: 36px 34px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
        }
        .left-brand-panel::after {
            content: "";
            position: absolute;
            top: 0;
            right: -26px;
            width: 52px;
            height: 100%;
            background: #fdebf0;
            border-top-left-radius: 100% 50%;
            border-bottom-left-radius: 100% 50%;
            pointer-events: none;
        }
        .brand-serif {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 28px;
            line-height: 1.2;
            margin-bottom: 4px;
            z-index: 2;
        }
        .brand-tagline {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.78);
            margin-bottom: 0;
            z-index: 2;
        }
        .logo-medallion {
            width: 174px;
            height: 174px;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #6b2b40 0%, #491727 78%);
            box-shadow:
                inset 0 2px 6px rgba(255, 255, 255, 0.14),
                0 12px 26px rgba(0, 0, 0, 0.34);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            margin: 14px 0;
            z-index: 2;
            overflow: hidden;
        }
        .logo-medallion img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }
        .left-caption {
            font-size: 12px;
            line-height: 1.55;
            color: rgba(255, 255, 255, 0.76);
            max-width: 280px;
            margin-bottom: 14px;
            z-index: 2;
        }
        .btn-back-home {
            background: #5b2235;
            color: #f6e7ec;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 50px;
            padding: 8px 20px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            z-index: 2;
        }
        .btn-back-home:hover {
            background: #6f2b42;
            color: #ffffff;
        }
        .right-form-panel {
            background: #fdf9fa;
            padding: 44px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            z-index: 1;
        }
        .welcome-pill {
            display: inline-block;
            background: #fce7ed;
            color: var(--rose-cta);
            font-size: 11px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 50px;
            margin-bottom: 10px;
            width: fit-content;
        }
        .form-heading {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 30px;
            color: #3d1220;
            margin-bottom: 4px;
        }
        .form-sub {
            font-size: 12.5px;
            color: #7d646b;
            margin-bottom: 24px;
        }
        .soft-input-group {
            position: relative;
            margin-bottom: 14px;
        }
        .soft-input-group .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #c77b8e;
            font-size: 13px;
            pointer-events: none;
        }
        .soft-input {
            width: 100%;
            border: 1.5px solid #efd8df;
            background: #ffffff;
            border-radius: 14px;
            padding: 12px 16px 12px 44px;
            font-size: 13.5px;
            color: var(--ink-main);
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .soft-input:focus {
            border-color: var(--rose-cta);
            box-shadow: 0 0 0 3px rgba(200, 109, 130, 0.14);
        }
        .btn-rose-submit {
            width: 100%;
            border: none;
            border-radius: 50px;
            background: linear-gradient(90deg, #cd7287 0%, #bd6177 100%);
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 24px;
            box-shadow: 0 10px 22px rgba(189, 97, 119, 0.32);
            transition: transform 0.2s ease, filter 0.2s ease;
        }
        .btn-rose-submit:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            color: #ffffff;
        }
        .switch-link {
            color: #b8586f;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .switch-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 820px) {
            .split-auth-wrapper {
                grid-template-columns: 1fr;
            }
            .left-brand-panel::after {
                display: none;
            }
            .right-form-panel {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="split-auth-wrapper">
        <!-- Panel Kiri Burgundy -->
        <div class="left-brand-panel">
            <div>
                <div class="brand-serif">Kembang Cerita</div>
                <p class="brand-tagline">Rangkaian Kisah, Penuh Makna.</p>
            </div>

            <div class="logo-medallion">
                <img src="<?php echo htmlspecialchars($logo_login_url); ?>"
                     alt="Logo <?php echo htmlspecialchars($nama_toko); ?>"
                     onerror="this.style.display='none';">
            </div>

            <div>
                <p class="left-caption">
                    Setiap kelopak menyimpan cerita. Masuk untuk merangkai buket spesial bagi orang tersayang.
                </p>
                <a href="index.php" class="btn-back-home">
                    <i class="fa fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Panel Kanan Form Masuk / Daftar -->
        <div class="right-form-panel">
            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger rounded-4 py-2 px-3 small mb-3"><?php echo htmlspecialchars($pesan_error); ?></div>
            <?php endif; ?>
            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success rounded-4 py-2 px-3 small mb-3"><?php echo htmlspecialchars($pesan_sukses); ?></div>
            <?php endif; ?>

            <div id="formMasukBox">
                <span class="welcome-pill">Selamat Datang Kembali</span>
                <h1 class="form-heading">Masuk ke Akun</h1>
                <p class="form-sub">Silakan isi email atau no. HP dan kata sandimu di bawah ini</p>

                <form method="POST">
                    <div class="soft-input-group">
                        <i class="fa fa-envelope input-icon"></i>
                        <input type="text" name="identitas" class="soft-input" placeholder="Alamat Email / No. HP" required>
                    </div>
                    <div class="soft-input-group mb-4">
                        <i class="fa fa-lock input-icon"></i>
                        <input type="password" name="password" class="soft-input" placeholder="Kata Sandi" required>
                    </div>
                    <button type="submit" name="login_pelanggan" class="btn-rose-submit">
                        Masuk Sekarang <i class="fa fa-arrow-right ms-1"></i>
                    </button>
                </form>

                <div class="text-center mt-4 small text-muted">
                    Belum punya akun? <a class="switch-link" onclick="toggleAuthMode('daftar')">Daftar di Sini</a>
                </div>
            </div>

            <div id="formDaftarBox" style="display: none;">
                <span class="welcome-pill">Buat Akun Baru</span>
                <h2 class="form-heading">Daftar Akun</h2>
                <p class="form-sub">Lengkapi data dirimu untuk mulai memesan buket</p>

                <form method="POST">
                    <div class="soft-input-group">
                        <i class="fa fa-user input-icon"></i>
                        <input type="text" name="nama_baru" class="soft-input" placeholder="Nama Lengkap" required>
                    </div>
                    <div class="soft-input-group">
                        <i class="fa fa-phone input-icon"></i>
                        <input type="text" name="telp_baru" class="soft-input" placeholder="No. WhatsApp / Telepon" required>
                    </div>
                    <div class="soft-input-group">
                        <i class="fa fa-envelope input-icon"></i>
                        <input type="email" name="email_baru" class="soft-input" placeholder="Alamat Email (Opsional)">
                    </div>
                    <div class="soft-input-group">
                        <i class="fa fa-location-dot input-icon"></i>
                        <input type="text" name="alamat_baru" class="soft-input" placeholder="Alamat Pengiriman (Opsional)">
                    </div>
                    <div class="soft-input-group mb-3">
                        <i class="fa fa-lock input-icon"></i>
                        <input type="password" name="pass_baru" class="soft-input" placeholder="Kata Sandi" required>
                    </div>
                    <button type="submit" name="daftar_pelanggan" class="btn-rose-submit">
                        Daftar &amp; Masuk <i class="fa fa-arrow-right ms-1"></i>
                    </button>
                </form>

                <div class="text-center mt-3 small text-muted">
                    Sudah punya akun? <a class="switch-link" onclick="toggleAuthMode('masuk')">Masuk di Sini</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleAuthMode(mode) {
            const masukBox = document.getElementById('formMasukBox');
            const daftarBox = document.getElementById('formDaftarBox');
            if (mode === 'daftar') {
                masukBox.style.display = 'none';
                daftarBox.style.display = 'block';
            } else {
                daftarBox.style.display = 'none';
                masukBox.style.display = 'block';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>