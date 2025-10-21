<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// ==================== KONEKSI DATABASE ====================
$c = mysqli_connect("localhost", "root", "", "kasir");
if (!$c) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

/**
 * Helper untuk upload gambar
 * @param string $field - nama input file (contoh 'gambar')
 * @return array ['success' => bool, 'filename' => string|null, 'error' => string|null]
 */
function handle_upload($field = 'gambar') {
    $result = ['success' => false, 'filename' => null, 'error' => null];

    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        // tidak ada file diupload
        $result['success'] = true;
        $result['filename'] = null;
        return $result;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'Upload error code: ' . $file['error'];
        return $result;
    }

    // validasi ukuran (limit 5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        $result['error'] = 'File terlalu besar, maksimal 5MB.';
        return $result;
    }

    // validasi tipe (image)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    if (!in_array($mime, $allowed)) {
        $result['error'] = 'Tipe file tidak diizinkan. Hanya JPG/PNG/GIF/WEBP.';
        return $result;
    }

    $targetDir = __DIR__ . "/js/image/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // gunakan nama file yang unik
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetPath = $targetDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $result['error'] = 'Gagal memindahkan file ke folder tujuan.';
        return $result;
    }

    $result['success'] = true;
    $result['filename'] = $safeName;
    return $result;
}

/* ==================== LOGIN ==================== */
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($c, $_POST['username']);
    $password = md5($_POST['password']);

    $check  = mysqli_query($c, "SELECT * FROM user WHERE username='$username' AND password='$password'");
    $hitung = mysqli_num_rows($check);

    if ($hitung > 0) {
        $_SESSION['login'] = true;
        header('location:index.php');
        exit;
    } else {
        echo '<script>alert("Username atau Password salah"); window.location.href="login.php"</script>';
        exit;
    }
}

/* ==================== TAMBAH MENU ==================== */
if (isset($_POST['tambahmenu'])) {
    $namamakanan = mysqli_real_escape_string($c, $_POST['namamenu']);
    $deskripsi   = mysqli_real_escape_string($c, $_POST['deskripsi']);
    $stock       = intval($_POST['stock']);
    $harga       = intval($_POST['harga']);

    // handle upload
    $upload = handle_upload('gambar');
    if ($upload['success'] === false) {
        echo '<script>alert("Gagal upload gambar: ' . addslashes($upload['error']) . '"); window.location.href="stock.php";</script>';
        exit;
    }

    // jika tidak upload file, gunakan default noimage.png
    $gambar = $upload['filename'] ?? 'noimage.png';
    if ($gambar === null) $gambar = 'noimage.png';

    $sql = "INSERT INTO makanan (namamakanan, deskripsi, stock, harga, foto) 
            VALUES ('$namamakanan','$deskripsi','$stock','$harga','$gambar')";

    // NOTE: kolom foto di db saya asumsikan bernama 'foto' — ubah jika nama kolom berbeda (sebelumnya kamu pakai 'gambar' di beberapa tempat)
    $insert = mysqli_query($c, $sql);

    if ($insert) {
        header('location:stock.php');
        exit;
    } else {
        // jika gagal, hapus file yang baru diupload (jika bukan default)
        if (!empty($upload['filename']) && file_exists(__DIR__ . "/js/image/" . $upload['filename'])) {
            unlink(__DIR__ . "/js/image/" . $upload['filename']);
        }
        die("Query error: " . mysqli_error($c) . "<br>SQL: " . $sql);
    }
}

