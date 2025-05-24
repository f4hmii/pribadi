<?php
include '../db_connection.php' ; // koneksi ke DB

// Proses update status
if (isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $query = "UPDATE pembayaran SET status='$status' WHERE id=$id";
    mysqli_query($conn, $query);
}

// Ambil data pembayaran
$result = mysqli_query($conn, "SELECT 
    p.produk_id,
    p.foto_url, 
    pa.nama_pengguna, 
    p.nama_produk, 
    p.deskripsi, 
    p.stock,
    k.nama_kategori,
    p.harga FROM produk p
    LEFT JOIN kategori k
    ON p.kategori_id = k.kategori_id
    LEFT JOIN pengguna pa 
    ON p.seller_id = pa.pengguna_id 
    ORDER BY p.produk_id ASC");
?>

<h2>Kelola Produk</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>No</th>
        <th>gambar</th>
        <th>toko</th>
        <th>nama produk</th>
        <th>Deskripsi</th>
        <th>Stok</th>
        <th>Kategori</th>
        <th>Harga</th>
        <th>Action</th>
    </tr>

    <?php
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
    ?>
        <tr>
            <td><?= $no++ ?></td>
             <td><img src= "../uploads/<?= $row['foto_url']; ?>" width="100" height="100"></td>
            <td><?= $row['nama_pengguna'] ?></td>
             <td><?= $row['nama_produk'] ?></td>
              <td><?= $row['deskripsi'] ?></td>
              <td><?= $row['stock'] ?></td>
              <td><?= $row['nama_kategori'] ?></td>
              <td><?= $row['harga'] ?></td>    
            <td>
                <button><a href="editProduk.php?produk_id=<?= $row['produk_id'] ?>">Edit</a></button>
                <button><a href="hapusProduk.php?produk_id=<?= $row['produk_id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a></button>
            </td>
        </tr>
    <?php } ?>
</table>
