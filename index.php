<?php
session_start();
include "view/header.php";
include 'db_connection.php';

$query = "SELECT * FROM produk WHERE verified = 1 ORDER BY produk_id DESC";
$result = $conn->query($query);

$products = [];
while ($row = $result->fetch_assoc()) {
  $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Web Page</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/feather-icons"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="index.css">
  <style>
    .carousel {
      position: relative;
      width: 100%;
      overflow: hidden;
      margin: 0 auto;
    }
    .carousel-slides {
      display: flex;
      transition: transform 0.5s ease;
    }
    .slide {
      min-width: 100%;
    }
    .slide img {
      width: 100%;
      display: block;
    }
    .carousel-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(0,0,0,0.5);
      color: white;
      border: none;
      padding: 10px 15px;
      cursor: pointer;
      z-index: 10;
      font-size: 1.5rem;
    }
    .prev-btn {
      left: 10px;
    }
    .next-btn {
      right: 10px;
    }
    .carousel-btn:hover {
      background: rgba(0,0,0,0.8);
    }
  </style>
</head>
<body class="bg-gray-50">

  <div class="carousel">
    <button class="carousel-btn prev-btn">
      <i class="fas fa-chevron-left"></i>
    </button>
    <div class="carousel-slides">
      <div class="slide">
        <img src="https://www.newbalance.co.id/media/weltpixel/owlcarouselslider/images/s/e/secondary_banner_desktop_2400_x_900-20241220-065408.jpg" alt="Banner 1">
      </div>
      <div class="slide">
        <img src="https://www.newbalance.co.id/media/weltpixel/owlcarouselslider/images/s/e/secondary_banner-20240805-072521.jpg" alt="Banner 2">
      </div>
      <div class="slide">
        <img src="https://www.newbalance.co.id/media/weltpixel/owlcarouselslider/images/s/e/secondary_banner_copy-20241118-093422.jpg" alt="Banner 3">
      </div>
      <div class="slide">
        <img src="https://i.pinimg.com/736x/d5/cf/48/d5cf48081afa823efe25b2275446725b.jpg" alt="Banner 4">
      </div>
    </div>
    <button class="carousel-btn next-btn">
      <i class="fas fa-chevron-right"></i>
    </button>
  </div>

  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">Fashion Sale Collection</h1>
    
    <?php if (count($products) > 0): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($products as $product): ?>
          <div class="relative w-full bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 hover:shadow-lg transition-shadow duration-300">
            <!-- Favorite Button -->
            <form method="POST" action="favorite.php" class="absolute top-3 right-3 z-10">
              <input type="hidden" name="produk_id" value="<?= $product['produk_id'] ?>">
              <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors duration-200">
                <i data-feather="heart" class="w-5 h-5"></i>
              </button>
            </form>

            <!-- Product Image -->
            <a href="pages/detail.php?id=<?= $product['produk_id'] ?>" class="block p-4">
              <img class="rounded-t-lg w-full h-48 object-contain" src="uploads/<?= $product['foto_url'] ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>" />
            </a>

            <!-- Product Info -->
            <div class="px-5 pb-5">
              <a href="pages/detail.php?id=<?= $product['produk_id'] ?>">
                <h5 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white mb-1"><?= htmlspecialchars($product['nama_produk']) ?></h5>
              </a>
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2"><?= htmlspecialchars($product['deskripsi']) ?></p>

              <div class="flex items-center justify-between mb-3">
                <span class="text-2xl font-bold text-gray-900 dark:text-white">Rp<?= number_format($product['harga'], 0, ',', '.') ?></span>
                <a href="add_to_cart.php?id=<?= $product['produk_id'] ?>" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-colors duration-200">
                  Add to Cart
                </a>
              </div>

              <a href="checkout.php?id=<?= $product['produk_id'] ?>" class="block w-full text-center text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800 transition-colors duration-200">
                Checkout Sekarang
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-12">
        <h3 class="text-xl font-medium text-gray-500">Tidak ada produk yang tersedia saat ini</h3>
        <p class="text-gray-400 mt-2">Silakan kembali lagi nanti</p>
      </div>
    <?php endif; ?>
  </div>

  <footer class="bg-gray-800 text-white mt-12">
    <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8 py-8">
      <div>
        <h3 class="text-lg font-semibold mb-4">About Us</h3>
        <p class="text-gray-400">We are a leading sportswear brand committed to providing high-quality products for athletes and fitness enthusiasts.</p>
        <div class="mt-4 flex space-x-4">
          <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200"><i class="fab fa-twitter"></i></a>
          <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200"><i class="fab fa-instagram"></i></a>
          <a href="#" class="text-gray-400 hover:text-white transition-colors duration-200"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>
      <div>
        <h3 class="text-lg font-semibold mb-4">Customer Service</h3>
        <ul class="text-gray-400 space-y-2">
          <li><a href="#" class="hover:text-white transition-colors duration-200">Contact Us</a></li>
          <li><a href="#" class="hover:text-white transition-colors duration-200">Order Tracking</a></li>
          <li><a href="#" class="hover:text-white transition-colors duration-200">Returns & Exchanges</a></li>
          <li><a href="#" class="hover:text-white transition-colors duration-200">Shipping & Delivery</a></li>
          <li><a href="#" class="hover:text-white transition-colors duration-200">FAQs</a></li>
        </ul>
      </div>
      <div>
        <h3 class="text-lg font-semibold mb-4">Newsletter</h3>
        <p class="text-gray-400 mb-4">Subscribe to get the latest information on new products and upcoming sales.</p>
        <form class="mt-4">
          <input class="w-full p-2 rounded-lg text-gray-900 mb-2" placeholder="Enter your email" type="email" />
          <button class="w-full bg-red-600 hover:bg-red-700 p-2 rounded-lg transition-colors duration-200" type="submit">Subscribe</button>
        </form>
      </div>
    </div>
    <div class="border-t border-gray-700 py-4 text-center text-gray-400">
      <p>© 2023 Movr. All rights reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <script>
    feather.replace();
    
    // Carousel functionality
    const carouselSlides = document.querySelector('.carousel-slides');
    const slides = document.querySelectorAll('.slide');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    let currentIndex = 0;
    let autoSlideInterval;

    function updateCarousel() {
      const offset = -currentIndex * 100;
      carouselSlides.style.transform = `translateX(${offset}%)`;
    }

    prevBtn.addEventListener('click', () => {
      currentIndex = (currentIndex - 1 + slides.length) % slides.length;
      updateCarousel();
      resetAutoSlide();
    });

    nextBtn.addEventListener('click', () => {
      currentIndex = (currentIndex + 1) % slides.length;
      updateCarousel();
      resetAutoSlide();
    });

    function startAutoSlide() {
      autoSlideInterval = setInterval(() => {
        currentIndex = (currentIndex + 1) % slides.length;
        updateCarousel();
      }, 3000);
    }

    function resetAutoSlide() {
      clearInterval(autoSlideInterval);
      startAutoSlide();
    }

    startAutoSlide();
  </script>
</body>
</html>