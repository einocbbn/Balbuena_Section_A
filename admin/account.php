<?php
require_once '../database/admin_auth.php';
require_once '../database/config.php';
require_once '../database/validation.php';

$adminId = $_SESSION['admin_id'];

$admin = null;
$errors = [];
$success = '';

$fullName = '';
$username = '';
$email = '';
$newPassword = '';
$confirmPassword = '';

try {
    $stmt = $pdo->prepare("
        SELECT
            id,
            full_name,
            username,
            email
        FROM admins
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$adminId]);
    $admin = $stmt->fetch();

    if (!$admin) {
        unset(
            $_SESSION['admin_id'],
            $_SESSION['admin_name'],
            $_SESSION['admin_username'],
            $_SESSION['admin_email']
        );

        session_regenerate_id(true);

        header('Location: login.php');
        exit;
    }

    $fullName = $admin['full_name'];
    $username = $admin['username'];
    $email = $admin['email'];

} catch (PDOException $e) {
    $errors[] = 'Unable to load your account information right now.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $admin) {

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {

        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($fullName === '') {
            $errors[] = 'Full name is required.';
        } elseif (!preg_match("/^[A-Za-zÀ-ÿ\s.'-]+$/u", $fullName)) {
            $errors[] = 'Full name must contain letters only.';
        } elseif (strlen($fullName) < 4) {
            $errors[] = 'Full name must be at least 4 characters.';
        } elseif (strlen($fullName) > 100) {
            $errors[] = 'Full name must not exceed 100 characters.';
        }

        if ($username === '') {
            $errors[] = 'Username is required.';
        } elseif (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
            $errors[] = 'Username can contain letters, numbers, and underscores only.';
        } elseif (strlen($username) < 4) {
            $errors[] = 'Username must be at least 4 characters.';
        } elseif (strlen($username) > 50) {
            $errors[] = 'Username must not exceed 50 characters.';
        }

        if ($email === '') {
            $errors[] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (strlen($email) > 150) {
            $errors[] = 'Email address must not exceed 150 characters.';
        }

        if (empty($errors)) {

            try {
                $stmt = $pdo->prepare("
                    SELECT id
                    FROM admins
                    WHERE (username = ? OR email = ?)
                    AND id != ?
                    LIMIT 1
                ");

                $stmt->execute([
                    $username,
                    $email,
                    $adminId
                ]);

                $existingAdmin = $stmt->fetch();

                if ($existingAdmin) {

                    $stmt = $pdo->prepare("
                        SELECT id
                        FROM admins
                        WHERE username = ?
                        AND id != ?
                        LIMIT 1
                    ");

                    $stmt->execute([
                        $username,
                        $adminId
                    ]);

                    if ($stmt->fetch()) {
                        $errors[] = 'Username is already taken.';
                    }

                    $stmt = $pdo->prepare("
                        SELECT id
                        FROM admins
                        WHERE email = ?
                        AND id != ?
                        LIMIT 1
                    ");

                    $stmt->execute([
                        $email,
                        $adminId
                    ]);

                    if ($stmt->fetch()) {
                        $errors[] = 'Email address is already registered.';
                    }
                }

                if (empty($errors)) {

                    $stmt = $pdo->prepare("
                        UPDATE admins
                        SET
                            full_name = ?,
                            username = ?,
                            email = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $fullName,
                        $username,
                        $email,
                        $adminId
                    ]);

                    $_SESSION['admin_name'] = $fullName;
                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_email'] = $email;

                    $admin['full_name'] = $fullName;
                    $admin['username'] = $username;
                    $admin['email'] = $email;

                    $success = 'Your account information has been updated successfully.';
                }

            } catch (PDOException $e) {
                $errors[] = 'Unable to update your account information right now.';
            }
        }
    }

    if ($action === 'change_password') {

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newPassword === '') {
            $errors[] = 'New password is required.';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }

        if ($confirmPassword === '') {
            $errors[] = 'Please confirm your new password.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {

            try {
                $hashedPassword = password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );

                $stmt = $pdo->prepare("
                    UPDATE admins
                    SET password = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $hashedPassword,
                    $adminId
                ]);

                $success = 'Your password has been changed successfully.';

                $newPassword = '';
                $confirmPassword = '';

            } catch (PDOException $e) {
                $errors[] = 'Unable to change your password right now.';
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

    <title>My Account | BBN Bites Admin</title>

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="account.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >
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

            <a href="dashboard.php" class="admin-nav-link">
                <span>▣</span>
                DASHBOARD
            </a>

            <a href="products.php" class="admin-nav-link">
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

            <a href="account.php" class="admin-nav-link active">
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

                <p class="admin-eyebrow">
                    ACCOUNT MANAGEMENT
                </p>

                <h1>
                    MY ACCOUNT
                </h1>

            </div>

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

        <?php if ($success !== ''): ?>

            <div class="admin-success">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>

        <?php if ($admin): ?>

            <section class="admin-account-grid">

                <div class="admin-account-card">

                    <div class="admin-account-card-header">

                        <div>

                            <p class="admin-eyebrow">
                                PROFILE
                            </p>

                            <h2>
                                ACCOUNT INFORMATION
                            </h2>

                        </div>

                    </div>

                    <form
                        method="post"
                        action="account.php"
                        class="admin-account-form"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="update_profile"
                        >

                        <div class="admin-form-group">

                            <label for="full_name">
                                FULL NAME
                            </label>

                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                value="<?= htmlspecialchars($fullName) ?>"
                                maxlength="100"
                                required
                            >

                        </div>

                        <div class="admin-form-group">

                            <label for="username">
                                USERNAME
                            </label>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= htmlspecialchars($username) ?>"
                                maxlength="50"
                                required
                            >

                        </div>

                        <div class="admin-form-group">

                            <label for="email">
                                EMAIL ADDRESS
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($email) ?>"
                                maxlength="150"
                                required
                            >

                        </div>

                        <div class="admin-account-actions">

                            <button
                                type="submit"
                                class="admin-btn-primary"
                            >
                                SAVE CHANGES
                            </button>

                        </div>

                    </form>

                </div>

                <div class="admin-account-card">

                    <div class="admin-account-card-header">

                        <div>

                            <p class="admin-eyebrow">
                                SECURITY
                            </p>

                            <h2>
                                CHANGE PASSWORD
                            </h2>

                        </div>

                    </div>

                    <form
                        method="post"
                        action="account.php"
                        class="admin-account-form"
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="change_password"
                        >

                        <div class="admin-form-group">

                            <label for="new_password">
                                NEW PASSWORD
                            </label>

                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >

                            <small>
                                Password must be at least 8 characters.
                            </small>

                        </div>

                        <div class="admin-form-group">

                            <label for="confirm_password">
                                CONFIRM NEW PASSWORD
                            </label>

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >

                        </div>

                        <div class="admin-account-actions">

                            <button
                                type="submit"
                                class="admin-btn-primary"
                            >
                                CHANGE PASSWORD
                            </button>

                        </div>

                    </form>

                </div>

            </section>

        <?php endif; ?>

    </main>

</div>

</body>
</html>