<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$isInsidePages = basename(dirname($_SERVER['PHP_SELF'])) === 'pages';
$basePath = $isInsidePages ? '../' : '';

$cartCount = 0;

if (!empty($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
?>

<header class="site-header">
    <div class="container nav-wrap">

        <a class="brand" href="<?= $basePath ?>index.php" aria-label="BBN Bites home">
            <img src="<?= $basePath ?>assets/logo.png" alt="BBN Bites logo">

            <div class="brand-text">
                <span class="brand-name">BBN BITES</span>
                <span class="brand-subtitle">BURGERS &amp; QUESADILLAS</span>
            </div>
        </a>

        <input class="nav-toggle" type="checkbox" id="nav-toggle">

        <label class="nav-toggle-label" for="nav-toggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </label>

        <nav class="main-nav">

            <a href="<?= $basePath ?>index.php"
               class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">
                HOME
            </a>

            <?php if (isset($_SESSION['customer_id'])): ?>

            <a href="<?= $basePath ?>pages/menu.php"
               class="<?= $currentPage == 'menu.php' ? 'active' : '' ?>">
                MENU
            </a>

            <a href="<?= $basePath ?>pages/about.php"
               class="<?= $currentPage == 'about.php' ? 'active' : '' ?>">
                ABOUT US
            </a>

            <a href="<?= $basePath ?>pages/mission.php"
               class="<?= $currentPage == 'mission.php' ? 'active' : '' ?>">
                OUR MISSION
            </a>

            <a href="<?= $basePath ?>pages/contact.php"
               class="<?= $currentPage == 'contact.php' ? 'active' : '' ?>">
                CONTACT US
            </a>

            <a href="<?= $basePath ?>pages/cart.php"
               class="cart-link <?= $currentPage == 'cart.php' ? 'active' : '' ?>">
                🛒
                <span class="cart-count"><?= $cartCount ?></span>
            </a>

            <a href="<?= $basePath ?>pages/logout.php">
            LOGOUT
            </a>

            <?php else: ?>

                <a href="<?= $basePath ?>pages/login.php"
                class="<?= $currentPage == 'login.php' ? 'active' : '' ?>">
                    LOGIN
                </a>

                <a href="<?= $basePath ?>pages/register.php"
                class="<?= $currentPage == 'register.php' ? 'active' : '' ?>">
                    REGISTER
                </a>

            <?php endif; ?>
        
        </nav>

    </div>
</header>