<?php
require_once '../database/customer_auth.php';
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
            customer_id,
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
        AND customer_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $orderId,
        $_SESSION['customer_id']
    ]);

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
    $error = 'Unable to load your order details right now.';
}

$statuses = [
    'Pending',
    'Confirmed',
    'Preparing',
    'Ready',
    'Completed'
];

$currentStatus = $order['status'] ?? 'Pending';

$currentStatusIndex = array_search($currentStatus, $statuses, true);

if ($currentStatusIndex === false) {
    $currentStatusIndex = -1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= (int) $orderId ?> | BBN Bites</title>

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="orders.css">
    <link rel="stylesheet" href="../header-footer/header.css">
    <link rel="stylesheet" href="../header-footer/footer.css">
    <link rel="stylesheet" href="order_view.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >
</head>
<body>

<?php require_once '../header-footer/header.php'; ?>

<main class="customer-order-page">

    <section class="customer-order-container">

        <div class="customer-order-top">

            <div>
                <p class="customer-order-eyebrow">ORDER DETAILS</p>

                <h1>
                    ORDER #<?= (int) $orderId ?>
                </h1>
            </div>

            <a href="orders.php" class="customer-order-back">
                BACK TO MY ORDERS
            </a>

        </div>

        <?php if ($error !== ''): ?>

            <div class="customer-order-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php elseif ($order): ?>

            <?php if ($currentStatus === 'Cancelled'): ?>

                <section class="customer-order-status-card cancelled">

                    <p class="customer-order-status-label">
                        ORDER STATUS
                    </p>

                    <h2>
                        ORDER CANCELLED
                    </h2>

                    <p>
                        This order has been cancelled.
                    </p>

                </section>

            <?php else: ?>

                <section class="customer-order-status-card">

                    <div class="customer-order-status-heading">

                        <div>
                            <p class="customer-order-status-label">
                                ORDER STATUS
                            </p>

                            <h2>
                                <?= htmlspecialchars($currentStatus) ?>
                            </h2>
                        </div>

                    </div>

                    <div class="order-tracker">

                        <?php foreach ($statuses as $index => $status): ?>

                            <?php
                            $isCompleted = $currentStatusIndex >= $index;
                            $isCurrent = $currentStatus === $status;
                            ?>

                            <div class="tracker-step <?= $isCompleted ? 'completed' : '' ?> <?= $isCurrent ? 'current' : '' ?>">

                                <div class="tracker-circle">

                                    <?php if ($isCompleted): ?>
                                        ✓
                                    <?php else: ?>
                                        <?= $index + 1 ?>
                                    <?php endif; ?>

                                </div>

                                <span>
                                    <?= htmlspecialchars(strtoupper($status)) ?>
                                </span>

                            </div>

                            <?php if ($index < count($statuses) - 1): ?>

                                <div class="tracker-line <?= $currentStatusIndex > $index ? 'completed' : '' ?>"></div>

                            <?php endif; ?>

                        <?php endforeach; ?>

                    </div>

                    <p class="customer-order-status-message">

                        <?php if ($currentStatus === 'Pending'): ?>

                            Your order has been received and is waiting for confirmation.

                        <?php elseif ($currentStatus === 'Confirmed'): ?>

                            Your order has been confirmed by BBN Bites.

                        <?php elseif ($currentStatus === 'Preparing'): ?>

                            Your order is currently being prepared.

                        <?php elseif ($currentStatus === 'Ready'): ?>

                            Your order is ready!

                        <?php elseif ($currentStatus === 'Completed'): ?>

                            Your order has been completed. Thank you for ordering with BBN Bites!

                        <?php endif; ?>

                    </p>

                </section>

            <?php endif; ?>

            <section class="customer-order-grid">

                <div class="customer-order-products">

                    <div class="customer-order-card-header">

                        <div>
                            <p class="customer-order-card-eyebrow">
                                PURCHASE
                            </p>

                            <h2>
                                ORDERED PRODUCTS
                            </h2>
                        </div>

                        <span class="customer-order-count">
                            <?= count($orderItems) ?>
                        </span>

                    </div>

                    <?php if (empty($orderItems)): ?>

                        <div class="customer-order-empty">
                            No products found for this order.
                        </div>

                    <?php else: ?>

                        <div class="customer-order-items">

                            <?php foreach ($orderItems as $item): ?>

                                <div class="customer-order-item">

                                    <div class="customer-order-item-info">

                                        <strong>
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </strong>

                                        <span>
                                            ₱<?= number_format((float) $item['price'], 2) ?>
                                            ×
                                            <?= (int) $item['quantity'] ?>
                                        </span>

                                    </div>

                                    <strong class="customer-order-item-subtotal">
                                        ₱<?= number_format((float) $item['subtotal'], 2) ?>
                                    </strong>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                    <div class="customer-order-total">

                        <span>
                            TOTAL
                        </span>

                        <strong>
                            ₱<?= number_format((float) $order['total_amount'], 2) ?>
                        </strong>

                    </div>

                </div>

                <div class="customer-order-information">

                    <div class="customer-order-card-header">

                        <div>
                            <p class="customer-order-card-eyebrow">
                                CUSTOMER
                            </p>

                            <h2>
                                ORDER INFORMATION
                            </h2>
                        </div>

                    </div>

                    <div class="customer-order-details">

                        <div>
                            <span>ORDER DATE</span>

                            <strong>
                                <?= date('M d, Y', strtotime($order['created_at'])) ?>
                            </strong>

                            <small>
                                <?= date('h:i A', strtotime($order['created_at'])) ?>
                            </small>
                        </div>

                        <div>
                            <span>FULL NAME</span>

                            <strong>
                                <?= htmlspecialchars($order['customer_name']) ?>
                            </strong>
                        </div>

                        <div>
                            <span>CONTACT NUMBER</span>

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
                                <span>ORDER NOTES</span>

                                <strong>
                                    <?= nl2br(htmlspecialchars($order['notes'])) ?>
                                </strong>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </section>

        <?php endif; ?>

    </section>

</main>

<?php require_once '../header-footer/footer.php'; ?>

</body>
</html>