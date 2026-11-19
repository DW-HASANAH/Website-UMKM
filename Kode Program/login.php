<?php
include 'koneksi.php';

$error = '';
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Cek login ke tabel pelanggan
    $result = mysqli_query($conn, "SELECT * FROM pelanggan WHERE email = '$email'");
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['id_pelanggan'] = $row['id_pelanggan'];
            $_SESSION['nama_pelanggan'] = $row['nama'];
            header("Location: index.php");
            exit;
        }
    }
    $error = "Email atau password yang kamu masukkan salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kembang Cerita Flowers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,500&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --burgundy-main: #632b3e;
            --burgundy-dark: #451c2a;
            --rose-pink: #d9778c;
            --soft-cream: #fffafb;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fbe8ec 0%, #f5cbd5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        /* Bokeh Gelembung Melayang di Background */
        .bokeh {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.6) 0%, rgba(255,255,255,0) 70%);
            animation: floatBokeh 8s infinite ease-in-out;
            z-index: 0;
            pointer-events: none;
        }
        .bokeh-1 { width: 220px; height: 220px; top: -40px; left: 5%; animation-delay: 0s; }
        .bokeh-2 { width: 300px; height: 300px; bottom: -80px; right: 8%; animation-delay: 2s; }
        .bokeh-3 { width: 150px; height: 150px; top: 40%; left: 48%; animation-delay: 4s; }

        @keyframes floatBokeh {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }

        /* Container Utama */
        .auth-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 960px;
            background: var(--soft-cream);
            border-radius: 36px;
            box-shadow: 0 25px 60px rgba(99, 43, 62, 0.2);
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
        }

        /* Sisi Kiri: Area Artistik Burgundy + Gelombang SVG */
        .art-panel {
            position: relative;
            width: 48%;
            background: linear-gradient(160deg, var(--burgundy-main) 0%, var(--burgundy-dark) 100%);
            color: white;
            padding: 50px 45px 50px 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        /* Gelombang Vertikal Organik Pemisah Kiri & Kanan */
        .vertical-wave {
            position: absolute;
            top: 0;
            right: -1px;
            height: 100%;
            width: 65px;
            z-index: 5;
            pointer-events: none;
        }

        /* Bingkai Blob Organik agar Logo Menyatu Alami (Tidak Kotak!) */
        .organic-blob {
            width: 195px;
            height: 195px;
            background: radial-gradient(circle, rgba(255,228,235,0.22) 0%, rgba(255,255,255,0.05) 100%);
            border-radius: 62% 38% 46% 54% / 60% 44% 56% 40%;
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25), inset 0 0 20px rgba(255, 255, 255, 0.2);
            animation: morphAndFloat 7s ease-in-out infinite;
            margin: 18px 0;
            position: relative;
            z-index: 6;
        }

        .organic-blob img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: inherit;
            /* Masking lembut agar pinggiran gambar menyatu */
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        @keyframes morphAndFloat {
            0%, 100% {
                border-radius: 62% 38% 46% 54% / 60% 44% 56% 40%;
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                border-radius: 42% 58% 62% 38% / 45% 60% 40% 55%;
                transform: translateY(-10px) rotate(2deg);
            }
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.85rem;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 6;
        }

        /* Sisi Kanan: Form Panel */
        .form-panel {
            width: 52%;
            padding: 55px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--soft-cream);
            position: relative;
            z-index: 4;
        }

        .form-title {
            font-family: 'Playfair Display', serif;
            color: var(--burgundy-main);
            font-weight: 700;
            font-size: 2rem;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 18px;
        }

        .input-group-custom i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--rose-pink);
            font-size: 15px;
        }

        .form-control-pill {
            width: 100%;
            border-radius: 50px;
            padding: 13px 20px 13px 48px;
            font-size: 14px;
            background-color: #ffffff;
            border: 1.5px solid #f0d5dc;
            color: #4a2e35;
            transition: all 0.3s ease;
        }

        .form-control-pill:focus {
            outline: none;
            border-color: var(--rose-pink);
            box-shadow: 0 0 0 4px rgba(217, 119, 140, 0.15);
        }

        .btn-feminine-grad {
            background: linear-gradient(135deg, #d9778c 0%, #b8566d 100%);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 8px 20px rgba(217, 119, 140, 0.35);
            transition: all 0.3s ease;
        }

        .btn-feminine-grad:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(217, 119, 140, 0.45);
            color: white;
        }

        .back-link {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 13px;
            padding: 7px 18px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(4px);
            transition: all 0.2s;
            position: relative;
            z-index: 6;
        }
        .back-link:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }

        /* Responsif untuk layar HP */
        @media (max-width: 768px) {
            .art-panel, .form-panel { width: 100%; }
            .vertical-wave { display: none; }
            .art-panel { padding: 40px 25px; border-bottom-left-radius: 35px; border-bottom-right-radius: 35px; }
            .form-panel { padding: 35px 25px; }
        }
    </style>
