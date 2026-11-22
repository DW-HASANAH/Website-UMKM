<?php
include '../koneksi.php';

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

$nama_toko = 'Kembang Cerita Flowers';
$foto_logo = 'logo.jpg';

try {
    $q_toko = mysqli_query($conn, "SELECT nama_toko, foto_logo FROM profil_toko WHERE id_profil = 1 LIMIT 1");
    if ($q_toko && mysqli_num_rows($q_toko) > 0) {
        $r_toko = mysqli_fetch_assoc($q_toko);
        if (!empty($r_toko['nama_toko'])) $nama_toko = $r_toko['nama_toko'];
        if (!empty($r_toko['foto_logo'])) $foto_logo = $r_toko['foto_logo'];
    }
} catch (Throwable $e) {
    // Gunakan nilai default jika tabel belum tersedia
}

$pesan = '';
$error = '';

function simpanBanyakFotoProduk($conn, $id_produk, $fileInputName, $uploadDir) {
    $tersimpan = [];
    if (empty($_FILES[$fileInputName]['name']) || !is_array($_FILES[$fileInputName]['name'])) {
        return $tersimpan;
    }

    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
    $total_file = count($_FILES[$fileInputName]['name']);

    for ($i = 0; $i < $total_file; $i++) {
        $err = $_FILES[$fileInputName]['error'][$i] ?? UPLOAD_ERR_NO_FILE;
        $tmp = $_FILES[$fileInputName]['tmp_name'][$i] ?? '';
        $orig = $_FILES[$fileInputName]['name'][$i] ?? '';

        if ($err !== UPLOAD_ERR_OK || $tmp === '' || $orig === '') {
            continue;
        }

        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext, true)) {
            continue;
        }

        $nama_file = 'produk_' . time() . '_' . $i . '_' . rand(100, 999) . '.' . $ext;
        if (@move_uploaded_file($tmp, $uploadDir . $nama_file)) {
            $tersimpan[] = $nama_file;
            if ((int)$id_produk > 0) {
                try {
                    $nf_esc = mysqli_real_escape_string($conn, $nama_file);
                    $id_p_int = (int)$id_produk;
                    $urutan_now = $i + 1;
                    mysqli_query($conn, "INSERT INTO produk_foto (id_produk, nama_file, urutan)
                        VALUES ('$id_p_int', '$nf_esc', '$urutan_now')");
                } catch (Throwable $e) {
                    // Abaikan jika tabel produk_foto belum tersedia
                }
            }
        }
    }

    return $tersimpan;
}

function ambilGaleriProdukAdmin($conn, $id_produk, $foto_utama) {
    $galeri = [];
    if (!empty($foto_utama)) {
        $galeri[] = $foto_utama;
    }
    try {
        $id_p_int = (int)$id_produk;
        $q = mysqli_query($conn, "SELECT nama_file FROM produk_foto WHERE id_produk = '$id_p_int' ORDER BY urutan ASC, id_foto ASC");
        if ($q) {
            while ($r = mysqli_fetch_assoc($q)) {
                if (!empty($r['nama_file']) && !in_array($r['nama_file'], $galeri, true)) {
                    $galeri[] = $r['nama_file'];
                }
            }
        }
    } catch (Throwable $e) {
        // Abaikan jika tabel produk_foto belum tersedia
    }
    if (empty($galeri)) {
        $galeri[] = 'default.jpg';
    }
    return $galeri;
}

