<?php
session_start();

// Fungsi untuk mendapatkan status peminjaman
function getPeminjamanStatus($tanggalKembali) {
    return ($tanggalKembali < date('Y-m-d')) ? 'Sudah Dikembalikan' : 'Masih Dipinjam';
}

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai petugas/admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'petugas' && $_SESSION['user_type'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
require_once 'conn.php';

// Menentukan jumlah data per halaman
$dataPerPage = 4;

// Menentukan halaman saat ini
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Menghitung offset data
$offset = ($currentPage - 1) * $dataPerPage;

// Mendapatkan daftar petugas dari database
$queryPetugas = "SELECT * FROM petugas";
$resultPetugas = mysqli_query($conn, $queryPetugas);

// Mendapatkan daftar anggota dari database
$queryAnggota = "SELECT * FROM anggota";
$resultAnggota = mysqli_query($conn, $queryAnggota);

// Mendapatkan daftar buku yang masih tersedia dari database
$queryBuku = "SELECT * FROM buku WHERE stok > 0";
$resultBuku = mysqli_query($conn, $queryBuku);

// Mendapatkan daftar pengembalian dengan batasan data per halaman
$query = "SELECT p.*, b.judul_buku, a.nama_anggota, pt.nama_petugas
          FROM pengembalian p
          INNER JOIN buku b ON p.id_buku = b.id_buku
          INNER JOIN anggota a ON p.id_anggota = a.id_anggota
          INNER JOIN petugas pt ON p.id_petugas = pt.id_petugas
          LIMIT $offset, $dataPerPage";
$result = mysqli_query($conn, $query);

// Memeriksa apakah query berhasil dieksekusi
if ($result) {
    // Memeriksa apakah terdapat data pengembalian
    if (mysqli_num_rows($result) > 0) {
        $daftarPengembalian = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $daftarPengembalian = [];
    }
} else {
    echo "Error: " . mysqli_error($conn);
    exit();
}

// Proses Tambah Pengembalian
if (isset($_POST['tambah_pengembalian'])) {
    $tanggalPengembalian = $_POST['tanggal_pengembalian'];
    $denda = $_POST['denda'];
    $idBuku = $_POST['id_buku'];
    $idAnggota = $_POST['id_anggota'];
    $idPetugas = $_POST['id_petugas'];

     // Validasi form peminjaman
    $errors = [];

    // Validasi ID petugas
    if (empty($idPetugas)) {
        $errors[] = "Nama Petugas harus diisi";
    }

    // Validasi ID anggota
    if (empty($idAnggota)) {
        $errors[] = "Nama Anggota harus diisi";
    }

    // Validasi ID buku
    if (empty($idBuku)) {
        $errors[] = "Judul Buku harus diisi";
    }

    // Validasi Denda
    if (empty($denda)) {
        $errors[] = "Denda harus diisi";
    }

    // Validasi ID anggota
    if (empty($tanggalPengembalian)) {
        $errors[] = "tanggal pengembalian harus diisi";
    }

    $queryInsert = "INSERT INTO pengembalian (id_buku, id_anggota, id_petugas, tanggal_pengembalian, denda) 
                    VALUES ('$idBuku', '$idAnggota', '$idPetugas', '$tanggalPengembalian', '$denda')";

    if (empty($errors)) {
        // Eksekusi query INSERT
        if (mysqli_query($conn, $queryInsert)) {
            header("Location: pengembalian.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
            exit();
        }
    }
}

// Proses Edit Pengembalian
if (isset($_POST['edit_pengembalian'])) {
    $idPengembalian = $_POST['edit_id_pengembalian'];
    $tanggalPengembalian = $_POST['edit_tanggal_pengembalian'];
    $denda = $_POST['edit_denda'];

    $queryUpdate = "UPDATE pengembalian SET tanggal_pengembalian='$tanggalPengembalian', denda='$denda' WHERE id_pengembalian='$idPengembalian'";

    if (mysqli_query($conn, $queryUpdate)) {
        header("Location: pengembalian.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
        exit();
    }
}

// Proses Hapus Pengembalian
if (isset($_POST['hapus_pengembalian'])) {
    $idPengembalian = $_POST['hapus_id_pengembalian'];

    $queryDelete = "DELETE FROM pengembalian WHERE id_pengembalian='$idPengembalian'";

    if (mysqli_query($conn, $queryDelete)) {
        header("Location: pengembalian.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
        exit();
    }
}

// Menghitung total data peminjaman
$queryTotalData = "SELECT COUNT(*) AS total_data FROM pengembalian";
$resultTotalData = mysqli_query($conn, $queryTotalData);
$rowTotalData = mysqli_fetch_assoc($resultTotalData);
$totalData = $rowTotalData['total_data'];

// Menghitung jumlah halaman
$totalPages = ceil($totalData / $dataPerPage);


mysqli_close($conn);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Pengembalian Buku</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        .container {
            margin-top: 50px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Pengembalian Buku</h1>
        <a href="dashboard_petugas.php" class="btn btn-secondary mb-3">Kembali ke Dashboard Petugas</a>
        <!-- Tombol Tambah Pengembalian -->
        <button type="button" class="btn btn-primary mb-2" data-toggle="modal" data-target="#tambahModal">Tambah Pengembalian</button>

        <!-- Tabel Daftar Pengembalian -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Pengembalian</th>
                    <th>Judul Buku</th>
                    <th>Nama Anggota</th>
                    <th>Nama Petugas</th>
                    <th>Tanggal Pengembalian</th>
                    <th>Denda</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftarPengembalian as $pengembalian) { ?>
                    <tr>
                        <td><?php echo $pengembalian['id_pengembalian']; ?></td>
                        <td><?php echo $pengembalian['judul_buku']; ?></td>
                        <td><?php echo $pengembalian['nama_anggota']; ?></td>
                        <td><?php echo $pengembalian['nama_petugas']; ?></td>
                        <td><?php echo $pengembalian['tanggal_pengembalian']; ?></td>
                        <td><?php echo $pengembalian['denda']; ?></td>
                        <td><?php echo getPeminjamanStatus($pengembalian['tanggal_pengembalian']); ?></td>
                        <td>
                            <!-- Tombol Edit -->
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal<?php echo $pengembalian['id_pengembalian']; ?>">Edit</button>

                            <!-- Tombol Hapus -->
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#hapusModal<?php echo $pengembalian['id_pengembalian']; ?>">Hapus</button>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal<?php echo $pengembalian['id_pengembalian']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $pengembalian['id_pengembalian']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel<?php echo $pengembalian['id_pengembalian']; ?>">Edit Pengembalian</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="edit_id_pengembalian" value="<?php echo $pengembalian['id_pengembalian']; ?>">
                                        <div class="form-group">
                                            <label for="edit_tanggal_pengembalian">Tanggal Pengembalian</label>
                                            <input type="date" class="form-control" id="edit_tanggal_pengembalian" name="edit_tanggal_pengembalian" value="<?php echo $pengembalian['tanggal_pengembalian']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_denda">Denda</label>
                                            <input type="text" class="form-control" id="edit_denda" name="edit_denda" value="<?php echo $pengembalian['denda']; ?>" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary" name="edit_pengembalian">Simpan</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Hapus -->
                    <div class="modal fade" id="hapusModal<?php echo $pengembalian['id_pengembalian']; ?>" tabindex="-1" role="dialog" aria-labelledby="hapusModalLabel<?php echo $pengembalian['id_pengembalian']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="hapusModalLabel<?php echo $pengembalian['id_pengembalian']; ?>">Hapus Pengembalian</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="hapus_id_pengembalian" value="<?php echo $pengembalian['id_pengembalian']; ?>">
                                        <p>Apakah Anda yakin ingin menghapus pengembalian dengan ID <?php echo $pengembalian['id_pengembalian']; ?>?</p>
                                        <button type="submit" class="btn btn-danger" name="hapus_pengembalian">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </tbody>
        </table>
        <nav aria-label="Halaman">
    <ul class="pagination">
        <?php if ($currentPage > 1) { ?>
            <li class="page-item">
                <a class="page-link" href="pengembalian.php?page=<?php echo ($currentPage - 1); ?>">Sebelumnya</a>
            </li>
        <?php } ?>
        <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
            <li class="page-item <?php if ($i === $currentPage) echo 'active'; ?>">
                <a class="page-link" href="pengembalian.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php } ?>
        <?php if ($currentPage < $totalPages) { ?>
            <li class="page-item">
                <a class="page-link" href="pengembalian.php?page=<?php echo ($currentPage + 1); ?>">Selanjutnya</a>
            </li>
        <?php } ?>
    </ul>
</nav>

    </div>

    <!-- Modal Tambah Pengembalian -->
    <div class="modal fade" id="tambahModal" tabindex="-1" role="dialog" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahModalLabel">Tambah Pengembalian</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="id_buku">Judul Buku</label>
                            <select class="form-control" id="id_buku" name="id_buku" required>
                                <option value="">Pilih Judul Buku</option>
                                <?php while ($rowBuku = mysqli_fetch_assoc($resultBuku)) { ?>
                                    <option value="<?php echo $rowBuku['id_buku']; ?>"><?php echo $rowBuku['judul_buku']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_anggota">Nama Anggota</label>
                            <select class="form-control" id="id_anggota" name="id_anggota" required>
                                <option value="">Pilih Nama Anggota</option>
                                <?php while ($rowAnggota = mysqli_fetch_assoc($resultAnggota)) { ?>
                                    <option value="<?php echo $rowAnggota['id_anggota']; ?>"><?php echo $rowAnggota['nama_anggota']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="id_petugas">Nama Petugas</label>
                            <select class="form-control" id="id_petugas" name="id_petugas" required>
                                <option value="">Pilih Nama Petugas</option>
                                <?php while ($rowPetugas = mysqli_fetch_assoc($resultPetugas)) { ?>
                                    <option value="<?php echo $rowPetugas['id_petugas']; ?>"><?php echo $rowPetugas['nama_petugas']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_pengembalian">Tanggal Pengembalian</label>
                            <input type="date" class="form-control" id="tanggal_pengembalian" name="tanggal_pengembalian" required>
                        </div>
                        <div class="form-group">
                            <label for="denda">Denda</label>
                            <input type="text" class="form-control" id="denda" name="denda" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="tambah_pengembalian">Tambah</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
