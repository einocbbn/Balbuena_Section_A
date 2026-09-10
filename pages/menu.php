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
                <p class="menu-eyebrow"> BBN BITES</p>
                <h1>OUR MENU</h1>
                <p>BURGERS &amp; QUESADILLAS</p>
            </div>
            <section class="menu-category">
                <div class="menu-category-heading">
                    <h2>BURGERS</h2>
                </div>
                <div class="menu-grid">
                    <article class="menu-card">
                        <div class="menu-image">
                            <img src="../assets/biggie-burger.jpg" alt="Biggie Burger">
                        </div>
                        <div class="menu-info">
                            <h3> BIGGIE BURGER </h3>
                            <p> A delicious and satisfying burger packed with flavor. </p>
                            <span class="menu-price"> ₱--- </span>
                        </div>
                    </article>
                    <!-- BACON BURGER -->
                    <article class="menu-card">
                        <div class="menu-image">
                            <img src="../assets/bacon-burger.jpg" alt="Bacon Burger">
                        </div>
                        <div class="menu-info">
                            <h3>BACON BURGER</h3>
                            <p>A flavorful burger topped with delicious crispy bacon.</p>
                            <span class="menu-price">₱---</span>
                        </div>
                    </article>
                </div>
            </section>
            <section class="menu-category">
                <div class="menu-category-heading">
                    <h2>QUESADILLAS</h2>
                </div>
                <div class="menu-grid">
                    <!-- CLASSIC QUESADILLA -->
                    <article class="menu-card">
                        <div class="menu-image">
                            <img src="../assets/classic-quesadilla.jpg" alt="Classic Quesadilla">
                        </div>
                        <div class="menu-info">
                            <h3>CLASSIC QUESADILLA</h3>
                            <p>A classic cheesy quesadilla made for every bite.</p>
                            <span class="menu-price">₱---</span>
                        </div>
                    </article>
                    <!-- SPINACH CHICKEN QUESADILLA -->
                    <article class="menu-card">
                        <div class="menu-image">
                            <img src="../assets/spinach-chicken-quesadilla.jpg" alt="Spinach Chicken Quesadilla">
                        </div>
                        <div class="menu-info">
                            <h3>SPINACH CHICKEN QUESADILLA</h3>
                            <p>A savory combination of chicken, spinach and melted cheese.</p>
                            <span class="menu-price">₱---</span>
                        </div>
                    </article>
                </div>
            </section>
            <section class="menu-category extras-category">
                <div class="menu-category-heading">
                    <h2>EXTRAS</h2>
                </div>
                <div class="menu-grid">
                    <!-- LUMPIANG SHANGHAI -->
                    <article class="menu-card">
                        <div class="menu-image">
                            <img src="../assets/lumpiang-shanghai.jpg" alt="Lumpiang Shanghai">
                        </div>
                        <div class="menu-info">
                            <h3>LUMPIANG SHANGHAI</h3>
                            <p>Crispy and delicious lumpiang shanghai.</p>
                            <span class="menu-price">₱---</span>
                        </div>
                    </article>
                    <!-- CHICKSILOG -->
                    <article class="menu-card">
                        <div class="menu-image">
                            <img src="../assets/chicksilog.jpg" alt="Chicksilog">
                        </div>
                        <div class="menu-info">
                            <h3>CHICKSILOG</h3>
                            <p>A satisfying Filipino-style chicken silog meal.</p>
                            <span class="menu-price">₱---</span>
                        </div>
                    </article>
                    <!-- LIEMPOSILOG -->
                    <article class="menu-card">
                        <div class="menu-image">
                            <img src="../assets/liemposilog.jpg" alt="Liempo Silog">
                        </div>
                        <div class="menu-info">
                            <h3>LIEMPOSILOG</h3>
                            <p>Flavorful liempo served with a classic silog meal.</p>
                            <span class="menu-price">₱---</span>
                        </div>
                    </article>
                    <!-- HUNGARIAN SILOG -->
                    <article class="menu-card">
                        <div class="menu-image">
                            <img src="../assets/hungariansilog.jpg" alt="Hungarian Silog">
                        </div>
                        <div class="menu-info">
                            <h3>HUNGARIAN SILOG</h3>
                            <p>A hearty Hungarian sausage served with silog.</p>
                            <span class="menu-price">₱---</span>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </section>
</main>
<?php include '../header-footer/footer.php'; ?>
</body>
</html>