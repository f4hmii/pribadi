<?php
include 'db_connection.php';

// Tangani penambahan ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produk_id = $_POST['produk_id'];
    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $quantity = $_POST['quantity'];

    // Validasi input dasar
    if (!$produk_id || !$size || !$color || !$quantity) {
        echo "Data tidak lengkap.";
        exit;
    }

    // Simpan ke database (tabel cart)
    $stmt = $conn->prepare("INSERT INTO cart (produk_id, nama_produk, harga, size, color, quantity) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isissi", $produk_id, $nama_produk, $harga, $size, $color, $quantity);
    $stmt->execute();
}

// Ambil semua item dari keranjang
$result = $conn->query("SELECT * FROM cart ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Keranjang Belanja</h1>

        <?php if ($result->num_rows > 0): ?>
            <div class="bg-white shadow rounded-lg p-4">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2">Produk</th>
                            <th class="py-2">Warna</th>
                            <th class="py-2">Ukuran</th>
                            <th class="py-2">Jumlah</th>
                            <th class="py-2">Harga</th>
                            <th class="py-2">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $grandTotal = 0; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php $total = $row['harga'] * $row['quantity'];
                            $grandTotal += $total; ?>
                            <tr class="border-b">
                                <td class="py-2"><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td class="py-2"><?php echo htmlspecialchars($row['color']); ?></td>
                                <td class="py-2"><?php echo htmlspecialchars($row['size']); ?></td>
                                <td class="py-2"><?php echo $row['quantity']; ?></td>
                                <td class="py-2">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                <td class="py-2 font-semibold">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-bold pt-4">Total Belanja:</td>
                            <td class="font-bold pt-4">Rp <?php echo number_format($grandTotal, 0, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-600">Keranjang kamu kosong.</p>
        <?php endif; ?>
    </div>

</body>

</html>