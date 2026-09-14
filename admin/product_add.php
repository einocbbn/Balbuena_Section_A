<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';

$errors = [];

$name = '';
$description = '';
$price = '';
$category = '';
$status = 'Available';

$categories = ['BURGERS', 'QUESADILLAS', 'EXTRAS'];
$statuses = ['Available', 'Unavailable'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($name === '') {
        $errors[] = 'Product name is required.';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Product name must not exceed 100 characters.';
    }

    if ($description === '') {
        $errors[] = 'Product description is required.';
    }

    if ($price === '') {
        $errors[] = 'Product price is required.';
    } elseif (!is_numeric($price)) {
        $errors[] = 'Product price must be a valid number.';
    } elseif ((float) $price < 0) {
        $errors[] = 'Product price cannot be negative.';
    }

    if (!in_array($category, $categories, true)) {
        $errors[] = 'Please select a valid category.';
    }

    if (!in_array($status, $statuses, true)) {
        $errors[] = 'Please select a valid status.';
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Product image is required.';
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'There was a problem uploading the product image.';
    } else {
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($fileType, $allowedTypes, true)) {
            $errors[] = 'Only JPG, PNG, and WEBP images are allowed.';
        }

        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Product image must not exceed 5MB.';
        }
    }

    if (empty($errors)) {
        $uploadDirectory = '../assets/';

        $originalName = $_FILES['image']['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $safeName = preg_replace(
            '/[^A-Za-z0-9_-]/',
            '_',
            strtolower($name)
        );

        $fileName = $safeName . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDirectory . $fileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $errors[] = 'Unable to save the product image.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO products
                    (name, description, price, image, category, status)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $name,
                    $description,
                    $price,
                    $fileName,
                    $category,
                    $status
                ]);

                header('Location: products.php?success=added');
                exit;

            } catch (PDOException $e) {
                if (file_exists($uploadPath)) {
                    unlink($uploadPath);
                }

                $errors[] = 'Unable to add the product. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BBN BITES | Add Product</title>

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
                <h1>ADD PRODUCT</h1>
            </div>

            <a href="products.php" class="admin-btn-secondary">
                BACK TO PRODUCTS
            </a>

        </header>

        <?php if (!empty($errors)): ?>

            <div class="admin-error">

                <ul>
                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?= htmlspecialchars($error) ?>
                        </li>

                    <?php endforeach; ?>
                </ul>

            </div>

        <?php endif; ?>

        <section class="admin-product-form-card">

            <form
                method="POST"
                enctype="multipart/form-data"
                class="admin-product-form"
            >

                <div class="admin-form-group">

                    <label for="name">
                        PRODUCT NAME
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($name) ?>"
                        maxlength="100"
                        required
                    >

                </div>

                <div class="admin-form-group">

                    <label for="description">
                        DESCRIPTION
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        required
                    ><?= htmlspecialchars($description) ?></textarea>

                </div>

                <div class="admin-form-row">

                    <div class="admin-form-group">

                        <label for="price">
                            PRICE
                        </label>

                        <input
                            type="number"
                            id="price"
                            name="price"
                            value="<?= htmlspecialchars($price) ?>"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>

                    <div class="admin-form-group">

                        <label for="category">
                            CATEGORY
                        </label>

                        <select
                            id="category"
                            name="category"
                            required
                        >

                            <option value="">
                                SELECT CATEGORY
                            </option>

                            <?php foreach ($categories as $item): ?>

                                <option
                                    value="<?= htmlspecialchars($item) ?>"
                                    <?= $category === $item ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($item) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="admin-form-group">

                        <label for="status">
                            STATUS
                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                        >

                            <?php foreach ($statuses as $item): ?>

                                <option
                                    value="<?= htmlspecialchars($item) ?>"
                                    <?= $status === $item ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($item) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

                <div class="admin-form-group">

                    <label for="image">
                        PRODUCT IMAGE
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <small class="admin-form-help">
                        JPG, PNG, or WEBP. Maximum file size: 5MB.
                    </small>

                    <div
                        class="admin-image-preview"
                        id="imagePreview"
                    >

                        <img
                            id="previewImage"
                            src=""
                            alt="Product preview"
                        >

                    </div>

                </div>

                <div class="admin-form-actions">

                    <button
                        type="submit"
                        class="admin-btn-primary"
                    >
                        ADD PRODUCT
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

<script>
const imageInput = document.getElementById('image');
const imagePreview = document.getElementById('imagePreview');
const previewImage = document.getElementById('previewImage');

imageInput.addEventListener('change', function () {
    const file = this.files[0];

    if (!file) {
        imagePreview.style.display = 'none';
        previewImage.src = '';
        return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {
        previewImage.src = event.target.result;
        imagePreview.style.display = 'block';
    };

    reader.readAsDataURL(file);
});
</script>

</body>
</html>