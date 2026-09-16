<?php
require_once '../database/customer_auth.php';
require_once '../database/config.php';
require_once '../database/validation.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (empty($_SESSION['cart'])) {
    header('Location: menu.php');
    exit;
}

$cartItems = [];
$cartTotal = 0;

$productIds = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));

$stmt = $pdo->prepare("
    SELECT id, name, price, image, status
    FROM products
    WHERE id IN ($placeholders)
    AND status = 'Available'
    ORDER BY id ASC
");

$stmt->execute($productIds);
$products = $stmt->fetchAll();

foreach ($products as $product) {
    $productId = (int) $product['id'];
    $quantity = (int) ($_SESSION['cart'][$productId] ?? 0);

    if ($quantity <= 0) {
        continue;
    }

    $price = (float) $product['price'];
    $subtotal = $price * $quantity;

    $product['quantity'] = $quantity;
    $product['subtotal'] = $subtotal;

    $cartItems[] = $product;
    $cartTotal += $subtotal;
}

if (empty($cartItems)) {
    $_SESSION['cart'] = [];
    header('Location: menu.php');
    exit;
}

$error = '';
$orderSuccess = false;
$orderId = null;

$allowedPaymentMethods = [
    'GCash',
    'Maya',
    'Cash on Delivery'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim($_POST['customer_name'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');

    $paymentProof = null;

    $validationErrors = validateCheckout(
        $customerName,
        $contactNumber,
        $email,
        $address,
        $notes
    );

    if (!empty($validationErrors)) {
        $error = $validationErrors[0];
    }

    if ($error === '' && !in_array($paymentMethod, $allowedPaymentMethods, true)) {
        $error = 'Please select a valid payment method.';
    }

    if ($error === '' && $paymentMethod !== 'Cash on Delivery') {
        if (
            !isset($_FILES['payment_proof']) ||
            $_FILES['payment_proof']['error'] === UPLOAD_ERR_NO_FILE
        ) {
            $error = 'Please upload your proof of payment.';
        }
    }

    if ($error === '' && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['payment_proof'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'There was a problem uploading your proof of payment.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = 'Proof of payment must not exceed 5 MB.';
        } else {
            $allowedMimeTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!isset($allowedMimeTypes[$mimeType])) {
                $error = 'Proof of payment must be a JPG, PNG, or WEBP image.';
            } else {
                $uploadDirectory = dirname(__DIR__) . '/uploads/payment_proofs/';

                if (!is_dir($uploadDirectory)) {
                    if (!mkdir($uploadDirectory, 0755, true)) {
                        $error = 'Unable to prepare the payment proof upload folder.';
                    }
                }

                if ($error === '') {
                    $extension = $allowedMimeTypes[$mimeType];
                    $uniqueName = 'payment_' . bin2hex(random_bytes(16)) . '.' . $extension;
                    $destination = $uploadDirectory . $uniqueName;

                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        $error = 'Unable to save your proof of payment.';
                    } else {
                        $paymentProof = $uniqueName;
                    }
                }
            }
        }
    }

    if ($error === '') {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO orders
                (
                    customer_id,
                    customer_name,
                    contact_number,
                    email,
                    address,
                    notes,
                    total_amount,
                    payment_method,
                    payment_proof,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
            ");

            $stmt->execute([
                $_SESSION['customer_id'],
                $customerName,
                $contactNumber,
                $email,
                $address,
                $notes,
                $cartTotal,
                $paymentMethod,
                $paymentProof
            ]);

            $orderId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_name,
                    quantity,
                    price,
                    subtotal
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($cartItems as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['id'],
                    $item['name'],
                    $item['quantity'],
                    $item['price'],
                    $item['subtotal']
                ]);
            }

            $pdo->commit();

            $_SESSION['cart'] = [];
            $orderSuccess = true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($paymentProof !== null) {
                $uploadedFile = dirname(__DIR__) . '/uploads/payment_proofs/' . $paymentProof;

                if (is_file($uploadedFile)) {
                    unlink($uploadedFile);
                }
            }

            $error = 'Something went wrong while placing your order. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBN BITES | Checkout</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../header-footer/header.css">
    <link rel="stylesheet" href="../header-footer/footer.css">
    <link rel="stylesheet" href="checkout.css">
</head>
<body>

<?php include '../header-footer/header.php'; ?>

<main>
    <section class="checkout-page">
        <div class="container">

```
        <div class="checkout-title">
            <p class="checkout-eyebrow">BBN BITES</p>
            <h1>CHECKOUT</h1>
            <p>COMPLETE YOUR ORDER</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="checkout-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="checkout-layout">

            <div class="checkout-form-wrapper">

                <form method="POST" action="checkout.php" class="checkout-form" enctype="multipart/form-data">

                    <h2>CUSTOMER INFORMATION</h2>

                    <div class="form-group">
                        <label for="customer_name">FULL NAME</label>
                        <input
                            type="text"
                            id="customer_name"
                            name="customer_name"
                            value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="contact_number">CONTACT NUMBER</label>
                        <input
                            type="text"
                            id="contact_number"
                            name="contact_number"
                            value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>"
                            maxlength="11"
                            inputmode="numeric"
                            required
                        >
                        <small id="contact-error" class="checkout-field-error"></small>
                    </div>

                    <div class="form-group">
                        <label for="email">EMAIL ADDRESS</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="address">DELIVERY / PICKUP ADDRESS</label>
                        <textarea
                            id="address"
                            name="address"
                            rows="4"
                            required
                        ><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="notes">ORDER NOTES <span>(OPTIONAL)</span></label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            placeholder="Special instructions for your order..."
                        ><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="checkout-payment-section">
                        <h2>PAYMENT INFORMATION</h2>

                        <div class="form-group">
                            <label>MODE OF PAYMENT</label>

                            <div class="payment-options">

                                <label class="payment-option">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="GCash"
                                        <?= ($_POST['payment_method'] ?? '') === 'GCash' ? 'checked' : '' ?>
                                        required
                                    >
                                    <span>
                                        <strong>GCash</strong>
                                        <small>Pay through GCash</small>
                                    </span>
                                </label>

                                <label class="payment-option">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="Maya"
                                        <?= ($_POST['payment_method'] ?? '') === 'Maya' ? 'checked' : '' ?>
                                    >
                                    <span>
                                        <strong>Maya</strong>
                                        <small>Pay through Maya</small>
                                    </span>
                                </label>

                                <label class="payment-option">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="Cash on Delivery"
                                        <?= ($_POST['payment_method'] ?? '') === 'Cash on Delivery' ? 'checked' : '' ?>
                                    >
                                    <span>
                                        <strong>Cash on Delivery</strong>
                                        <small>Pay when your order arrives</small>
                                    </span>
                                </label>

                            </div>
                        </div>

                        <div id="online-payment-section" class="online-payment-section">

                            <div class="payment-instructions">
                                <h3>PROOF OF PAYMENT</h3>
                                <h3>GCASH: 09618048468
                                    MAYA: 09618049468</h3>
                                <p> 
                                    Please upload a clear screenshot
                                    or photo of your payment receipt.
                                </p>
                            </div>

                            <div class="form-group">
                                <label for="payment_proof">
                                    UPLOAD PROOF OF PAYMENT
                                </label>

                                <input
                                    type="file"
                                    id="payment_proof"
                                    name="payment_proof"
                                    accept="image/jpeg,image/png,image/webp"
                                >

                                <small class="checkout-upload-note">
                                    JPG, PNG, or WEBP only. Maximum file size: 5 MB.
                                </small>

                                <div id="payment-preview" class="payment-preview"></div>
                            </div>

                        </div>

                    </div>

                    <div class="checkout-actions">

                        <a href="cart.php" class="checkout-secondary-btn">
                            BACK TO CART
                        </a>

                        <button type="submit" class="checkout-btn">
                            PLACE ORDER
                        </button>

                    </div>

                </form>

            </div>

            <aside class="checkout-summary">

                <h2>YOUR ORDER</h2>

                <?php foreach ($cartItems as $item): ?>

                    <div class="checkout-item">

                        <div class="checkout-item-image">
                            <img
                                src="../assets/<?= htmlspecialchars($item['image']) ?>"
                                alt="<?= htmlspecialchars($item['name']) ?>"
                            >
                        </div>

                        <div class="checkout-item-info">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>

                            <p>
                                <?= (int) $item['quantity'] ?>
                                ×
                                ₱<?= number_format((float) $item['price'], 2) ?>
                            </p>
                        </div>

                        <strong>
                            ₱<?= number_format((float) $item['subtotal'], 2) ?>
                        </strong>

                    </div>

                <?php endforeach; ?>

                <div class="checkout-total">
                    <span>TOTAL</span>
                    <strong>₱<?= number_format($cartTotal, 2) ?></strong>
                </div>

            </aside>

        </div>
    </div>
</section>
```

</main>

<?php include '../header-footer/footer.php'; ?>

<?php if ($orderSuccess): ?>

<div class="success-modal" id="successModal">

```
<div class="success-modal-overlay"></div>

<div class="success-modal-content">

    <button
        type="button"
        class="success-modal-close"
        onclick="closeSuccessModal()"
        aria-label="Close"
    >
        ×
    </button>

    <div class="success-icon">
        ✓
    </div>

    <p class="success-eyebrow">BBN BITES</p>

    <h2>ORDER CONFIRMED!</h2>

    <p class="success-message">
        Thank you for ordering with BBN Bites.
    </p>

    <div class="order-number">
        <span>ORDER NUMBER</span>
        <strong>#<?= htmlspecialchars($orderId) ?></strong>
    </div>

    <p class="success-status">
        Your order has been received and is currently
        <strong>Pending</strong>.
    </p>

    <a href="menu.php" class="success-modal-btn">
        BACK TO MENU
    </a>

</div>
```

</div>

<script>
document.body.classList.add('modal-open');

function closeSuccessModal() {
    const modal = document.getElementById('successModal');

    if (modal) {
        modal.classList.add('closing');

        setTimeout(function() {
            window.location.href = 'menu.php';
        }, 200);
    }
}
</script>

<?php endif; ?>

<script>
const contactNumber = document.getElementById('contact_number');
const contactError = document.getElementById('contact-error');

if (contactNumber && contactError) {
    contactNumber.addEventListener('input', function () {
        const value = this.value;

        if (/[^0-9]/.test(value)) {
            contactError.textContent = 'Invalid input. Contact number must contain numbers only.';
            this.setCustomValidity('Contact number must contain numbers only.');
        } else if (value !== '' && !value.startsWith('09')) {
            contactError.textContent = 'Invalid input. Contact number must start with 09.';
            this.setCustomValidity('Contact number must start with 09.');
        } else if (value.length > 0 && value.length < 11) {
            contactError.textContent = 'Contact number must be exactly 11 digits.';
            this.setCustomValidity('Contact number must be exactly 11 digits.');
        } else {
            contactError.textContent = '';
            this.setCustomValidity('');
        }
    });
}

const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
const onlinePaymentSection = document.getElementById('online-payment-section');
const paymentProof = document.getElementById('payment_proof');
const paymentPreview = document.getElementById('payment-preview');

function updatePaymentFields() {
    const selected = document.querySelector('input[name="payment_method"]:checked');

    if (!selected) {
        onlinePaymentSection.style.display = 'none';
        paymentProof.required = false;
        return;
    }

    if (selected.value === 'Cash on Delivery') {
        onlinePaymentSection.style.display = 'none';
        paymentProof.required = false;
    } else {
        onlinePaymentSection.style.display = 'block';
        paymentProof.required = true;
    }
}

paymentMethods.forEach(function (method) {
    method.addEventListener('change', updatePaymentFields);
});

if (paymentProof) {
    paymentProof.addEventListener('change', function () {
        paymentPreview.innerHTML = '';

        const file = this.files[0];

        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            paymentPreview.textContent = 'Please select an image file.';
            this.value = '';
            return;
        }

        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = 'Payment proof preview';

        paymentPreview.appendChild(image);
    });
}

updatePaymentFields();
</script>

</body>
</html>
