<?php
include '../db_connection.php' ; // koneksi ke DB

// Proses update status
if (isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $query = "UPDATE pengguna SET status='$status' WHERE id=$id";
    mysqli_query($conn, $query);
}

// Ambil data pembayaran
$result = mysqli_query($conn, "SELECT * FROM pengguna ORDER BY pengguna_id ASC");
?>

<h2>Kelola User</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>No</th>
        <th>Username</th>
        <th>Nama Pengguna</th>
        <th>Email</th>
        <th>Telepon</th>
        <th>Alamat</th>
        <th>Role</th>
        <th>Action</th>
    </tr>

    <?php
    $no = 1;
    while ($row = mysqli_fetch_assoc($result)) {
    ?>
        <tr>
            <td><?= $no++ ?></td>
             <td><?= $row['username'] ?></td>
            <td><?= $row['nama_pengguna'] ?></td>
             <td><?= $row['email'] ?></td>
              <td><?= $row['nomor_telepon'] ?></td>
              <td><?= $row['alamat'] ?></td>
              <td><?= $row['role'] ?></td>  
            <td>
                 <button><a href="edituser.php?pengguna_id=<?= $row['pengguna_id'] ?>">Edit</a></button>
                <button><a href="hapusUser.php?pengguna_id=<?= $row['pengguna_id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a></button>
            </td>
        </tr>
    <?php } ?>
</table>
