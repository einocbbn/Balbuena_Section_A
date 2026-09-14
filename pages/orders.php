<?php
require_once '../database/customer_auth.php';
require_once '../database/config.php';

$orders = [];
$error = '';

try {
    $stmt = $pdo->prepare("
        SELECT
            id,
            total_amount,
            status,
            created_at,
            updated_at
        FROM orders
        WHERE customer_id = ?
        ORDER BY created_at DESC, id DESC
    ");

    $stmt->execute([
        $_SESSION['customer_id']
    ]);

    $orders = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = 'Unable to load your orders right now.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBN Bites | My Orders</title>

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="orders.css">
    <link rel="stylesheet" href="../header-footer/header.css">
    <link rel="stylesheet" href="../header-footer/footer.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >
</head>
<body>

<?php require_once '../header-footer/header.php'; ?>

<main class="orders-page">

    <section class="orders-container">

        <div class="orders-header">

            <div>
                <p class="orders-eyebrow">YOUR ORDERS</p>
                <h1>MY ORDERS</h1>
            </div>

        </div>

        <?php if ($error !== ''): ?>

            <div class="orders-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php elseif (empty($orders)): ?>

            <div class="orders-empty">

                <h2>NO ORDERS YET</h2>

                <p>
                    You haven't placed any orders yet.
                </p>

                <a href="menu.php" class="orders-btn">
                    ORDER NOW
                </a>

            </div>

        <?php else: ?>

            <div class="orders-list">

                <?php foreach ($orders as $order): ?>

                    <article class="order-card">

                        <div class="order-card-header">

                            <div>
                                <span class="order-label">
                                    ORDER
                                </span>

                                <h2>
                                    #<?= (int) $order['id'] ?>
                                </h2>
                            </div>

                            <span class="order-status">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>

                        </div>

                        <div class="order-card-info">

                            <div>
                                <span>DATE</span>

                                <strong>
                                    <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                </strong>
                            </div>

                            <div>
                                <span>TIME</span>

                                <strong>
                                    <?= date('h:i A', strtotime($order['created_at'])) ?>
                                </strong>
                            </div>

                            <div>
                                <span>TOTAL</span>

                                <strong>
                                    ₱<?= number_format((float) $order['total_amount'], 2) ?>
                                </strong>
                            </div>

                        </div>

                        <div class="order-card-footer">

                            <span>
                                STATUS:
                                <strong>
                                    <?= htmlspecialchars($order['status']) ?>
                                </strong>
                            </span>

                            <a
                                href="order_view.php?id=<?= (int) $order['id'] ?>"
                                class="orders-btn"
                            >
                                VIEW ORDER
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</main>

<?php require_once '../header-footer/footer.php'; ?>

</body>
</html>