<?php include '../db_connection.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Data Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
<a href="../index.php" class="btn btn-secondary position-absolute top-0 start-0 m-3">
         Kembali
    </a>
    <h2>Data Produk</h2>
    <a href="tambah.php" class="btn btn-primary mb-3">Tambah Produk</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Gambar</th>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th>Stok</th>  
                <th>Size</th>
                <th>Color</th>
                <th>Harga</th>
                <th>Aksi</th>
                
            </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT * FROM produk";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td><img src='../uploads/{$row['foto_url']}' width='80'></td>
                <td>{$row['nama_produk']}</td>
                <td>{$row['deskripsi']}</td>
                <td>{$row['stock']}</td>
                <td>{$row['size']}</td>
                <td>{$row['color']}</td>
                <td>Rp " . number_format($row['harga']) . "</td>
                <td>
                    <a href='edit.php?id={$row['produk_id']}' class='btn btn-warning btn-sm'>Edit</a>
                    <a href='hapus.php?produk_id={$row['produk_id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin?')\">Hapus</a>
                </td>
            </tr>";
        }
        ?>
        </tbody>
    </table>
</body>
</html>
