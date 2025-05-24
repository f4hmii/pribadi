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
$result = mysqli_query($conn, "SELECT * FROM pembayaran ORDER BY tanggal_pembayaran DESC");
?>

<h2>Kelola Pembayaran</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>No</th>
        <th>Pembeli</th>
        <th>Total Harga</th>
        <th>Metode Pembayaran</th>
        <th>Pengiriman</th>
        <th>Bukti Pembayaran</th>
        <th>Tanggal Pembayaran</th>
        <th>Status</th>
        <th>Ubah Status</th>
    </tr>

    <?php
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
    ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['nama_pengguna'] ?></td>
             <td><?= $row['jumlah_pembayaran'] ?></td>
              <td><?= $row['metode_pembayaran'] ?></td>
              <td><?= $row['pengiriman'] ?></td>
              <!-- <td><a href="bukti/<?= $row['bukti_pembayaran'] ?>" target="_blank">Lihat Bukti</a></td> -->
               <?php
                $bukti_pembayaran = base64_encode($row['bukti_pembayaran']);
               ?>
                <td><img src= "data:image/jpeg;base64, <?= $bukti_pembayaran;?>" width="100" height="100"></td>
              <td><?= $row['tanggal_pembayaran'] ?></td>
              <td><?= $row['status'] ?></td>    
            <td>
                <form method="post">
                    <input type="hidden" name="status" value="<?= $row['status'] ?>">
                    <select name="status">
                        <option <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option <?= $row['status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                        <option <?= $row['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                    <button type="submit" name="update_status">Update</button>
                </form>
            </td>
        </tr>
    <?php } ?>
</table>
