<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: messages.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        DELETE FROM customer_messages
        WHERE status = ?
    ");

    $stmt->execute(['Read']);

    $deletedCount = $stmt->rowCount();

    if ($deletedCount === 0) {
        header('Location: messages.php?error=no_read_messages');
        exit;
    }

    header('Location: messages.php?success=deleted&count=' . $deletedCount);
    exit;
} catch (PDOException $e) {
    header('Location: messages.php?error=delete_failed');
    exit;
}
