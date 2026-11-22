<?php
include '../koneksi.php';

$error = '';
$success = '';

if (isset($_POST['daftar_admin'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek apakah username sudah ada
    $cek = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $ins = mysqli_query($conn, "INSERT INTO admin (username, password_hash, nama_lengkap) VALUES ('$username', '$password', '$nama')");
        if ($ins) {
            $success = "Admin baru berhasil didaftarkan! <a href='login.php'>Login di sini</a>";
        } else {
            $error = "Gagal mendaftarkan admin.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Admin - Kembang Cerita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff5f7; font-family: 'Poppins', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(88,40,57,0.1); width: 100%; max-width: 420px; }
        .btn-custom { background-color: #632b3e; color: white; border-radius: 50px; }
        .btn-custom:hover { background-color: #451c2a; color: white; }
    </style>
</head>
<body>
    <div class="card p-4 bg-white">
        <h4 class="fw-bold text-center mb-1" style="color: #632b3e;">Registrasi Admin</h4>
        <p class="text-muted small text-center mb-3">Tambah pengelola toko baru</p>

        <?php if($error): ?>
            <div class="alert alert-danger py-2 small text-center rounded-pill"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success py-2 small text-center rounded-pill"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-2">
                <label class="form-label small">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-2">
                <label class="form-label small">Username Admin</label>
                <input type="text" name="username" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-3">
                <label class="form-label small">Password</label>
                <input type="password" name="password" class="form-control rounded-pill px-3" required>
            </div>
            <button type="submit" name="daftar_admin" class="btn btn-custom w-100 py-2 fw-bold shadow-sm">Daftarkan Admin</button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php" class="small text-muted text-decoration-none">Sudah punya akun? Login</a>
        </div>
    </div>
</body>
</html>