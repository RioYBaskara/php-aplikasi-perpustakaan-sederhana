<?php
session_start();

// Memeriksa apakah pengguna telah login dan memiliki akses sebagai petugas/admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'petugas' && $_SESSION['user_type'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
require_once 'conn.php';

// Mendapatkan daftar anggota dari database
$queryAnggota = "SELECT * FROM anggota";
$resultAnggota = mysqli_query($conn, $queryAnggota);

// Mendapatkan daftar buku yang masih tersedia dari database
$queryBuku = "SELECT * FROM buku WHERE stok > 0";
$resultBuku = mysqli_query($conn, $queryBuku);

// Penanganan saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idAnggota = $_POST['nama_anggota'];
    $idBuku = $_POST['judul_buku'];
    $tanggalPinjam = $_POST['tanggal_pinjam'];
    $tanggalKembali = $_POST['tanggal_kembali'];
    $idPetugas = $_SESSION['user_id']; // Menggunakan id_petugas dari sesi pengguna yang sedang login

    // Validasi form peminjaman
    $errors = [];

    // Validasi ID anggota
    if (empty($idAnggota)) {
        $errors[] = "Nama Anggota harus diisi";
    }

    // Validasi ID buku
    if (empty($idBuku)) {
        $errors[] = "Judul Buku harus diisi";
    }

    // Validasi tanggal pinjam
    if (empty($tanggalPinjam)) {
        $errors[] = "Tanggal Pinjam harus diisi";
    }

    // Validasi tanggal kembali
    if (empty($tanggalKembali)) {
        $errors[] = "Tanggal Kembali harus diisi";
    }

    // Cek apakah terdapat error validasi
    if (empty($errors)) {
        // Cek stok buku sebelum melakukan peminjaman
        $queryStok = "SELECT stok FROM buku WHERE judul_buku = '$idBuku'";
        $resultStok = mysqli_query($conn, $queryStok);

        if ($resultStok && mysqli_num_rows($resultStok) > 0) {
            $rowStok = mysqli_fetch_assoc($resultStok);
            $stokBuku = $rowStok['stok'];

            if ($stokBuku > 0) {
                // Mulai transaksi
                mysqli_begin_transaction($conn);

                // Lakukan proses peminjaman
                $query = "INSERT INTO peminjaman (tanggal_pinjam, tanggal_kembali, id_buku, id_anggota, id_petugas) 
                          VALUES ('$tanggalPinjam', '$tanggalKembali', 
                          (SELECT id_buku FROM buku WHERE judul_buku = '$idBuku' FOR UPDATE), 
                          (SELECT id_anggota FROM anggota WHERE nama_anggota = '$idAnggota' FOR UPDATE), 
                          $idPetugas)";

                $result = mysqli_query($conn, $query);

                if ($result) {
                    // Perbarui stok buku
                    $updateQuery = "UPDATE buku SET stok = stok - 1 WHERE judul_buku = '$idBuku'";
                    $updateResult = mysqli_query($conn, $updateQuery);

                    if ($updateResult) {
                        // Commit transaksi jika semua operasi berhasil
                        mysqli_commit($conn);

                        // Redirect ke halaman daftar peminjaman setelah peminjaman berhasil
                        header("Location: daftar_peminjaman.php");
                        exit();
                    } else {
                        // Rollback transaksi jika ada kesalahan dalam pembaruan stok buku
                        mysqli_rollback($conn);
                        echo "Error updating book stock: " . mysqli_error($conn);
                    }
                } else {
                    // Rollback transaksi jika ada kesalahan dalam operasi peminjaman
                    mysqli_rollback($conn);
                    echo "Error: " . mysqli_error($conn);
                }
            } else {
                $errors[] = "Stok buku tidak tersedia";
            }
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Peminjaman Buku</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Peminjaman Buku</h1>
        <a href="dashboard_petugas.php" class="btn btn-secondary mb-3">Kembali ke Dashboard Petugas</a>
        <form method="POST">
            <div class="form-group">
                <label for="nama_anggota">Nama Anggota</label>
                <select class="form-control" id="nama_anggota" name="nama_anggota">
                    <option value="">Pilih Nama Anggota</option>
                    <?php while ($rowAnggota = mysqli_fetch_assoc($resultAnggota)): ?>
                        <option value="<?php echo $rowAnggota['nama_anggota']; ?>"><?php echo $rowAnggota['nama_anggota']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="judul_buku">Judul Buku Yang Tersedia</label>
                <select class="form-control" id="judul_buku" name="judul_buku">
                    <option value="">Pilih Judul Buku</option>
                    <?php while ($rowBuku = mysqli_fetch_assoc($resultBuku)): ?>
                        <option value="<?php echo $rowBuku['judul_buku']; ?>"><?php echo $rowBuku['judul_buku']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="tanggal_pinjam">Tanggal Pinjam</label>
                <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam">
            </div>
            <div class="form-group">
                <label for="tanggal_kembali">Tanggal Kembali</label>
                <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mt-3">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
