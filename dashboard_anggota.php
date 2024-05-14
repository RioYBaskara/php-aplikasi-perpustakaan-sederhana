<?php
session_start();

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai anggota
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'anggota') {
  header("Location: login.php");
  exit();
}

// Koneksi ke database
require_once 'conn.php';

// Jumlah item per halaman
$itemsPerHalaman = 4;

// Menentukan halaman saat ini
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Mengatur batas awal dan akhir data yang akan ditampilkan
$batasAwal = ($currentPage - 1) * $itemsPerHalaman;

// Query untuk mengambil jumlah total buku
$queryTotal = "SELECT COUNT(*) as total FROM buku";
$resultTotal = mysqli_query($conn, $queryTotal);
$dataTotal = mysqli_fetch_assoc($resultTotal);
$totalBuku = $dataTotal['total'];

// Menghitung jumlah halaman
$totalHalaman = ceil($totalBuku / $itemsPerHalaman);

// Query untuk mengambil data buku dengan batas awal dan akhir yang ditentukan
$query = "SELECT * FROM buku LIMIT $batasAwal, $itemsPerHalaman";
$result = mysqli_query($conn, $query);

// Cek apakah query berhasil dijalankan
if (!$result) {
  die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard Anggota</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    .btn-pinjam {
      background-color: green;
      color: white;
    }
    .container {
      margin-top: 50px;
    }
    .navbar {
      background-color: #007bff;
      color: #fff;
    }
    .navbar-brand {
      font-weight: bold;
    }
    .navbar-nav.ml-auto .nav-item {
      margin-left: 10px;
    }
    .sidebar {
      background-color: #f8f9fa;
      min-height: 100vh;
      width: 200px;
    }
    .sidebar ul {
      list-style-type: none;
      padding-left: 0;
    }
    .sidebar li {
      padding: 10px;
      border-bottom: 1px solid #ddd;
    }
    .sidebar a {
      color: #333;
      text-decoration: none;
    }
    .main {
      padding: 20px;
    }
    .book {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      background-color: #DCDCDC;
    }
    .book img {
      width: 100px;
      height: 150px;
      object-fit: cover;
      margin-right: 20px;
    }
    .book .details {
      flex-grow: 1;
    }
    .book .details h4 {
      margin: 0;
    }
    .book .details p {
      margin: 5px 0;
    }
    .book .status {
      font-weight: bold;
    }


    .bg-red a {
      color: white;
      text-decoration: none;
    }

    .bg-red a:hover {
      color: yellow;
      text-shadow: 1px 1px 2px black;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="#">Aplikasi Perpustakaan Sederhana</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <a class="nav-link" href="profil_anggota.php">Profil</a>
        </li>
        <li class="nav-item bg-red">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Sidebar -->
  <div class="container-fluid">
    <div class="row">
      <nav class="col-md-2 d-none d-md-block bg-light sidebar">
        <div class="sidebar-sticky">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link" href="#">Daftar Buku</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="riwayat_pinjam.php">Riwayat Pinjam Buku</a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Konten Utama -->
      <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
        <div class="container">
          <h1>Daftar Buku</h1>
          <table class="table">
            <thead>
              <tr>
                <th>Nama Buku</th>
                <th>Penerbit Buku</th>
                <th>Tahun Penerbit Buku</th>
                <th>Penulis Buku</th>
                <th>Stok Buku</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Tampilkan data buku dari database
              while ($row = mysqli_fetch_assoc($result)) {
                $namaBuku = $row['judul_buku'];
                $penerbitBuku = $row['penerbit_buku'];
                $tahunBuku = $row['tahun_penerbit'];
                $penulisBuku = $row['penulis_buku'];
                $stokBuku = $row['stok'];

                echo "<tr>";
                echo "<td>$namaBuku</td>";
                echo "<td>$penerbitBuku</td>";
                echo "<td>$tahunBuku</td>";
                echo "<td>$penulisBuku</td>";
                echo "<td>$stokBuku</td>";
                echo "<td><button class='btn btn-pinjam' data-toggle='modal' data-target='#hubungiPetugasModal'>Pinjam</button></td>";
                echo "</tr>";
              }
              ?>
            </tbody>
          </table>

          <nav aria-label="Halaman">
    <ul class="pagination">
        <?php if ($currentPage > 1) { ?>
            <li class="page-item">
                <a class="page-link" href="dashboard_anggota.php?page=<?php echo ($currentPage - 1); ?>">Sebelumnya</a>
            </li>
        <?php } ?>
        <?php for ($i = 1; $i <= $totalHalaman; $i++) { ?>
            <li class="page-item <?php if ($i === $currentPage) echo 'active'; ?>">
                <a class="page-link" href="dashboard_anggota.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php } ?>
        <?php if ($currentPage < $totalHalaman) { ?>
            <li class="page-item">
                <a class="page-link" href="dashboard_anggota.php?page=<?php echo ($currentPage + 1); ?>">Selanjutnya</a>
            </li>
        <?php } ?>
    </ul>
</nav>

        </div>
      </main>
    </div>
  </div>

  <!-- Modal Hubungi Petugas -->
  <div class="modal fade" id="hubungiPetugasModal" tabindex="-1" role="dialog" aria-labelledby="hubungiPetugasModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="hubungiPetugasModalLabel">Hubungi Petugas!</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Silakan hubungi petugas untuk melakukan peminjaman buku ini.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Tambahkan script JavaScript di bawah ini -->
  <script>
    function hubungiPetugas() {
      $('#hubungiPetugasModal').modal('show');
    }
  </script>

  <!-- Tambahkan script JavaScript Bootstrap di bawah ini -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
