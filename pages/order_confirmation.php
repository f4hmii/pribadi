<?php
session_start();
include '../db_connection.php';
include '../view/header.php'; // Atau sesuaikan path header Anda

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    $_SESSION['error_message'] = "ID Pesanan tidak valid.";
    header("Location: ../index.php");
    exit();
}

// Ambil detail pesanan
$stmt_order = $conn->prepare("SELECT p.pesanan_id, p.tanggal_pesan, p.status, p.total_harga, u.nama_pengguna, u.alamat, u.nomor_telepon
                             FROM pesanan p
                             JOIN pengguna u ON p.buyer_id = u.pengguna_id
                             WHERE p.pesanan_id = ? AND p.buyer_id = ?");
$stmt_order->bind_param("ii", $order_id, $_SESSION['id']);
$stmt_order->execute();
$result_order = $stmt_order->get_result();
$order_data = $result_order->fetch_assoc();

if (!$order_data) {
    $_SESSION['error_message'] = "Pesanan tidak ditemukan atau Anda tidak memiliki akses.";
    header("Location: ../index.php");
    exit();
}

// Ambil detail produk dalam pesanan
$stmt_order_details = $conn->prepare("SELECT pd.jumlah, pd.harga_satuan, pr.nama_produk, pr.foto_url
                                      FROM pesanan_detail pd
                                      JOIN produk pr ON pd.produk_id = pr.produk_id
                                      WHERE pd.pesanan_id = ?");
$stmt_order_details->bind_param("i", $order_id);
$stmt_order_details->execute();
$order_products = $stmt_order_details->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pesanan #<?= $order_id ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Detail Pesanan Anda</h1>

        <?php
        // MODIFIKASI INI
        if (isset($_SESSION['success_message'])) {
            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">' . $_SESSION['success_message'] . '</span>
                  </div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">' . $_SESSION['error_message'] . '</span>
                  </div>';
            unset($_SESSION['error_message']);
        }
        // AKHIR MODIFIKASI
        ?>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Informasi Pesanan</h2>
            <p><strong>ID Pesanan:</strong> #<?= $order_data['pesanan_id'] ?></p>
            <p><strong>Tanggal Pesan:</strong> <?= date('d F Y H:i', strtotime($order_data['tanggal_pesan'])) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($order_data['status']) ?></p>
            <p><strong>Total Harga:</strong> Rp <?= number_format($order_data['total_harga'], 0, ',', '.') ?></p>
            <h3 class="text-lg font-semibold mt-4 mb-2">Alamat Pengiriman</h3>
            <p><strong>Nama Penerima:</strong> <?= htmlspecialchars($order_data['nama_pengguna']) ?></p>
            <p><strong>Alamat:</strong> <?= htmlspecialchars($order_data['alamat']) ?></p>
            <p><strong>Nomor Telepon:</strong> <?= htmlspecialchars($order_data['nomor_telepon']) ?></p>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Produk dalam Pesanan</h2>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b">
                        <th class="py-2">Produk</th>
                        <th class="py-2">Harga Satuan</th>
                        <th class="py-2">Jumlah</th>
                        <th class="py-2">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $order_products->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="py-2 flex items-center">
                                <img src="../uploads/<?php echo htmlspecialchars($product['foto_url']); ?>" alt="<?php echo htmlspecialchars($product['nama_produk']); ?>" class="w-12 h-12 object-cover mr-4 rounded">
                                <span><?= htmlspecialchars($product['nama_produk']) ?></span>
                            </td>
                            <td class="py-2">Rp <?= number_format($product['harga_satuan'], 0, ',', '.') ?></td>
                            <td class="py-2"><?= $product['jumlah'] ?></td>
                            <td class="py-2">Rp <?= number_format($product['jumlah'] * $product['harga_satuan'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6 text-center">
            <a href="../index.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>