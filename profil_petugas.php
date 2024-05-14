<?php
session_start();

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai petugas
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'petugas') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
require_once 'conn.php';

// Mengambil ID petugas dari sesi
$petugasId = $_SESSION['user_id'];

// Query untuk mengambil data petugas berdasarkan ID petugas
$query = "SELECT * FROM petugas WHERE id_petugas = $petugasId";
$result = mysqli_query($conn, $query);

// Memeriksa apakah query berhasil dijalankan dan terdapat data petugas
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    // Mengambil data petugas
    $namaPetugas = $row['nama_petugas'];
    $jabatanPetugas = $row['jabatan_petugas'];
    $noTelpPetugas = $row['no_telp_petugas'];
    $alamatPetugas = $row['alamat_petugas'];
} else {
    // Jika tidak terdapat data petugas, redirect ke halaman login
    header("Location: login.php");
    exit();
}

// Memeriksa apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data dari form
    $namaPetugas = $_POST['nama_petugas'];
    $jabatanPetugas = $_POST['jabatan_petugas'];
    $noTelpPetugas = $_POST['no_telp_petugas'];
    $alamatPetugas = $_POST['alamat_petugas'];

    // Query untuk mengupdate data petugas
    $queryUpdate = "UPDATE petugas SET nama_petugas = '$namaPetugas', jabatan_petugas = '$jabatanPetugas', no_telp_petugas = '$noTelpPetugas', alamat_petugas = '$alamatPetugas' WHERE id_petugas = $petugasId";
    $resultUpdate = mysqli_query($conn, $queryUpdate);

    // Cek apakah query update berhasil dijalankan
    if ($resultUpdate) {
        // Redirect ke halaman profil petugas
        header("Location: profil_petugas.php");
        exit();
    } else {
        // Gagal mengupdate petugas, tampilkan pesan error
        $errorMessage = "Gagal mengupdate profil petugas: " . mysqli_error($conn);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil Petugas</title>
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
        <h1>Profil Petugas</h1>
        <?php if (isset($errorMessage)) { ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php } ?>
        <form method="POST">
            <div class="form-group">
                <label for="nama_petugas">Nama Petugas</label>
                <input type="text" class="form-control" id="nama_petugas" name="nama_petugas" value="<?php echo $namaPetugas; ?>" required>
            </div>
            <div class="form-group">
                <label for="jabatan_petugas">Jabatan Petugas</label>
                <input type="text" class="form-control" id="jabatan_petugas" name="jabatan_petugas" value="<?php echo $jabatanPetugas; ?>" required>
            </div>
            <div class="form-group">
                <label for="no_telp_petugas">No. Telepon Petugas</label>
                <input type="text" class="form-control" id="no_telp_petugas" name="no_telp_petugas" value="<?php echo $noTelpPetugas; ?>" required>
            </div>
            <div class="form-group">
                <label for="alamat_petugas">Alamat Petugas</label>
                <textarea class="form-control" id="alamat_petugas" name="alamat_petugas" rows="3" required><?php echo $alamatPetugas; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="dashboard_petugas.php" class="btn btn-secondary">Kembali ke Dashboard Petugas</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
