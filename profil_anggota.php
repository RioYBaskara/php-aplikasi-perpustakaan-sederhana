<?php
session_start();

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai anggota
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'anggota') {
  header("Location: login.php");
  exit();
}

// Koneksi ke database
require_once 'conn.php';

// Mendapatkan data anggota dari database berdasarkan id_anggota di sesi
$idAnggota = $_SESSION['user_id'];
$query = "SELECT * FROM anggota WHERE id_anggota = $idAnggota";
$result = mysqli_query($conn, $query);

// Cek apakah query berhasil dijalankan
if (!$result) {
  die("Query error: " . mysqli_error($conn));
}

// Memeriksa apakah data anggota ditemukan
if (mysqli_num_rows($result) == 0) {
  die("Data anggota tidak ditemukan");
}

// Mendapatkan data anggota
$row = mysqli_fetch_assoc($result);
$kodeAnggota = $row['kode_anggota'];
$namaAnggota = $row['nama_anggota'];
$jenisKelamin = $row['jk_anggota'];
$jurusanAnggota = $row['jurusan_anggota'];
$noTelpAnggota = $row['no_telp_anggota'];
$alamatAnggota = $row['alamat_anggota'];

// Memproses form pengeditan anggota
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $namaAnggota = $_POST['nama_anggota'];
  $jenisKelamin = $_POST['jenis_kelamin'];
  $jurusanAnggota = $_POST['jurusan_anggota'];
  $noTelpAnggota = $_POST['no_telp_anggota'];
  $alamatAnggota = $_POST['alamat_anggota'];

  // Validasi dan update data anggota ke database
  $query = "UPDATE anggota SET nama_anggota = '$namaAnggota', jk_anggota = '$jenisKelamin', jurusan_anggota = '$jurusanAnggota', no_telp_anggota = '$noTelpAnggota', alamat_anggota = '$alamatAnggota' WHERE id_anggota = $idAnggota";
  $result = mysqli_query($conn, $query);

  // Cek apakah query berhasil dijalankan
  if ($result) {
    // Redirect ke halaman profil_anggota.php dengan pesan sukses
    header("Location: profil_anggota.php?status=success");
    exit();
  } else {
    // Redirect ke halaman profil_anggota.php dengan pesan error
    header("Location: profil_anggota.php?status=error");
    exit();
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Profil Anggota</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .container {
      margin-top: 50px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Profil Anggota</h1>
    <?php
    // Menampilkan pesan sukses atau error setelah mengedit profil
    if (isset($_GET['status'])) {
      if ($_GET['status'] === 'success') {
        echo '<div class="alert alert-success" role="alert">Profil berhasil diperbarui</div>';
      } elseif ($_GET['status'] === 'error') {
        echo '<div class="alert alert-danger" role="alert">Terjadi kesalahan saat memperbarui profil</div>';
      }
    }
    ?>
    <form method="POST">
      <div class="form-group">
        <label for="kode_anggota">Kode Anggota</label>
        <input type="text" class="form-control" id="kode_anggota" name="kode_anggota" value="<?php echo $kodeAnggota; ?>" disabled>
      </div>
      <div class="form-group">
        <label for="nama_anggota">Nama Anggota</label>
        <input type="text" class="form-control" id="nama_anggota" name="nama_anggota" value="<?php echo $namaAnggota; ?>" required>
      </div>
      <div class="form-group">
        <label for="jenis_kelamin">Jenis Kelamin</label>
        <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
          <option value="1" <?php if ($jenisKelamin == 1) echo 'selected'; ?>>Laki-laki</option>
          <option value="2" <?php if ($jenisKelamin == 2) echo 'selected'; ?>>Perempuan</option>
        </select>
      </div>
      <div class="form-group">
        <label for="jurusan_anggota">Jurusan</label>
        <select class="form-control" id="jurusan_anggota" name="jurusan_anggota" required>
          <option value="SIJA" <?php if ($jurusanAnggota == 'SIJA') echo 'selected'; ?>>SIJA</option>
          <option value="TKJ" <?php if ($jurusanAnggota == 'TKJ') echo 'selected'; ?>>TKJ</option>
          <option value="DPIB" <?php if ($jurusanAnggota == 'DPIB') echo 'selected'; ?>>DPIB</option>
        </select>
      </div>
      <div class="form-group">
        <label for="no_telp_anggota">Nomor Telepon</label>
        <input type="text" class="form-control" id="no_telp_anggota" name="no_telp_anggota" value="<?php echo $noTelpAnggota; ?>" required>
      </div>
      <div class="form-group">
        <label for="alamat_anggota">Alamat</label>
        <textarea class="form-control" id="alamat_anggota" name="alamat_anggota" rows="3" required><?php echo $alamatAnggota; ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="dashboard_petugas.php" class="btn btn-secondary">Kembali ke Dashboard Petugas</a>
    </form>
  </div>

  <!-- Tambahkan script JavaScript Bootstrap di bawah ini -->
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
