<?php
include 'koneksi.php';
$error = '';

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $ins = mysqli_query($conn, "INSERT INTO pelanggan (nama, no_hp, email, password_hash, alamat) VALUES ('$nama', '$no_hp', '$email', '$password', '$alamat')");
    if ($ins) {
        echo "<script>alert('Registrasi berhasil, silakan login!'); window.location='login.php';</script>";
    } else {
        $error = "Registrasi gagal, email sudah terdaftar.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - Kembang Cerita Flowers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #fbeeee 0%, #f4acb7 100%); font-family: 'Poppins', sans-serif; min-height: 100vh; }
        .card { border: none; border-radius: 25px; box-shadow: 0 15px 35px rgba(88, 40, 57, 0.15); }
        .btn-custom { background-color: #d9778c; color: white; border-radius: 50px; padding: 10px; font-weight: 600; border: none; }
        .btn-custom:hover { background-color: #c2657a; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4 bg-white">
                    <div class="text-center mb-3">
                        <h4 class="fw-bold" style="color: #4a2e35;">Daftar Akun Baru</h4>
                        <p class="text-muted small">Buat akun untuk mulai berbelanja</p>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-danger py-2 small rounded-pill text-center"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control rounded-pill form-control-sm px-3" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">No. HP / WhatsApp</label>
                            <input type="text" name="no_hp" class="form-control rounded-pill form-control-sm px-3" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Email</label>
                            <input type="email" name="email" class="form-control rounded-pill form-control-sm px-3" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control rounded-4 form-control-sm p-3" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Password</label>
                            <input type="password" name="password" class="form-control rounded-pill form-control-sm px-3" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-custom w-100 rounded-pill py-2 shadow-sm">Daftar Sekarang</button>
                    </form>
                    <div class="text-center mt-3">
                        <span class="small text-muted">Sudah punya akun?</span> 
                        <a href="login.php" class="small fw-bold text-decoration-none" style="color: #d9778c;">Login di sini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>