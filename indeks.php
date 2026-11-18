<?php
include 'koneksi.php';

// PROFIL TOKO
$profil = $pdo->query("SELECT * FROM profil_toko LIMIT 1")
              ->fetch(PDO::FETCH_ASSOC);

// KATEGORI
$kategori = $pdo->query("SELECT * FROM kategori")
                ->fetchAll(PDO::FETCH_ASSOC);

// PRODUK
$produk = $pdo->query("
    SELECT produk.*, kategori.nama_kategori
    FROM produk
    LEFT JOIN kategori ON produk.id_kategori = kategori.id_kategori
    WHERE produk.status = 'Aktif'
    ORDER BY produk.id_produk DESC
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// PROMO
$promo = $pdo->query("
    SELECT * FROM promo
    WHERE status = 'Aktif'
    AND CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai
    ORDER BY id_promo DESC
    LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);

// TESTIMONI
$testimoni = $pdo->query("
    SELECT * FROM testimoni
    WHERE status = 'Disetujui'
    ORDER BY id_testimoni DESC
    LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);

$namaToko = $profil['nama_toko'] ?? 'Kembang Cerita';
$deskripsi = $profil['deskripsi'] ?? 'Rangkaian bunga indah untuk setiap momen berharga.';
$wa = preg_replace('/\D/', '', $profil['no_whatsapp'] ?? '');

$instagram = 'https://www.instagram.com/kembangcerita_flowers/';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= htmlspecialchars($namaToko) ?> | Flower Boutique</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
:root {
    --pink: #e94f91;
    --dark-pink: #bc3571;
    --light-pink: #fff0f6;
    --purple: #a78bca;
    --peach: #ffcfb3;
    --green: #91b99a;
    --dark: #543747;
}

* {
    scroll-behavior: smooth;
}

body {
    font-family: 'DM Sans', sans-serif;
    color: var(--dark);
    background: #fffafd;
}

h1, h2, h3, .brand {
    font-family: 'Playfair Display', serif;
}

.navbar {
    background: rgba(255,255,255,.96);
    padding: 16px 0;
    box-shadow: 0 3px 18px rgba(200,70,130,.08);
}

.brand {
    font-size: 26px;
    font-weight: bold;
    color: var(--pink);
}

.brand span {
    color: var(--purple);
}

.nav-link {
    color: #62495a;
    font-weight: 600;
    margin: 0 8px;
}

.nav-link:hover {
    color: var(--pink);
}

.icon-btn {
    width: 40px;
    height: 40px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border-radius: 50%;
    background: var(--light-pink);
    color: var(--pink);
    text-decoration: none;
    margin-left: 8px;
}

.hero {
    background: linear-gradient(120deg, #ffe1ec, #fff0f6 55%, #f2e6ff);
    padding: 85px 0;
    overflow: hidden;
}

.hero h1 {
    font-size: clamp(42px, 5vw, 65px);
    line-height: 1.15;
    color: #9e3d70;
}

.hero h1 span {
    color: var(--pink);
    font-style: italic;
}

.hero p {
    color: #765a6a;
    font-size: 16px;
    line-height: 1.8;
    max-width: 480px;
}

.hero-image {
    width: 100%;
    height: 390px;
    object-fit: cover;
    border-radius: 48% 48% 18px 18px;
    box-shadow: 0 15px 35px rgba(190,70,130,.16);
}

.main-btn {
    display: inline-block;
    padding: 13px 27px;
    background: var(--pink);
    color: white;
    border-radius: 30px;
    text-decoration: none;
    font-weight: 700;
    box-shadow: 0 6px 18px rgba(233,79,145,.25);
    transition: .3s;
}

.main-btn:hover {
    background: var(--dark-pink);
    color: white;
    transform: translateY(-3px);
}

.section {
    padding: 75px 0;
}

.section-heading {
    text-align: center;
    margin-bottom: 40px;
}

.section-heading h2 {
    color: #a33e72;
    font-size: 37px;
    font-weight: bold;
}

.section-heading p {
    color: #9b8290;
}

.category-card {
    text-align: center;
    background: white;
    padding: 25px 12px;
    border-radius: 18px;
    box-shadow: 0 5px 20px rgba(200,80,140,.07);
    transition: .3s;
    height: 100%;
}

.category-card:hover {
    transform: translateY(-7px);
    background: #fff0f6;
}

.category-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: auto auto 15px;
    font-size: 30px;
}

.category-card h6 {
    font-weight: 700;
    margin: 0;
}

.product-card {
    border: none;
    background: white;
    border-radius: 18px;
    overflow: hidden;
    height: 100%;
    box-shadow: 0 5px 22px rgba(190,70,130,.08);
    transition: .3s;
}

.product-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 12px 28px rgba(190,70,130,.15);
}

