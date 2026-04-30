<?php
session_start();
require_once 'lidhjaDatabazes.php';

// 1. CLASS DEFINITION FOR CART OPERATIONS
class CartManager {
    private $db;
    private $conn;

    public function __construct($db_instance) {
        $this->db = $db_instance;
        $this->conn = $this->db->getConnection();
    }

    public function addToCart($guest_id, $p_id, $sasia) {
        $stmt_check = $this->conn->prepare("SELECT * FROM shporta WHERE session_id = ? AND product_id = ?");
        $stmt_check->bind_param("si", $guest_id, $p_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            $stmt_update = $this->conn->prepare("UPDATE shporta SET sasia = sasia + ? WHERE session_id = ? AND product_id = ?");
            $stmt_update->bind_param("isi", $sasia, $guest_id, $p_id);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $stmt_insert = $this->conn->prepare("INSERT INTO shporta (session_id, product_id, sasia) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sii", $guest_id, $p_id, $sasia);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt_check->close();
    }

    public function updateQuantity($c_id, $new_qty) {
        $stmt = $this->conn->prepare("UPDATE shporta SET sasia = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_qty, $c_id);
        $stmt->execute();
        $stmt->close();
    }

    public function deleteFromCart($remove_id) {
        $stmt = $this->conn->prepare("DELETE FROM shporta WHERE id = ?");
        $stmt->bind_param("i", $remove_id);
        $stmt->execute();
        $stmt->close();
    }

    public function getAllProducts() {
        return $this->conn->query("SELECT * FROM products");
    }

    public function getCartItems($guest_id) {
        $query = "SELECT shporta.*, products.emri, products.cmimi 
                  FROM shporta 
                  JOIN products ON shporta.product_id = products.id 
                  WHERE shporta.session_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $guest_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Close connection method to prevent resource leaks
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// 2. INITIALIZATION
$database = new Database();
$cartManager = new CartManager($database);
$guest_id = session_id();

// 3. ACTION HANDLERS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_to_cart'])) {
        $cartManager->addToCart($guest_id, $_POST['product_id'], $_POST['sasia']);
        $cartManager->closeConnection();
        header("Location: produktet.php#cart-section");
        exit();
    }

    if (isset($_POST['update_cart'])) {
        $cartManager->updateQuantity($_POST['cart_id'], $_POST['new_qty']);
        $cartManager->closeConnection();
        header("Location: produktet.php#cart-section");
        exit();
    }
}

if (isset($_GET['remove'])) {
    $cartManager->deleteFromCart($_GET['remove']);
    $cartManager->closeConnection();
    header("Location: produktet.php#cart-section");
    exit();
}

// FETCH DATA
$all_products = $cartManager->getAllProducts();
$cart_items = $cartManager->getCartItems($guest_id);
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
        html { scroll-behavior: smooth; }
        .cart-container { padding: 5rem 9%; background: #fff; border-top: 2px solid #8B0000; margin-top: 4rem; }
        .cart-table { width: 100%; border-collapse: collapse; font-size: 1.6rem; }
        .cart-table th { background: #f7f7f7; padding: 1.5rem; border: 1px solid #eee; }
        .cart-table td { padding: 1.5rem; text-align: center; border: 1px solid #eee; }
        .qty-input { width: 60px; padding: .5rem; font-size: 1.5rem; text-align: center; }
        .btn-update { background: #8B0000; color: white; padding: .5rem 1rem; border: none; border-radius: .5rem; cursor: pointer; }
        .btn-update:hover { background: #6b0000; }
        .total-box { font-size: 2.5rem; font-weight: bold; text-align: right; padding: 2rem; color: #130f40; }
    </style>
</head>
<body>

<header class="header">
    <a href="#" class="logo"> PayGearPlan </a>
    <nav class="navbar"><a href="\PayGearPlan\html\index.html">home</a></nav>
    <div class="icons">
        <a href="#cart-section"><div class="fas fa-shopping-cart"></div></a>
        <a href="\PayGearPlan\php\login.php"><div class="fas fa-user" id="login-btn"></div></a>
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

<?php $cartManager->closeConnection(); ?>
<script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>
<script src="\PayGearPlan\Projekti ne web 1\js\script.js"></script>
</body>
</html>
