<?php
require_once '../database/customer_auth.php';
require_once '../database/config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

    if ($action === 'add' && $productId > 0) {
        $stmt = $pdo->prepare("
            SELECT id
            FROM products
            WHERE id = ? AND status = 'Available'
            LIMIT 1
        ");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if ($product) {
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]++;
            } else {
                $_SESSION['cart'][$productId] = 1;
            }
        }

        header('Location: cart.php');
        exit;
    }

    if ($action === 'increase' && $productId > 0) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]++;
        }

        header('Location: cart.php');
        exit;
    }

    if ($action === 'decrease' && $productId > 0) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]--;

            if ($_SESSION['cart'][$productId] <= 0) {
                unset($_SESSION['cart'][$productId]);
            }
        }

        header('Location: cart.php');
        exit;
    }

    if ($action === 'remove' && $productId > 0) {
        unset($_SESSION['cart'][$productId]);

        header('Location: cart.php');
        exit;
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];

        header('Location: cart.php');
        exit;
    }
}

$cartItems = [];
$cartTotal = 0;
if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    $stmt = $pdo->prepare("
        SELECT id, name, description, price, image, category, status
        FROM products
        WHERE id IN ($placeholders)
        ORDER BY id ASC
    ");

    $stmt->execute($productIds);
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        $productId = (int) $product['id'];
        $quantity = (int) $_SESSION['cart'][$productId];

        if ($quantity <= 0) {
            continue;
        }

        $price = (float) $product['price'];
        $subtotal = $price * $quantity;

        $product['quantity'] = $quantity;
        $product['subtotal'] = $subtotal;

        $cartItems[] = $product;
        $cartTotal += $subtotal;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBN BITES | Cart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../header-footer/header.css">
    <link rel="stylesheet" href="../header-footer/footer.css">
    <link rel="stylesheet" href="cart.css">
</head>
<body>
<?php include '../header-footer/header.php'; ?>
<main>
    <section class="cart-page">
        <div class="container">
            <div class="cart-title">
                <p class="cart-eyebrow">BBN BITES</p>
                <h1>YOUR CART</h1>
                <p>REVIEW YOUR ORDER</p>
            </div>
            <?php if (empty($cartItems)): ?>
                <div class="empty-cart">
                    <h2>Your cart is empty.</h2>
                    <p>Add some delicious BBN Bites to get started.</p>
                    <a href="menu.php" class="cart-btn">GO TO MENU</a>
                </div>
            <?php else: ?>

                        <div class="cart-layout">
                            <div class="cart-items">
                                <?php foreach ($cartItems as $item): ?>
                                    <article class="cart-item">
                                        <div class="cart-item-image">
                                            <img src="../assets/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        </div>

                                        <div class="cart-item-info">
                                            <h2><?= htmlspecialchars($item['name']) ?></h2>
                                            <p>₱<?= number_format((float)$item['price'], 2) ?> each</p>

                                            <div class="cart-quantity">
                                                <form method="POST" action="cart.php">
                                                    <input type="hidden" name="action" value="decrease">
                                                    <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                                                    <button type="submit" class="quantity-btn">−</button>
                                                </form>

                                                <span><?= (int)$item['quantity'] ?></span>

                                                <form method="POST" action="cart.php">
                                                    <input type="hidden" name="action" value="increase">
                                                    <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                                                    <button type="submit" class="quantity-btn">+</button>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="cart-item-right">
                                            <strong>
                                                ₱<?= number_format((float)$item['subtotal'], 2) ?>
                                            </strong>

                                            <form method="POST" action="cart.php">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                                                <button type="submit" class="remove-btn">REMOVE</button>
                                            </form>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    <aside class="cart-summary">
                        <h2>ORDER SUMMARY</h2>
                        <div class="summary-row">
                            <span>Items</span>
                            <span><?= array_sum($_SESSION['cart']) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>₱<?= number_format($cartTotal, 2) ?></span>
                        </div>
                        <div class="summary-total">
                            <span>TOTAL</span>
                            <strong>₱<?= number_format($cartTotal, 2) ?></strong>
                        </div>
                        <a href="menu.php" class="cart-secondary-btn">CONTINUE SHOPPING</a>

                        <a href="checkout.php" class="cart-btn checkout-btn">CHECKOUT</a>

                        <form method="POST" action="cart.php" class="clear-cart-form">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="clear-cart-btn">CLEAR CART</button>
                        </form>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php include '../header-footer/footer.php'; ?>
</body>
</html>