<?php
session_start();
include '../db_connection.php';
include "../view/header.php"; // Pastikan header sudah di-include

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'buyer') {
    header("Location: ../pages/login.php");
    exit;
}

$buyer_id = $_SESSION['id'];

$sql = "SELECT p.pesanan_id, p.tanggal_pesan, p.total_harga, p.status AS order_status,
               GROUP_CONCAT(pr.nama_produk SEPARATOR ', ') AS nama_produk_list
        FROM pesanan p
        JOIN pesanan_detail pd ON p.pesanan_id = pd.pesanan_id
        JOIN produk pr ON pd.produk_id = pr.produk_id
        WHERE p.buyer_id = ?
        GROUP BY p.pesanan_id
        ORDER BY p.tanggal_pesan DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pesanan Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>Riwayat Pesanan Saya</h2>

    <?php
    if (isset($_SESSION['success_message'])) {
        echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
        unset($_SESSION['error_message']);
    }
    ?>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Tanggal Pesan</th>
                    <th>Produk</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['pesanan_id'] ?></td>
                    <td><?= date('d F Y H:i', strtotime($row['tanggal_pesan'])) ?></td>
                    <td><?= htmlspecialchars($row['nama_produk_list']) ?></td>
                    <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['order_status']) ?></td>
                    <td>
                        <?php if ($row['order_status'] === 'dikirim'): ?>
                            <a href="aksi_pesanan_buyer.php?action=confirm_received&pesanan_id=<?= $row['pesanan_id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Konfirmasi bahwa barang sudah diterima?')">Konfirmasi Diterima</a>
                        <?php elseif ($row['order_status'] === 'tertunda_pembayaran'): ?>
                            <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
                        <?php elseif ($row['order_status'] === 'diproses_penjual'): ?>
                            <span class="badge bg-info">Diproses Penjual</span>
                        <?php elseif ($row['order_status'] === 'selesai'): ?>
                            <span class="badge bg-success">Selesai</span>
                        <?php endif; ?>
                        <a href="../pages/order_confirmation.php?order_id=<?= $row['pesanan_id'] ?>" class="btn btn-info btn-sm">Detail</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Kamu belum memiliki pesanan.</div>
    <?php endif; ?>
</body>
</html>