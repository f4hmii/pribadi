<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    header("Location: ../pages/login.php");
    exit;
}

$seller_id = $_SESSION['id'];
$status_filter = $_GET['status'] ?? ''; // 'menunggu_konfirmasi_pembayaran', 'dikirim', etc.

$sql = "SELECT p.pesanan_id, p.tanggal_pesan, p.total_harga, p.status as order_status,
               u.nama_pengguna AS buyer_name, u.alamat, u.nomor_telepon,
               py.bukti_pembayaran, py.metode_pembayaran, py.status_pembayaran
        FROM pesanan p
        JOIN pengguna u ON p.buyer_id = u.pengguna_id
        JOIN pembayaran py ON p.pesanan_id = py.pesanan_id
        WHERE 1=1 ";

// Menyesuaikan query berdasarkan status yang diminta
if ($status_filter === 'menunggu_konfirmasi_pembayaran') {
    $sql .= " AND py.status_pembayaran = 'pending' AND p.status = 'tertunda_pembayaran'";
} elseif ($status_filter === 'dikirim') {
    $sql .= " AND p.status = 'diproses_penjual'"; // Setelah penjual konfirmasi pembayaran
}

// Filter berdasarkan produk yang dimiliki oleh seller
$sql .= " AND p.pesanan_id IN (SELECT DISTINCT pesanan_id FROM pesanan_detail pd JOIN produk pr ON pd.produk_id = pr.produk_id WHERE pr.seller_id = ?)";
$sql .= " ORDER BY p.tanggal_pesan DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pesanan Saya - Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>Pesanan dengan Status: <?= htmlspecialchars(str_replace('_', ' ', $status_filter)) ?></h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Pembeli</th>
                    <th>Total Harga</th>
                    <th>Status Pesanan</th>
                    <th>Metode Pembayaran</th>
                    <th>Bukti Pembayaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['pesanan_id'] ?></td>
                    <td><?= htmlspecialchars($row['buyer_name']) ?></td>
                    <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['order_status']) ?></td>
                    <td><?= htmlspecialchars($row['metode_pembayaran']) ?></td>
                    <td>
                        <?php if ($row['bukti_pembayaran']): ?>
                            <a href="../uploads/bukti_pembayaran/<?= htmlspecialchars($row['bukti_pembayaran']) ?>" target="_blank">Lihat Bukti</a>
                        <?php else: ?>
                            Tidak ada
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($status_filter === 'menunggu_konfirmasi_pembayaran'): ?>
                            <a href="aksi_pesanan_seller.php?action=confirm_payment&pesanan_id=<?= $row['pesanan_id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Konfirmasi pembayaran ini?')">Konfirmasi Pembayaran</a>
                            <a href="aksi_pesanan_seller.php?action=reject_payment&pesanan_id=<?= $row['pesanan_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tolak pembayaran ini?')">Tolak Pembayaran</a>
                        <?php elseif ($status_filter === 'dikirim'): ?>
                            <a href="aksi_pesanan_seller.php?action=mark_as_shipped&pesanan_id=<?= $row['pesanan_id'] ?>" class="btn btn-primary btn-sm" onclick="return confirm('Tandai pesanan ini sebagai dikirim?')">Tandai Dikirim</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Tidak ada pesanan dengan status ini.</div>
    <?php endif; ?>
</body>
</html>