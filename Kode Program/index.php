<?php
include 'koneksi.php';

// Ambil data profil toko dari database (id_profil = 1)
$profil = mysqli_query($conn, "SELECT * FROM profil_toko WHERE id_profil = 1 LIMIT 1");
$toko = mysqli_fetch_assoc($profil);

// PERBAIKAN: Ambil data produk aktif langsung dari tabel produk (tanpa JOIN kategori)
$produk = mysqli_query($conn, "SELECT * FROM produk WHERE status = 'Aktif' ORDER BY id_produk DESC");
$testimoni = mysqli_query($conn, "SELECT * FROM testimoni WHERE status = 'Disetujui'");
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $toko['nama_toko'] ?? 'Kembang Cerita Flowers'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --feminine-pink: #d9778c;     /* Pink mawar feminin yang elegan */
            --feminine-light: #fff0f3;    /* Background pastel lembut */
            --feminine-dark: #4a2e35;     /* Cokelat kemerahan hangat untuk teks */
            --feminine-accent: #f4acb7;   /* Pink muda aksen */
        }
        body { font-family: 'Poppins', sans-serif; background-color: #fff9fb; color: var(--feminine-dark); transition: background-color 0.3s, color 0.3s; }
        
        /* Top bar feminin */
        .top-bar { background: linear-gradient(135deg, #d9778c, #b55d72) !important; color: white; }

        .hero-section {
            background: linear-gradient(135deg, #ffe5ec 0%, #fef6f8 100%);
            border-radius: 25px;
            padding: 50px;
            margin: 25px 0;
            border: 1px solid #f8d7da;
            box-shadow: 0 10px 30px rgba(217, 119, 140, 0.08);
        }
        [data-bs-theme="dark"] .hero-section {
            background: linear-gradient(135deg, #2c1e22 0%, #1a1a1a 100%);
            border: 1px solid #5a3a42;
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
            transform: translateY(-2px);
        }
        
        .card-product { 
            border: none; 
            border-radius: 20px; 
            transition: transform 0.2s, box-shadow 0.2s; 
            box-shadow: 0 5px 20px rgba(217, 119, 140, 0.06); 
            background-color: white;
        }
        .card-product:hover { 
            transform: translateY(-6px); 
            box-shadow: 0 10px 25px rgba(217, 119, 140, 0.15); 
        }
        .card-img-top { border-top-left-radius: 20px; border-top-right-radius: 20px; height: 230px; object-fit: cover; }
        
        /* Floating WhatsApp */
        .float-wa {
            position: fixed; bottom: 25px; right: 25px; background-color: #25d366; color: white;
            border-radius: 50px; padding: 12px 22px; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3); z-index: 1000;
            text-decoration: none; font-weight: 500; transition: transform 0.2s;
        }
        .float-wa:hover { transform: scale(1.05); color: white; }

        .badge-kategori {
            background-color: #fbebee;
            color: var(--feminine-pink);
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 50px;
        }
    </style>
</head>
<body>

    <!-- Top Bar Diskon & Status Toko -->
    <div class="top-bar py-2 text-center small fw-medium">
        <span><i class="fa fa-tag me-1"></i> Diskon Spesial 10% Khusus Pesanan Pertama! &nbsp;|&nbsp; 
        Status Toko: <strong><?php echo $toko['status_toko'] ?? 'Buka'; ?></strong></span>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top shadow-sm bg-body py-3">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                <img src="uploads/<?php echo $toko['foto_logo'] ?: 'logo.jpg'; ?>" width="45" height="45" class="rounded-circle me-2 object-fit-cover shadow-sm" style="border: 2px solid var(--feminine-pink);">
                <span style="color: var(--feminine-pink); font-weight: 700;"><?php echo $toko['nama_toko']; ?></span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active fw-medium" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#katalog">Katalog Buket</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#ulasan">Ulasan</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium" href="#kontak">Kontak</a></li>
                    
                    <!-- Mode Gelap / Terang Toggle -->
                    <li class="nav-item mx-2">
                        <button class="btn btn-sm btn-outline-light text-dark rounded-circle shadow-sm" id="modeToggle" onclick="toggleMode()" style="width: 35px; height: 35px;">
                            <i class="fa fa-moon" id="modeIcon"></i>
                        </button>
                    </li>

                    <li class="nav-item ms-2">
                        <?php if(isset($_SESSION['id_pelanggan'])): ?>
                            <a href="keranjang.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-medium">
                                <i class="fa fa-shopping-bag me-1"></i> Keranjang
                            </a>
                            <a href="logout.php" class="btn btn-dark btn-sm rounded-pill ms-2 px-3">Logout</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-feminine btn-sm px-4 fw-medium shadow-sm">Login / Daftar</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container">
        <div class="hero-section text-center text-md-start d-md-flex align-items-center justify-content-between">
            <div class="mb-4 mb-md-0">
                <span class="badge mb-3 px-3 py-2 rounded-pill" style="background-color: #fde2e4; color: var(--feminine-pink); font-weight: 500;">
                    <i class="fa fa-heart me-1"></i> Handcrafted with Love
                </span>
                <h1 class="fw-bold display-5" style="color: var(--feminine-dark);">Rangkaian Kisah, <br><span style="color: var(--feminine-pink);">Penuh Makna.</span></h1>
                <p class="text-muted mt-2 mb-4" style="max-width: 500px;"><?php echo $toko['deskripsi']; ?></p>
                <a href="#katalog" class="btn btn-feminine btn-lg px-5 shadow-sm fw-medium">Belanja Sekarang</a>
            </div>
            <div class="text-center">
                <img src="uploads/<?php echo $toko['foto_logo'] ?: 'logo.jpg'; ?>" alt="Buket Bunga" class="img-fluid rounded-4 shadow-lg" style="max-height: 300px; border: 4px solid white;">
            </div>
        </div>
    </div>

    <!-- Katalog Produk -->
    <div class="container my-5" id="katalog">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="color: var(--feminine-dark);">Katalog Buket Pilihan</h2>
            <p class="text-muted">Pilih buket bunga tercantik untuk orang tersayang</p>
        </div>

        <div class="row g-4">
            <?php if(mysqli_num_rows($produk) > 0): ?>
                <?php while($p = mysqli_fetch_assoc($produk)): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card card-product h-100 bg-body">
                        <img src="uploads/<?php echo $p['foto'] ?: 'default.jpg'; ?>" class="card-img-top" alt="<?php echo $p['nama_produk']; ?>">
                        <div class="card-body d-flex flex-column p-4">
                            <span class="badge badge-kategori align-self-start mb-2">Buket Spesial</span>
                            <h5 class="card-title fs-6 fw-bold mb-2" style="color: var(--feminine-dark);"><?php echo $p['nama_produk']; ?></h5>
                            <p class="fw-bold mb-3 fs-5" style="color: var(--feminine-pink);">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></p>
                            
                            <a href="tambah_keranjang.php?id=<?php echo $p['id_produk']; ?>" class="btn btn-outline-dark btn-sm rounded-pill mt-auto py-2 fw-medium">
                             <i class="fa fa-shopping-bag me-1"></i> Tambah ke Keranjang
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted py-5">
                    <p>Belum ada produk di katalog. Silakan tambahkan melalui panel admin.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bagian Ulasan Pelanggan -->
    <div class="container my-5 py-4" id="ulasan">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="color: var(--feminine-dark);">Apa Kata Mereka?</h2>
            <p class="text-muted">Ulasan hangat dari pelanggan setia Kembang Cerita</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card p-4 shadow-sm border-0 h-100 bg-body rounded-4">
                    <div class="text-warning mb-2">
                        <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                    </div>
                    <p class="text-muted small italic">"Buketnya bener-bener estetik banget, bunganya segar dan pengantaran langsung oleh kurirnya ramah sekali!"</p>
                    <h6 class="fw-bold mb-0 mt-auto pt-3" style="color: var(--feminine-pink);">- Siska Amanda</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 shadow-sm border-0 h-100 bg-body rounded-4">
                    <div class="text-warning mb-2">
                        <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                    </div>
                    <p class="text-muted small italic">"Cocok banget buat kado wisuda sahabatku. Pasti bakal order lagi di sini langganan terus."</p>
                    <h6 class="fw-bold mb-0 mt-auto pt-3" style="color: var(--feminine-pink);">- Rina Melati</h6>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 shadow-sm border-0 h-100 bg-body rounded-4">
                    <div class="text-warning mb-2">
                        <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
                    </div>
                    <p class="text-muted small italic">"Pelayanan cepat, bisa request kartu ucapan juga sesuai keinginan. Recommended florist banget!"</p>
                    <h6 class="fw-bold mb-0 mt-auto pt-3" style="color: var(--feminine-pink);">- Dimas Prasetyo</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/<?php echo $toko['no_whatsapp']; ?>" target="_blank" class="float-wa">
        <i class="fa-brands fa-whatsapp fa-lg me-1"></i> Chat WhatsApp
    </a>

    <!-- Footer -->
    <footer id="kontak" class="text-light py-5 mt-5" style="background-color: var(--feminine-dark);">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3 text-white"><?php echo $toko['nama_toko']; ?></h5>
                    <p class="small text-white-50"><?php echo $toko['deskripsi']; ?></p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3 text-white">Kontak & Media Sosial</h6>
                    <p class="small mb-2"><i class="fa fa-phone me-2 text-danger"></i> <?php echo $toko['no_whatsapp']; ?></p>
                    <p class="small mb-2"><a href="https://www.instagram.com/kembangcerita_flowers?utm_source=ig_web_button_share_sheet&avpt=ZDNlZDc0MzIxNw==<?php echo $toko['instagram']; ?>" target="_blank" class="text-decoration-none text-light"><i class="fa-brands fa-instagram me-2 text-danger"></i> @<?php echo $toko['instagram']; ?></a></p>
                    <p class="small text-white-50"><i class="fa fa-map-marker-alt me-2"></i> <?php echo $toko['alamat']; ?></p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3 text-white">Sistem Toko</h6>
                    <p class="small text-white-50">Melayani pemesanan buket secara langsung maupun pengiriman kurir lokal terpercaya.</p>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="text-center small text-white-50">&copy; 2026 Kembang Cerita Flowers. All Rights Reserved.</div>
        </div>
    </footer>

    <!-- Script Dark/Light Mode -->
    <script>
        function toggleMode() {
            const html = document.documentElement;
            const icon = document.getElementById('modeIcon');
            if (html.getAttribute('data-bs-theme') === 'dark') {
                html.setAttribute('data-bs-theme', 'light');
                icon.className = 'fa fa-moon';
            } else {
                html.setAttribute('data-bs-theme', 'dark');
                icon.className = 'fa fa-sun';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>