/* ==================== EDIT MENU (dengan gambar & stok) ==================== */
if (isset($_POST['editmenu'])) {
    $idm         = intval($_POST['idm']);
    $namamakanan = mysqli_real_escape_string($c, $_POST['namamenu']);
    $deskripsi   = mysqli_real_escape_string($c, $_POST['deskripsi']);
    $harga       = intval($_POST['harga']);
    $stock       = intval($_POST['stock']);

    // Ambil data lama (kolom foto)
    $q = mysqli_query($c, "SELECT foto FROM makanan WHERE idmakanan='$idm' LIMIT 1");
    $old = mysqli_fetch_assoc($q);
    $foto_lama = $old ? $old['foto'] : null;

    // handle upload (jika ada)
    $upload = handle_upload('gambar');
    if ($upload['success'] === false) {
        echo '<script>alert("Gagal upload gambar: ' . addslashes($upload['error']) . '"); window.location.href="stock.php";</script>';
        exit;
    }

    $foto_baru = $foto_lama; // default tetap gambar lama
    if (!empty($upload['filename'])) {
        $foto_baru = $upload['filename'];

        // hapus file lama jika bukan default
        if (!empty($foto_lama) && $foto_lama !== 'noimage.png' && file_exists(__DIR__ . "/js/image/" . $foto_lama)) {
            @unlink(__DIR__ . "/js/image/" . $foto_lama);
        }
    }

    $update_sql = "
        UPDATE makanan 
        SET namamakanan='" . mysqli_real_escape_string($c, $namamakanan) . "',
            deskripsi='" . mysqli_real_escape_string($c, $deskripsi) . "',
            harga='$harga',
            stock='$stock',
            foto='" . mysqli_real_escape_string($c, $foto_baru) . "'
        WHERE idmakanan='$idm'
    ";

    $update = mysqli_query($c, $update_sql);

    if ($update) {
        echo '<script>alert("Menu berhasil diperbarui"); window.location.href="stock.php";</script>';
        exit;
    } else {
        // jika gagal, hapus file yang baru diupload (jika ada)
        if (!empty($upload['filename']) && file_exists(__DIR__ . "/js/image/" . $upload['filename'])) {
            @unlink(__DIR__ . "/js/image/" . $upload['filename']);
        }
        echo '<script>alert("Gagal memperbarui menu: ' . mysqli_real_escape_string($c, mysqli_error($c)) . '"); window.location.href="stock.php";</script>';
        exit;
    }
}

