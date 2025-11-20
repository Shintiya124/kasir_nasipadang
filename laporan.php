<?php
require 'ceklogin.php';

// ==================== KONEKSI DATABASE ====================
$c = mysqli_connect("localhost", "root", "", "kasir");
if (!$c) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemasukan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a2e0d6a64b.js" crossorigin="anonymous"></script>
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-chart-line"></i> Laporan Pemasukan</h4>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr class="text-center">
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>ID Pesanan</th>
                            <th>ID Pembayaran</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil semua data laporan, urutkan dari terbaru
                        $getlaporan = mysqli_query($c, "
                            SELECT l.*, p.bayar, p.kembalian 
                            FROM laporan l
                            LEFT JOIN pembayaran p ON l.idpembayaran = p.idpembayaran
                            ORDER BY l.tanggal DESC
                        ");

                        $totalpemasukan = 0;
                        $no = 1;

                        if (mysqli_num_rows($getlaporan) > 0) {
                            while ($l = mysqli_fetch_assoc($getlaporan)) {
                                $totalpemasukan += $l['total'];
                                $tanggal = date('d-m-Y H:i', strtotime($l['tanggal']));

                                echo "
                                <tr>
                                    <td align='center'>{$no}</td>
                                    <td>{$tanggal}</td>
                                    <td>#{$l['idpesanan']}</td>
                                    <td>#{$l['idpembayaran']}</td>
                                    <td>Rp" . number_format($l['total']) . "</td>
                                </tr>";
                                $no++;
                            }
                        } else {
                            echo "<tr><td colspan='5' align='center'>Belum ada data laporan</td></tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-success font-weight-bold">
                            <th colspan="4" class="text-right">Total Pemasukan</th>
                            <th>Rp<?= number_format($totalpemasukan); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card-footer text-muted text-center">
            <small>&copy; <?= date('Y'); ?> Aplikasi Kasir Nasi Padang</small>
        </div>
    </div>
</div>

</body>
</html>