// Tambah produk baru (mendukung upload lebih dari 1 foto)
if (isset($_POST['tambah_produk'])) {
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $harga = (int)($_POST['harga'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status = trim($_POST['status'] ?? 'Aktif');
    $uploadDir = dirname(__DIR__) . '/uploads/';

    if ($nama_produk === '' || $harga <= 0) {
        $error = 'Nama produk dan harga wajib diisi dengan benar.';
    } else {
        $uploaded_files = simpanBanyakFotoProduk($conn, 0, 'foto', $uploadDir);
        $foto_utama = !empty($uploaded_files) ? $uploaded_files[0] : 'default.jpg';

        $np = mysqli_real_escape_string($conn, $nama_produk);
        $ds = mysqli_real_escape_string($conn, $deskripsi);
        $st = mysqli_real_escape_string($conn, $status);
        $ft = mysqli_real_escape_string($conn, $foto_utama);

        $ok = mysqli_query($conn, "INSERT INTO produk (nama_produk, harga, stok, deskripsi, foto, status)
            VALUES ('$np', '$harga', '$stok', '$ds', '$ft', '$st')");
        if ($ok) {
            $id_baru = (int)mysqli_insert_id($conn);
            if (!empty($uploaded_files) && $id_baru > 0) {
                foreach ($uploaded_files as $idx => $f_name) {
                    try {
                        $f_esc = mysqli_real_escape_string($conn, $f_name);
                        $urutan = $idx + 1;
                        mysqli_query($conn, "INSERT INTO produk_foto (id_produk, nama_file, urutan)
                            VALUES ('$id_baru', '$f_esc', '$urutan')");
                    } catch (Throwable $e) {
                        // Abaikan jika tabel produk_foto belum tersedia
                    }
                }
            }
            $pesan = 'Produk baru berhasil ditambahkan (' . count($uploaded_files) . ' foto diunggah).';
        } else {
            $error = 'Gagal menambah produk: ' . mysqli_error($conn);
        }
    }
}

// Edit produk (termasuk tambah/ganti foto)
if (isset($_POST['edit_produk'])) {
    $id_edit = (int)($_POST['id_produk'] ?? 0);
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $harga = (int)($_POST['harga'] ?? 0);
    $stok = (int)($_POST['stok'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status = trim($_POST['status'] ?? 'Aktif');
    $ganti_foto_utama = isset($_POST['ganti_foto_utama']);
    $uploadDir = dirname(__DIR__) . '/uploads/';

    if ($id_edit <= 0 || $nama_produk === '' || $harga <= 0) {
        $error = 'Data edit produk tidak lengkap.';
    } else {
        $q_lama = mysqli_query($conn, "SELECT foto FROM produk WHERE id_produk = '$id_edit' LIMIT 1");
        $foto_lama = 'default.jpg';
        if ($q_lama && $r_lama = mysqli_fetch_assoc($q_lama)) {
            $foto_lama = $r_lama['foto'] ?: 'default.jpg';
        }

        $foto_baru_list = simpanBanyakFotoProduk($conn, $id_edit, 'foto_edit', $uploadDir);
        $foto_fix = $foto_lama;
        if (!empty($foto_baru_list) && ($ganti_foto_utama || $foto_lama === 'default.jpg' || $foto_lama === '')) {
            $foto_fix = $foto_baru_list[0];
        }

        $np = mysqli_real_escape_string($conn, $nama_produk);
        $ds = mysqli_real_escape_string($conn, $deskripsi);
        $st = mysqli_real_escape_string($conn, $status);
        $ft = mysqli_real_escape_string($conn, $foto_fix);

        $ok_upd = mysqli_query($conn, "UPDATE produk SET
            nama_produk = '$np',
            harga = '$harga',
            stok = '$stok',
            deskripsi = '$ds',
            foto = '$ft',
            status = '$st'
            WHERE id_produk = '$id_edit'");

        if ($ok_upd) {
            $pesan = 'Data produk berhasil diperbarui.';
        } else {
            $error = 'Gagal memperbarui produk: ' . mysqli_error($conn);
        }
    }
}

// Hapus produk
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    if ($id_hapus > 0) {
        try {
            mysqli_query($conn, "DELETE FROM produk_foto WHERE id_produk = '$id_hapus'");
        } catch (Throwable $e) {
            // Abaikan jika tabel belum tersedia
        }
        mysqli_query($conn, "DELETE FROM produk WHERE id_produk = '$id_hapus'");
        $pesan = 'Produk berhasil dihapus.';
    }
}

$daftar_produk = [];
try {
    $q_prod = mysqli_query($conn, "SELECT * FROM produk ORDER BY id_produk DESC");
    if ($q_prod) {
        while ($row = mysqli_fetch_assoc($q_prod)) {
            $row['galeri'] = ambilGaleriProdukAdmin($conn, (int)($row['id_produk'] ?? 0), $row['foto'] ?? '');
            $daftar_produk[] = $row;
        }
    }
} catch (Throwable $e) {
    $daftar_produk = [];
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - <?php echo htmlspecialchars($nama_toko); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,500&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --burgundy-main: #632b3e;
            --rose-pink: #d9778c;
            --soft-cream: #fffafb;
            --blush-bg: #fff5f7;
        }
        body {
            background-color: var(--blush-bg);
            font-family: 'Poppins', sans-serif;
            color: #4a2e35;
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
        .btn-rose {
            background-color: var(--rose-pink);
            color: #ffffff;
            border: none;
            border-radius: 50px;
        }
        .btn-rose:hover {
            background-color: #c2657a;
            color: #ffffff;
        }
        .thumb-preview-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        .thumb-preview-item {
            width: 58px;
            height: 58px;
            border-radius: 10px;
            object-fit: cover;
            border: 1.5px solid #f0d5dc;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>

            <div class="col-md-10 p-5">
                <div class="mb-4">
                    <h3 class="fw-bold form-title mb-1">Kelola Produk</h3>
                    <p class="text-muted small mb-0">Tambah, edit, unggah banyak foto, dan atur katalog buket Kembang Cerita Flowers</p>
                </div>

                <?php if ($pesan !== ''): ?>
                    <div class="alert alert-success rounded-4"><?php echo htmlspecialchars($pesan); ?></div>
                <?php endif; ?>
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger rounded-4"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Form Tambah Produk (Multi Foto) -->
                    <div class="col-lg-4">
                        <div class="card card-custom p-4">
                            <h5 class="fw-bold form-title mb-3">Tambah Buket Baru</h5>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Nama Produk</label>
                                    <input type="text" name="nama_produk" class="form-control rounded-pill px-3" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Harga (Rp)</label>
                                    <input type="number" name="harga" class="form-control rounded-pill px-3" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Stok</label>
                                    <input type="number" name="stok" class="form-control rounded-pill px-3" min="0" value="10" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Status</label>
                                    <select name="status" class="form-select rounded-pill px-3">
                                        <option value="Aktif">Aktif</option>
                                        <option value="Nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Foto Produk (Bisa Pilih Lebih dari 1)</label>
                                    <input type="file" name="foto[]" id="inputFotoTambah" class="form-control rounded-pill px-3" accept="image/*" multiple>
                                    <div class="thumb-preview-row" id="previewTambah"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Deskripsi</label>
                                    <textarea name="deskripsi" rows="3" class="form-control rounded-4 px-3"></textarea>
                                </div>
                                <button type="submit" name="tambah_produk" class="btn btn-rose w-100 py-2 fw-semibold">
                                    <i class="fa fa-plus me-1"></i> Simpan Produk
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Tabel Daftar Produk -->
                    <div class="col-lg-8">
                        <div class="card card-custom p-4">
                            <h5 class="fw-bold form-title mb-3">Daftar Katalog Buket</h5>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Harga</th>
                                            <th>Stok</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($daftar_produk)): ?>
                                            <?php foreach ($daftar_produk as $p): ?>
                                            <?php
                                                $id_p = (int)($p['id_produk'] ?? 0);
                                                $galeri_p = $p['galeri'] ?? ['default.jpg'];
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <img src="../uploads/<?php echo htmlspecialchars($galeri_p[0]); ?>" width="48" height="48" class="rounded-3 object-fit-cover" alt="">
                                                        <div>
                                                            <div class="fw-semibold small"><?php echo htmlspecialchars($p['nama_produk']); ?></div>
                                                            <small class="text-muted"><?php echo count($galeri_p); ?> foto</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="small fw-semibold" style="color: var(--rose-pink);">Rp <?php echo number_format((int)($p['harga'] ?? 0), 0, ',', '.'); ?></td>
                                                <td class="small"><?php echo (int)($p['stok'] ?? 0); ?></td>
                                                <td><span class="badge rounded-pill bg-secondary"><?php echo htmlspecialchars($p['status'] ?? 'Aktif'); ?></span></td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-primary rounded-pill px-2.5"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalEditProduk<?php echo $id_p; ?>"
                                                                title="Edit Produk">
                                                            <i class="fa fa-pen"></i>
                                                        </button>
                                                        <a href="produk.php?hapus=<?php echo $id_p; ?>"
                                                           class="btn btn-sm btn-outline-danger rounded-pill px-2.5"
                                                           onclick="return confirm('Hapus produk ini?')"
                                                           title="Hapus Produk">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">Belum ada data produk.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <?php foreach ($daftar_produk as $p): ?>
    <?php
        $id_p = (int)($p['id_produk'] ?? 0);
        $galeri_p = $p['galeri'] ?? ['default.jpg'];
        $st_p = $p['status'] ?? 'Aktif';
    ?>
    <div class="modal fade" id="modalEditProduk<?php echo $id_p; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold form-title">Edit Produk: <?php echo htmlspecialchars($p['nama_produk']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body px-4">
                        <input type="hidden" name="id_produk" value="<?php echo $id_p; ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Nama Produk</label>
                                <input type="text" name="nama_produk" class="form-control rounded-pill px-3" value="<?php echo htmlspecialchars($p['nama_produk']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Harga (Rp)</label>
                                <input type="number" name="harga" class="form-control rounded-pill px-3" min="0" value="<?php echo (int)($p['harga'] ?? 0); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Stok</label>
                                <input type="number" name="stok" class="form-control rounded-pill px-3" min="0" value="<?php echo (int)($p['stok'] ?? 0); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="status" class="form-select rounded-pill px-3">
                                    <option value="Aktif" <?php echo $st_p === 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Nonaktif" <?php echo $st_p === 'Nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Foto Saat Ini</label>
                                <div class="thumb-preview-row">
                                    <?php foreach ($galeri_p as $fg): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($fg); ?>" class="thumb-preview-item" alt="">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Tambah / Pilih Foto Baru (Bisa Lebih dari 1)</label>
                                <input type="file" name="foto_edit[]" class="form-control rounded-pill px-3" accept="image/*" multiple>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="ganti_foto_utama" value="1" id="gantiUtama<?php echo $id_p; ?>">
                                    <label class="form-check-label small" for="gantiUtama<?php echo $id_p; ?>">
                                        Jadikan foto pertama yang baru diunggah sebagai foto utama katalog
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Deskripsi</label>
                                <textarea name="deskripsi" rows="3" class="form-control rounded-4 px-3"><?php echo htmlspecialchars($p['deskripsi'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_produk" class="btn btn-rose px-4 fw-semibold">
                            <i class="fa fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script>
        const inputFotoTambah = document.getElementById('inputFotoTambah');
        const previewTambah = document.getElementById('previewTambah');
        if (inputFotoTambah && previewTambah) {
            inputFotoTambah.addEventListener('change', function() {
                previewTambah.innerHTML = '';
                Array.from(this.files || []).forEach(function(file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'thumb-preview-item';
                        previewTambah.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>