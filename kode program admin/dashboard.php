<?php
include '../koneksi.php';

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
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

function resolveAdminTokoLogo($fotoLogoDb) {
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

$logo_admin_url = resolveAdminTokoLogo($foto_logo);

$total_produk = 0;
$total_transaksi = 0;
$total_pelanggan = 0;
$total_pendapatan = 0;
$transaksi_terbaru = [];

// Siapkan data grafik 7 hari terakhir (fallback otomatis bila belum ada data)
$chart_labels = [];
$chart_omzet = [];
$chart_order = [];
$omzet_per_tanggal = [];
$order_per_tanggal = [];

for ($i = 6; $i >= 0; $i--) {
    $tgl_key = date('Y-m-d', strtotime("-$i days"));
    $tgl_label = date('d M', strtotime("-$i days"));
    $chart_labels[] = $tgl_label;
    $omzet_per_tanggal[$tgl_key] = 0;
    $order_per_tanggal[$tgl_key] = 0;
}

try {
    $q_p = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM produk");
    if ($q_p && $r = mysqli_fetch_assoc($q_p)) $total_produk = (int)($r['jml'] ?? 0);

    $q_t = mysqli_query($conn, "SELECT COUNT(*) AS jml, COALESCE(SUM(total_pembelian), 0) AS omzet FROM transaksi");
    if ($q_t && $r = mysqli_fetch_assoc($q_t)) {
        $total_transaksi = (int)($r['jml'] ?? 0);
        $total_pendapatan = (int)($r['omzet'] ?? 0);
    }

    $q_pl = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM pelanggan");
    if ($q_pl && $r = mysqli_fetch_assoc($q_pl)) $total_pelanggan = (int)($r['jml'] ?? 0);

    $q_recent = mysqli_query($conn, "SELECT t.*, p.*
        FROM transaksi t
        LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
        ORDER BY t.id_transaksi DESC LIMIT 5");
    if ($q_recent) {
        while ($row = mysqli_fetch_assoc($q_recent)) {
            $transaksi_terbaru[] = $row;
        }
    }

    $q_grafik = mysqli_query($conn, "SELECT DATE(tanggal_transaksi) AS tgl,
        COUNT(*) AS jml_order,
        COALESCE(SUM(total_pembelian), 0) AS total_omzet
        FROM transaksi
        WHERE tanggal_transaksi IS NOT NULL
        GROUP BY DATE(tanggal_transaksi)
        ORDER BY DATE(tanggal_transaksi) ASC");
    if ($q_grafik) {
        while ($g = mysqli_fetch_assoc($q_grafik)) {
            $tgl_row = (string)($g['tgl'] ?? '');
            if (array_key_exists($tgl_row, $omzet_per_tanggal)) {
                $omzet_per_tanggal[$tgl_row] = (int)($g['total_omzet'] ?? 0);
                $order_per_tanggal[$tgl_row] = (int)($g['jml_order'] ?? 0);
            }
        }
    }
} catch (Throwable $e) {
    // Abaikan jika query belum sesuai struktur tabel
}

foreach ($omzet_per_tanggal as $val_omzet) {
    $chart_omzet[] = $val_omzet;
}
foreach ($order_per_tanggal as $val_ord) {
    $chart_order[] = $val_ord;
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?php echo htmlspecialchars($nama_toko); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,500&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --burgundy-main: #632b3e;
            --burgundy-dark: #451c2a;
            --rose-pink: #d9778c;
            --soft-cream: #fffafb;
            --blush-bg: #fff5f7;
        }
        body {
            background-color: var(--blush-bg);
            font-family: 'Poppins', sans-serif;
            color: #4a2e35;
            transition: background-color 0.25s ease, color 0.25s ease;
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
        .card-custom {
            border: none;
            border-radius: 24px;
            background: var(--soft-cream);
            box-shadow: 0 10px 30px rgba(99, 43, 62, 0.06);
        }
        .form-title {
            font-family: 'Playfair Display', serif;
            color: var(--burgundy-main);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: #fbebee;
            color: var(--rose-pink);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .chart-box {
            position: relative;
            height: 290px;
            width: 100%;
        }

        /* Mode Malam / Dark Theme */
        [data-bs-theme="dark"] body {
            background-color: #161114;
            color: #f8e9ee;
        }
        [data-bs-theme="dark"] .card-custom {
            background-color: #22181d;
            color: #f8e9ee;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }
        [data-bs-theme="dark"] .form-title {
            color: #ff9eb5;
        }
        [data-bs-theme="dark"] .text-muted {
            color: #d8b8c2 !important;
        }
        [data-bs-theme="dark"] .stat-icon {
            background: #3d222b;
            color: #ff9eb5;
        }
        [data-bs-theme="dark"] .sidebar-menu a.active {
            background-color: #161114;
            color: #ff9eb5;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar-custom p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="sidebar-profile mb-4">
                        <div class="sidebar-avatar-ring">
                            <img src="<?php echo htmlspecialchars($logo_admin_url); ?>" alt="Logo <?php echo htmlspecialchars($nama_toko); ?>">
                        </div>
                        <div class="fw-semibold" style="font-size: 15px;">
                            <?php echo htmlspecialchars($_SESSION['nama_admin'] ?? 'Admin Kembang Cerita'); ?>
                        </div>
                        <small class="text-white-50 d-block" style="font-size: 11.5px;"><?php echo htmlspecialchars($nama_toko); ?></small>
                    </div>

                    <div class="sidebar-menu">
                        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="fa fa-chart-pie"></i> <span>Dashboard</span>
                        </a>
                        <a href="produk.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'produk.php' ? 'active' : ''; ?>">
                            <i class="fa fa-box"></i> <span>Kelola Produk</span>
                        </a>
                        <a href="transaksi.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'transaksi.php' ? 'active' : ''; ?>">
                            <i class="fa fa-shopping-bag"></i> <span>Transaksi</span>
                        </a>
                        <a href="pelanggan.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'pelanggan.php' ? 'active' : ''; ?>">
                            <i class="fa fa-users"></i> <span>Data Pelanggan</span>
                        </a>
                        <a href="feedback.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'feedback.php' ? 'active' : ''; ?>">
                            <i class="fa fa-comments"></i> <span>Ulasan / Feedback</span>
                        </a>
                        <a href="setting.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'setting.php' ? 'active' : ''; ?>">
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

            <!-- Konten Utama -->
            <div class="col-md-10 p-5">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h3 class="fw-bold form-title mb-1">Dashboard Admin</h3>
                        <p class="text-muted small mb-0">Ringkasan performa toko, grafik penjualan, dan transaksi terbaru</p>
                    </div>
                    <button type="button" class="btn btn-outline-danger rounded-pill px-3 py-2 fw-medium" id="themeToggleBtn" onclick="toggleAdminTheme()">
                        <i class="fa fa-moon me-1" id="themeToggleIcon"></i> <span id="themeToggleText">Mode Malam</span>
                    </button>
                </div>

                <!-- Kartu Statistik -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-custom p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Total Produk</span>
                                <span class="stat-icon"><i class="fa fa-box"></i></span>
                            </div>
                            <h4 class="fw-bold mb-0"><?php echo $total_produk; ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-custom p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Total Transaksi</span>
                                <span class="stat-icon"><i class="fa fa-shopping-bag"></i></span>
                            </div>
                            <h4 class="fw-bold mb-0"><?php echo $total_transaksi; ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-custom p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Total Pelanggan</span>
                                <span class="stat-icon"><i class="fa fa-users"></i></span>
                            </div>
                            <h4 class="fw-bold mb-0"><?php echo $total_pelanggan; ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card card-custom p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Total Pendapatan</span>
                                <span class="stat-icon"><i class="fa fa-wallet"></i></span>
                            </div>
                            <h5 class="fw-bold mb-0" style="color: var(--rose-pink);">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h5>
                        </div>
                    </div>
                </div>

                <!-- Grafik Pendapatan & Jumlah Pesanan (7 Hari Terakhir) -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card card-custom p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="fw-bold form-title mb-0">Grafik Pendapatan (7 Hari Terakhir)</h5>
                                    <small class="text-muted">Pergerakan omzet harian dalam Rupiah</small>
                                </div>
                            </div>
                            <div class="chart-box">
                                <canvas id="omzetChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card card-custom p-4 h-100">
                            <div class="mb-3">
                                <h5 class="fw-bold form-title mb-0">Jumlah Pesanan</h5>
                                <small class="text-muted">Volume transaksi per hari</small>
                            </div>
                            <div class="chart-box">
                                <canvas id="orderChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaksi Terbaru -->
                <div class="card card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold form-title mb-0">Transaksi Terbaru</h5>
                        <a href="transaksi.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Kode / Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Tipe</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transaksi_terbaru)): ?>
                                    <?php foreach ($transaksi_terbaru as $trx): ?>
                                    <?php
                                        $nama_plg = $trx['nama_pelanggan'] ?? $trx['nama_lengkap'] ?? $trx['nama'] ?? 'Pelanggan';
                                        $tipe_ord = $trx['tipe_order'] ?? 'Online';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold small"><?php echo htmlspecialchars($trx['kode_referensi'] ?? ('TRX-' . ($trx['id_transaksi'] ?? ''))); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($trx['tanggal_transaksi'] ?? '-'); ?></small>
                                        </td>
                                        <td class="small fw-medium"><?php echo htmlspecialchars($nama_plg); ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?php echo ($tipe_ord === 'Offline') ? 'bg-dark' : 'bg-primary'; ?>">
                                                <?php echo htmlspecialchars($tipe_ord); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold small" style="color: var(--rose-pink);">
                                            Rp <?php echo number_format((int)($trx['total_pembelian'] ?? 0), 0, ',', '.'); ?>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill bg-secondary"><?php echo htmlspecialchars($trx['status_transaksi'] ?? 'Diproses'); ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Belum ada transaksi terbaru.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const chartLabels = <?php echo json_encode($chart_labels); ?>;
        const chartOmzet = <?php echo json_encode($chart_omzet); ?>;
        const chartOrder = <?php echo json_encode($chart_order); ?>;

        let omzetChartInstance = null;
        let orderChartInstance = null;

        function getChartThemeColors(isDark) {
            return {
                textColor: isDark ? '#f8e9ee' : '#4a2e35',
                gridColor: isDark ? 'rgba(255, 229, 236, 0.12)' : 'rgba(99, 43, 62, 0.08)',
                lineColor: isDark ? '#ff8fa3' : '#d9778c',
                fillColor: isDark ? 'rgba(255, 143, 163, 0.22)' : 'rgba(217, 119, 140, 0.20)',
                barColor: isDark ? '#d9778c' : '#632b3e'
            };
        }

        function renderCharts() {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const palette = getChartThemeColors(isDark);

            if (omzetChartInstance) omzetChartInstance.destroy();
            if (orderChartInstance) orderChartInstance.destroy();

            const ctxOmzet = document.getElementById('omzetChart').getContext('2d');
            omzetChartInstance = new Chart(ctxOmzet, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: chartOmzet,
                        borderColor: palette.lineColor,
                        backgroundColor: palette.fillColor,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: palette.lineColor
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: palette.textColor } }
                    },
                    scales: {
                        x: { ticks: { color: palette.textColor }, grid: { color: palette.gridColor } },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: palette.textColor,
                                callback: function(value) {
                                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                                }
                            },
                            grid: { color: palette.gridColor }
                        }
                    }
                }
            });

            const ctxOrder = document.getElementById('orderChart').getContext('2d');
            orderChartInstance = new Chart(ctxOrder, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Pesanan',
                        data: chartOrder,
                        backgroundColor: palette.barColor,
                        borderRadius: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: palette.textColor } }
                    },
                    scales: {
                        x: { ticks: { color: palette.textColor }, grid: { display: false } },
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: palette.textColor },
                            grid: { color: palette.gridColor }
                        }
                    }
                }
            });
        }

        function updateToggleLabel() {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const icon = document.getElementById('themeToggleIcon');
            const text = document.getElementById('themeToggleText');
            if (isDark) {
                icon.className = 'fa fa-sun me-1';
                text.textContent = 'Mode Siang';
            } else {
                icon.className = 'fa fa-moon me-1';
                text.textContent = 'Mode Malam';
            }
        }

        function toggleAdminTheme() {
            const html = document.documentElement;
            const nextTheme = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', nextTheme);
            try {
                localStorage.setItem('kc_admin_theme', nextTheme);
            } catch (e) {}
            updateToggleLabel();
            renderCharts();
        }

        (function initAdminTheme() {
            try {
                const saved = localStorage.getItem('kc_admin_theme');
                if (saved === 'dark' || saved === 'light') {
                    document.documentElement.setAttribute('data-bs-theme', saved);
                }
            } catch (e) {}
            updateToggleLabel();
            renderCharts();
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>