<?php
session_start();
include '../db_connection.php'; 
include '../view/header.php';


if (isset($_GET['remove_favorite'])) {
    $productId = intval($_GET['remove_favorite']);
    $userId = 1; 

   
    $stmt = $conn->prepare("DELETE FROM favorit WHERE pengguna_id = ? AND produk_id = ?");
    $stmt->bind_param("ii", $userId, $productId);

    if ($stmt->execute()) {
       
    } else {
        echo "Error: " . $stmt->error;
    }

   
}


$userId = 1; 
$stmt = $conn->prepare("SELECT produk_id FROM favorit WHERE pengguna_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row['produk_id'];
}


function isFavorited($productId, $favorites) {
    return in_array($productId, $favorites);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorite Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="heading">
    <h1>FAVORITE</h1>
</div>

<div class="product-container">
    <?php
    // Product list array
    $products = [
        [
            'id' => 1,
            'title' => 'Tiro 24 T-Shirt Jersey',
            'category' => 'Pria',
            'price' => 'IDR 275.000',
            'img' => 'https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_IS1657_b%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75',
            'alt' => 'Rok Sifon Motif'
        ],
        [
            'id' => 2,
            'title' => 'Phoenix Suns Icon',
            'category' => 'Pria',
            'price' => 'IDR 827.000',
            'img' => 'https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_DV4855-570_d%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1080&q=75',
            'alt' => 'Rok Volume Gather'
        ],
        [
            'id' => 3,
            'title' => '2In1 Phoenix',
            'category' => 'Wanita',
            'price' => 'IDR 400.000',
            'img' => 'https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_LA2404001-03_a%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75',
            'alt' => 'Rok Volume Gather'
        ],
        [
            'id' => 4,
            'title' => 'Laica Dress',
            'category' => 'Wanita',
            'price' => 'IDR 830.000',
            'img' => 'https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_IR7468_c%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75',
            'alt' => 'Rok Volume Gather'
        ],
        [
            'id' => 5,
            'title' => 'Sport Essentials French',
            'category' => 'Pria',
            'price' => 'IDR 320.000',
            'img' => 'https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_MS41520-NNY_b%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75',
            'alt' => 'Rok Volume Gather'
        ],
        [
            'id' => 6,
            'title' => 'Athletics French Jogger',
            'category' => 'Pria',
            'price' => 'IDR 599.000',
            'img' => 'https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_FZ0767-410_b%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75',
            'alt' => ''
        ],
    ];

    foreach ($products as $product):
            if (isFavorited($product['id'], $favorites)):
        ?>
        <div class="product">
            <img src="<?php echo htmlspecialchars($product['img']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
            <h2><?php echo htmlspecialchars($product['title']); ?></h2>
            <p><?php echo htmlspecialchars($product['price']); ?></p>
			<!-- Tombol hapus -->
        <a href="favorite.php?remove_favorite=<?php echo $product['id']; ?>" class="remove-btn">Hapus</a>
        </div>
        <?php
            endif;
        endforeach;
        ?>
</div>
</body>
</html>
<footer class="bg-gray-800 text-white mt-12">
    <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="py-8">
            <h3 class="text-lg font-semibold mb-4">About Us</h3>
            <p class="text-gray-400">We are a leading sportswear brand committed to providing high-quality
                products
                for athletes and fitness enthusiasts.</p>
            <div class="mt-4">
                <a class="text-gray-400 hover:text-white" href="#"><i class="fab fa-facebook-f"></i></a>
                <a class="ml-4 text-gray-400 hover:text-white" href="#"><i class="fab fa-twitter"></i></a>
                <a class="ml-4 text-gray-400 hover:text-white" href="#"><i class="fab fa-instagram"></i></a>
                <a class="ml-4 text-gray-400 hover:text-white" href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <div class="py-8">
            <h3 class="text-lg font-semibold mb-4">Customer Service</h3>
            <ul class="text-gray-400">
                <li class="mb-2"><a class="hover:text-white" href="#">Contact Us</a></li>
                <li class="mb-2"><a class="hover:text-white" href="#">Order Tracking</a></li>
                <li class="mb-2"><a class="hover:text-white" href="#">Returns & Exchanges</a></li>
                <li class="mb-2"><a class="hover:text-white" href="#">Shipping & Delivery</a></li>
                <li class="mb-2"><a class="hover:text-white" href="#">FAQs</a></li>
            </ul>
        </div>
        <div class="py-8">
            <h3 class="text-lg font-semibold mb-4">Newsletter</h3>
            <p class="text-gray-400">Subscribe to get the latest information on new products and upcoming sales.
            </p>
            <form class="mt-4">
                <input class="w-full p-2 rounded-lg text-gray-900" placeholder="Enter your email" type="email" />
                <button class="mt-2 w-full bg-red-600 p-2 rounded-lg hover:bg-red-700"
                    type="submit">Subscribe</button>
            </form>
        </div>
        <div class="mt-8 text-center text-gray-400">
            <p>© 2023 Movr. All rights reserved.</p>
        </div>
</footer>

<script>
document.querySelectorAll('.heart-icon').forEach(icon => {
    icon.addEventListener('click', () => {
        const productId = icon.getAttribute('data-product-id');
        const isFavorited = icon.textContent === '♥'; 

       
        icon.textContent = isFavorited ? '♡' : '♥';

       
        fetch('favorite_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ productId: productId, favorite: !isFavorited })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
              
                icon.textContent = isFavorited ? '♥' : '♡';
                alert('Failed to update favorite status.');
            }
        })
        .catch(() => {
            
            icon.textContent = isFavorited ? '♥' : '♡';
            alert('Error updating favorite status.');
        });
    });
});
</script>


