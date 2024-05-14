<?php
session_start();

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai petugas
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'petugas') {
    header("Location: login.php");
    exit();
}

// Memeriksa apakah parameter ID buku telah diberikan
if (!isset($_GET['id'])) {
    header("Location: dashboard_petugas.php");
    exit();
}

// Koneksi ke database
require_once 'conn.php';

// Mengambil ID buku dari parameter
$idBuku = $_GET['id'];

// Query untuk menghapus buku berdasarkan ID buku
$queryDelete = "DELETE FROM buku WHERE id_buku = $idBuku";
$resultDelete = mysqli_query($conn, $queryDelete);

// Cek apakah query delete berhasil dijalankan
if ($resultDelete) {
    // Redirect ke halaman daftar buku
    header("Location: dashboard_petugas.php");
    exit();
} else {
    // Gagal menghapus buku, tampilkan pesan error
    $errorMessage = "Gagal menghapus buku: " . mysqli_error($conn);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Hapus Buku</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            max-width: 500px;
            margin: 50px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($errorMessage)) { ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php } else { ?>
            <div class="alert alert-success">Buku berhasil dihapus.</div>
        <?php } ?>
        <a href="dashboard_petugas.php" class="btn btn-primary">Kembali ke Dashboard</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
