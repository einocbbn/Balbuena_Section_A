<?php
require_once '../database/customer_auth.php';
require_once '../database/config.php';
require_once '../database/validation.php';

$errors = [];
$success = '';

$name = '';
$email = '';
$phone = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $errors = validateContact(
        $name,
        $email,
        $phone,
        $subject,
        $message
    );

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO customer_messages
                (customer_id, name, email, phone, subject, message, status)
                VALUES (?, ?, ?, ?, ?, ?, 'Unread')
            ");

            $stmt->execute([
                $_SESSION['customer_id'],
                $name,
                $email,
                $phone !== '' ? $phone : null,
                $subject,
                $message
            ]);

            $success = 'Your message has been sent successfully. Thank you for contacting BBN Bites!';

            $name = '';
            $email = '';
            $phone = '';
            $subject = '';
            $message = '';
        } catch (PDOException $e) {
            $errors[] = 'Unable to send your message right now. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBN BITES | Contact Us</title>
    <meta name="description" content="Contact BBN Bites">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/contact.css">
    <link rel="stylesheet" href="../header-footer/header.css">
    <link rel="stylesheet" href="../header-footer/footer.css">
</head>

<body>

<?php include '../header-footer/header.php'; ?>

<main>
    <section class="contact-page">
        <div class="container">
            <div class="contact-title">
                <p class="contact-eyebrow">BBN BITES</p>
                <h1>CONTACT US</h1>
                <p>BURGERS &amp; QUESADILLAS</p>
            </div>

            <div class="contact-content">

                <div class="contact-info">
                    <h2>GET IN TOUCH</h2>

                    <p>
                        Have a question, suggestion, or want to know more about our food?
                        Send us a message and we will be happy to hear from you.
                    </p>

                    <div class="contact-details">

                        <div class="contact-detail">
                            <h3>EMAIL</h3>
                            <a href="mailto:bjadeconiemarie@gmail.com">
                                bjadeconiemarie@gmail.com
                            </a>
                        </div>

                        <div class="contact-detail">
                            <h3>PHONE</h3>
                            <a href="tel:+639618049468">
                                +63 961 804 9468
                            </a>
                        </div>

                        <div class="contact-detail">
                            <h3>LOCATION</h3>
                            <p>Bolisong, Manjuyod Negros Oriental</p>
                        </div>

                    </div>
                </div>

                <div class="contact-form-wrapper">
                    <h2>SEND US A MESSAGE</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="checkout-error">
                            <?php foreach ($errors as $error): ?>
                                <p><?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success !== ''): ?>
                        <div class="contact-success">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form class="contact-form" action="contact.php" method="post">

                        <div class="form-row">

                            <div class="form-group">
                                <label for="name">FULL NAME</label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    placeholder="Enter your full name"
                                    value="<?= htmlspecialchars($name) ?>"
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

                        </div>

                        <div class="form-group">
                            <label for="phone">PHONE NUMBER</label>

                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                placeholder="Enter your phone number"
                                value="<?= htmlspecialchars($phone) ?>"
                                maxlength="11"
                                inputmode="numeric"
                            >

                            <span id="phone-error" class="validation-error"></span>
                        </div>

                        <div class="form-group">
                            <label for="subject">SUBJECT</label>

                            <input
                                type="text"
                                id="subject"
                                name="subject"
                                placeholder="What is your message about?"
                                value="<?= htmlspecialchars($subject) ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="message">MESSAGE</label>

                            <textarea
                                id="message"
                                name="message"
                                rows="7"
                                placeholder="Write your message here..."
                                required
                            ><?= htmlspecialchars($message) ?></textarea>
                        </div>

                        <button type="submit" class="btn contact-btn">
                            SEND MESSAGE
                        </button>

                    </form>
                </div>
            </div>

            <div class="contact-bottom">
                <h2>WE'D LOVE TO HEAR FROM YOU</h2>

                <p>
                    Whether you have feedback, questions, or simply want to say hello,
                    feel free to reach out to BBN Bites.
                </p>

                <a class="btn contact-menu-btn" href="menu.php">
                    VIEW OUR MENU
                </a>
            </div>

        </div>
    </section>
</main>

<?php include '../header-footer/footer.php'; ?>

<script>
const phone = document.getElementById('phone');
const phoneError = document.getElementById('phone-error');

if (phone) {
    phone.addEventListener('input', function () {
        const value = this.value;

        if (/[^0-9]/.test(value)) {
            phoneError.textContent = 'Invalid input. Phone number must contain numbers only.';
            this.setCustomValidity('Phone number must contain numbers only.');
        } else if (value !== '' && !value.startsWith('09')) {
            phoneError.textContent = 'Invalid input. Phone number must start with 09.';
            this.setCustomValidity('Phone number must start with 09.');
        } else if (value.length > 0 && value.length < 11) {
            phoneError.textContent = 'Phone number must be exactly 11 digits.';
            this.setCustomValidity('Phone number must be exactly 11 digits.');
        } else {
            phoneError.textContent = '';
            this.setCustomValidity('');
        }
    });
}
</script>

</body>
</html>