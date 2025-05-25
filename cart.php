<?php
session_start();
include 'db_connection.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['id'])) {
    header("Location: pages/login.php");
    exit();
}

$pengguna_id = $_SESSION['id'];

// --- START: MODIFIKASI LOGIKA CART.PHP ---
// Hapus atau komentari blok ini karena logika add_to_cart sudah di add_to_cart.php
// Ini untuk menghindari duplikasi dan potensi bug saat POST dari halaman lain ke cart.php
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update_cart']) && !isset($_POST['checkout_cart'])) {
    $produk_id = isset($_POST['produk_id']) ? intval($_POST['produk_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $size = isset($_POST['size']) ? $_POST['size'] : '';
    $color = isset($_POST['color']) ? $_POST['color'] : '';

    if ($produk_id > 0 && $quantity > 0) {
        $stmt_check = $conn->prepare("SELECT * FROM keranjang WHERE pengguna_id = ? AND produk_id = ? AND size = ? AND color = ?");
        $stmt_check->bind_param("iiss", $pengguna_id, $produk_id, $size, $color);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $row = $result_check->fetch_assoc();
            $new_quantity = $row['quantity'] + $quantity;
            $stmt_update = $conn->prepare("UPDATE keranjang SET quantity = ? WHERE keranjang_id = ?");
            $stmt_update->bind_param("ii", $new_quantity, $row['keranjang_id']);
            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = "Kuantitas produk berhasil diperbarui di keranjang!";
            } else {
                $_SESSION['error_message'] = "Gagal memperbarui kuantitas produk: " . $conn->error;
            }
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO keranjang (pengguna_id, produk_id, quantity, size, color) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("iiiss", $pengguna_id, $produk_id, $quantity, $size, $color);
            if ($stmt_insert->execute()) {
                $_SESSION['success_message'] = "Produk berhasil ditambahkan ke keranjang!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan produk ke keranjang: " . $conn->error;
            }
        }
        $stmt_check->close();
    } else {
        $_SESSION['error_message'] = "Produk ID atau Kuantitas tidak valid.";
    }
}
*/
// --- END: MODIFIKASI LOGIKA CART.PHP ---

// Tangani update kuantitas dari form di cart itu sendiri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $keranjang_id => $new_quantity) {
        $new_quantity = intval($new_quantity);
        if ($new_quantity > 0) {
            $stmt_update_qty = $conn->prepare("UPDATE keranjang SET quantity = ? WHERE keranjang_id = ? AND pengguna_id = ?");
            $stmt_update_qty->bind_param("iii", $new_quantity, $keranjang_id, $pengguna_id);
            $stmt_update_qty->execute();
            $stmt_update_qty->close();
        } else if ($new_quantity <= 0) {
            // Hapus item jika kuantitas <= 0
            $stmt_delete_item = $conn->prepare("DELETE FROM keranjang WHERE keranjang_id = ? AND pengguna_id = ?");
            $stmt_delete_item->bind_param("ii", $keranjang_id, $pengguna_id);
            $stmt_delete_item->execute();
            $stmt_delete_item->close();
        }
    }
    $_SESSION['success_message'] = "Keranjang berhasil diperbarui.";
    header("Location: cart.php"); // Refresh halaman
    exit();
}

// Tangani penghapusan item dari keranjang
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['keranjang_id'])) {
    $keranjang_id_to_remove = intval($_GET['keranjang_id']);
    $stmt_delete = $conn->prepare("DELETE FROM keranjang WHERE keranjang_id = ? AND pengguna_id = ?");
    $stmt_delete->bind_param("ii", $keranjang_id_to_remove, $pengguna_id);
    if ($stmt_delete->execute()) {
        $_SESSION['success_message'] = "Produk berhasil dihapus dari keranjang.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus produk dari keranjang: " . $conn->error;
    }
    $stmt_delete->close();
    header("Location: cart.php"); // Refresh halaman
    exit();
}


// Ambil semua item dari keranjang untuk pengguna yang sedang login
// Mengambil 'size' dan 'color' langsung dari tabel 'keranjang'
$result_cart = $conn->prepare("SELECT k.keranjang_id, k.produk_id, k.quantity, k.size, k.color, p.nama_produk, p.harga, p.stock, p.foto_url
                               FROM keranjang k
                               JOIN produk p ON k.produk_id = p.produk_id
                               WHERE k.pengguna_id = ?
                               ORDER BY k.keranjang_id DESC");
$result_cart->bind_param("i", $pengguna_id);
$result_cart->execute();
$cart_items = $result_cart->get_result();
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

        <?php
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
        ?>

        <?php if ($cart_items->num_rows > 0): ?>
            <form action="cart.php" method="POST">
                <input type="hidden" name="update_cart" value="1">
                <div class="bg-white shadow rounded-lg p-4">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Produk</th>
                                <th class="py-2">Warna</th>
                                <th class="py-2">Ukuran</th>
                                <th class="py-2">Harga Satuan</th>
                                <th class="py-2">Jumlah</th>
                                <th class="py-2">Total</th>
                                <th class="py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $grandTotal = 0; ?>
                            <?php while ($row = $cart_items->fetch_assoc()): ?>
                                <?php $total = $row['harga'] * $row['quantity'];
                                $grandTotal += $total; ?>
                                <tr class="border-b">
                                    <td class="py-2 flex items-center">
                                        <img src="uploads/<?php echo htmlspecialchars($row['foto_url']); ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" class="w-16 h-16 object-cover mr-4 rounded">
                                        <span><?php echo htmlspecialchars($row['nama_produk']); ?></span>
                                    </td>
                                    <td class="py-2"><?php echo htmlspecialchars($row['color']); ?></td>
                                    <td class="py-2"><?php echo htmlspecialchars($row['size']); ?></td>
                                    <td class="py-2">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <td class="py-2">
                                        <input type="number" name="quantities[<?= $row['keranjang_id'] ?>]" value="<?= $row['quantity']; ?>"
                                               min="1" max="<?= $row['stock']; ?>" class="w-20 border px-2 py-1 rounded-md"
                                               onchange="this.form.submit()"> </td>
                                    <td class="py-2 font-semibold">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                                    <td class="py-2">
                                        <a href="cart.php?action=remove&keranjang_id=<?= $row['keranjang_id'] ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Yakin ingin menghapus item ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right font-bold pt-4">Total Belanja:</td>
                                <td class="font-bold pt-4">Rp <?php echo number_format($grandTotal, 0, ',', '.'); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition mr-4">Update Keranjang</button>
                    <button type="submit" name="proceed_to_checkout" value="1" formaction="pages/checkout_confirmation.php" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                        Lanjutkan ke Checkout
                    </button>
                </div>
            </form>
        <?php else: ?>
            <p class="text-gray-600">Keranjang kamu kosong.</p>
            <a href="index.php" class="inline-block mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Lanjutkan Belanja</a>
        <?php endif; ?>
    </div>
</body>
</html>