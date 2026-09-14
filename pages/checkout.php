<?php
session_start();
require_once '../database/config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (empty($_SESSION['cart'])) {
    header('Location: menu.php');
    exit;
}

$cartItems = [];
$cartTotal = 0;

$productIds = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));

$stmt = $pdo->prepare("
    SELECT id, name, price, image, status
    FROM products
    WHERE id IN ($placeholders)
    AND status = 'Available'
    ORDER BY id ASC
");

$stmt->execute($productIds);
$products = $stmt->fetchAll();

foreach ($products as $product) {
    $productId = (int) $product['id'];
    $quantity = (int) ($_SESSION['cart'][$productId] ?? 0);

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

if (empty($cartItems)) {
    $_SESSION['cart'] = [];
    header('Location: menu.php');
    exit;
}

$error = '';
$orderSuccess = false;
$orderId = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim($_POST['customer_name'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($customerName === '') {
        $error = 'Please enter your name.';
    } elseif ($contactNumber === '') {
        $error = 'Please enter your contact number.';
    } elseif ($email === '') {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($address === '') {
        $error = 'Please enter your address.';
    }

    if ($error === '') {
        try {
            $pdo->beginTransaction();

            $orderStmt = $pdo->prepare("
                INSERT INTO orders (
                    customer_name,
                    contact_number,
                    email,
                    address,
                    notes,
                    total_amount,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, 'Pending')
            ");

            $orderStmt->execute([
                $customerName,
                $contactNumber,
                $email,
                $address,
                $notes !== '' ? $notes : null,
                $cartTotal
            ]);

            $orderId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_name,
                    quantity,
                    price,
                    subtotal
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($cartItems as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['id'],
                    $item['name'],
                    $item['quantity'],
                    $item['price'],
                    $item['subtotal']
                ]);
            }

            $pdo->commit();

            $_SESSION['cart'] = [];
            $orderSuccess = true;

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'Something went wrong while placing your order. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBN BITES | Checkout</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../header-footer/header.css">
    <link rel="stylesheet" href="../header-footer/footer.css">
    <link rel="stylesheet" href="checkout.css">
</head>
<body>

<?php include '../header-footer/header.php'; ?>

<main>
    <section class="checkout-page">
        <div class="container">

            <div class="checkout-title">
                <p class="checkout-eyebrow">BBN BITES</p>
                <h1>CHECKOUT</h1>
                <p>COMPLETE YOUR ORDER</p>
            </div>

            <?php if ($error !== ''): ?>
                <div class="checkout-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="checkout-layout">

                <div class="checkout-form-wrapper">

                    <form method="POST" action="checkout.php" class="checkout-form">

                        <h2>CUSTOMER INFORMATION</h2>

                        <div class="form-group">
                            <label for="customer_name">FULL NAME</label>
                            <input
                                type="text"
                                id="customer_name"
                                name="customer_name"
                                value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="contact_number">CONTACT NUMBER</label>
                            <input
                                type="text"
                                id="contact_number"
                                name="contact_number"
                                value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">EMAIL ADDRESS</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="address">DELIVERY / PICKUP ADDRESS</label>
                            <textarea
                                id="address"
                                name="address"
                                rows="4"
                                required
                            ><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes">ORDER NOTES <span>(OPTIONAL)</span></label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="3"
                                placeholder="Special instructions for your order..."
                            ><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="checkout-actions">
                            <a href="cart.php" class="checkout-secondary-btn">
                                BACK TO CART
                            </a>

                            <button type="submit" class="checkout-btn">
                                PLACE ORDER
                            </button>
                        </div>

                    </form>

                </div>

                <aside class="checkout-summary">

                    <h2>YOUR ORDER</h2>

                    <?php foreach ($cartItems as $item): ?>

                        <div class="checkout-item">

                            <div class="checkout-item-image">
                                <img
                                    src="../assets/<?= htmlspecialchars($item['image']) ?>"
                                    alt="<?= htmlspecialchars($item['name']) ?>"
                                >
                            </div>

                            <div class="checkout-item-info">
                                <h3><?= htmlspecialchars($item['name']) ?></h3>
                                <p>
                                    <?= (int)$item['quantity'] ?>
                                    ×
                                    ₱<?= number_format((float)$item['price'], 2) ?>
                                </p>
                            </div>

                            <strong>
                                ₱<?= number_format((float)$item['subtotal'], 2) ?>
                            </strong>

                        </div>

                    <?php endforeach; ?>

                    <div class="checkout-total">
                        <span>TOTAL</span>
                        <strong>₱<?= number_format($cartTotal, 2) ?></strong>
                    </div>

                </aside>

            </div>

        </div>
    </section>
</main>

<?php include '../header-footer/footer.php'; ?>
<?php if ($orderSuccess): ?>

<div class="success-modal" id="successModal">

    <div class="success-modal-overlay"></div>

    <div class="success-modal-content">

        <button
            type="button"
            class="success-modal-close"
            onclick="closeSuccessModal()"
            aria-label="Close"
        >
            ×
        </button>

        <div class="success-icon">
            ✓
        </div>

        <p class="success-eyebrow">BBN BITES</p>

        <h2>ORDER CONFIRMED!</h2>

        <p class="success-message">
            Thank you for ordering with BBN Bites.
        </p>

        <div class="order-number">
            <span>ORDER NUMBER</span>
            <strong>#<?= htmlspecialchars($orderId) ?></strong>
        </div>

        <p class="success-status">
            Your order has been received and is currently
            <strong>Pending</strong>.
        </p>

        <a href="menu.php" class="success-modal-btn">
            BACK TO MENU
        </a>

    </div>

</div>

<script>
    document.body.classList.add('modal-open');

    function closeSuccessModal() {
        const modal = document.getElementById('successModal');

        if (modal) {
            modal.classList.add('closing');

            setTimeout(function() {
                window.location.href = 'menu.php';
            }, 200);
        }
    }
</script>

<?php endif; ?>


</body>
</html>