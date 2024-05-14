<?php
session_start();

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai petugas
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'petugas') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
require_once 'conn.php';

// Mendefinisikan pesan kesalahan awal
$errorMessage = '';

// Fungsi untuk mendapatkan judul buku berdasarkan ID buku
function getJudulBuku($conn, $idBuku)
{
    $query = "SELECT judul_buku FROM buku WHERE id_buku = $idBuku";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['judul_buku'];
    }
    return '';
}

// Fungsi untuk mendapatkan jumlah rak terisi berdasarkan ID rak
function getJumlahRakTerisi($conn, $idRak)
{
    $query = "SELECT COUNT(*) AS jumlah_terisi FROM rak WHERE id_rak = $idRak";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['jumlah_terisi'];
    }
    return 0;
}

// Menentukan jumlah data per halaman
$dataPerPage = 4;

// Menentukan halaman saat ini
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Menghitung offset data
$offset = ($currentPage - 1) * $dataPerPage;

// Mendapatkan data buku dari database
$queryBuku = "SELECT id_buku, judul_buku FROM buku LIMIT $offset, $dataPerPage";
$resultBuku = mysqli_query($conn, $queryBuku);

// Menghitung total data peminjaman
$queryTotalData = "SELECT COUNT(*) AS total_data FROM rak";
$resultTotalData = mysqli_query($conn, $queryTotalData);
$rowTotalData = mysqli_fetch_assoc($resultTotalData);
$totalData = $rowTotalData['total_data'];

// Menghitung jumlah halaman
$totalPages = ceil($totalData / $dataPerPage);

// Memeriksa apakah terdapat data buku
if ($resultBuku && mysqli_num_rows($resultBuku) > 0) {
    $bukuRows = mysqli_fetch_all($resultBuku, MYSQLI_ASSOC);
} else {
    $bukuRows = [];
}

// Memeriksa apakah form telah disubmit untuk menambahkan rak baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $namaRak = $_POST['nama_rak'];
        $lokasiRak = $_POST['lokasi_rak'];
        $idBuku = $_POST['id_buku'];

        // Memeriksa apakah ID buku yang dimasukkan valid
        $queryBuku = "SELECT * FROM buku WHERE id_buku = $idBuku";
        $resultBuku = mysqli_query($conn, $queryBuku);
        if ($resultBuku && mysqli_num_rows($resultBuku) > 0) {
            // ID buku valid, tambahkan rak baru
            $queryAddRak = "INSERT INTO rak (nama_rak, lokasi_rak, id_buku) VALUES ('$namaRak', '$lokasiRak', $idBuku)";
            $resultAddRak = mysqli_query($conn, $queryAddRak);

            if ($resultAddRak) {
                // Rak berhasil ditambahkan, refresh halaman
                header("Location: daftar_rak.php");
                exit();
            } else {
                $errorMessage = 'Terjadi kesalahan saat menambahkan rak. Silakan coba lagi.';
            }
        } else {
            $errorMessage = 'ID buku tidak valid.';
        }
    } elseif ($action === 'edit') {
        $idRak = $_POST['id_rak'];
        $namaRak = $_POST['edit_nama_rak'];
        $lokasiRak = $_POST['edit_lokasi_rak'];
        $idBuku = $_POST['edit_id_buku'];

        // Memeriksa apakah ID buku yang dimasukkan valid
        $queryBuku = "SELECT * FROM buku WHERE id_buku = $idBuku";
        $resultBuku = mysqli_query($conn, $queryBuku);
        if ($resultBuku && mysqli_num_rows($resultBuku) > 0) {
            // ID buku valid, update data rak
            $queryEditRak = "UPDATE rak SET nama_rak = '$namaRak', lokasi_rak = '$lokasiRak', id_buku = $idBuku WHERE id_rak = $idRak";
            $resultEditRak = mysqli_query($conn, $queryEditRak);

            if ($resultEditRak) {
                // Data rak berhasil diubah, refresh halaman
                header("Location: daftar_rak.php");
                exit();
            } else {
                $errorMessage = 'Terjadi kesalahan saat mengedit rak. Silakan coba lagi.';
            }
        } else {
            $errorMessage = 'ID buku tidak valid.';
        }
    } elseif ($action === 'delete') {
        $idRak = $_POST['id_rak'];

        // Hapus data rak dari database
        $queryDeleteRak = "DELETE FROM rak WHERE id_rak = $idRak";
        $resultDeleteRak = mysqli_query($conn, $queryDeleteRak);

        if ($resultDeleteRak) {
            // Data rak berhasil dihapus, refresh halaman
            header("Location: daftar_rak.php");
            exit();
        } else {
            $errorMessage = 'Terjadi kesalahan saat menghapus rak. Silakan coba lagi.';
        }
    }
}

