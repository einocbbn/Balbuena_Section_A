<?php
session_start();

require_once '../database/config.php';
require_once '../database/validation.php';

if (isset($_SESSION['customer_id'])) {
    header('Location: ../index.php');
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = validateLogin($username, $password);

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                SELECT id, full_name, username, email, contact_number, password
                FROM customers
                WHERE username = ?
                LIMIT 1
            ");

            $stmt->execute([$username]);
            $customer = $stmt->fetch();

            if ($customer && password_verify($password, $customer['password'])) {
                session_regenerate_id(true);

                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['full_name'];
                $_SESSION['customer_username'] = $customer['username'];
                $_SESSION['customer_email'] = $customer['email'];

                header('Location: ../index.php');
                exit;
            }

            $errors[] = 'Invalid username or password.';

        } catch (PDOException $e) {
            $errors[] = 'Login failed. Please try again.';
        }
    }
}

$registered = isset($_GET['registered']) && $_GET['registered'] === '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | BBN BITES</title>
    
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
            <h1>WELCOME BACK</h1>
            <p>BURGERS &amp; QUESADILLAS</p>
        </div>

        <?php if ($registered): ?>
            <div class="auth-success">
                <p>Registration successful. You can now log in.</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="auth-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="auth-card">

            <h2>LOGIN</h2>

            <p class="auth-description">
                Log in to your BBN Bites customer account to access our menu,
                cart, checkout, and other customer features.
            </p>

            <form action="login.php" method="post" class="auth-form">

                <div class="form-group">
                    <label for="username">USERNAME</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?= htmlspecialchars($username) ?>"
                        placeholder="Enter your username"
                        maxlength="50"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">PASSWORD</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn auth-btn">
                    LOGIN
                </button>

            </form>

            <div class="auth-footer">
                <p>
                    Don't have an account?
                    <a href="register.php">CREATE ACCOUNT</a>
                </p>
            </div>

        </div>

    </div>
</main>

<?php include '../header-footer/footer.php'; ?>

</body>
</html>