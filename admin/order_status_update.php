<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId || $orderId <= 0) {
    header('Location: orders.php');
    exit;
}

$allowedStatuses = [
    'Pending',
    'Confirmed',
    'Preparing',
    'Ready',
    'Completed',
    'Cancelled'
];

$status = trim($_POST['status'] ?? '');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: order_view.php?id=' . $orderId);
    exit;
}

if (!in_array($status, $allowedStatuses, true)) {
    header('Location: order_view.php?id=' . $orderId . '&error=invalid_status');
    exit;
}

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
        header('Location: orders.php');
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE orders
        SET status = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");

    $stmt->execute([
        $status,
        $orderId
    ]);

    header('Location: order_view.php?id=' . $orderId . '&success=updated');
    exit;

} catch (PDOException $e) {
    header('Location: order_view.php?id=' . $orderId . '&error=update_failed');
    exit;
}
?>