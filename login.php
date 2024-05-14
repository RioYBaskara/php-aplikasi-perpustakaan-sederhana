<?php
session_start();

// Memeriksa apakah pengguna telah berhasil login sebelumnya
if(isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
  // Pengguna telah login, alihkan ke dashboard sesuai tipe pengguna
  if($_SESSION['user_type'] === 'anggota') {
    header("Location: dashboard_anggota.php");
    exit();
  } else if($_SESSION['user_type'] === 'petugas') {
    header("Location: dashboard_petugas.php");
    exit();
  }
}

// Memeriksa apakah form login telah dikirim
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Mengimpor file koneksi database
  require_once 'conn.php';

  // Mengambil data form
  $username = $_POST['username'];
  $password = $_POST['password'];
  $userType = $_POST['user_type'];

  // Menentukan tabel dan kolom yang sesuai dengan tipe pengguna
  $tableName = ($userType === 'anggota') ? 'anggota' : 'petugas';
  $idColumn = ($userType === 'anggota') ? 'id_anggota' : 'id_petugas';
  $nameColumn = ($userType === 'anggota') ? 'nama_anggota' : 'nama_petugas';

  // Membuat query untuk memeriksa keberadaan pengguna dengan username dan password yang benar
  $query = "SELECT $idColumn, $nameColumn FROM $tableName WHERE $nameColumn = '$username' AND $idColumn = '$password'";
  $result = mysqli_query($conn, $query);

  if($result && mysqli_num_rows($result) > 0) {
    // Login berhasil, simpan informasi pengguna dalam sesi
    $user = mysqli_fetch_assoc($result);
    $_SESSION['user_id'] = $user[$idColumn];
    $_SESSION['user_type'] = $userType;

    // Alihkan ke dashboard sesuai tipe pengguna
    if($userType === 'anggota') {
      header("Location: dashboard_anggota.php");
      exit();
    } else if($userType === 'petugas') {
      header("Location: dashboard_petugas.php");
      exit();
    }
  } else {
    $errorMessage = "Nama dan ID salah, ulangi";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Halaman Login</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body {
      background-color: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    .card {
      width: 400px;
    }
    .card-header {
      text-align: center;
      background-color: #007bff;
      color: #fff;
      font-weight: bold;
    }
    .card-body {
      padding: 20px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .btn-login {
      width: 100%;
    }
    .error-message {
      color: red;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="card-header">
      Halaman Login
    </div>
    <div class="card-body">
      <form method="POST">
        <div class="form-group">
          <label for="username">Nama</label>
          <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
          <label for="password">ID</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group">
          <label for="user_type">Tipe Pengguna</label>
          <select class="form-control" id="user_type" name="user_type" required>
            <option value="anggota">Anggota</option>
            <option value="petugas">Petugas</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-login">Login</button>
        <?php if(isset($errorMessage)): ?>
          <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
      </form>
    </div>
  </div>
</body>
</html>
