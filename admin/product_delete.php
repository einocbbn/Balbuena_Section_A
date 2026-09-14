<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$productId || $productId < 1) {
    header('Location: products.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT *
        FROM products
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: products.php');
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM order_items
        WHERE product_id = ?
    ");

    $stmt->execute([$productId]);
    $orderItemCount = (int) $stmt->fetchColumn();

} catch (PDOException $e) {
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $confirmation = trim($_POST['confirmation'] ?? '');

    if ($confirmation !== 'DELETE') {
        $error = 'Please type DELETE to confirm product deletion.';
    } else {

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                DELETE FROM products
                WHERE id = ?
            ");

            $stmt->execute([$productId]);

            if ($stmt->rowCount() !== 1) {
                throw new Exception('Product could not be deleted.');
            }

            $pdo->commit();

            if (
                !empty($product['image']) &&
                file_exists('../assets/' . $product['image'])
            ) {
                unlink('../assets/' . $product['image']);
            }

            header('Location: products.php?success=deleted');
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'Unable to delete the product. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BBN BITES | Delete Product</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body>

<div class="admin-layout">

    <aside class="admin-sidebar">

        <div class="admin-sidebar-brand">

            <img
                src="../assets/logo.png"
                alt="BBN Bites Logo"
            >

            <div>
                <strong>BBN BITES</strong>
                <span>ADMIN</span>
            </div>

        </div>

        <nav class="admin-nav">

            <a
                href="dashboard.php"
                class="admin-nav-link"
            >
                <span>▣</span>
                DASHBOARD
            </a>

            <a
                href="products.php"
                class="admin-nav-link active"
            >
                <span>▤</span>
                PRODUCTS
            </a>

            <a
                href="orders.php"
                class="admin-nav-link"
            >
                <span>▧</span>
                ORDERS
            </a>

            <a
                href="messages.php"
                class="admin-nav-link"
            >
                <span>✉</span>
                MESSAGES
            </a>

            <a
                href="account.php"
                class="admin-nav-link"
            >
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

            <a
                href="logout.php"
                class="admin-logout"
            >
                LOGOUT
            </a>

        </div>

    </aside>

    <main class="admin-main">

        <header class="admin-topbar">

            <div>
                <p class="admin-eyebrow">
                    MENU MANAGEMENT
                </p>

                <h1>
                    DELETE PRODUCT
                </h1>
            </div>

            <a
                href="products.php"
                class="admin-btn-secondary"
            >
                BACK TO PRODUCTS
            </a>

        </header>

        <?php if (isset($error)): ?>

            <div class="admin-error">
                <p>
                    <?= htmlspecialchars($error) ?>
                </p>
            </div>

        <?php endif; ?>

        <section class="admin-delete-card">

            <div class="admin-delete-product">

                <?php if (!empty($product['image'])): ?>

                    <img
                        src="../assets/<?= htmlspecialchars($product['image']) ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        class="admin-delete-image"
                    >

                <?php else: ?>

                    <div class="admin-delete-no-image">
                        NO IMAGE
                    </div>

                <?php endif; ?>

                <div class="admin-delete-details">

                    <p class="admin-eyebrow">
                        PRODUCT
                    </p>

                    <h2>
                        <?= htmlspecialchars($product['name']) ?>
                    </h2>

                    <p>
                        <?= htmlspecialchars($product['description']) ?>
                    </p>

                    <strong>
                        ₱<?= number_format((float) $product['price'], 2) ?>
                    </strong>

                    <span class="admin-category">
                        <?= htmlspecialchars($product['category']) ?>
                    </span>

                </div>

            </div>

            <?php if ($orderItemCount > 0): ?>

                <div class="admin-delete-warning">

                    <strong>
                        This product has order history.
                    </strong>

                    <p>
                        This product appears in
                        <?= $orderItemCount ?>
                        existing order item<?= $orderItemCount === 1 ? '' : 's' ?>.
                        Deleting it will not delete those orders.
                        Historical order information will be preserved.
                    </p>

                </div>

            <?php else: ?>

                <div class="admin-delete-warning">

                    <strong>
                        Are you sure you want to delete this product?
                    </strong>

                    <p>
                        This action cannot be undone.
                    </p>

                </div>

            <?php endif; ?>

            <form
                method="POST"
                class="admin-delete-form"
            >

                <div class="admin-form-group">

                    <label for="confirmation">
                        TYPE DELETE TO CONFIRM
                    </label>

                    <input
                        type="text"
                        id="confirmation"
                        name="confirmation"
                        placeholder="DELETE"
                        autocomplete="off"
                        required
                    >

                </div>

                <div class="admin-form-actions">

                    <button
                        type="submit"
                        class="admin-action-delete-button"
                    >
                        DELETE PRODUCT
                    </button>

                    <a
                        href="products.php"
                        class="admin-btn-secondary"
                    >
                        CANCEL
                    </a>

                </div>

            </form>

        </section>

    </main>

</div>

</body>
</html>