<?php
session_start();

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai petugas/admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'petugas' && $_SESSION['user_type'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
require_once 'conn.php';

// Mendefinisikan pesan kesalahan awal
$errorMessage = '';

// Fungsi untuk mendapatkan status peminjaman berdasarkan tanggal kembali
function getPeminjamanStatus($tanggalKembali)
{
    return ($tanggalKembali < date('Y-m-d')) ? 'Sudah Dikembalikan' : 'Masih Dipinjam';
}

// Menentukan jumlah data per halaman
$dataPerPage = 4;

// Menentukan halaman saat ini
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Menghitung offset data
$offset = ($currentPage - 1) * $dataPerPage;

// Query untuk mengambil data peminjaman dengan pagination
$query = "SELECT p.id_peminjaman, p.tanggal_pinjam, p.tanggal_kembali, b.judul_buku, a.nama_anggota, pt.nama_petugas
          FROM peminjaman p
          INNER JOIN buku b ON p.id_buku = b.id_buku
          INNER JOIN anggota a ON p.id_anggota = a.id_anggota
          INNER JOIN petugas pt ON p.id_petugas = pt.id_petugas
          LIMIT $offset, $dataPerPage";
$result = mysqli_query($conn, $query);

// Cek apakah query berhasil dijalankan
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

// Menghitung total data peminjaman
$queryTotalData = "SELECT COUNT(*) AS total_data FROM peminjaman";
$resultTotalData = mysqli_query($conn, $queryTotalData);
$rowTotalData = mysqli_fetch_assoc($resultTotalData);
$totalData = $rowTotalData['total_data'];

// Menghitung jumlah halaman
$totalPages = ceil($totalData / $dataPerPage);

// Memeriksa apakah form telah disubmit untuk menghapus peminjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {
    $idPeminjaman = $_POST['id_peminjaman'];

    // Menghapus data peminjaman berdasarkan ID
    $queryDeletePeminjaman = "DELETE FROM peminjaman WHERE id_peminjaman = $idPeminjaman";
    $resultDeletePeminjaman = mysqli_query($conn, $queryDeletePeminjaman);

    if ($resultDeletePeminjaman) {
        // Peminjaman berhasil dihapus, refresh halaman
        header("Location: daftar_peminjaman.php?page=$currentPage");
        exit();
    } else {
        $errorMessage = 'Terjadi kesalahan saat menghapus peminjaman. Silakan coba lagi.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Peminjaman</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 50px;
        }

        .error-message {
            color: red;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Daftar Peminjaman</h1>
    <?php if (!empty($errorMessage)) { ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php } ?>
    <a href="dashboard_petugas.php" class="btn btn-secondary mb-3">Kembali ke Dashboard Petugas</a>
    <table class="table">
        <thead>
        <tr>
            <th>ID Peminjaman</th>
            <th>Tanggal Pinjam</th>
            <th>Tanggal Kembali</th>
            <th>Judul Buku</th>
            <th>Nama Anggota</th>
            <th>Nama Petugas</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Tampilkan data peminjaman dari database
        while ($row = mysqli_fetch_assoc($result)) {
            $idPeminjaman = $row['id_peminjaman'];
            $tanggalPinjam = $row['tanggal_pinjam'];
            $tanggalKembali = $row['tanggal_kembali'];
            $judulBuku = $row['judul_buku'];
            $namaAnggota = $row['nama_anggota'];
            $namaPetugas = $row['nama_petugas'];
            $status = getPeminjamanStatus($tanggalKembali);

            echo "<tr>";
            echo "<td>$idPeminjaman</td>";
            echo "<td>$tanggalPinjam</td>";
            echo "<td>$tanggalKembali</td>";
            echo "<td>$judulBuku</td>";
            echo "<td>$namaAnggota</td>";
            echo "<td>$namaPetugas</td>";
            echo "<td>$status</td>";
            echo "<td>
                    <form method='POST' class='d-inline' onsubmit='return confirm(\"Apakah Anda yakin ingin menghapus peminjaman ini?\")'>
                        <input type='hidden' name='id_peminjaman' value='$idPeminjaman'>
                        <button type='submit' name='hapus' class='btn btn-danger btn-sm'>Hapus</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>

    <nav aria-label="Halaman">
        <ul class="pagination ">
            <?php if ($currentPage > 1) { ?>
                <li class="page-item">
                    <a class="page-link" href="daftar_peminjaman.php?page=<?php echo ($currentPage - 1); ?>">Sebelumnya</a>
                </li>
            <?php } ?>
            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                <li class="page-item <?php if ($i === $currentPage) echo 'active'; ?>">
                    <a class="page-link" href="daftar_peminjaman.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php } ?>
            <?php if ($currentPage < $totalPages) { ?>
                <li class="page-item">
                    <a class="page-link" href="daftar_peminjaman.php?page=<?php echo ($currentPage + 1); ?>">Selanjutnya</a>
                </li>
            <?php } ?>
        </ul>
    </nav>
</div>
</body>
</html>
