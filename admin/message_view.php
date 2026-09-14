<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';

$messageId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$messageId || $messageId <= 0) {
    header('Location: messages.php');
    exit;
}

$message = null;
$error = '';

try {
    $stmt = $pdo->prepare("
        SELECT
            id,
            customer_id,
            name,
            email,
            phone,
            subject,
            message,
            status,
            created_at
        FROM customer_messages
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$messageId]);
    $message = $stmt->fetch();

    if (!$message) {
        header('Location: messages.php');
        exit;
    }

} catch (PDOException $e) {
    $error = 'Unable to load this message right now.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $message && $error === '') {

    $action = $_POST['action'] ?? '';

    if ($action === 'mark_read' && $message['status'] === 'Unread') {

        try {
            $stmt = $pdo->prepare("
                UPDATE customer_messages
                SET status = 'Read'
                WHERE id = ?
            ");

            $stmt->execute([$messageId]);

            header('Location: messages.php?success=read');
            exit;

        } catch (PDOException $e) {
            $error = 'Unable to update the message right now.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Message #<?= (int) $messageId ?> | BBN Bites Admin</title>

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="message_view.css">

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

                <p class="admin-eyebrow">
                    CUSTOMER COMMUNICATION
                </p>

                <h1>
                    MESSAGE #<?= (int) $messageId ?>
                </h1>

            </div>

            <a href="messages.php" class="admin-btn-secondary">
                BACK TO MESSAGES
            </a>

        </header>

        <?php if ($error !== ''): ?>

            <div class="admin-error">
                <p><?= htmlspecialchars($error) ?></p>
            </div>

        <?php elseif ($message): ?>

            <section class="admin-message-card">

                <div class="admin-message-header">

                    <div>

                        <p class="admin-eyebrow">
                            CUSTOMER MESSAGE
                        </p>

                        <h2>
                            <?= htmlspecialchars($message['subject']) ?>
                        </h2>

                    </div>

                    <span class="admin-status">
                        <?= htmlspecialchars($message['status']) ?>
                    </span>

                </div>

                <div class="admin-message-info">

                    <div>

                        <span>FROM</span>

                        <strong>
                            <?= htmlspecialchars($message['name']) ?>
                        </strong>

                    </div>

                    <div>

                        <span>EMAIL</span>

                        <strong>
                            <?= htmlspecialchars($message['email']) ?>
                        </strong>

                    </div>

                    <div>

                        <span>PHONE</span>

                        <strong>

                            <?php if (!empty($message['phone'])): ?>

                                <?= htmlspecialchars($message['phone']) ?>

                            <?php else: ?>

                                No phone provided

                            <?php endif; ?>

                        </strong>

                    </div>

                    <div>

                        <span>DATE</span>

                        <strong>
                            <?= date('M d, Y', strtotime($message['created_at'])) ?>
                        </strong>

                        <small>
                            <?= date('h:i A', strtotime($message['created_at'])) ?>
                        </small>

                    </div>

                </div>

                <div class="admin-message-content">

                    <p class="admin-eyebrow">
                        MESSAGE
                    </p>

                    <div class="admin-message-text">
                        <?= nl2br(htmlspecialchars($message['message'])) ?>
                    </div>

                </div>

                <div class="admin-message-actions">

                    <a href="messages.php" class="admin-btn-secondary">
                        BACK TO MESSAGES
                    </a>

                    <?php if ($message['status'] === 'Unread'): ?>

                        <form method="post">

                            <input
                                type="hidden"
                                name="action"
                                value="mark_read"
                            >

                            <button
                                type="submit"
                                class="admin-btn-primary"
                            >
                                MARK AS READ
                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </section>

        <?php endif; ?>

    </main>

</div>

</body>
</html>