.product-image {
    height: 235px;
    width: 100%;
    object-fit: cover;
    background: #fff0f6;
}

.product-body {
    padding: 18px;
}

.product-category {
    color: var(--purple);
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.product-name {
    font-weight: 700;
    font-size: 16px;
    margin: 8px 0;
}

.product-price {
    color: var(--pink);
    font-size: 19px;
    font-weight: 700;
}

.buy-btn {
    display: block;
    background: #fff0f6;
    color: var(--pink);
    text-decoration: none;
    padding: 10px;
    border-radius: 10px;
    text-align: center;
    font-weight: 700;
    transition: .3s;
}

.buy-btn:hover {
    background: var(--pink);
    color: white;
}

.promo-section {
    background: linear-gradient(110deg, #e94f91, #c47cc9);
    color: white;
    border-radius: 25px;
    padding: 45px;
    overflow: hidden;
}

.promo-section h2 {
    font-size: 38px;
}

.promo-card {
    background: white;
    color: #a33e72;
    padding: 25px;
    border-radius: 18px;
    height: 100%;
    box-shadow: 0 5px 18px rgba(0,0,0,.06);
}

.testimonial-card {
    background: white;
    padding: 27px;
    border-radius: 18px;
    height: 100%;
    box-shadow: 0 5px 20px rgba(190,70,130,.08);
}

.stars {
    color: #f4ad45;
    letter-spacing: 3px;
}

.why-box {
    background: #f5eafa;
    padding: 28px 15px;
    border-radius: 18px;
    text-align: center;
    height: 100%;
}

.why-box i {
    color: var(--pink);
    font-size: 30px;
}

footer {
    background: #533448;
    color: #fff0f6;
    padding: 55px 0 20px;
}

footer a {
    color: #ffd4e6;
    text-decoration: none;
    transition: .3s;
}

footer a:hover {
    color: white;
}

.social-link {
    width: 43px;
    height: 43px;
    border-radius: 50%;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    background: rgba(255,255,255,.13);
    color: white;
    font-size: 21px;
    transition: .3s;
}

.social-link:hover {
    background: var(--pink);
    color: white;
    transform: translateY(-4px);
}

.instagram-text {
    font-size: 14px;
    color: #ffd4e6;
}

.empty-data {
    text-align: center;
    padding: 30px;
    color: #a68b9b;
}

@media(max-width:768px) {
    .hero {
        padding: 55px 0;
    }

    .hero-image {
        height: 290px;
        margin-top: 30px;
    }

    .section {
        padding: 55px 0;
    }

    .promo-section {
        padding: 28px;
    }

    .section-heading h2 {
        font-size: 30px;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg sticky-top">
<div class="container">

<a class="navbar-brand brand" href="index.php">
    <i class="bi bi-flower1"></i>
    <?= htmlspecialchars($namaToko) ?><span>.</span>
</a>

<button class="navbar-toggler" type="button"
data-bs-toggle="collapse" data-bs-target="#navbarMenu">
    <span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="navbarMenu">
<ul class="navbar-nav ms-auto align-items-lg-center">
    <li class="nav-item"><a class="nav-link" href="#beranda">Beranda</a></li>
    <li class="nav-item"><a class="nav-link" href="#kategori">Kategori</a></li>
    <li class="nav-item"><a class="nav-link" href="#produk">Produk</a></li>
    <li class="nav-item"><a class="nav-link" href="#promo">Promo</a></li>
    <li class="nav-item"><a class="nav-link" href="#tentang">Tentang Kami</a></li>
</ul>

<a href="#produk" class="icon-btn" title="Cari produk">
    <i class="bi bi-search"></i>
</a>

<a href="keranjang.php" class="icon-btn" title="Keranjang">
    <i class="bi bi-bag-heart"></i>
</a>
</div>

</div>
</nav>

<!-- HERO -->
<section class="hero" id="beranda">
<div class="container">
<div class="row align-items-center">

<div class="col-lg-6">
    <span class="badge rounded-pill text-bg-light px-3 py-2 mb-3">
        🌷 Bercerita lewat bunga
    </span>

    <h1>
        Flowers That Make
        <span>Every Moment</span>
        Special.
    </h1>

    <p class="mt-3">
        <?= htmlspecialchars($deskripsi) ?>
        Hadirkan kebahagiaan melalui rangkaian bunga yang indah,
        fresh, dan penuh makna.
    </p>

    <a href="#produk" class="main-btn mt-3">
        Explore Collection <i class="bi bi-arrow-right"></i>
    </a>

    <div class="mt-4 small text-muted">
        <i class="bi bi-heart-fill text-danger"></i>
        Made with love, delivered with care.
    </div>
</div>

<div class="col-lg-6">
    <img class="hero-image"
    src="https://images.unsplash.com/photo-1494972308805-463bc619d34e?w=1000"
    alt="Beautiful flower bouquet">
</div>

</div>
</div>
</section>

<!-- KATEGORI -->
<section class="section" id="kategori">
<div class="container">

<div class="section-heading">
    <span class="text-danger">✿ OUR COLLECTION ✿</span>
    <h2>Find Your Favorite Bloom</h2>
    <p>Berbagai pilihan bunga untuk setiap cerita dan perasaan.</p>
</div>

<div class="row g-3">
<?php
$icons = ['bi-flower2','bi-balloon-heart','bi-gift','bi-stars','bi-flower1','bi-heart'];
$colors = ['#ffe0ed','#eee2ff','#ffe9d8','#e3f3e6','#fce1f0','#fff1d7'];
$i = 0;
?>

<?php foreach ($kategori as $k): ?>
<div class="col-6 col-md-4 col-lg-2">
    <div class="category-card">
        <div class="category-icon"
        style="background:<?= $colors[$i % count($colors)] ?>">
            <i class="bi <?= $icons[$i % count($icons)] ?>"
            style="color:#df5793"></i>
        </div>

        <h6><?= htmlspecialchars($k['nama_kategori']) ?></h6>
    </div>
</div>
<?php $i++; endforeach; ?>

<?php if (count($kategori) == 0): ?>
<div class="col-12 empty-data">Belum ada kategori tersedia.</div>
<?php endif; ?>
</div>

</div>
</section>

<!-- PRODUK -->
<section class="section" id="produk" style="background:#fff1f6">
<div class="container">

<div class="section-heading">
    <span class="text-danger">✿ HANDPICKED FOR YOU ✿</span>
    <h2>Our Beautiful Flowers</h2>
    <p>Pilihan rangkaian bunga terbaik untuk orang tersayang.</p>
</div>

<div class="row g-4">
<?php foreach ($produk as $p): ?>
<div class="col-6 col-lg-3">
<div class="product-card">

<?php if (!empty($p['foto'])): ?>
<img class="product-image"
src="uploads/<?= htmlspecialchars($p['foto']) ?>"
alt="<?= htmlspecialchars($p['nama_produk']) ?>">
<?php else: ?>
<div class="product-image d-flex align-items-center justify-content-center">
    <i class="bi bi-flower2 fs-1 text-danger"></i>
</div>
<?php endif; ?>

<div class="product-body">

<span class="product-category">
    <?= htmlspecialchars($p['nama_kategori'] ?? 'Flowers') ?>
</span>

<h6 class="product-name">
    <?= htmlspecialchars($p['nama_produk']) ?>
</h6>

<div class="product-price">
    Rp<?= number_format($p['harga'],0,',','.') ?>
</div>

<p class="small text-muted mb-3">
    <i class="bi bi-box-seam"></i>
    Stok: <?= (int)$p['stok'] ?>
</p>

<a href="detail_produk.php?id=<?= (int)$p['id_produk'] ?>"
class="buy-btn">
    <i class="bi bi-eye"></i> Lihat Produk
</a>

</div>
</div>
</div>
<?php endforeach; ?>

<?php if (count($produk) == 0): ?>
<div class="col-12 empty-data">
    <i class="bi bi-flower2 fs-1"></i>
    <p>Produk bunga akan segera hadir!</p>
</div>
<?php endif; ?>
</div>

</div>
</section>

<!-- PROMO -->
<section class="section" id="promo">
<div class="container">

<div class="promo-section">
<div class="row align-items-center">

<div class="col-lg-5">
    <span class="badge bg-light text-danger mb-3">
        SPECIAL FOR YOU
    </span>

    <h2>Spread Love Through Flowers!</h2>

    <p>
        Berikan kejutan manis dengan rangkaian bunga pilihan.
        Temukan promo spesial dari toko kami.
    </p>
</div>

<div class="col-lg-7">
<div class="row g-3">

<?php foreach ($promo as $pr): ?>
<div class="col-md-6">
    <div class="promo-card">
        <h5><?= htmlspecialchars($pr['nama_promo']) ?></h5>

        <p class="small">
            <?= htmlspecialchars($pr['deskripsi'] ?? '') ?>
        </p>

        <h3>
        <?php if ($pr['jenis_diskon'] == 'Persentase'): ?>
            <?= number_format($pr['nilai_diskon'],0) ?>% OFF
        <?php else: ?>
            Rp<?= number_format($pr['nilai_diskon'],0,',','.') ?>
        <?php endif; ?>
        </h3>
    </div>
</div>
<?php endforeach; ?>

<?php if (count($promo) == 0): ?>
<div class="col-12">
    <div class="promo-card">
        <h4>Little Things, Big Happiness 🌸</h4>
        <p class="mb-0">Pantau terus promo menarik dari toko kami!</p>
    </div>
</div>
<?php endif; ?>

</div>
</div>

</div>
</div>

</div>
</section>

<!-- KEUNGGULAN -->
<section class="section" id="tentang">
<div class="container">

<div class="section-heading">
    <span class="text-danger">✿ WHY CHOOSE US ✿</span>
    <h2>Why You'll Love Us</h2>
    <p>Kami ingin setiap bunga membawa kebahagiaan.</p>
</div>

<div class="row g-3">
<div class="col-6 col-md-3">
    <div class="why-box">
        <i class="bi bi-flower1"></i>
        <h6 class="fw-bold mt-3">Fresh Flowers</h6>
        <small>Bunga pilihan dengan tampilan indah.</small>
    </div>
</div>

<div class="col-6 col-md-3">
    <div class="why-box">
        <i class="bi bi-heart"></i>
        <h6 class="fw-bold mt-3">Made with Love</h6>
        <small>Dirangkai dengan penuh perhatian.</small>
    </div>
</div>

<div class="col-6 col-md-3">
    <div class="why-box">
        <i class="bi bi-gift"></i>
        <h6 class="fw-bold mt-3">Beautiful Gift</h6>
        <small>Cocok untuk berbagai momen spesial.</small>
    </div>
</div>

<div class="col-6 col-md-3">
    <div class="why-box">
        <i class="bi bi-chat-heart"></i>
        <h6 class="fw-bold mt-3">Friendly Service</h6>
        <small>Pelayanan ramah untuk pelanggan.</small>
    </div>
</div>
</div>

</div>
</section>

<!-- TESTIMONI -->
<section class="section" id="testimoni" style="background:#fff1f6">
<div class="container">

<div class="section-heading">
    <span class="text-danger">✿ HAPPY CUSTOMERS ✿</span>
    <h2>Love From Our Customers</h2>
    <p>Cerita bahagia dari pelanggan kami.</p>
</div>

<div class="row g-3">
<?php foreach ($testimoni as $t): ?>
<div class="col-md-4">
<div class="testimonial-card">

<div class="stars">
    <?= str_repeat('★', max(0,min(5,(int)$t['rating']))) ?>
</div>

<p class="mt-3">
    "<?= htmlspecialchars($t['isi_testimoni']) ?>"
</p>

<h6 class="fw-bold mb-0">
    <?= htmlspecialchars($t['nama_pelanggan']) ?>
</h6>

<small class="text-muted">Our lovely customer</small>

</div>
</div>
<?php endforeach; ?>

<?php if (count($testimoni) == 0): ?>
<div class="col-12 empty-data">
    Belum ada testimoni pelanggan.
</div>
<?php endif; ?>
</div>

</div>
</section>

<!-- FOOTER -->
<footer>
<div class="container">

<div class="row g-4">

<div class="col-md-5">
    <h3 class="brand text-white">
        <i class="bi bi-flower1"></i>
        <?= htmlspecialchars($namaToko) ?>
    </h3>

    <p class="mt-3">
        <?= htmlspecialchars($deskripsi) ?>
    </p>

    <div class="d-flex gap-3 mt-3">

        <!-- INSTAGRAM RESMI KEMBANG CERITA -->
        <a href="<?= htmlspecialchars($instagram) ?>"
           target="_blank"
           rel="noopener noreferrer"
           class="social-link"
           title="Instagram Kembang Cerita Flowers"
           aria-label="Instagram Kembang Cerita Flowers">
            <i class="bi bi-instagram"></i>
        </a>

    </div>

    <p class="instagram-text mt-3">
        Follow our Instagram
        <br>
        <a href="<?= htmlspecialchars($instagram) ?>"
           target="_blank"
           rel="noopener noreferrer">
            <i class="bi bi-instagram"></i>
            @kembangcerita_flowers
        </a>
    </p>
</div>

<div class="col-md-3">
    <h5>Quick Links</h5>
    <p><a href="#beranda">Beranda</a></p>
    <p><a href="#kategori">Kategori</a></p>
    <p><a href="#produk">Produk</a></p>
    <p><a href="#promo">Promo</a></p>
    <p><a href="#tentang">Tentang Kami</a></p>
</div>

<div class="col-md-4">
    <h5>Contact Us</h5>

    <p>
        <i class="bi bi-geo-alt"></i>
        <?= nl2br(htmlspecialchars($profil['alamat'] ?? '-')) ?>
    </p>

    <p>
        <i class="bi bi-envelope"></i>
        <?= htmlspecialchars($profil['email'] ?? '-') ?>
    </p>

    <p>
        <i class="bi bi-whatsapp"></i>
        <?= htmlspecialchars($profil['no_whatsapp'] ?? '-') ?>
    </p>
</div>

</div>

<hr class="border-light opacity-25">

<p class="text-center small mb-0">
    © <?= date('Y') ?> <?= htmlspecialchars($namaToko) ?>.
    Made with <i class="bi bi-heart-fill text-danger"></i> for flower lovers.
</p>

</div>
</footer>

<!-- WHATSAPP FLOATING BUTTON -->
<?php if ($wa): ?>
<a href="https://wa.me/<?= htmlspecialchars($wa) ?>"
target="_blank"
rel="noopener noreferrer"
style="position:fixed;bottom:25px;right:25px;background:#25D366;color:white;width:55px;height:55px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;z-index:999;text-decoration:none;box-shadow:0 5px 15px #0002">
    <i class="bi bi-whatsapp"></i>
</a>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>