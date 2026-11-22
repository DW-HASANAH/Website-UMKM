<?php
$sidebar_nama_toko = $nama_toko ?? 'Kembang Cerita Flowers';
$sidebar_foto_logo = $foto_logo ?? 'logo.jpg';

if (!isset($nama_toko) && isset($conn)) {
    try {
        $q_sb_toko = mysqli_query($conn, "SELECT nama_toko, foto_logo FROM profil_toko WHERE id_profil = 1 LIMIT 1");
        if ($q_sb_toko && mysqli_num_rows($q_sb_toko) > 0) {
            $r_sb_toko = mysqli_fetch_assoc($q_sb_toko);
            if (!empty($r_sb_toko['nama_toko'])) $sidebar_nama_toko = $r_sb_toko['nama_toko'];
            if (!empty($r_sb_toko['foto_logo'])) $sidebar_foto_logo = $r_sb_toko['foto_logo'];
        }
    } catch (Throwable $e) {
        // Gunakan nilai default jika tabel belum tersedia
    }
}

if (!function_exists('resolveSidebarTokoLogo')) {
    function resolveSidebarTokoLogo($fotoLogoDb) {
        $baseDir = dirname(__DIR__) . '/uploads/';
        $bersih = basename((string)$fotoLogoDb);
        if ($bersih === '') {
            $bersih = 'logo.jpg';
        }
        if (file_exists($baseDir . $bersih)) {
            return '../uploads/' . $bersih;
        }

        $tanpaExt = preg_replace('/\.(jpe?g|png|webp)$/i', '', $bersih);
        foreach (['.jpeg', '.jpg', '.png', '.webp'] as $ext) {
            if (file_exists($baseDir . $tanpaExt . $ext)) {
                return '../uploads/' . $tanpaExt . $ext;
            }
        }

        foreach (['logo.jpeg', 'logo.jpg', 'logo.png', 'logo.webp'] as $cadangan) {
            if (file_exists($baseDir . $cadangan)) {
                return '../uploads/' . $cadangan;
            }
        }

        return '../uploads/' . $bersih;
    }
}

$sidebar_logo_url = resolveSidebarTokoLogo($sidebar_foto_logo);
$halaman_aktif = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');
?>
<style>
    :root {
        --burgundy-main: #632b3e;
        --burgundy-dark: #451c2a;
        --rose-pink: #d9778c;
        --blush-bg: #fff5f7;
    }
    .sidebar-custom {
        background: linear-gradient(160deg, var(--burgundy-main) 0%, var(--burgundy-dark) 100%);
        min-height: 100vh;
        color: #ffffff;
        box-shadow: 4px 0 20px rgba(99, 43, 62, 0.12);
        overflow-x: hidden;
    }
    .sidebar-profile {
        text-align: center;
        padding-bottom: 18px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.14);
    }
    .sidebar-avatar-ring {
        width: 84px;
        height: 84px;
        margin: 0 auto 12px;
        border-radius: 50%;
        padding: 4px;
        border: 2px solid rgba(255, 255, 255, 0.45);
        box-shadow: 0 0 0 4px rgba(217, 119, 140, 0.25);
    }
    .sidebar-avatar-ring img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }
    .sidebar-menu a {
        color: rgba(255, 255, 255, 0.88);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 18px;
        margin-bottom: 6px;
        border-radius: 50px;
        font-size: 13.5px;
        font-weight: 500;
        transition: all 0.25s ease;
    }
    .sidebar-menu a:hover {
        background: rgba(217, 119, 140, 0.24);
        color: #ffffff;
        transform: translateX(3px);
    }
    .sidebar-menu a.active {
        background-color: var(--blush-bg);
        color: var(--burgundy-main);
        font-weight: 600;
        margin-right: -24px;
        padding-right: 28px;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-top-left-radius: 50px;
        border-bottom-left-radius: 50px;
        box-shadow: -4px 6px 16px rgba(0, 0, 0, 0.14);
    }
    .sidebar-menu a.active i {
        color: var(--rose-pink);
    }
    .sidebar-footer a {
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-radius: 50px;
        font-size: 13px;
        margin-bottom: 6px;
        transition: all 0.2s ease;
    }
    .sidebar-footer a:hover {
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
    }
    [data-bs-theme="dark"] .sidebar-menu a.active {
        background-color: #161114;
        color: #ff9eb5;
    }
</style>

<div class="col-md-2 sidebar-custom p-4 d-flex flex-column justify-content-between">
    <div>
        <div class="sidebar-profile mb-4">
            <div class="sidebar-avatar-ring">
                <img src="<?php echo htmlspecialchars($sidebar_logo_url); ?>" alt="Logo <?php echo htmlspecialchars($sidebar_nama_toko); ?>">
            </div>
            <div class="fw-semibold" style="font-size: 15px;">
                <?php echo htmlspecialchars($_SESSION['nama_admin'] ?? 'Admin Kembang Cerita'); ?>
            </div>
            <small class="text-white-50 d-block" style="font-size: 11.5px;"><?php echo htmlspecialchars($sidebar_nama_toko); ?></small>
        </div>

        <div class="sidebar-menu">
            <a href="dashboard.php" class="<?php echo $halaman_aktif === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fa fa-chart-pie"></i> <span>Dashboard</span>
            </a>
            <a href="produk.php" class="<?php echo $halaman_aktif === 'produk.php' ? 'active' : ''; ?>">
                <i class="fa fa-box"></i> <span>Kelola Produk</span>
            </a>
            <a href="transaksi.php" class="<?php echo $halaman_aktif === 'transaksi.php' ? 'active' : ''; ?>">
                <i class="fa fa-shopping-bag"></i> <span>Transaksi</span>
            </a>
            <a href="pelanggan.php" class="<?php echo $halaman_aktif === 'pelanggan.php' ? 'active' : ''; ?>">
                <i class="fa fa-users"></i> <span>Data Pelanggan</span>
            </a>
            <a href="feedback.php" class="<?php echo $halaman_aktif === 'feedback.php' ? 'active' : ''; ?>">
                <i class="fa fa-comments"></i> <span>Ulasan / Feedback</span>
            </a>
            <a href="setting.php" class="<?php echo $halaman_aktif === 'setting.php' ? 'active' : ''; ?>">
                <i class="fa fa-cog"></i> <span>Pengaturan Toko</span>
            </a>
        </div>
    </div>

    <div class="sidebar-footer pt-3">
        <a href="../index.php" target="_blank">
            <i class="fa fa-globe"></i> <span>Lihat Toko</span>
        </a>
        <a href="logout.php" style="background: rgba(255, 255, 255, 0.1);">
            <i class="fa fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </div>
</div>