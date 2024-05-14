<?php 
 
session_start();
 
if (!isset($_SESSION['nama_anggota'])) {
    header("Location: index.php");
}
 
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
 
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Berhasil Login</title>
</head>
<body>
    <div class="container-logout">
        <form action="" method="POST" class="login-username">
            <?php echo "<h1>Selamat Datang, " . $_SESSION['nama_anggota'] ."!". "</h1>"; ?>
             
            <div class="input-group">
            <a href="index.php?alert=logout" class="btn">Halaman Login</a>
            <a href="logout.php" class="btn">Logout</a>
            </div>
        </form>
    </div>
</body>
</html>