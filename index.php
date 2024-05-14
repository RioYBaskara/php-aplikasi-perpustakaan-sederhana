<?php
	// Kode untuk memeriksa apakah pengguna sudah login atau belum
	session_start();
	if (!isset($_SESSION["username"])) {
		header("Location: login.php");
		exit();
	}

	// Kode untuk mengambil data buku dari database
	$servername = "localhost";
	$username = "root";
	$password = "password";
	$dbname = "pp";

	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$sql = "SELECT * FROM buku";
	$result = $conn->query($sql);

	$buku = array();
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			$buku[] = $row;
		}
	}

	$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
	<title>Aplikasi Perpustakaan - Dashboard</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
	<style>
		.container {
			margin-top: 50px;
		}
	</style>
</head>
<body>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<a class="navbar-brand" href="#">Aplikasi Perpustakaan</a>
		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav ml-auto">
				<li class="nav-item">
					<a class="nav-link" href="logout.php">Logout</a>
					<!-- Kode logout.php untuk menghapus session dan mengarahkan pengguna kembali ke halaman login -->
				</li>
			</ul>
		</div>
	</nav>

	<div class="container">
		<h3>Daftar Buku</h3>
		<hr>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Kode Buku</th>
					<th>Judul Buku</th>
					<th>Penulis</th>
					<th>Penerbit</th>
					<th>Tahun Terbit</th>
					<th>Stok</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($buku as $row) { ?>
					<tr>
						<td><?php echo $row["kode_buku"]; ?></td>
						<td><?php echo $row["judul_buku"]; ?></td>
						<td><?php echo $row["penulis_buku"]; ?></td>
						<td><?php echo $row["penerbit_buku"]; ?></td>
						<td><?php echo $row["tahun_penerbit"]; ?></td>
						<td><?php echo $row["stok"]; ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</body>
</html>
s