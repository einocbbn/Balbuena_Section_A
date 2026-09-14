<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';

$totalOrders = 0;
$pendingOrders = 0;
$completedOrders = 0;
$totalSales = 0;
$unreadMessages = 0;
$totalProducts = 0;

try {
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM orders
    ");
    $totalOrders = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM orders
        WHERE status = 'Pending'
    ");
    $pendingOrders = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM orders
        WHERE status = 'Completed'
    ");
    $completedOrders = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("
        SELECT COALESCE(SUM(total_amount), 0)
        FROM orders
        WHERE status != 'Cancelled'
    ");
    $totalSales = (float) $stmt->fetchColumn();

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM customer_messages
        WHERE status = 'Unread'
    ");
    $unreadMessages = (int) $stmt->fetchColumn();

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM products
    ");
    $totalProducts = (int) $stmt->fetchColumn();

} catch (PDOException $e) {
    $dashboardError = 'Unable to load dashboard information.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BBN BITES | Admin Dashboard</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="admin.css">
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

            <a href="dashboard.php" class="admin-nav-link active">
                <span>▣</span>
                DASHBOARD
            </a>

            <a href="products.php" class="admin-nav-link">
                <span>▤</span>
                PRODUCTS
            </a>

            <a href="orders.php" class="admin-nav-link">
                <span>▧</span>
                ORDERS
            </a>

            <a href="messages.php" class="admin-nav-link">
                <span>✉</span>
                MESSAGES

                <?php if ($unreadMessages > 0): ?>
                    <span class="admin-nav-badge">
                        <?= $unreadMessages ?>
                    </span>
                <?php endif; ?>
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
                <p class="admin-eyebrow">BBN BITES</p>
                <h1>ADMIN DASHBOARD</h1>
            </div>

            <div class="admin-welcome">
                Welcome,
                <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong>
            </div>
        </header>

        <?php if (isset($dashboardError)): ?>

            <div class="admin-error">
                <p><?= htmlspecialchars($dashboardError) ?></p>
            </div>

        <?php endif; ?>

        <section class="admin-stats">

            <a href="orders.php" class="admin-stat-card">
                <span class="admin-stat-label">TOTAL ORDERS</span>
                <strong><?= $totalOrders ?></strong>
                <small>View all orders</small>
            </a>

            <a href="orders.php?status=Pending" class="admin-stat-card">
                <span class="admin-stat-label">PENDING ORDERS</span>
                <strong><?= $pendingOrders ?></strong>
                <small>Orders waiting</small>
            </a>

            <a href="orders.php?status=Completed" class="admin-stat-card">
                <span class="admin-stat-label">COMPLETED ORDERS</span>
                <strong><?= $completedOrders ?></strong>
                <small>Completed orders</small>
            </a>

            <div class="admin-stat-card">
                <span class="admin-stat-label">TOTAL SALES</span>
                <strong>₱<?= number_format($totalSales, 2) ?></strong>
                <small>Non-cancelled orders</small>
            </div>

        </section>

        <section class="admin-dashboard-grid">

            <div class="admin-panel">

                <div class="admin-panel-header">
                    <div>
                        <p class="admin-eyebrow">MENU MANAGEMENT</p>
                        <h2>PRODUCTS</h2>
                    </div>

                    <span class="admin-panel-count">
                        <?= $totalProducts ?>
                    </span>
                </div>

                <p>
                    Add, edit, view, and remove products from your BBN Bites menu.
                </p>

                <div class="admin-panel-actions">
                    <a href="products.php" class="admin-btn-secondary">
                        VIEW PRODUCTS
                    </a>

                    <a href="product_add.php" class="admin-btn-primary">
                        ADD PRODUCT
                    </a>
                </div>

            </div>

            <div class="admin-panel">

                <div class="admin-panel-header">
                    <div>
                        <p class="admin-eyebrow">CUSTOMER ORDERS</p>
                        <h2>ORDERS</h2>
                    </div>

                    <span class="admin-panel-count">
                        <?= $totalOrders ?>
                    </span>
                </div>

                <p>
                    Review customer orders and update their order status.
                </p>

                <div class="admin-panel-actions">
                    <a href="orders.php" class="admin-btn-primary">
                        MANAGE ORDERS
                    </a>
                </div>

            </div>

            <div class="admin-panel">

                <div class="admin-panel-header">
                    <div>
                        <p class="admin-eyebrow">CUSTOMER FEEDBACK</p>
                        <h2>MESSAGES</h2>
                    </div>

                    <span class="admin-panel-count">
                        <?= $unreadMessages ?>
                    </span>
                </div>

                <p>
                    Read feedback, questions, and messages submitted through Contact Us.
                </p>

                <div class="admin-panel-actions">
                    <a href="messages.php" class="admin-btn-primary">
                        VIEW MESSAGES
                    </a>
                </div>

            </div>

            <div class="admin-panel">

                <div class="admin-panel-header">
                    <div>
                        <p class="admin-eyebrow">ACCOUNT</p>
                        <h2>MY ACCOUNT</h2>
                    </div>
                </div>

                <p>
                    Manage your administrator account information and password.
                </p>

                <div class="admin-panel-actions">
                    <a href="account.php" class="admin-btn-primary">
                        MANAGE ACCOUNT
                    </a>
                </div>

            </div>

        </section>

    </main>

</div>

</body>
</html>