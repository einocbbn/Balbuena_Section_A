<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';

$search = trim($_GET['search'] ?? '');
$selectedStatus = trim($_GET['status'] ?? '');

$statuses = [
    'Unread',
    'Read'
];

$messages = [];
$error = '';

try {
    $sql = "
        SELECT
            id,
            name,
            email,
            phone,
            subject,
            message,
            status,
            created_at
        FROM customer_messages
        WHERE 1=1
    ";

    $params = [];

    if ($search !== '') {
        $sql .= "
            AND (
                name LIKE ?
                OR email LIKE ?
                OR subject LIKE ?
                OR message LIKE ?
            )
        ";

        $searchTerm = '%' . $search . '%';

        $params[] = $searchTerm;
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

    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Unable to load messages right now.';
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | BBN Bites Admin</title>
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

        <a href="orders.php" class="admin-nav-link">
            <span>▧</span>
            ORDERS
        </a>

        <a href="messages.php" class="admin-nav-link active">
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
            <p class="admin-eyebrow">CUSTOMER COMMUNICATION</p>
            <h1>MESSAGES</h1>
        </div>
    </header>

    <?php if ($error !== ''): ?>
        <div class="admin-error">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'read'): ?>
        <div class="admin-success">
            Message marked as read.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
        <div class="admin-success">
            <?= (int) ($_GET['count'] ?? 0) ?> read message(s) deleted successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'no_read_messages'): ?>
        <div class="admin-error">
            There are no read messages to delete.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'delete_failed'): ?>
        <div class="admin-error">
            Unable to delete read messages right now.
        </div>
    <?php endif; ?>

    <section class="admin-product-tools">
        <form method="get" action="messages.php" class="admin-search-form">
            <input
                type="text"
                name="search"
                placeholder="Search messages..."
                value="<?= htmlspecialchars($search) ?>"
            >

            <select name="status">
                <option value="">
                    ALL STATUSES
                </option>

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
                <a href="messages.php" class="admin-btn-secondary">
                    CLEAR
                </a>
            <?php endif; ?>

            <button
                type="submit"
                form="delete-read-messages-form"
                class="admin-action-delete"
                onclick="return confirm('Are you sure you want to delete ALL read messages? This action cannot be undone.');"
            >
                DELETE ALL READ
            </button>
        </form>

        <form
            id="delete-read-messages-form"
            method="post"
            action="messages_delete.php"
            style="display:none;"
        >
        </form>
    </section>

    <section class="admin-product-table-wrapper">
        <table class="admin-product-table">
            <thead>
                <tr>
                    <th>FROM</th>
                    <th>SUBJECT</th>
                    <th>CONTACT</th>
                    <th>STATUS</th>
                    <th>DATE</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="6" class="admin-empty">
                            No messages found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <td>
                                <strong class="admin-product-name">
                                    <?= htmlspecialchars($message['name']) ?>
                                </strong>

                                <small class="admin-product-description">
                                    <?= htmlspecialchars($message['email']) ?>
                                </small>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($message['subject']) ?>
                                </strong>

                                <small class="admin-product-description">
                                    <?= htmlspecialchars(mb_strimwidth($message['message'], 0, 70, '...')) ?>
                                </small>
                            </td>

                            <td>
                                <?php if (!empty($message['phone'])): ?>
                                    <?= htmlspecialchars($message['phone']) ?>
                                <?php else: ?>
                                    <span class="admin-product-description">
                                        No phone
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="admin-status">
                                    <?= htmlspecialchars($message['status']) ?>
                                </span>
                            </td>

                            <td>
                                <?= date('M d, Y', strtotime($message['created_at'])) ?>

                                <small class="admin-product-description">
                                    <?= date('h:i A', strtotime($message['created_at'])) ?>
                                </small>
                            </td>

                            <td>
                                <div class="admin-action-buttons">
                                    <a
                                        href="message_view.php?id=<?= (int) $message['id'] ?>"
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
```

</div>
</body>
</html>