</head>
<body>

    <!-- Efek Gelembung Bokeh Melayang -->
    <div class="bokeh bokeh-1"></div>
    <div class="bokeh bokeh-2"></div>
    <div class="bokeh bokeh-3"></div>

    <div class="auth-wrapper">
        <!-- Panel Kiri: Latar Burgundy + Gelombang SVG + Bingkai Organik -->
        <div class="art-panel">
            <h2 class="brand-title fw-bold mb-1">Kembang Cerita</h2>
            <p class="small text-white-50 mb-2">Rangkaian Kisah, Penuh Makna.</p>

            <!-- Bingkai Kelopak Organik yang Bergerak Lembut -->
            <div class="organic-blob">
                <img src="uploads/logo.jpg" alt="Logo Kembang Cerita Flowers">
            </div>

            <p class="small text-white-50 px-3 mb-4" style="font-size: 12.5px; line-height: 1.6; position: relative; z-index: 6;">
                Setiap kelopak menyimpan cerita. Masuk untuk merangkai buket spesial bagi orang tersayang.
            </p>

            <a href="index.php" class="back-link">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Beranda
            </a>

            <!-- Gelombang SVG Vertikal Pemisah Kiri & Kanan -->
            <svg class="vertical-wave" viewBox="0 0 100 500" preserveAspectRatio="none">
                <path d="M100,0 L40,0 C85,110 5,185 55,275 C95,350 15,420 55,500 L100,500 Z" fill="#fffafb"/>
                <path d="M100,0 L58,0 C95,115 22,190 68,280 C102,350 32,425 70,500 L100,500 Z" fill="#f7dce3" fill-opacity="0.55"/>
            </svg>
        </div>

        <!-- Panel Kanan: Form Login -->
        <div class="form-panel">
            <div class="mb-4">
                <span class="badge rounded-pill mb-2 px-3 py-1" style="background-color: #fde8ed; color: #d9778c; font-weight: 500; font-size: 12px;">
                    <i class="fa fa-sparkles me-1"></i> Selamat Datang Kembali
                </span>
                <h2 class="form-title mb-1">Masuk ke Akun</h2>
                <p class="text-muted small">Silakan isi email dan kata sandimu di bawah ini</p>
            </div>

            <?php if($error): ?>
                <div class="alert alert-danger py-2 px-3 small rounded-pill text-center border-0" style="background-color: #ffe3e8; color: #b02a46;">
                    <i class="fa fa-exclamation-circle me-1"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group-custom">
                    <i class="fa fa-envelope"></i>
                    <input type="email" name="email" class="form-control-pill" placeholder="Alamat Email" required>
                </div>

                <div class="input-group-custom mb-4">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" class="form-control-pill" placeholder="Kata Sandi" required>
                </div>

                <button type="submit" name="login" class="btn btn-feminine-grad w-100 mb-3">
                    Masuk Sekarang <i class="fa fa-arrow-right ms-1"></i>
                </button>
            </form>

            <div class="text-center mt-3">
                <span class="small text-muted">Belum punya akun?</span>
                <a href="register.php" class="small fw-semibold text-decoration-none ms-1" style="color: var(--rose-pink);">
                    Daftar di Sini
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>