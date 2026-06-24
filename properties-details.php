<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";
require_once "includes/functions.php";

if (empty($_GET['id'])) {
    redirect("properties.php");
}

$property_id = $_GET['id'];
$message = "";

$stmt = $pdo->prepare("
    SELECT 
        p.*,
        u.full_name AS agent_name,
        u.email AS agent_email,
        u.phone AS agent_phone,
        a.agency_name,
        a.position,
        a.bio
    FROM properties p
    LEFT JOIN agents a ON p.agent_id = a.agent_id
    LEFT JOIN users u ON a.user_id = u.user_id
    WHERE p.property_id = ?
");

$stmt->execute([$property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    redirect("properties.php");
}

$imageStmt = $pdo->prepare("
    SELECT *
    FROM property_images
    WHERE property_id = ?
    ORDER BY is_primary DESC, display_order ASC
");

$imageStmt->execute([$property_id]);
$images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);

$mainImage = count($images) > 0 ? $images[0]['image_url'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $enquiry_message = trim($_POST['message']);

    if ($name && $email && $enquiry_message) {
        $client_id = null;

        if (isLoggedIn()) {
            $clientStmt = $pdo->prepare("SELECT client_id FROM clients WHERE user_id = ?");
            $clientStmt->execute([$_SESSION['user_id']]);
            $client = $clientStmt->fetch(PDO::FETCH_ASSOC);
            $client_id = $client['client_id'] ?? null;
        }

        $insert = $pdo->prepare("
            INSERT INTO enquiries 
            (client_id, property_id, name, email, phone, message)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $insert->execute([
            $client_id,
            $property_id,
            $name,
            $email,
            $phone,
            $enquiry_message
        ]);

        $message = "Your enquiry was submitted successfully.";
    } else {
        $message = "Please complete all required fields.";
    }
}

require_once "includes/header.php";
?>

<section class="section">
    <div class="container">
        <div class="details-layout">
            <div>
                <?php if ($mainImage): ?>
                    <img 
                        id="mainPropertyImage"
                        class="details-image" 
                        src="<?= url($mainImage); ?>" 
                        alt="<?= e($property['title']); ?>"
                        loading="eager"
                    >
                <?php endif; ?>

                <?php if (count($images) > 1): ?>
                    <div class="image-gallery">
                        <?php foreach ($images as $image): ?>
                            <img 
                                class="gallery-thumb"
                                src="<?= url($image['image_url']); ?>" 
                                alt="Property image"
                            >
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="panel">
                    <span class="badge"><?= e($property['listing_type']); ?></span>

                    <h1><?= e($property['title']); ?></h1>

                    <p class="price">
                        R<?= number_format($property['price'], 2); ?>
                    </p>

                    <p><strong>Location:</strong> <?= e($property['location']); ?></p>
                    <p><strong>Address:</strong> <?= e($property['address']); ?></p>
                    <p><strong>Property Type:</strong> <?= e($property['property_type']); ?></p>
                    <p><strong>Status:</strong> <?= e($property['status']); ?></p>

                    <br>
                    
                    <h2>Amenities</h2>
                    <p>
                        <strong>Bedrooms:</strong> <?= e($property['bedrooms']); ?> |
                        <strong>Bathrooms:</strong> <?= e($property['bathrooms']); ?> |
                        <strong>Garages:</strong> <?= e($property['garages']); ?>
                    </p>

                    <p>
                        <strong>Floor Size:</strong> <?= e($property['floor_size']); ?> m²

                        <?php if (!empty($property['erf_size'])): ?>
                            | <strong>Erf Size:</strong> <?= e($property['erf_size']); ?> m²
                        <?php endif; ?>
                    </p>

                    <br>

                    <h2>Description</h2>
                    <p><?= e($property['description']); ?></p>

                    <br>

                    <?php
                    $mapQuery = urlencode(($property['address'] ?? '') . ' ' . ($property['location'] ?? ''));
                    ?>

                    <a 
                        class="btn btn-secondary" 
                        href="https://www.google.com/maps/search/?api=1&query=<?= $mapQuery; ?>" 
                        target="_blank"
                    >
                        View on Map
                    </a>

                    <a 
                        class="btn btn-secondary" 
                        href="<?= url('mortgage-calculator.php?price=' . $property['price']); ?>"
                    >
                        Calculate Mortgage
                    </a>

                    <?php if (isLoggedIn()): ?>
                        <form action="<?= url('save-favourites.php'); ?>" method="POST">
                            <input 
                                type="hidden" 
                                name="property_id" 
                                value="<?= e($property['property_id']); ?>"
                            >

                            <button class="btn" type="submit">
                                Save to Favourites
                            </button>
                        </form>
                    <?php else: ?>
                        <p>
                            <a href="<?= url('login.php'); ?>">Login</a> to save this property to your favourites.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <aside>
                <div class="panel">
                    <h2>Agent Details</h2>

                    <p><strong>Name:</strong> <?= e($property['agent_name'] ?? 'No agent assigned'); ?></p>
                    <p><strong>Agency:</strong> <?= e($property['agency_name'] ?? 'N/A'); ?></p>
                    <p><strong>Position:</strong> <?= e($property['position'] ?? 'N/A'); ?></p>
                    <p><strong>Email:</strong> <?= e($property['agent_email'] ?? 'N/A'); ?></p>
                    <p><strong>Phone:</strong> <?= e($property['agent_phone'] ?? 'N/A'); ?></p>

                    <?php if (!empty($property['bio'])): ?>
                        <br>
                        <p><?= e($property['bio']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="panel">
                    <h2>Send Enquiry</h2>

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

                        <button class="btn" type="submit">
                            Submit Enquiry
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>