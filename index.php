<?php
session_start();

// ==================== KONEKSI DATABASE ====================
$c = mysqli_connect("localhost","root","","kasir");
if (!$c) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

require 'ceklogin.php'; // pastikan user sudah login


// ==================== TAMBAH PESANAN BARU ====================
if (isset($_POST['tambahpesanan'])) {
    $idpelanggan = intval($_POST['idpelanggan']); // diperbaiki: pakai intval untuk keamanan

    $insert = mysqli_query($c, "INSERT INTO pesanan (idpelanggan, tanggal) 
                                VALUES ('$idpelanggan', NOW())");

    if ($insert) {
        echo "<script>alert('Pesanan baru berhasil ditambahkan'); window.location.href='index.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menambah pesanan baru: ".mysqli_error($c)."'); window.location.href='index.php';</script>";
        exit;
    }
}

// ==================== HAPUS PESANAN ====================
if (isset($_GET['delete'])) {  // diperbaiki: sebelumnya tidak ada handler delete
    $idorder = intval($_GET['delete']);

    // Hapus detail pesanan dulu
    mysqli_query($c, "DELETE FROM detailpesanan WHERE idpesanan='$idorder'");

    // Hapus pesanan utama
    $hapus = mysqli_query($c, "DELETE FROM pesanan WHERE idpesanan='$idorder'");

    if ($hapus) {
        echo "<script>alert('Pesanan berhasil dihapus'); window.location.href='index.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menghapus pesanan: ".mysqli_error($c)."'); window.location.href='index.php';</script>";
        exit;
    }
}

// ==================== HITUNG JUMLAH PESANAN ====================
$h1 = mysqli_query($c, "SELECT * FROM pesanan");
$h2 = mysqli_num_rows($h1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
<title>Data Pesanan</title>
<link href="css/styles.css" rel="stylesheet" />
<link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous">
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand" href="index.php">Aplikasi Kasir</a>
    <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle"><i class="fas fa-bars"></i></button>
</nav>

<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
            <div class="nav">
                        <div class="sb-sidenav-menu-heading">menu</div>
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            order
                        </a>
                        <a class="nav-link" href="stock.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                             menu
                        </a>
                        <a class="nav-link" href="masuk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            stok menu
                        </a>
                        <a class="nav-link" href="pelanggan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            kelola pelanggan
                        </a>
                        <a class="nav-link" href="logout.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                            logout
                        </a>
                    </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                User
            </div>
        </nav>
    </div>

    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid">
                <h1 class="mt-4">Data Pesanan</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Selamat datang</li>
                </ol>

                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary text-white mb-4">
                            <div class="card-body">Jumlah Pesanan: <?=$h2;?></div>
                        </div>
                    </div>
                </div>

                <!-- Button to Open the Modal -->
                <button type="button" class="btn btn-info mb-4" data-toggle="modal" data-target="#modalTambahPesanan">
                    Tambah Pesanan Baru
                </button>

                <div class="card mb-4">
                    <div class="card-header">Data Pesanan</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>Tanggal</th>
                                        <th>Nama Pelanggan</th>
                                        <th>Jumlah Item</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $get = mysqli_query($c,"SELECT p.*, pl.namapelanggan, pl.alamat 
                                                        FROM pesanan p 
                                                        JOIN pelanggan pl ON p.idpelanggan=pl.id_pelanggan 
                                                        ORDER BY p.tanggal DESC");
                                while($p=mysqli_fetch_array($get)){
                                    $idorder = $p['idpesanan'];
                                    $tanggal = $p['tanggal'];
                                    $namapelanggan = $p['namapelanggan'];
                                    $alamat = $p['alamat'];

                                    $hitungjumlah = mysqli_query($c, "SELECT * FROM detailpesanan WHERE idpesanan='$idorder'");
                                    $jumlah = mysqli_num_rows($hitungjumlah);
                                ?>
                                    <tr>
                                        <td><?=$idorder;?></td>
                                        <td><?=$tanggal;?></td>
                                        <td><?=$namapelanggan;?> - <?=$alamat;?></td>
                                        <td><?=$jumlah;?></td>
                                        <td>
                                            <a href="view.php?idp=<?=$idorder;?>" class="btn btn-primary" target="_blank">Tampilkan</a>
                                            <a href="index.php?delete=<?=$idorder;?>" class="btn btn-danger" onclick="return confirm('Yakin hapus pesanan ini?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="py-4 bg-light mt-auto">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small">
                    <div class="text-muted">Copyright &copy; 2023</div>
                    <div>
                        <a href="#">Privacy Policy</a> &middot;
                        <a href="#">Terms &amp; Conditions</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable();
});
</script>
</body>

<!-- Modal Tambah Pesanan -->
<div class="modal fade" id="modalTambahPesanan">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="index.php">
                <div class="modal-header">
                    <h4 class="modal-title">tambah pesanan</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    Pilih Pelanggan
                    <select name="idpelanggan" class="form-control" required>
                        <?php
                        $getpelanggan = mysqli_query($c,"SELECT * FROM pelanggan ORDER BY namapelanggan");
                        while($pl=mysqli_fetch_array($getpelanggan)){
                            $namapelanggan = $pl['namapelanggan'];
                            $idpelanggan = $pl['id_pelanggan'];
                            $alamat = $pl['alamat'];
                        ?>
                        <option value="<?=$idpelanggan;?>"><?=$namapelanggan;?> - <?=$alamat;?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" name="tambahpesanan">Submit</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
</html>
