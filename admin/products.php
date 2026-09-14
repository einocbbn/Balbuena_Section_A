<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';

$categories = ['BURGERS', 'QUESADILLAS', 'EXTRAS'];
$products = [];

$selectedCategory = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');

try {
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];

    if ($selectedCategory !== '' && in_array($selectedCategory, $categories, true)) {
        $sql .= " AND category = ?";
        $params[] = $selectedCategory;
    }

    if ($search !== '') {
        $sql .= " AND (name LIKE ? OR description LIKE ?)";
        $searchValue = '%' . $search . '%';
        $params[] = $searchValue;
        $params[] = $searchValue;
    }

    $sql .= " ORDER BY category ASC, id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = 'Unable to load products.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BBN BITES | Products</title>

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

            <a href="products.php" class="admin-nav-link active">
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
                <p class="admin-eyebrow">MENU MANAGEMENT</p>
                <h1>PRODUCTS</h1>
            </div>

            <a href="product_add.php" class="admin-btn-primary">
                + ADD PRODUCT
            </a>
        </header>

        <?php if (isset($error)): ?>

            <div class="admin-error">
                <p><?= htmlspecialchars($error) ?></p>
            </div>

        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>

            <div class="admin-success">

                <?php if ($_GET['success'] === 'added'): ?>
                    Product added successfully.

                <?php elseif ($_GET['success'] === 'updated'): ?>
                    Product updated successfully.

                <?php elseif ($_GET['success'] === 'deleted'): ?>
                    Product deleted successfully.

                <?php endif; ?>

            </div>

        <?php endif; ?>

        <section class="admin-product-tools">

            <form method="get" action="products.php" class="admin-search-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Search products..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <select name="category">

                    <option value="">ALL CATEGORIES</option>

                    <?php foreach ($categories as $category): ?>

                        <option
                            value="<?= htmlspecialchars($category) ?>"
                            <?= $selectedCategory === $category ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($category) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <button type="submit" class="admin-btn-primary">
                    SEARCH
                </button>

                <?php if ($search !== '' || $selectedCategory !== ''): ?>

                    <a href="products.php" class="admin-btn-secondary">
                        CLEAR
                    </a>

                <?php endif; ?>

            </form>

        </section>

        <section class="admin-product-table-wrapper">

            <table class="admin-product-table">

                <thead>
                    <tr>
                        <th>IMAGE</th>
                        <th>PRODUCT</th>
                        <th>CATEGORY</th>
                        <th>PRICE</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($products)): ?>

                    <tr>
                        <td colspan="6" class="admin-empty">
                            No products found.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($products as $product): ?>

                        <tr>

                            <td>

                                <?php if (!empty($product['image'])): ?>

                                    <img
                                        src="../assets/<?= htmlspecialchars($product['image']) ?>"
                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                        class="admin-product-image"
                                    >

                                <?php else: ?>

                                    <div class="admin-no-image">
                                        NO IMAGE
                                    </div>

                                <?php endif; ?>

                            </td>

                            <td>
                                <strong class="admin-product-name">
                                    <?= htmlspecialchars($product['name']) ?>
                                </strong>

                                <?php if (!empty($product['description'])): ?>

                                    <small class="admin-product-description">
                                        <?= htmlspecialchars($product['description']) ?>
                                    </small>

                                <?php endif; ?>

                            </td>

                            <td>
                                <span class="admin-category">
                                    <?= htmlspecialchars($product['category']) ?>
                                </span>
                            </td>

                            <td>
                                <strong>
                                    ₱<?= number_format((float) $product['price'], 2) ?>
                                </strong>
                            </td>

                            <td>

                                <span class="admin-status <?= strtolower($product['status']) === 'available' ? 'status-available' : 'status-unavailable' ?>">
                                    <?= htmlspecialchars($product['status']) ?>
                                </span>

                            </td>

                            <td>

                                <div class="admin-action-buttons">

                                    <a
                                        href="product_edit.php?id=<?= (int) $product['id'] ?>"
                                        class="admin-action-edit"
                                    >
                                        EDIT
                                    </a>

                                    <a
                                        href="product_delete.php?id=<?= (int) $product['id'] ?>"
                                        class="admin-action-delete"
                                        onclick="return confirm('Are you sure you want to delete this product?');"
                                    >
                                        DELETE
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