/* ==================== TAMBAH PELANGGAN ==================== */
if (isset($_POST['tambahpelanggan'])) {
    $namapelanggan = mysqli_real_escape_string($c, $_POST['namapelanggan']);
    $telepon       = mysqli_real_escape_string($c, $_POST['telepon']);
    $alamat        = mysqli_real_escape_string($c, $_POST['alamat']);

    $insert = mysqli_query($c, "INSERT INTO pelanggan (namapelanggan, telepon, alamat) 
                                VALUES ('$namapelanggan', '$telepon', '$alamat')");

    if ($insert) {
        header('location:pelanggan.php');
        exit;
    } else {
        echo '<script>alert("Gagal menambah pelanggan baru"); window.location.href="pelanggan.php"</script>';
        exit;
    }
}

/* ==================== TAMBAH PESANAN ==================== */
if (isset($_POST['tambahpesanan'])) {
    $idpelanggan = intval($_POST['idpelanggan']);
    $insert = mysqli_query($c, "INSERT INTO pesanan (idpelanggan, tanggal) VALUES ($idpelanggan, NOW())");
    if ($insert) {
        header('location:index.php');
        exit;
    } else {
        echo '<script>alert("Gagal menambah pesanan baru: '.mysqli_error($c).'"); window.location.href="index.php"</script>';
        exit;
    }
}

/* ==================== TAMBAH DETAIL PESANAN ==================== */
if (isset($_POST['addmakanan'])) {
    $idmakanan = intval($_POST['idmakanan']);
    $idp       = intval($_POST['idp']);
    $qty       = intval($_POST['qty']);

    $cekStock = mysqli_query($c, "SELECT stock FROM makanan WHERE idmakanan='$idmakanan'");
    $dataStock = mysqli_fetch_array($cekStock);
    $stocksekarang = intval($dataStock['stock']);

    if ($stocksekarang >= $qty) {
        $selisih = $stocksekarang - $qty;
        $insert = mysqli_query($c, "INSERT INTO detailpesanan (idpesanan, idmakanan, qty) VALUES ('$idp','$idmakanan','$qty')");
        $update = mysqli_query($c, "UPDATE makanan SET stock='$selisih' WHERE idmakanan='$idmakanan'");
        if ($insert && $update) {
            header('location:view.php?idp=' . $idp);
            exit;
        } else {
            echo '<script>alert("Gagal menambah detail pesanan"); window.location.href="view.php?idp=' . $idp . '"</script>';
            exit;
        }
    } else {
        echo '<script>alert("Stock menu tidak cukup"); window.location.href="view.php?idp=' . $idp . '"</script>';
        exit;
    }
}

/* ==================== MENU MASUK (menambah stok) ==================== */
if (isset($_POST['menumasuk'])) {
    $idmakanan = intval($_POST['idmakanan']);
    $qty       = intval($_POST['qty']);

    $caristock = mysqli_query($c,"SELECT stock FROM makanan WHERE idmakanan='$idmakanan'");
    $caristock2 = mysqli_fetch_array($caristock);
    $stocksekarang = intval($caristock2['stock']);
    $newstock = $stocksekarang + $qty;

    $insertmenumasuk = mysqli_query($c, "INSERT INTO masuk (idmakanan, qty) VALUES('$idmakanan','$qty')");
    $updatemenumasuk = mysqli_query($c, "UPDATE makanan SET stock='$newstock' WHERE idmakanan='$idmakanan'");

    if ($insertmenumasuk && $updatemenumasuk) {
        header('location:masuk.php');
        exit;
    } else {
        echo '<script>alert("Gagal menambah menu masuk: '.mysqli_error($c).'"); window.location.href="masuk.php"</script>';
        exit;
    }
}

/* ==================== EDIT DATA MASUK (STOK) ==================== */
if (isset($_POST['editdatamenumasuk'])) {
    $qty = intval($_POST['qty']);
    $idmasuk = intval($_POST['idmasuk']);
    $idmakanan = intval($_POST['idmakanan']);

    $caritahu = mysqli_query($c,"SELECT * FROM masuk WHERE idmasuk='$idmasuk'");
    $caritahu2 = mysqli_fetch_array($caritahu);
    $qtysekarang = intval($caritahu2['qty']);

    $caristock = mysqli_query($c,"SELECT * FROM makanan WHERE idmakanan='$idmakanan'");
    $caristock2 = mysqli_fetch_array($caristock);
    $stocksekarang = intval($caristock2['stock']);

    if($qty >= $qtysekarang){
        $selisih = $qty - $qtysekarang;
        $newstock = $stocksekarang + $selisih;
    } else {
        $selisih = $qtysekarang - $qty;
        $newstock = $stocksekarang - $selisih;
    }

    $query1 = mysqli_query($c, "UPDATE masuk SET qty='$qty' WHERE idmasuk='$idmasuk'");
    $query2 = mysqli_query($c, "UPDATE makanan SET stock='$newstock' WHERE idmakanan='$idmakanan'");

    if($query1 && $query2){
        header('location:masuk.php');
        exit;
    } else {
        echo '<script>alert("Gagal update data masuk"); window.location.href="masuk.php"</script>';
        exit;
    }
}

/* ==================== HAPUS (GET & POST) ==================== */
/* Hapus data masuk (GET) */
if (isset($_GET['hapusmasuk'])) {
    $idmasuk = intval($_GET['hapusmasuk']);

    $ambil = mysqli_query($c, "SELECT * FROM masuk WHERE idmasuk='$idmasuk'");
    $data  = mysqli_fetch_array($ambil);

    if ($data) {
        $idmakanan = intval($data['idmakanan']);
        $qty       = intval($data['qty']);

        mysqli_query($c, "UPDATE makanan SET stock = stock - $qty WHERE idmakanan='$idmakanan'");
        mysqli_query($c, "DELETE FROM masuk WHERE idmasuk='$idmasuk'");
    }

    header('location:masuk.php');
    exit;
}

/* Hapus menu (GET) */
if (isset($_GET['hapusmenu'])) {
    $idmakanan = intval($_GET['idmakanan']);

    // ambil foto dulu
    $qfoto = mysqli_query($c, "SELECT foto FROM makanan WHERE idmakanan='$idmakanan' LIMIT 1");
    $ff = mysqli_fetch_assoc($qfoto);
    $foto_n = $ff ? $ff['foto'] : null;

    // Hapus juga data terkait
    mysqli_query($c, "DELETE FROM detailpesanan WHERE idmakanan='$idmakanan'");
    mysqli_query($c, "DELETE FROM masuk WHERE idmakanan='$idmakanan'");

    // Hapus data makanan
    $hapus = mysqli_query($c, "DELETE FROM makanan WHERE idmakanan='$idmakanan'");

    // hapus file gambar jika ada dan bukan default
    if ($hapus && $foto_n && $foto_n !== 'noimage.png' && file_exists(__DIR__ . "/js/image/" . $foto_n)) {
        @unlink(__DIR__ . "/js/image/" . $foto_n);
    }

    header('location:stock.php');
    exit;
}

/* Hapus pelanggan (GET) */
if (isset($_GET['hapuspelanggan'])) {
    $id = intval($_GET['id_pelanggan']);

    $ambilpesanan = mysqli_query($c, "SELECT idpesanan FROM pesanan WHERE idpelanggan='$id'");
    while ($p = mysqli_fetch_array($ambilpesanan)) {
        $idp = intval($p['idpesanan']);
        $getdetails = mysqli_query($c, "SELECT * FROM detailpesanan WHERE idpesanan='$idp'");
        while ($d = mysqli_fetch_array($getdetails)) {
            $idmakanan = intval($d['idmakanan']);
            $qty = intval($d['qty']);
            mysqli_query($c, "UPDATE makanan SET stock = stock + $qty WHERE idmakanan='$idmakanan'");
        }
        mysqli_query($c, "DELETE FROM detailpesanan WHERE idpesanan='$idp'");
        mysqli_query($c, "DELETE FROM pesanan WHERE idpesanan='$idp'");
    }

    mysqli_query($c, "DELETE FROM pelanggan WHERE id_pelanggan='$id'");
    header('location:pelanggan.php');
    exit;
}

/* Hapus pesanan (GET) */
if (isset($_GET['hapuspesanan'])) {
    $idp = intval($_GET['idp']);
    $getdetails = mysqli_query($c, "SELECT * FROM detailpesanan WHERE idpesanan='$idp'");
    while ($d = mysqli_fetch_array($getdetails)) {
        $idmakanan = intval($d['idmakanan']);
        $qty = intval($d['qty']);
        mysqli_query($c, "UPDATE makanan SET stock = stock + $qty WHERE idmakanan='$idmakanan'");
    }
    mysqli_query($c, "DELETE FROM detailpesanan WHERE idpesanan='$idp'");
    mysqli_query($c, "DELETE FROM pesanan WHERE idpesanan='$idp'");
    header('location:index.php');
    exit;
}

/* Hapus detail pesanan (GET) */
if (isset($_GET['hapusdetail'])) {
    $iddetail = intval($_GET['hapusdetail']);
    $ambil = mysqli_query($c, "SELECT * FROM detailpesanan WHERE iddetailpesanan='$iddetail'");
    $data  = mysqli_fetch_array($ambil);

    if ($data) {
        $idmakanan = intval($data['idmakanan']);
        $qty       = intval($data['qty']);
        $idp       = intval($data['idpesanan']);
        $updateStok = mysqli_query($c, "UPDATE makanan SET stock = stock + $qty WHERE idmakanan='$idmakanan'");
        $hapusDetail = mysqli_query($c, "DELETE FROM detailpesanan WHERE iddetailpesanan='$iddetail'");

        if ($updateStok && $hapusDetail) {
            header('location:view.php?idp=' . $idp);
            exit;
        } else {
            echo '<script>alert("Gagal menghapus detail pesanan atau mengupdate stok."); window.location.href="view.php?idp=' . $idp . '"</script>';
            exit;
        }
    } else {
        echo '<script>alert("Data detail pesanan tidak ditemukan."); window.location.href="index.php"</script>';
        exit;
    }
}

/* Hapus lewat POST untuk fallback (hapusmenu sudah di-handle) */
if (isset($_POST['hapusmenu'])) {
    $idm = intval($_POST['idm']);
    // ambil foto dulu
    $qfoto = mysqli_query($c, "SELECT foto FROM makanan WHERE idmakanan='$idm' LIMIT 1");
    $ff = mysqli_fetch_assoc($qfoto);
    $foto_n = $ff ? $ff['foto'] : null;

    mysqli_query($c, "DELETE FROM detailpesanan WHERE idmakanan='$idm'");
    mysqli_query($c, "DELETE FROM masuk WHERE idmakanan='$idm'");
    $hapus = mysqli_query($c, "DELETE FROM makanan WHERE idmakanan='$idm'");

    if ($hapus && $foto_n && $foto_n !== 'noimage.png' && file_exists(__DIR__ . "/js/image/" . $foto_n)) {
        @unlink(__DIR__ . "/js/image/" . $foto_n);
    }

    if ($hapus) {
        header('location:stock.php');
        exit;
    } else {
        die("Gagal menghapus menu: " . mysqli_error($c));
    }
}

/* Hapus data masuk via POST */
if (isset($_POST['hapusmasuk'])) {
    $idmasuk = intval($_POST['idmasuk']);
    $idmakanan = intval($_POST['idmakanan']);
    $caritahu = mysqli_query($c, "SELECT qty FROM masuk WHERE idmasuk='$idmasuk'");
    $data = mysqli_fetch_array($caritahu);
    $qty = intval($data['qty']);
    mysqli_query($c, "UPDATE makanan SET stock = stock - $qty WHERE idmakanan='$idmakanan'");
    mysqli_query($c, "DELETE FROM masuk WHERE idmasuk='$idmasuk'");
    header('location:masuk.php');
    exit;
}

/* Hapus order via POST (fallback) */
if (isset($_POST['hapusorder'])) {
    $ido = intval($_POST['ido']);
    $cekdata = mysqli_query($c, "SELECT * FROM detailpesanan WHERE idpesanan='$ido'");
    $success = true;
    while ($ok = mysqli_fetch_array($cekdata)) {
        $qty = intval($ok['qty']);
        $idmakanan = intval($ok['idmakanan']);
        $iddp = intval($ok['iddetailpesanan']);
        $caristock = mysqli_query($c, "SELECT stock FROM makanan WHERE idmakanan='$idmakanan' LIMIT 1");
        $caristock2 = mysqli_fetch_array($caristock);
        $stocksekarang = intval($caristock2['stock']);
        $newstock = $stocksekarang + $qty;
        $queryupdate = mysqli_query($c, "UPDATE makanan SET stock='$newstock' WHERE idmakanan='$idmakanan'");
        if (!$queryupdate) $success = false;
        $querydelete = mysqli_query($c, "DELETE FROM detailpesanan WHERE iddetailpesanan='$iddp'");
        if (!$querydelete) $success = false;
    }
    $query = mysqli_query($c, "DELETE FROM pesanan WHERE idpesanan='$ido'");
    if (!$query) $success = false;
    if ($success) {
        header('location:index.php');
        exit;
    } else {
        echo '<script>alert("Gagal menghapus order dengan benar. Cek error log."); window.location.href="index.php"</script>';
        exit;
    }
}
// ===================== PEMBAYARAN PESANAN =====================
if (isset($_POST['bayarpesanan'])) {
    $idp = $_POST['idp'];
    $bayar = $_POST['bayar'];

    // Ambil total pesanan
    $gettotal = mysqli_query($c, "SELECT SUM(dp.qty * m.harga) AS total
                                  FROM detailpesanan dp
                                  JOIN makanan m ON dp.idmakanan = m.idmakanan
                                  WHERE dp.idpesanan = '$idp'");
    $data = mysqli_fetch_assoc($gettotal);
    $total = $data['total'] ?? 0;

    if ($total <= 0) {
        echo "<script>alert('Pesanan kosong, tidak bisa diproses!'); window.location='view.php?idp=$idp';</script>";
        exit;
    }

    // Hitung kembalian
    $kembalian = $bayar - $total;

    if ($bayar < $total) {
        echo "<script>alert('Uang bayar kurang!'); window.location='view.php?idp=$idp';</script>";
        exit;
    }

    // Simpan ke tabel pembayaran
    $insert = mysqli_query($c, "INSERT INTO pembayaran (idpesanan, total, bayar, kembalian)
                                VALUES ('$idp', '$total', '$bayar', '$kembalian')");

    if ($insert) {
        // Update status pesanan jadi "Lunas"
        mysqli_query($c, "UPDATE pesanan SET status='Lunas' WHERE idpesanan='$idp'");
        echo "<script>alert('Pembayaran berhasil disimpan!'); window.location='view.php?idp=$idp';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan pembayaran!'); window.location='view.php?idp=$idp';</script>";
    }
}
// ==================== EDIT PEMBAYARAN ====================
if (isset($_POST['editpembayaran'])) {
    $idpembayaran = $_POST['idpembayaran'];
    $total = $_POST['total'];
    $bayar = $_POST['bayar'];
    $kembalian = $_POST['kembalian'];
    $idp = $_POST['idp'];

    $update = mysqli_query($c, "UPDATE pembayaran 
                                SET total='$total', bayar='$bayar', kembalian='$kembalian' 
                                WHERE idpembayaran='$idpembayaran'");

    if ($update) {
        echo "<script>alert('Data pembayaran berhasil diubah'); window.location='view.php?idp=$idp';</script>";
    } else {
        echo "<script>alert('Gagal mengubah data pembayaran'); window.location='view.php?idp=$idp';</script>";
    }
}

// ==================== HAPUS PEMBAYARAN ====================
if (isset($_GET['hapuspembayaran'])) {
    $idpembayaran = $_GET['hapuspembayaran'];
    $idp = $_GET['idp'];

    $hapus = mysqli_query($c, "DELETE FROM pembayaran WHERE idpembayaran='$idpembayaran'");

    if ($hapus) {
        echo "<script>alert('Pembayaran berhasil dihapus'); window.location='view.php?idp=$idp';</script>";
    } else {
        echo "<script>alert('Gagal menghapus pembayaran'); window.location='view.php?idp=$idp';</script>";
    }
}


?>
