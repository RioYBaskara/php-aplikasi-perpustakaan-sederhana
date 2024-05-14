<?php
session_start();

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai petugas
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'petugas') {
  header("Location: login.php");
  exit();
}

// Koneksi ke database
require_once 'conn.php';

// Fungsi untuk membersihkan dan melindungi input dari serangan SQL Injection
function sanitizeInput($input) {
  global $conn;
  $clean = mysqli_real_escape_string($conn, $input);
  return htmlspecialchars($clean);
}

// Inisialisasi variabel
$idPetugas = '';
$namaPetugas = '';
$jabatanPetugas = '';
$noTelpPetugas = '';
$alamatPetugas = '';

// Memeriksa apakah aksi (tambah/edit/hapus) telah di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Jika aksi adalah tambah
  if (isset($_POST['action']) && $_POST['action'] === 'tambah') {
    // Mendapatkan data dari form tambah
    $namaPetugas = sanitizeInput($_POST['nama_petugas']);
    $jabatanPetugas = sanitizeInput($_POST['jabatan_petugas']);
    $noTelpPetugas = sanitizeInput($_POST['no_telp_petugas']);
    $alamatPetugas = sanitizeInput($_POST['alamat_petugas']);

    // Query untuk menambah petugas ke database
    $query = "INSERT INTO petugas (nama_petugas, jabatan_petugas, no_telp_petugas, alamat_petugas) VALUES ('$namaPetugas', '$jabatanPetugas', '$noTelpPetugas', '$alamatPetugas')";
    $result = mysqli_query($conn, $query);

    if ($result) {
      // Redirect ke halaman daftar_petugas.php setelah berhasil menambah petugas
      header("Location: daftar_petugas.php");
      exit();
    } else {
      echo "Error: " . mysqli_error($conn);
    }
  }

  // Jika aksi adalah edit
  if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    // Mendapatkan data dari form edit
    $idPetugas = sanitizeInput($_POST['id_petugas']);
    $namaPetugas = sanitizeInput($_POST['nama_petugas']);
    $jabatanPetugas = sanitizeInput($_POST['jabatan_petugas']);
    $noTelpPetugas = sanitizeInput($_POST['no_telp_petugas']);
    $alamatPetugas = sanitizeInput($_POST['alamat_petugas']);

    // Query untuk mengupdate petugas di database
    $query = "UPDATE petugas SET nama_petugas = '$namaPetugas', jabatan_petugas = '$jabatanPetugas', no_telp_petugas = '$noTelpPetugas', alamat_petugas = '$alamatPetugas' WHERE id_petugas = '$idPetugas'";
    $result = mysqli_query($conn, $query);

    if ($result) {
      // Redirect ke halaman daftar_petugas.php setelah berhasil mengedit petugas
      header("Location: daftar_petugas.php");
      exit();
    } else {
      echo "Error: " . mysqli_error($conn);
    }
  }
}

// Jika aksi adalah hapus
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id'])) {
  // Memeriksa apakah ID petugas yang akan dihapus telah diberikan
  $idPetugas = sanitizeInput($_GET['id']);

  // Query untuk menghapus petugas dari database
  $query = "DELETE FROM petugas WHERE id_petugas = '$idPetugas'";
  $result = mysqli_query($conn, $query);

  if ($result) {
    // Redirect ke halaman daftar_petugas.php setelah berhasil menghapus petugas
    header("Location: daftar_petugas.php");
    exit();
  } else {
    echo "Error: " . mysqli_error($conn);
  }
}

// Jika aksi adalah edit dan ID petugas yang akan diedit telah diberikan
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
  // Memeriksa apakah ID petugas yang akan diedit telah diberikan
  $idPetugas = sanitizeInput($_GET['id']);

  // Query untuk mengambil data petugas berdasarkan ID
  $query = "SELECT * FROM petugas WHERE id_petugas = '$idPetugas'";
  $result = mysqli_query($conn, $query);

  if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    // Mengisi nilai awal variabel dari data yang ada
    $namaPetugas = $row['nama_petugas'];
    $jabatanPetugas = $row['jabatan_petugas'];
    $noTelpPetugas = $row['no_telp_petugas'];
    $alamatPetugas = $row['alamat_petugas'];
  } else {
    echo "Error: " . mysqli_error($conn);
  }
}

// Query untuk mengambil data petugas
$query = "SELECT * FROM petugas";
$result = mysqli_query($conn, $query);

// Memeriksa apakah query berhasil dijalankan dan terdapat data petugas
if ($result && mysqli_num_rows($result) > 0) {
  $petugas = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
  $petugas = [];
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Daftar Petugas</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .container {
      max-width: 800px;
      margin: 50px auto;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Daftar Petugas</h1>
    <a href="dashboard_petugas.php" class="btn btn-secondary mb-3">Kembali ke Dashboard Petugas</a>

    <!-- Tabel Daftar Petugas -->
    <table class="table mt-4">
      <thead>
        <tr>
          <th>ID Petugas</th>
          <th>Nama Petugas</th>
          <th>Jabatan Petugas</th>
          <th>No. Telepon</th>
          <th>Alamat</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($petugas as $row) { ?>
          <tr>
            <td><?php echo $row['id_petugas']; ?></td>
            <td><?php echo $row['nama_petugas']; ?></td>
            <td><?php echo $row['jabatan_petugas']; ?></td>
            <td><?php echo $row['no_telp_petugas']; ?></td>
            <td><?php echo $row['alamat_petugas']; ?></td>
            <td>
              <a href="daftar_petugas.php?action=edit&id=<?php echo $row['id_petugas']; ?>" class="btn btn-primary">Edit</a>
              <a href="daftar_petugas.php?action=hapus&id=<?php echo $row['id_petugas']; ?>" class="btn btn-danger">Hapus</a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>


    <!-- Form Tambah/Edit Petugas -->
    <form method="POST" action="daftar_petugas.php">
      <input type="hidden" name="id_petugas" value="<?php echo $idPetugas; ?>">
      <div class="form-group">
        <label for="nama_petugas">Nama Petugas</label>
        <input type="text" class="form-control" id="nama_petugas" name="nama_petugas" value="<?php echo $namaPetugas; ?>">
      </div>
      <div class="form-group">
        <label for="jabatan_petugas">Jabatan Petugas</label>
        <input type="text" class="form-control" id="jabatan_petugas" name="jabatan_petugas" value="<?php echo $jabatanPetugas; ?>">
      </div>
      <div class="form-group">
        <label for="no_telp_petugas">No. Telepon</label>
        <input type="text" class="form-control" id="no_telp_petugas" name="no_telp_petugas" value="<?php echo $noTelpPetugas; ?>">
      </div>
      <div class="form-group">
        <label for="alamat_petugas">Alamat</label>
        <textarea class="form-control" id="alamat_petugas" name="alamat_petugas"><?php echo $alamatPetugas; ?></textarea>
      </div>
      <?php if ($idPetugas === '') { ?>
        <button type="submit" class="btn btn-primary" name="action" value="tambah">Tambah Petugas</button>
      <?php } else { ?>
        <button type="submit" class="btn btn-primary" name="action" value="edit">Edit Petugas</button>
        <a href="daftar_petugas.php?action=hapus&id=<?php echo $idPetugas; ?>" class="btn btn-danger">Hapus Petugas</a>
      <?php } ?>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
