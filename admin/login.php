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
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = validateLogin($username, $password);

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            SELECT id, full_name, username, email, password
            FROM admins
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];

            header('Location: dashboard.php');
            exit;
        }

        $errors[] = 'Invalid username or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBN BITES | Admin Login</title>
    <meta name="description" content="BBN Bites Admin Login">

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
            <h1>ADMIN LOGIN</h1>
            <span>ADMINISTRATOR ACCESS</span>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="admin-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="admin-auth-form" method="post" action="login.php">

            <div class="form-group">
                <label for="username">USERNAME</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    value="<?= htmlspecialchars($username) ?>"
                    required
                    autofocus
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

            <button type="submit" class="btn admin-btn">
                LOGIN
            </button>

        </form>

        <div class="admin-auth-footer">
            <p>Need an admin account?</p>
            <a href="register.php">REGISTER HERE</a>
        </div>

    </div>
</main>

</body>
</html>