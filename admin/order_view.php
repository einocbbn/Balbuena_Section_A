<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId || $orderId <= 0) {
    header('Location: orders.php');
    exit;
}

$order = null;
$orderItems = [];
$error = '';

try {
    $stmt = $pdo->prepare("
        SELECT
            id,
            customer_name,
            contact_number,
            email,
            address,
            notes,
            total_amount,
            status,
            created_at,
            updated_at
        FROM orders
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: orders.php');
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT
            id,
            product_id,
            product_name,
            quantity,
            price,
            subtotal
        FROM order_items
        WHERE order_id = ?
        ORDER BY id ASC
    ");

    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = 'Unable to load order details right now.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= (int) $orderId ?> | BBN Bites Admin</title>

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="admin.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >
</head>

<body>

<div class="admin-layout">

    <aside class="admin-sidebar">

        <div class="admin-sidebar-brand">
            <img src="../assets/logo.png" alt="BBN Bites Logo">

            <div>
                <strong>BBN BITES</strong>
                <span>ADMIN</span>
            </div>
        </div>

        <nav class="admin-nav">

            <a href="dashboard.php" class="admin-nav-link">
                <span>▣</span>
                DASHBOARD
            </a>

            <a href="products.php" class="admin-nav-link">
                <span>▤</span>
                PRODUCTS
            </a>

            <a href="orders.php" class="admin-nav-link active">
                <span>▧</span>
                ORDERS
            </a>

            <a href="messages.php" class="admin-nav-link">
                <span>✉</span>
                MESSAGES
            </a>

            <a href="account.php" class="admin-nav-link">
                <span>◉</span>
                MY ACCOUNT
            </a>

        </nav>

        <div class="admin-sidebar-bottom">

            <div class="admin-user">
                <strong>
                    <?= htmlspecialchars($_SESSION['admin_name']) ?>
                </strong>

                <span>
                    @<?= htmlspecialchars($_SESSION['admin_username']) ?>
                </span>
            </div>

            <a href="logout.php" class="admin-logout">
                LOGOUT
            </a>

        </div>

    </aside>

    <main class="admin-main">

        <header class="admin-topbar">

            <div>
                <p class="admin-eyebrow">ORDER MANAGEMENT</p>
                <h1>ORDER #<?= (int) $order['id'] ?></h1>
            </div>

            <a href="orders.php" class="admin-btn-secondary">
                BACK TO ORDERS
            </a>

        </header>

        <?php if ($error !== ''): ?>

            <div class="admin-error">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>

            <div class="admin-success">
                Order status updated successfully.
            </div>

        <?php elseif (isset($_GET['error'])): ?>

            <div class="admin-error">

                <?php if ($_GET['error'] === 'invalid_status'): ?>

                    Invalid order status.

                <?php elseif ($_GET['error'] === 'update_failed'): ?>

                    Unable to update the order status right now.

                <?php endif; ?>

            </div>

        <?php endif; ?>

        <?php else: ?>

            <section class="admin-order-summary">

                <div class="admin-order-summary-header">

                    <div>
                        <p class="admin-eyebrow">ORDER INFORMATION</p>

                        <h2>
                            ORDER #<?= (int) $order['id'] ?>
                        </h2>
                    </div>

                    <form method="post" action="order_status_update.php?id=<?= (int) $order['id'] ?>" class="admin-order-status-form">

                        <select name="status" required>

                            <?php foreach (['Pending', 'Confirmed', 'Preparing', 'Ready', 'Completed', 'Cancelled'] as $status): ?>

                                <option
                                    value="<?= htmlspecialchars($status) ?>"
                                    <?= $order['status'] === $status ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($status) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <button type="submit" class="admin-btn-primary">
                            UPDATE STATUS
                        </button>

                    </form>

                </div>

                <div class="admin-order-info-grid">

                    <div class="admin-order-info-item">

                        <span>ORDER DATE</span>

                        <strong>
                            <?= date('M d, Y', strtotime($order['created_at'])) ?>
                        </strong>

                        <small>
                            <?= date('h:i A', strtotime($order['created_at'])) ?>
                        </small>

                    </div>

                    <div class="admin-order-info-item">

                        <span>CUSTOMER</span>

                        <strong>
                            <?= htmlspecialchars($order['customer_name']) ?>
                        </strong>

                    </div>

                    <div class="admin-order-info-item">

                        <span>CONTACT NUMBER</span>

                        <strong>
                            <?= htmlspecialchars($order['contact_number']) ?>
                        </strong>

                    </div>

                    <div class="admin-order-info-item">

                        <span>EMAIL</span>

                        <strong>
                            <?= htmlspecialchars($order['email']) ?>
                        </strong>

                    </div>

                </div>

            </section>

            <section class="admin-order-details-grid">

                <div class="admin-order-items-card">

                    <div class="admin-panel-header">

                        <div>
                            <p class="admin-eyebrow">PURCHASE</p>
                            <h2>ORDERED PRODUCTS</h2>
                        </div>

                        <span class="admin-panel-count">
                            <?= count($orderItems) ?>
                        </span>

                    </div>

                    <?php if (empty($orderItems)): ?>

                        <div class="admin-empty">
                            No products found for this order.
                        </div>

                    <?php else: ?>

                        <div class="admin-order-items">

                            <?php foreach ($orderItems as $item): ?>

                                <div class="admin-order-item">

                                    <div class="admin-order-item-info">

                                        <strong>
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </strong>

                                        <span>
                                            ₱<?= number_format((float) $item['price'], 2) ?>
                                            ×
                                            <?= (int) $item['quantity'] ?>
                                        </span>

                                    </div>

                                    <strong class="admin-order-item-subtotal">
                                        ₱<?= number_format((float) $item['subtotal'], 2) ?>
                                    </strong>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                    <div class="admin-order-total">

                        <span>
                            TOTAL
                        </span>

                        <strong>
                            ₱<?= number_format((float) $order['total_amount'], 2) ?>
                        </strong>

                    </div>

                </div>

                <div class="admin-order-customer-card">

                    <div class="admin-panel-header">

                        <div>
                            <p class="admin-eyebrow">CUSTOMER</p>
                            <h2>ORDER DETAILS</h2>
                        </div>

                    </div>

                    <div class="admin-customer-details">

                        <div>

                            <span>NAME</span>

                            <strong>
                                <?= htmlspecialchars($order['customer_name']) ?>
                            </strong>

                        </div>

                        <div>

                            <span>CONTACT</span>

                            <strong>
                                <?= htmlspecialchars($order['contact_number']) ?>
                            </strong>

                        </div>

                        <div>

                            <span>EMAIL</span>

                            <strong>
                                <?= htmlspecialchars($order['email']) ?>
                            </strong>

                        </div>

                        <div>

                            <span>ADDRESS</span>

                            <strong>
                                <?= nl2br(htmlspecialchars($order['address'])) ?>
                            </strong>

                        </div>

                        <?php if (!empty($order['notes'])): ?>

                            <div>

                                <span>NOTES</span>

                                <strong>
                                    <?= nl2br(htmlspecialchars($order['notes'])) ?>
                                </strong>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </section>

        <?php endif; ?>

    </main>

</div>

</body>
</html>