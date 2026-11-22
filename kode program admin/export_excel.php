<?php
include '../koneksi.php';

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

$filename = "Laporan_Kembang_Cerita_" . date('Y-m-d') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");

$query = mysqli_query($conn, "SELECT t.id_transaksi, t.kode_referensi, t.tanggal_transaksi, t.tipe_order,
    t.metode_bayar, t.status_bayar, t.status_transaksi, t.total_pembelian,
    p.nama_pelanggan, p.telepon, p.alamat
    FROM transaksi t
    LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
    ORDER BY t.id_transaksi DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h3>Laporan Rekap Transaksi & Pelanggan - Kembang Cerita Flowers</h3>
    <p>Tanggal Cetak: <?php echo date('d-m-Y H:i'); ?></p>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr style="background-color:#632b3e; color:#ffffff;">
                <th>No</th>
                <th>Kode Referensi</th>
                <th>Tanggal</th>
                <th>Nama Pelanggan</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th>Tipe Order</th>
                <th>Metode Pembayaran</th>
                <th>Status Bayar</th>
                <th>Status Transaksi</th>
                <th>Total Pembelian (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $grand_total = 0;
            while ($row = mysqli_fetch_assoc($query)):
                $grand_total += (int)$row['total_pembelian'];
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['kode_referensi'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['tanggal_transaksi']); ?></td>
                <td><?php echo htmlspecialchars($row['nama_pelanggan'] ?? 'Pelanggan'); ?></td>
                <td><?php echo htmlspecialchars($row['telepon'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['alamat'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['tipe_order'] ?? 'Online'); ?></td>
                <td><?php echo htmlspecialchars($row['metode_bayar'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['status_bayar'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['status_transaksi']); ?></td>
                <td><?php echo (int)$row['total_pembelian']; ?></td>
            </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="10" align="right"><strong>Total Keseluruhan</strong></td>
                <td><strong><?php echo $grand_total; ?></strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>