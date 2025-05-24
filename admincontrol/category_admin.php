<?php
include '../db_connection.php';

$sql = "SELECT 
        p.nama_produk,k.nama_kategori,p.stock,p.foto_url
        FROM produk p JOIN kategori k ON p.kategori_id = k.kategori_id;";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>List Category</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    </style>
</head>
<body>

<h2>Data Produk</h2>

<table>
    <thead>
        <tr>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Jumlah</th>
            <th>Gambar</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_produk']); ?></td>
                <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
                <td><?= htmlspecialchars($row['stock']); ?></td>
                <td>
                    <img src="../uploads/<?= htmlspecialchars($row['foto_url']); ?>" width="100" height="100">
                </td>
                 <td>
                <button><a href="editProdukCategory.php?kategori_id=<?= $row['kategori_id'] ?>">Edit</a></button>
                <button><a href="hapusProduk.php?kategori_id=<?= $row['kategori_id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a></button>
            </td>
            </tr>
            <?php endwhile; ?>
        <?php elseif (!$result): ?>
            <tr><td colspan="5">Query error: <?= htmlspecialchars($conn->error); ?></td></tr>
        <?php else: ?>
            <tr><td colspan="5">Tidak ada data produk</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
