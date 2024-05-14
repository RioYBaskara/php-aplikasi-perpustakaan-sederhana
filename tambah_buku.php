<?php
session_start();

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai petugas
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'petugas') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
require_once 'conn.php';

// Inisialisasi variabel untuk menyimpan kode buku baru
$kodeBuku = '';

// Query untuk mengambil data kode buku terakhir
$queryKodeBuku = "SELECT kode_buku FROM buku ORDER BY id_buku DESC LIMIT 1";
$resultKodeBuku = mysqli_query($conn, $queryKodeBuku);

// Cek apakah query berhasil dijalankan dan terdapat data
if ($resultKodeBuku && mysqli_num_rows($resultKodeBuku) > 0) {
    $rowKodeBuku = mysqli_fetch_assoc($resultKodeBuku);
    $lastKodeBuku = $rowKodeBuku['kode_buku'];

    // Mengambil angka kelipatan dari kode buku terakhir
    $lastNumber = intval(substr($lastKodeBuku, 2));
    $nextNumber = $lastNumber + 1;

    // Format angka kelipatan dengan panjang tetap 3 digit
    $nextNumberFormatted = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

    // Gabungkan dengan awalan "BK" untuk mendapatkan kode buku baru
    $kodeBuku = 'BK' . $nextNumberFormatted;
} else {
    // Jika tidak terdapat data kode buku sebelumnya, set kode buku awal sebagai BK001
    $kodeBuku = 'BK001';
}

// Memeriksa apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data dari form
    $judulBuku = $_POST['judul_buku'];
    $penerbitBuku = $_POST['penerbit_buku'];
    $tahunBuku = $_POST['tahun_penerbit'];
    $penulisBuku = $_POST['penulis_buku'];
    $stokBuku = $_POST['stok'];

    // Query untuk menambahkan buku baru
    $query = "INSERT INTO buku (kode_buku, judul_buku, penerbit_buku, tahun_penerbit, penulis_buku, stok) VALUES ('$kodeBuku', '$judulBuku', '$penerbitBuku', '$tahunBuku', '$penulisBuku', $stokBuku)";
    $result = mysqli_query($conn, $query);

    // Cek apakah query berhasil dijalankan
    if ($result) {
        // Redirect ke halaman daftar buku
        header("Location: tambah_buku.php");
        exit();
    } else {
        // Gagal menambahkan buku, tampilkan pesan error
        $errorMessage = "Gagal menambahkan buku: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Buku</title>
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
    <h1>Tambah Buku</h1>
    <?php if (isset($errorMessage)) { ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php } ?>
    <form method="POST">
        <div class="form-group">
            <label for="kode_buku">Kode Buku</label>
            <input type="text" class="form-control" id="kode_buku" name="kode_buku" value="<?php echo $kodeBuku; ?>"
                   readonly>
        </div>
        <div class="form-group">
            <label for="judul_buku">Judul Buku</label>
            <input type="text" class="form-control" id="judul_buku" name="judul_buku" required>
        </div>
        <div class="form-group">
            <label for="penerbit_buku">Penerbit Buku</label>
            <input type="text" class="form-control" id="penerbit_buku" name="penerbit_buku" required>
        </div>
        <div class="form-group">
            <label for="tahun_penerbit">Tahun Penerbit</label>
            <input type="text" class="form-control" id="tahun_penerbit" name="tahun_penerbit" required>
        </div>
        <div class="form-group">
            <label for="penulis_buku">Penulis Buku</label>
            <input type="text" class="form-control" id="penulis_buku" name="penulis_buku" required>
        </div>
        <div class="form-group">
            <label for="stok">Stok Buku</label>
            <input type="number" class="form-control" id="stok" name="stok" required>
        </div>
        <button type="submit" class="btn btn-primary">Tambah</button>
        <a href="dashboard_petugas.php" class="btn btn-secondary">Kembali ke Dashboard Petugas</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
