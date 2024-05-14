<?php
session_start();

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai anggota
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'anggota') {
  header("Location: login.php");
  exit();
}

// Koneksi ke database
require_once 'conn.php';

// Mendapatkan id anggota dari sesi
$idAnggota = $_SESSION['user_id'];

// Query untuk mengambil riwayat pinjam buku
$query = "SELECT p.*, b.judul_buku, peng.tanggal_pengembalian, peng.denda
          FROM peminjaman p
          JOIN buku b ON p.id_buku = b.id_buku
          LEFT JOIN pengembalian peng ON p.id_peminjaman = peng.id_buku
          WHERE p.id_anggota = $idAnggota";
$result = mysqli_query($conn, $query);

// Cek apakah query berhasil dijalankan
if (!$result) {
  die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Riwayat Pinjam Buku</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    /* Gaya CSS yang sama dengan yang telah diberikan */
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
          <a class="nav-link" href="dashboard_anggota.php">Dashboard</a>
        </li>
        <li class="nav-item">
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
              <a class="nav-link" href="dashboard_anggota.php">Daftar Buku</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Riwayat Pinjam Buku</a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Konten Utama -->
      <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
        <div class="container">
          <h1>Riwayat Pinjam Buku</h1>
          <?php
          // Cek apakah ada riwayat pinjam buku
          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              $judulBuku = $row['judul_buku'];
              $tanggalPinjam = $row['tanggal_pinjam'];
              $tanggalKembali = $row['tanggal_pengembalian'];
              $denda = $row['denda'];
              $status = $tanggalKembali ? "Telah Dikembalikan" : "Belum Dikembalikan";

              echo "<div class='book'>";
              echo "<div class='details'>";
              echo "<h4>$judulBuku</h4>";
              echo "<p>Tanggal Pinjam: $tanggalPinjam</p>";
              if ($tanggalKembali) {
                echo "<p>Tanggal Kembali: $tanggalKembali</p>";
                echo "<p>Denda: $denda</p>";
              }
              echo "<p>Status: $status</p>";
              echo "</div>";
              echo "</div>";
            }
          } else {
            echo "<p>Belum ada riwayat pinjam buku.</p>";
          }
          ?>
        </div>
      </main>
    </div>
  </div>

  <!-- Tambahkan script JavaScript Bootstrap di bawah ini -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
