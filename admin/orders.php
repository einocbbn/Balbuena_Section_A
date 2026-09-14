<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';

$search = trim($_GET['search'] ?? '');
$selectedStatus = trim($_GET['status'] ?? '');

$statuses = [
    'Pending',
    'Confirmed',
    'Preparing',
    'Ready',
    'Completed',
    'Cancelled'
];

$orders = [];

try {
    $sql = "
        SELECT
            id,
            customer_name,
            contact_number,
            email,
            address,
            total_amount,
            status,
            created_at
        FROM orders
        WHERE 1=1
    ";

    $params = [];

    if ($search !== '') {
        $sql .= "
            AND (
                customer_name LIKE ?
                OR contact_number LIKE ?
                OR email LIKE ?
            )
        ";

        $searchTerm = '%' . $search . '%';

        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($selectedStatus !== '' && in_array($selectedStatus, $statuses, true)) {
        $sql .= " AND status = ?";
        $params[] = $selectedStatus;
    }

    $sql .= " ORDER BY created_at DESC, id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $orders = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = 'Unable to load orders right now.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BBN BITES | Orders</title>

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
                <p class="admin-eyebrow">CUSTOMER ORDERS</p>
                <h1>ORDERS</h1>
            </div>

        </header>

        <?php if (isset($error)): ?>

            <div class="admin-error">
                <p><?= htmlspecialchars($error) ?></p>
            </div>

        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>

            <div class="admin-success">

                <?php if ($_GET['success'] === 'updated'): ?>
                    Order status updated successfully.

                <?php elseif ($_GET['success'] === 'cancelled'): ?>
                    Order cancelled successfully.

                <?php endif; ?>

            </div>

        <?php endif; ?>

        <section class="admin-product-tools">

            <form method="get" action="orders.php" class="admin-search-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Search customer, contact, or email..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <select name="status">

                    <option value="">ALL STATUSES</option>

                    <?php foreach ($statuses as $status): ?>

                        <option
                            value="<?= htmlspecialchars($status) ?>"
                            <?= $selectedStatus === $status ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars(strtoupper($status)) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <button type="submit" class="admin-btn-primary">
                    SEARCH
                </button>

                <?php if ($search !== '' || $selectedStatus !== ''): ?>

                    <a href="orders.php" class="admin-btn-secondary">
                        CLEAR
                    </a>

                <?php endif; ?>

            </form>

        </section>

        <section class="admin-product-table-wrapper">

            <table class="admin-product-table admin-order-table">

                <thead>
                    <tr>
                        <th>ORDER</th>
                        <th>CUSTOMER</th>
                        <th>CONTACT</th>
                        <th>TOTAL</th>
                        <th>STATUS</th>
                        <th>DATE</th>
                        <th>ACTION</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($orders)): ?>

                    <tr>
                        <td colspan="7" class="admin-empty">
                            No orders found.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($orders as $order): ?>

                        <?php
                        $statusClass = strtolower($order['status']);
                        $statusClass = str_replace(' ', '-', $statusClass);
                        ?>

                        <tr>

                            <td>
                                <strong>
                                    #<?= (int) $order['id'] ?>
                                </strong>
                            </td>

                            <td>
                                <strong class="admin-product-name">
                                    <?= htmlspecialchars($order['customer_name']) ?>
                                </strong>
                            </td>

                            <td>
                                <span>
                                    <?= htmlspecialchars($order['contact_number']) ?>
                                </span>

                                <?php if (!empty($order['email'])): ?>

                                    <small class="admin-product-description">
                                        <?= htmlspecialchars($order['email']) ?>
                                    </small>

                                <?php endif; ?>

                            </td>

                            <td>
                                <strong>
                                    ₱<?= number_format((float) $order['total_amount'], 2) ?>
                                </strong>
                            </td>

                            <td>

                                <span class="admin-status status-<?= htmlspecialchars($statusClass) ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>

                            </td>

                            <td>
                                <?= date('M d, Y', strtotime($order['created_at'])) ?>

                                <small class="admin-product-description">
                                    <?= date('h:i A', strtotime($order['created_at'])) ?>
                                </small>
                            </td>

                            <td>

                                <div class="admin-action-buttons">

                                    <a
                                        href="order_view.php?id=<?= (int) $order['id'] ?>"
                                        class="admin-action-edit"
                                    >
                                        VIEW
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </section>

    </main>

</div>

</body>
</html>