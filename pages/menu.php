<?php
require_once '../database/config.php';
$categories = ['BURGERS', 'QUESADILLAS', 'EXTRAS'];
$products = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("
        SELECT id, name, description, price, image, category, status
        FROM products
        WHERE category = ? AND status = 'Available'
        ORDER BY id ASC
    ");
    $stmt->execute([$category]);
    $products[$category] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBN BITES | Menu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../header-footer/header.css">
    <link rel="stylesheet" href="../header-footer/footer.css">
</head>
<body>
<?php include '../header-footer/header.php'; ?>
<main>
    <section class="menu-page">
        <div class="container">
            <div class="menu-title">
                <p class="menu-eyebrow">BBN BITES</p>
                <h1>OUR MENU</h1>
                <p>BURGERS &amp; QUESADILLAS</p>
            </div>
            <?php foreach ($categories as $category): ?>
                <section class="menu-category <?= $category === 'EXTRAS' ? 'extras-category' : '' ?>">
                    <div class="menu-category-heading">
                        <h2><?= htmlspecialchars($category) ?></h2>
                    </div>
                    <div class="menu-grid">
                        <?php if (!empty($products[$category])): ?>
                            <?php foreach ($products[$category] as $product): ?>
                                <article class="menu-card">
                                    <div class="menu-image">
                                        <img src="../assets/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                    </div>
                                    <div class="menu-info">
                                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                                        <p><?= htmlspecialchars($product['description']) ?></p>
                                        <span class="menu-price">₱<?= number_format((float)$product['price'], 2) ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No available products in this category.</p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php include '../header-footer/footer.php'; ?>
</body>
</html>