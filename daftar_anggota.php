<?php
session_start();

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai petugas
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'petugas') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
require_once 'conn.php';

// Fungsi untuk mendapatkan data anggota berdasarkan ID Anggota
function getAnggota($conn, $idAnggota)
{
    $query = "SELECT * FROM anggota WHERE id_anggota = $idAnggota";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Memeriksa apakah form untuk menambah atau mengubah anggota telah disubmit
if (isset($_POST['simpan_anggota'])) {
    $idAnggota = $_POST['id_anggota'];
    $kodeAnggota = $_POST['kode_anggota'];
    $namaAnggota = $_POST['nama_anggota'];
    $jkAnggota = $_POST['jk_anggota'];
    $jurusanAnggota = $_POST['jurusan_anggota'];
    $noTelpAnggota = $_POST['no_telp_anggota'];
    $alamatAnggota = $_POST['alamat_anggota'];

    // Jika ID Anggota tidak kosong, maka ini adalah proses pengubahan anggota
    if (!empty($idAnggota)) {
        // Query untuk mengubah data anggota berdasarkan ID Anggota
        $queryUbah = "UPDATE anggota SET kode_anggota = '$kodeAnggota', nama_anggota = '$namaAnggota', jk_anggota = '$jkAnggota', jurusan_anggota = '$jurusanAnggota', no_telp_anggota = '$noTelpAnggota', alamat_anggota = '$alamatAnggota' WHERE id_anggota = $idAnggota";
        $resultUbah = mysqli_query($conn, $queryUbah);

        if ($resultUbah) {
            // Redirect ke halaman daftar anggota
            header("Location: daftar_anggota.php");
            exit();
        } else {
            // Gagal mengubah anggota, tampilkan pesan error
            $errorMessage = "Gagal mengubah anggota: " . mysqli_error($conn);
        }
    } else {
        // Jika ID Anggota kosong, maka ini adalah proses penambahan anggota
        // Query untuk menambahkan anggota baru
        $queryTambah = "INSERT INTO anggota (kode_anggota, nama_anggota, jk_anggota, jurusan_anggota, no_telp_anggota, alamat_anggota) VALUES ('$kodeAnggota', '$namaAnggota', '$jkAnggota', '$jurusanAnggota', '$noTelpAnggota', '$alamatAnggota')";
        $resultTambah = mysqli_query($conn, $queryTambah);

        if ($resultTambah) {
            // Redirect ke halaman daftar anggota
            header("Location: daftar_anggota.php");
            exit();
        } else {
            // Gagal menambahkan anggota, tampilkan pesan error
            $errorMessage = "Gagal menambahkan anggota: " . mysqli_error($conn);
        }
    }
}

// Memeriksa apakah parameter edit diberikan dalam URL
if (isset($_GET['edit'])) {
    $idAnggota = $_GET['edit'];
    $anggota = getAnggota($conn, $idAnggota);
}

// Memeriksa apakah parameter delete diberikan dalam URL
if (isset($_GET['delete'])) {
    $idAnggota = $_GET['delete'];

    // Query untuk menghapus anggota berdasarkan ID Anggota
    $queryHapus = "DELETE FROM anggota WHERE id_anggota = $idAnggota";
    $resultHapus = mysqli_query($conn, $queryHapus);

    if ($resultHapus) {
        // Redirect ke halaman daftar anggota
        header("Location: daftar_anggota.php");
        exit();
    } else {
        // Gagal menghapus anggota, tampilkan pesan error
        $errorMessage = "Gagal menghapus anggota: " . mysqli_error($conn);
    }
}

// Query untuk mengambil data anggota
$queryAnggota = "SELECT * FROM anggota";
$resultAnggota = mysqli_query($conn, $queryAnggota);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Anggota</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 50px;
            margin-bottom: 100px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Daftar Anggota</h1>
    <a href="dashboard_petugas.php" class="btn btn-secondary mb-3">Kembali ke Dashboard Petugas</a>

    <?php if (isset($errorMessage)) { ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php } ?>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Kode Anggota</th>
            <th>Nama Anggota</th>
            <th>Jenis Kelamin</th>
            <th>Jurusan</th>
            <th>No. Telepon</th>
            <th>Alamat</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($rowAnggota = mysqli_fetch_assoc($resultAnggota)) { ?>
            <tr>
                <td><?php echo $rowAnggota['kode_anggota']; ?></td>
                <td><?php echo $rowAnggota['nama_anggota']; ?></td>
                <td><?php echo $rowAnggota['jk_anggota']; ?></td>
                <td><?php echo $rowAnggota['jurusan_anggota']; ?></td>
                <td><?php echo $rowAnggota['no_telp_anggota']; ?></td>
                <td><?php echo $rowAnggota['alamat_anggota']; ?></td>
                <td>
                    <a href="daftar_anggota.php?edit=<?php echo $rowAnggota['id_anggota']; ?>"
                       class="btn btn-sm btn-primary">Edit</a>
                    <a href="daftar_anggota.php?delete=<?php echo $rowAnggota['id_anggota']; ?>"
                       class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?')">Hapus</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <h2><?php echo isset($anggota) ? 'Ubah' : 'Tambah'; ?> Anggota</h2>
    <form method="POST">
        <?php if (isset($anggota)) { ?>
            <input type="hidden" name="id_anggota" value="<?php echo $anggota['id_anggota']; ?>">
        <?php } ?>
        <div class="form-group">
            <label for="kode_anggota">Kode Anggota</label>
            <input type="text" class="form-control" id="kode_anggota" name="kode_anggota"
                   value="<?php echo isset($anggota) ? $anggota['kode_anggota'] : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="nama_anggota">Nama Anggota</label>
            <input type="text" class="form-control" id="nama_anggota" name="nama_anggota"
                   value="<?php echo isset($anggota) ? $anggota['nama_anggota'] : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="jk_anggota">Jenis Kelamin</label>
            <select class="form-control" id="jk_anggota" name="jk_anggota" required>
                <option value="1" <?php echo isset($anggota) && $anggota['jk_anggota'] == 1 ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="2" <?php echo isset($anggota) && $anggota['jk_anggota'] == 2 ? 'selected' : ''; ?>>Perempuan</option>
            </select>
        </div>
                <div class="form-group">
            <label for="jurusan_anggota">Jurusan</label>
            <select class="form-control" id="jurusan_anggota" name="jurusan_anggota" required>
                <option value="SIJA" <?php echo isset($anggota) && $anggota['jurusan_anggota'] == 'SIJA' ? 'selected' : ''; ?>>SIJA</option>
                <option value="TKJ" <?php echo isset($anggota) && $anggota['jurusan_anggota'] == 'TKJ' ? 'selected' : ''; ?>>TKJ</option>
                <option value="DPIB" <?php echo isset($anggota) && $anggota['jurusan_anggota'] == 'DPIB' ? 'selected' : ''; ?>>DPIB</option>
            </select>
        </div>
        <div class="form-group">
            <label for="no_telp_anggota">No. Telepon</label>
            <input type="text" class="form-control" id="no_telp_anggota" name="no_telp_anggota"
                   value="<?php echo isset($anggota) ? $anggota['no_telp_anggota'] : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="alamat_anggota">Alamat</label>
            <input type="text" class="form-control" id="alamat_anggota" name="alamat_anggota"
                   value="<?php echo isset($anggota) ? $anggota['alamat_anggota'] : ''; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary" name="simpan_anggota">Simpan</button>
        <a href="daftar_anggota.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
