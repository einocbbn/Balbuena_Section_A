<?php
session_start();

require_once '../database/config.php';
require_once '../database/validation.php';

if (isset($_SESSION['customer_id'])) {
    header('Location: ../index.php');
    exit;
}

$errors = [];

$fullName = '';
$username = '';
$email = '';
$contactNumber = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = validateRegistration(
        $fullName,
        $username,
        $email,
        $contactNumber,
        $password,
        $confirmPassword
    );

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT id
            FROM customers
            WHERE username = ? OR email = ?
            LIMIT 1
        ");

        $stmt->execute([$username, $email]);
        $existingCustomer = $stmt->fetch();

        if ($existingCustomer) {
            $errors[] = 'Username or email address is already registered.';
        }
    }

    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO customers (
                    full_name,
                    username,
                    email,
                    contact_number,
                    password
                ) VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $fullName,
                $username,
                $email,
                $contactNumber,
                $hashedPassword
            ]);

            header('Location: login.php?registered=1');
            exit;

        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | BBN BITES</title>
    <meta name="description" content="Create your BBN Bites customer account">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../header-footer/header.css">
    <link rel="stylesheet" href="../header-footer/footer.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

<?php include '../header-footer/header.php'; ?>

<main class="auth-page">
    <div class="auth-container">

        <div class="auth-header">
            <p class="auth-eyebrow">BBN BITES</p>
            <h1>CREATE ACCOUNT</h1>
            <p>BURGERS &amp; QUESADILLAS</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="auth-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="auth-card">

            <h2>REGISTER</h2>

            <p class="auth-description">
                Create your BBN Bites customer account to access our full menu,
                cart, checkout, and other customer features.
            </p>

            <form action="register.php" method="post" class="auth-form">

                <div class="form-group">
                    <label for="full_name">FULL NAME</label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?= htmlspecialchars($fullName) ?>"
                        placeholder="Enter your full name"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="username">USERNAME</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($username) ?>"
                        placeholder="Create a username"
                        maxlength="50"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">EMAIL ADDRESS</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="Enter your email address"
                        maxlength="150"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="contact_number">CONTACT NUMBER</label>

                    <input
                        type="text"
                        id="contact_number"
                        name="contact_number"
                        value="<?= htmlspecialchars($contactNumber) ?>"
                        placeholder="09XXXXXXXXX"
                        inputmode="numeric"
                        maxlength="11"
                        pattern="09[0-9]{9}"
                        autocomplete="tel"
                        required
                    >

                    <small
                        class="validation-error"
                        id="contact-number-error"
                    ></small>
                </div>

                <div class="form-group">
                    <label for="password">PASSWORD</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">CONFIRM PASSWORD</label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <small
                        class="validation-error"
                        id="password-error"
                    ></small>
                </div>

                <button type="submit" class="btn auth-btn">
                    CREATE ACCOUNT
                </button>

            </form>

            <div class="auth-footer">
                <p>
                    Already have an account?
                    <a href="login.php">LOGIN</a>
                </p>
            </div>

        </div>

    </div>
</main>

<?php include '../header-footer/footer.php'; ?>

<script>
const contactNumber = document.getElementById('contact_number');
const contactNumberError = document.getElementById('contact-number-error');

contactNumber.addEventListener('input', function () {
    const value = this.value;

    if (value === '') {
        contactNumberError.textContent = '';
        this.setCustomValidity('');
        return;
    }

    if (!/^[0-9]*$/.test(value)) {
        contactNumberError.textContent =
            'Contact number must contain numbers only.';

        this.setCustomValidity(
            'Contact number must contain numbers only.'
        );

        return;
    }

    if (!value.startsWith('09')) {
        contactNumberError.textContent =
            'Contact number must start with 09.';

        this.setCustomValidity(
            'Contact number must start with 09.'
        );

        return;
    }

    if (value.length < 11) {
        contactNumberError.textContent =
            'Contact number must be exactly 11 digits.';

        this.setCustomValidity(
            'Contact number must be exactly 11 digits.'
        );

        return;
    }

    contactNumberError.textContent = '';
    this.setCustomValidity('');
});

const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm_password');
const passwordError = document.getElementById('password-error');

function checkPasswords() {
    if (confirmPassword.value === '') {
        passwordError.textContent = '';
        confirmPassword.setCustomValidity('');
        return;
    }

    if (password.value !== confirmPassword.value) {
        passwordError.textContent = 'Passwords do not match.';
        confirmPassword.setCustomValidity('Passwords do not match.');
    } else {
        passwordError.textContent = '';
        confirmPassword.setCustomValidity('');
    }
}

password.addEventListener('input', checkPasswords);
confirmPassword.addEventListener('input', checkPasswords);
</script>

</body>
</html>