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

// Query untuk mengambil data buku berdasarkan ID buku
$query = "SELECT * FROM buku WHERE id_buku = $idBuku";
$result = mysqli_query($conn, $query);

// Memeriksa apakah query berhasil dijalankan dan terdapat data buku
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    // Mengambil data buku
    $kodeBuku = $row['kode_buku'];
    $judulBuku = $row['judul_buku'];
    $penerbitBuku = $row['penerbit_buku'];
    $tahunBuku = $row['tahun_penerbit'];
    $penulisBuku = $row['penulis_buku'];
    $stokBuku = $row['stok'];
} else {
    // Jika tidak terdapat data buku, redirect ke halaman daftar buku
    header("Location: dashboard_petugas.php");
    exit();
}

// Memeriksa apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data dari form
    $judulBuku = $_POST['judul_buku'];
    $penerbitBuku = $_POST['penerbit_buku'];
    $tahunBuku = $_POST['tahun_penerbit'];
    $penulisBuku = $_POST['penulis_buku'];
    $stokBuku = $_POST['stok'];

    // Query untuk mengupdate data buku
    $queryUpdate = "UPDATE buku SET judul_buku = '$judulBuku', penerbit_buku = '$penerbitBuku', tahun_penerbit = '$tahunBuku', penulis_buku = '$penulisBuku', stok = $stokBuku WHERE id_buku = $idBuku";
    $resultUpdate = mysqli_query($conn, $queryUpdate);

    // Cek apakah query update berhasil dijalankan
    if ($resultUpdate) {
        // Redirect ke halaman daftar buku
        header("Location: dashboard_petugas.php");
        exit();
    } else {
        // Gagal mengupdate buku, tampilkan pesan error
        $errorMessage = "Gagal mengupdate buku: " . mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Buku</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .form-container {
            max-width: 500px;
            margin: 50px auto;
        }
    </style>
</head>
<body>
    <div class="container form-container">
        <h1>Edit Buku</h1>
        <?php if (isset($errorMessage)) { ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php } ?>
        <form method="POST">
            <div class="form-group">
                <label for="kode_buku">Kode Buku</label>
                <input type="text" class="form-control" id="kode_buku" name="kode_buku" value="<?php echo $kodeBuku; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="judul_buku">Judul Buku</label>
                <input type="text" class="form-control" id="judul_buku" name="judul_buku" value="<?php echo $judulBuku; ?>" required>
            </div>
            <div class="form-group">
                <label for="penerbit_buku">Penerbit Buku</label>
                <input type="text" class="form-control" id="penerbit_buku" name="penerbit_buku" value="<?php echo $penerbitBuku; ?>" required>
            </div>
            <div class="form-group">
                <label for="tahun_penerbit">Tahun Penerbit</label>
                <input type="text" class="form-control" id="tahun_penerbit" name="tahun_penerbit" value="<?php echo $tahunBuku; ?>" required>
            </div>
            <div class="form-group">
                <label for="penulis_buku">Penulis Buku</label>
                <input type="text" class="form-control" id="penulis_buku" name="penulis_buku" value="<?php echo $penulisBuku; ?>" required>
            </div>
            <div class="form-group">
                <label for="stok">Stok Buku</label>
                <input type="number" class="form-control" id="stok" name="stok" value="<?php echo $stokBuku; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