<style>
    * {
            margin: 0;
             padding: 0;
             box-sizing: border-box;
             outline: none;
             border: none;
             text-decoration: none;
             font-family: "Franklin Gothic Medium", "Arial Narrow", Arial, sans-serif;
        }

        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
             width: 100vw;

        }

        h1 {
            font-size: 4rem;
           
        }

        .nav-link {
            position: relative;
            display: inline-block;
            padding: 0.5rem 1rem;
            transition: color 0.3s ease;
            }
            
            .nav-link::after {
            content: '';
            position: absolute;
            width: 100%;
            transform: scaleX(0);
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #000;
            transform-origin: bottom right;
            transition: transform 0.25s ease-out;
            }
            
            .nav-link:hover::after {
            transform: scaleX(1);
            transform-origin: bottom left;
            }
            
            .nav-icon:hover {
            color: #000;
            transform: scale(1.2);
            }
            
            .hover-image {
            position: relative;
            }
            
            .hover-image img {
            transition: opacity 0.3s ease;
            }
            
            .hover-image img.second {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            }
            
            .hover-image:hover img.first {
            opacity: 0;
            }
            
            .hover-image:hover img.second {
            opacity: 1;
            }
            
            .btn {
            transition: transform 0.2s ease, background-color 0.2s ease;
            }
            
            .btn:active {
            transform: scale(0.95);
            }
            
            .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 600px;
            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
            z-index: 6;
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
            
            }
            
            .dropdown:hover .dropdown-content {
            display: block;
            }
            
            .dropdown-voucher .dropdown-content {
            min-width: 150px;
            
            
            
            }
            
            .dropdown-outlet .dropdown-content {
            min-width: 150px;
            
            }

        .heading {
            font-size:35px;
            font-family: sans-serif;
            padding-left: 5%;
            text-decoration: underline;
        }

        .product-container {
            margin: 0 auto;      
            padding: 20px; 
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            display: grid;     
            grid-template-columns: repeat(2, 1fr); 
            grid-gap: 20px;
            width: 90%;
            display: flex;
            flex-wrap: wrap;
         
          
            

        }

        .product {
            display:flex ;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;

            border: 1px solid #ccc;
             padding: 20px;
             flex: 1 1 45%;
             align-items: flex-start;
             margin-bottom: 20px; 
        }

        .product:last-child {
             border-bottom: none;
        } 

        .product img {
            width: 10%;
            height: auto;
            border-radius: 5px;
        }

        .product-details {
            flex: 1;
            margin-left: 0;
            padding-left: 0%;
        }

        .product-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 20px;

        }

        .product-info {
            font-size: 15px;
            color: #666;
            /* margin-bottom: 0px; */
            gap: 2%;
        }

        .product-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-actions .change {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }

        .product-actions .change:hover {
            text-decoration: underline;
        }

        .product-actions button {
            padding: 10px 20px;
            border: 1px solid #000;
            background: none;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }

        .product-actions button:hover {
            background: #f0f0f0;
        }

        .product-actions .delete {
            font-size: 18px;
            cursor: pointer;
            color: #666;
        }

        .product-actions .delete:hover {
            color: #ff0000;
        }

        .price {
            color: red;
            font-size: 20px;
            font-weight: bold;
            margin-top:20%;
            text-align: center;
        }

        .heart-icon {
            color: red;
            font-size: 250%;
            cursor: pointer;
            left: 20px;
            margin-top:20%;
        }

        .fa-regular{
            font-size:150% ;
            margin-top:20%;
        }
		
		.remove-btn {
			color: red;
			text-decoration: none;
			font-weight: bold;
			margin-top: 10px;
			display: inline-block;
		}
		.remove-btn:hover {
			text-decoration: underline;
		}
</style>
</body>
</html>
