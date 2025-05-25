<?php
session_start();
include '../db_connection.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$pengguna_id = $_SESSION['id'];
$total_belanja = 0;
$checkout_items = [];

// Pindahkan logika pengecekan keranjang kosong ke sini, sebelum include header.php
$stmt_cart = $conn->prepare("SELECT k.keranjang_id, k.produk_id, k.quantity, k.size, k.color, p.nama_produk, p.harga, p.stock, p.foto_url
                               FROM keranjang k
                               JOIN produk p ON k.produk_id = p.produk_id
                               WHERE k.pengguna_id = ?
                               ORDER BY k.keranjang_id DESC");
$stmt_cart->bind_param("i", $pengguna_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();

if ($result_cart->num_rows === 0) {
    $_SESSION['error_message'] = "Keranjang Anda kosong, tidak ada yang bisa di-checkout.";
    header("Location: cart.php"); // Line 27 pada error Anda ada di sini
    exit(); // Penting untuk menghentikan eksekusi setelah redirect
}

while ($row = $result_cart->fetch_assoc()) {
    $subtotal = $row['harga'] * $row['quantity'];
    $total_belanja += $subtotal;
    $checkout_items[] = $row;
}

// Sekarang include header.php setelah semua logika PHP yang mungkin memanggil header() selesai
include '../view/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Konfirmasi Checkout</h1>

        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">' . $_SESSION['error_message'] . '</span>
                  </div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Ringkasan Pesanan</h2>
            <table class="w-full text-left border-collapse mb-4">
                <thead>
                    <tr class="border-b">
                        <th class="py-2">Produk</th>
                        <th class="py-2">Jumlah</th>
                        <th class="py-2">Harga Satuan</th>
                        <th class="py-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($checkout_items as $item): ?>
                    <tr class="border-b">
                        <td class="py-2 flex items-center">
                            <img src="../uploads/<?php echo htmlspecialchars($item['foto_url']); ?>" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>" class="w-12 h-12 object-cover mr-4 rounded">
                            <span><?php echo htmlspecialchars($item['nama_produk']); ?> (Ukuran: <?= htmlspecialchars($item['size']); ?>, Warna: <?= htmlspecialchars($item['color']); ?>)</span>
                        </td>
                        <td class="py-2"><?= $item['quantity']; ?></td>
                        <td class="py-2">Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                        <td class="py-2">Rp <?= number_format($item['harga'] * $item['quantity'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right font-bold pt-4">Total Belanja:</td>
                        <td class="font-bold pt-4">Rp <?= number_format($total_belanja, 0, ',', '.'); ?></td>
                    </tr>
                </tfoot>
            </table>

            <h2 class="text-xl font-semibold mb-4">Informasi Pembayaran</h2>
            <form action="../checkout_process.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="checkout_cart" value="1">
                <input type="hidden" name="total_belanja" value="<?= $total_belanja ?>">

                <div class="mb-4">
                    <label for="metode_pembayaran" class="block text-gray-700 font-bold mb-2">Pilih Metode Pembayaran:</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="block w-full border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">-- Pilih Metode --</option>
                        <option value="bank_transfer">Transfer Bank</option>
                        <option value="e-wallet">E-Wallet</option>
                        <option value="cod">Cash On Delivery (COD)</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="bukti_pembayaran" class="block text-gray-700 font-bold mb-2">Upload Bukti Pembayaran (Opsional jika COD):</label>
                    <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept="image/jpeg, image/png, image/jpg, image/webp" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                    <p class="mt-1 text-sm text-gray-500" id="file_input_help">JPG, PNG, JPEG, atau WEBP (MAX. 5MB).</p>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                        Konfirmasi Pembayaran dan Checkout
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('metode_pembayaran').addEventListener('change', function() {
            var buktiInput = document.getElementById('bukti_pembayaran');
            if (this.value === 'cod') {
                buktiInput.removeAttribute('required');
            } else {
                buktiInput.setAttribute('required', 'required');
            }
        });
    </script>
</body>
</html>