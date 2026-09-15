<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId || $orderId <= 0) {
    header('Location: orders.php?error=invalid_order');
    exit;
}

$error = '';

try {
    $stmt = $pdo->prepare("
        SELECT id, status
        FROM orders
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: orders.php?error=order_not_found');
        exit;
    }

    if ($order['status'] !== 'Completed') {
        header('Location: orders.php?error=delete_not_allowed');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Delete Order | BBN Bites Admin</title>

            <link rel="stylesheet" href="../css/styles.css">
            <link rel="stylesheet" href="admin.css">
            
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
                        <h1>DELETE ORDER</h1>
                    </div>

                    <a href="order_view.php?id=<?= (int) $orderId ?>" class="admin-btn-secondary">
                        BACK TO ORDER
                    </a>
                </header>

                <section class="admin-delete-card">
                    <p class="admin-eyebrow">CONFIRM ACTION</p>

                    <h2>DELETE ORDER #<?= (int) $orderId ?>?</h2>

                    <p>
                        This order is marked as completed.
                        Deleting it will permanently remove the order and its ordered products.
                    </p>

                    <form method="post" action="order_delete.php?id=<?= (int) $orderId ?>">
                        <button type="submit" class="admin-action-delete">
                            DELETE ORDER
                        </button>

                        <a href="order_view.php?id=<?= (int) $orderId ?>" class="admin-btn-secondary">
                            CANCEL
                        </a>
                    </form>
                </section>

            </main>

        </div>

        </body>
        </html>
        <?php
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT id, status
            FROM orders
            WHERE id = ?
            FOR UPDATE
        ");

        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            $pdo->rollBack();
            header('Location: orders.php?error=order_not_found');
            exit;
        }

        if ($order['status'] !== 'Completed') {
            $pdo->rollBack();
            header('Location: orders.php?error=delete_not_allowed');
            exit;
        }

        $stmt = $pdo->prepare("
            DELETE FROM orders
            WHERE id = ?
            AND status = 'Completed'
        ");

        $stmt->execute([$orderId]);

        if ($stmt->rowCount() !== 1) {
            $pdo->rollBack();
            header('Location: orders.php?error=delete_failed');
            exit;
        }

        $pdo->commit();

        header('Location: orders.php?success=deleted');
        exit;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: orders.php?error=delete_failed');
    exit;
}