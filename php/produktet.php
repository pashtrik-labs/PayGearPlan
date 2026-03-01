<?php
session_start();
require "lidhjaDatabazes.php";
$guest_id = session_id(); 

// --- 1. ACTION: ADD TO CART ---
if (isset($_POST['add_to_cart'])) {
    $p_id = $_POST['product_id'];
    $sasia = $_POST['sasia'];

    $check = $conn->query("SELECT * FROM shporta WHERE session_id='$guest_id' AND product_id='$p_id'");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE shporta SET sasia = sasia + $sasia WHERE session_id='$guest_id' AND product_id='$p_id'");
    } else {
        $conn->query("INSERT INTO shporta (session_id, product_id, sasia) VALUES ('$guest_id', '$p_id', '$sasia')");
    }
    header("Location: produktet.php#cart-section");
    exit();
}

// --- 2. ACTION: UPDATE QUANTITY ---
if (isset($_POST['update_cart'])) {
    $c_id = $_POST['cart_id'];
    $new_qty = $_POST['new_qty'];
    $conn->query("UPDATE shporta SET sasia = '$new_qty' WHERE id = '$c_id'");
    header("Location: produktet.php#cart-section");
    exit();
}

// --- 3. ACTION: DELETE FROM CART ---
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    $conn->query("DELETE FROM shporta WHERE id = '$remove_id'");
    header("Location: produktet.php#cart-section");
    exit();
}

// FETCH DATA
$all_products = $conn->query("SELECT * FROM products");
$cart_items = $conn->query("SELECT shporta.*, products.emri, products.cmimi FROM shporta JOIN products ON shporta.product_id = products.id WHERE shporta.session_id = '$guest_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayGearPlan - Shop</title>
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="\PayGearPlan\css\style.css">
    <style>
        html { scroll-behavior: smooth; } /* Makes the jump look nice */
        .cart-container { padding: 5rem 9%; background: #fff; border-top: 2px solid #8B0000; margin-top: 4rem; }
        .cart-table { width: 100%; border-collapse: collapse; font-size: 1.6rem; }
        .cart-table th { background: #f7f7f7; padding: 1.5rem; border: 1px solid #eee; }
        .cart-table td { padding: 1.5rem; text-align: center; border: 1px solid #eee; }
        .qty-input { width: 60px; padding: .5rem; font-size: 1.5rem; text-align: center; }
        .btn-update { background: #8B0000; color: white; padding: .5rem 1rem; border: none; border-radius: .5rem; cursor: pointer; }
        .btn-update:hover { background: #8B0000; }
        .total-box { font-size: 2.5rem; font-weight: bold; text-align: right; padding: 2rem; color: #130f40; }
    </style>
</head>
<body>

<header class="header">
    <a href="#" class="logo"> PayGearPlan </a>
    <nav class="navbar"><a href="\PayGearPlan\html\index.html">home</a></nav>
    <div class="icons">
        <a href="#cart-section"><div class="fas fa-shopping-cart"></div></a>
    </div>
</header>

<section class="products" id="products" style="margin-top: 8rem;">
    <h1 class="heading"> our <span>products</span> </h1>
    
    <?php if($all_products->num_rows > 0): ?>
        <?php while($item = $all_products->fetch_assoc()): ?>
        <div class="swiper product-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide box">
                    <img src="\PayGearPlan\assets\img\<?php echo $item['imazhi']; ?>" alt="">
                    <h3><?php echo $item['emri']; ?></h3>
                    <div class="price"> $<?php echo $item['cmimi']; ?>/- </div>
                    <div class="stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                    </div>
                    
                    <form method="POST" action="produktet.php#cart-section">
                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                        <input type="number" name="sasia" value="1" min="1" class="qty-input" style="margin-bottom: 1rem;">
                        <br>
                        <button type="submit" name="add_to_cart" class="btn">add to cart</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</section>


<section class="cart-container" id="cart-section">
    <h1 class="heading"> your <span>cart</span> </h1>
    
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            while($cart = $cart_items->fetch_assoc()): 
                $sub = $cart['cmimi'] * $cart['sasia'];
                $grand_total += $sub;
            ?>
            <tr>
                <td><?php echo $cart['emri']; ?></td>
                <td>$<?php echo number_format($cart['cmimi'], 2); ?></td>
                <td>
                    <form method="POST" action="produktet.php#cart-section">
                        <input type="hidden" name="cart_id" value="<?php echo $cart['id']; ?>">
                        <input type="number" name="new_qty" value="<?php echo $cart['sasia']; ?>" min="1" class="qty-input">
                        <button type="submit" name="update_cart" class="btn-update">Update</button>
                    </form>
                </td>
                <td>$<?php echo number_format($sub, 2); ?></td>
                <td>
                    <a href="produktet.php?remove=<?php echo $cart['id']; ?>#cart-section" 
                       class="fas fa-trash" style="color:red; font-size:2rem;" 
                       onclick="return confirm('Remove this item?')"></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="total-box"> Grand Total: $<?php echo number_format($grand_total, 2); ?> </div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="#" class="btn" style="background:#130f40;">Proceed to Checkout</a>
    </div>
</section>

<script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>
<script src="\PayGearPlan\Projekti ne web 1\js\script.js"></script>
</body>
</html>