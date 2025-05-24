<?php
include 'db_connection.php'; // Koneksi database

// Ambil ID produk dari URL
if (!isset($_GET['id'])) {
    echo "Produk tidak ditemukan.";
    exit;
}

$produk_id = $_GET['id'];

// Ambil data produk dari database
$stmt = $conn->prepare("SELECT * FROM produk WHERE produk_id = ?");
$stmt->bind_param("i", $produk_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "Produk tidak ditemukan.";
    exit;
}

// Assign data produk
$product_name = $product['nama_produk'];
$price = $product['harga'];
$stock = $product['stock']; // Perhatikan ini 'stock' bukan 'stok'
$deskripsi = $product['deskripsi'];
$gambarUtama = $product['foto_url'];

// Karena di database hanya ada 1 gambar (foto_url), kita tidak bisa menampilkan gambar lain
$gambarLain = []; // Kosongkan array karena tidak ada gambar tambahan di database

// Jika Anda ingin menampilkan gambar yang sama sebagai thumbnail, bisa diisi dengan gambar utama
// $gambarLain = [$gambarUtama];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product_name); ?> - Detail Produk</title>
    <script>
        function showSize(size) {
            document.getElementById("selected-size").textContent = size;
            document.getElementById("sizeInput").value = size;
        }

        function selectColor(color) {
            document.getElementById("selected-color").textContent = color;
            document.getElementById("colorInput").value = color;

            var buttons = document.querySelectorAll("button[id^='color-']");
            buttons.forEach(btn => btn.classList.remove("ring-2", "ring-offset-2"));

            document.getElementById("color-" + color).classList.add("ring-2", "ring-offset-2");
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">

    <div class="max-w-6xl mx-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-10">
        <!-- Gambar Produk -->
        <div>
            <img class="w-full rounded-lg shadow-md mb-4"
                src="uploads/<?php echo htmlspecialchars($gambarUtama); ?>"
                alt="Gambar Utama"
                onerror="this.onerror=null; this.src='uploads/image-not-found.png'; console.error('Gambar utama tidak ditemukan: <?php echo htmlspecialchars($gambarUtama); ?>');" />

            <div class="grid grid-cols-2 gap-4">
                <?php foreach ($gambarLain as $gambar): ?>
                    <?php if ($gambar): ?>
                        <img class="w-full rounded-lg shadow-md" src="<?php echo htmlspecialchars($gambar); ?>" alt="Gambar Produk" />
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Informasi Produk -->
        <div>
            <h2 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($product_name); ?></h2>
            <p class="text-xl text-gray-800 mb-4">Rp <?php echo number_format($price, 0, ',', '.'); ?></p>

            <!-- Warna -->
            <div class="mb-4">
                <span class="text-gray-700">Colour: <span id="selected-color" class="font-semibold"></span></span>
                <div class="flex space-x-2 mt-2">
                    <?php if (!empty($warna)): ?>
    <?php foreach ($warna as $color): ?>
        <button type="button" onclick="selectColor('<?php echo $color; ?>')" id="color-<?php echo $color; ?>"
            class="w-10 h-10 rounded-full border" style="background-color:<?php echo $color; ?>;">
        </button>
    <?php endforeach; ?>
<?php endif; ?>

                </div>
            </div>

            <!-- Ukuran -->
            <div class="mb-4">
                <span class="text-gray-700">Size: <span id="selected-size" class="font-semibold"></span></span>
                <div class="flex space-x-2 mt-2">
                    <?php if (!empty($ukuran)): ?>
    <?php foreach ($ukuran as $size): ?>
        <button type="button" onclick="showSize('<?php echo $size; ?>')"
            class="border px-4 py-2 rounded-lg hover:bg-gray-200">
            <?php echo $size; ?>
        </button>
    <?php endforeach; ?>
<?php endif; ?>

                </div>
            </div>

            <!-- Stock dan Quantity -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-1">Stock: <span id="stock"><?php echo $stock; ?></span></label>
                <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $stock; ?>" value="1"
                    class="w-24 border px-2 py-1 rounded-md" />
            </div>

            <!-- Form Tambah ke Keranjang -->
            <form action="cart.php" method="post" class="mt-4">
                <input type="hidden" name="produk_id" value="<?php echo $produk_id; ?>">
                <input type="hidden" name="nama_produk" value="<?php echo htmlspecialchars($product_name); ?>">
                <input type="hidden" name="harga" value="<?php echo $price; ?>">
                <input type="hidden" name="size" id="sizeInput">
                <input type="hidden" name="color" id="colorInput">
                <input type="hidden" name="quantity" id="hiddenQuantity" value="1">
                <button type="submit"
                    class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Add to Cart
                </button>
            </form>

            <!-- Deskripsi Produk -->
            <div class="mt-8">
                <h3 class="text-2xl font-semibold mb-2">Deskripsi Produk</h3>
                <p class="text-gray-700 leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($deskripsi)); ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("quantity").addEventListener("input", function() {
            document.getElementById("hiddenQuantity").value = this.value;
        });
    </script>

</body>

</html>