<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";
require_once "includes/functions.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $contact_message = trim($_POST['message']);

    if ($name && $email && $contact_message) {
        $client_id = null;

        if (isLoggedIn()) {
            $clientStmt = $pdo->prepare("SELECT client_id FROM clients WHERE user_id = ?");
            $clientStmt->execute([$_SESSION['user_id']]);
            $client = $clientStmt->fetch(PDO::FETCH_ASSOC);
            $client_id = $client['client_id'] ?? null;
        }

        $stmt = $pdo->prepare("
            INSERT INTO enquiries 
            (client_id, property_id, name, email, phone, message)
            VALUES (?, NULL, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $client_id,
            $name,
            $email,
            $phone,
            $contact_message
        ]);

        $message = "Your message was sent successfully.";
    } else {
        $message = "Please complete all required fields.";
    }
}

require_once "includes/header.php";
?>

<section class="section">
    <div class="container">
        <div class="form-card">
            <h1>Contact Us</h1>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?= e($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="enquiryForm">
                <div class="form-group">
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        placeholder="Your Name" 
                        required
                    >
                </div>

                <div class="form-group">
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        placeholder="Your Email" 
                        required
                    >
                </div>

                <div class="form-group">
                    <input 
                        type="text" 
                        name="phone" 
                        id="phone" 
                        placeholder="Your Phone Number"
                    >
                </div>

                <div class="form-group">
                    <textarea 
                        name="message" 
                        id="message" 
                        placeholder="Your Message" 
                        required
                    ></textarea>
                </div>

                <button class="btn" type="submit">Send Message</button>
            </form>
        </div>

        <div class="panel">
            <h2>Contact Details</h2>
            <p><strong>Email:</strong> info@britsrealty.co.za</p>
            <p><strong>Phone:</strong> 012 345 6789</p>
            <p><strong>Address:</strong> Pretoria, Gauteng</p>
        </div>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>