<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/config.php';
require_once '../database/validation.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

$fullName = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = validateAdminRegistration(
    $fullName,
    $username,
    $email,
    $password,
    $confirmPassword
);

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT id
            FROM admins
            WHERE username = ? OR email = ?
            LIMIT 1
        ");

        $stmt->execute([$username, $email]);
        $existingAdmin = $stmt->fetch();

        if ($existingAdmin) {
            $stmt = $pdo->prepare("
                SELECT id
                FROM admins
                WHERE username = ?
                LIMIT 1
            ");
            $stmt->execute([$username]);

            if ($stmt->fetch()) {
                $errors[] = 'Username is already registered.';
            }

            $stmt = $pdo->prepare("
                SELECT id
                FROM admins
                WHERE email = ?
                LIMIT 1
            ");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $errors[] = 'Email address is already registered.';
            }
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO admins
            (full_name, username, email, password)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $fullName,
            $username,
            $email,
            $hashedPassword
        ]);

        header('Location: login.php?registered=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBN BITES | Admin Registration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="admin.css">
</head>

<body>

<main class="admin-auth-page">
    <div class="admin-auth-container">

        <div class="admin-auth-header">
            <img src="../assets/logo.png" alt="BBN Bites Logo">
            <p>BBN BITES</p>
            <h1>ADMIN REGISTRATION</h1>
            <span>CREATE ADMIN ACCOUNT</span>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="admin-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="admin-auth-form" method="post" action="register.php">

            <div class="form-group">
                <label for="full_name">FULL NAME</label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    placeholder="Enter your full name"
                    value="<?= htmlspecialchars($fullName) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="username">USERNAME</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    value="<?= htmlspecialchars($username) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">EMAIL ADDRESS</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    value="<?= htmlspecialchars($email) ?>"
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
                    required
                >
            </div>

            <button type="submit" class="btn admin-btn">
                CREATE ADMIN ACCOUNT
            </button>

        </form>

        <div class="admin-auth-footer">
            <p>Already have an admin account?</p>
            <a href="login.php">LOGIN HERE</a>
        </div>

    </div>
</main>

</body>
</html>