// Mendapatkan data rak dari database
$querypagination = "SELECT * FROM rak LIMIT $offset, $dataPerPage";;
$result = mysqli_query($conn, $querypagination);

// Memeriksa apakah terdapat data rak
if ($result && mysqli_num_rows($result) > 0) {
    $rakRows = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $rakRows = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Rak</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 50px;
        }

        .error-message {
            color: red;
        }

        h2 {
            margin-top: 50px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Daftar Rak</h1>
    <?php if (!empty($errorMessage)) { ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php } ?>
    <form method="POST" id="addForm">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="nama_rak">Nama Rak</label>
                <input type="text" class="form-control" id="nama_rak" name="nama_rak" required>
            </div>
            <div class="form-group col-md-4">
                <label for="lokasi_rak">Lokasi Rak</label>
                <input type="text" class="form-control" id="lokasi_rak" name="lokasi_rak" required>
            </div>
            <div class="form-group col-md-4">
                <label for="id_buku">ID Buku</label>
                <select class="form-control" id="id_buku" name="id_buku" required>
                    <?php foreach ($bukuRows as $buku) { ?>
                        <option value="<?php echo $buku['id_buku']; ?>"><?php echo $buku['judul_buku']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <input type="hidden" name="action" value="add">
        <button type="submit" class="btn btn-primary">Tambah Rak</button>
        <a href="dashboard_petugas.php" class="btn btn-secondary">Kembali ke Dashboard Petugas</a>
    </form>
                        
    <h2>Data Rak</h2>
    <table class="table">
        <thead>
        <tr>
            <th>ID Rak</th>
            <th>Nama Rak</th>
            <th>Lokasi Rak</th>
            <th>ID Buku</th>
            <th>Judul Buku</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rakRows as $rak) { ?>
            <tr>
                <td><?php echo $rak['id_rak']; ?></td>
                <td><?php echo $rak['nama_rak']; ?></td>
                <td><?php echo $rak['lokasi_rak']; ?></td>
                <td><?php echo $rak['id_buku']; ?></td>
                <td><?php echo getJudulBuku($conn, $rak['id_buku']); ?></td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                            data-target="#editModal<?php echo $rak['id_rak']; ?>">Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                            data-target="#deleteModal<?php echo $rak['id_rak']; ?>">Hapus
                    </button>
                </td>
            </tr>

            <!-- Modal Edit -->
            <div class="modal fade" id="editModal<?php echo $rak['id_rak']; ?>" tabindex="-1" role="dialog"
                 aria-labelledby="editModalLabel<?php echo $rak['id_rak']; ?>" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel<?php echo $rak['id_rak']; ?>">Edit Rak</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="edit_nama_rak">Nama Rak</label>
                                    <input type="text" class="form-control" id="edit_nama_rak"
                                           name="edit_nama_rak" value="<?php echo $rak['nama_rak']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_lokasi_rak">Lokasi Rak</label>
                                    <input type="text" class="form-control" id="edit_lokasi_rak"
                                           name="edit_lokasi_rak" value="<?php echo $rak['lokasi_rak']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_id_buku">ID Buku</label>
                                    <select class="form-control" id="edit_id_buku" name="edit_id_buku" required>
                                        <?php foreach ($bukuRows as $buku) { ?>
                                            <option value="<?php echo $buku['id_buku']; ?>" <?php if ($buku['id_buku'] === $rak['id_buku']) echo 'selected'; ?>><?php echo $buku['judul_buku']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" name="id_rak" value="<?php echo $rak['id_rak']; ?>">
                                <input type="hidden" name="action" value="edit">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Delete -->
            <div class="modal fade" id="deleteModal<?php echo $rak['id_rak']; ?>" tabindex="-1" role="dialog"
                 aria-labelledby="deleteModalLabel<?php echo $rak['id_rak']; ?>" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel<?php echo $rak['id_rak']; ?>">Hapus Rak</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                Apakah Anda yakin ingin menghapus rak ini?
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" name="id_rak" value="<?php echo $rak['id_rak']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-danger">Hapus</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
        </tbody>
    </table>
    <nav aria-label="Halaman">
        <ul class="pagination ">
            <?php if ($currentPage > 1) { ?>
                <li class="page-item">
                    <a class="page-link" href="daftar_rak.php?page=<?php echo ($currentPage - 1); ?>">Sebelumnya</a>
                </li>
            <?php } ?>
            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                <li class="page-item <?php if ($i === $currentPage) echo 'active'; ?>">
                    <a class="page-link" href="daftar_rak.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php } ?>
            <?php if ($currentPage < $totalPages) { ?>
                <li class="page-item">
                    <a class="page-link" href="daftar_rak.php?page=<?php echo ($currentPage + 1); ?>">Selanjutnya</a>
                </li>
            <?php } ?>
        </ul>
    </nav>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
