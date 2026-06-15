<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";
require_once "includes/functions.php";

if (!isLoggedIn()) {
    redirect("login.php");
}

if (isAgentOrAdmin()) {
    redirect("admin/dashboard.php");
}

$user_id = $_SESSION['user_id'];

$userStmt = $pdo->prepare("
    SELECT full_name, email, phone, created_at
    FROM users
    WHERE user_id = ?
");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

$favouritesStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM favourites
    WHERE user_id = ?
");
$favouritesStmt->execute([$user_id]);
$totalFavourites = $favouritesStmt->fetchColumn();

$clientStmt = $pdo->prepare("
    SELECT client_id
    FROM clients
    WHERE user_id = ?
");
$clientStmt->execute([$user_id]);
$client = $clientStmt->fetch(PDO::FETCH_ASSOC);

$totalEnquiries = 0;

if ($client) {
    $enquiriesStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM enquiries
        WHERE client_id = ?
    ");
    $enquiriesStmt->execute([$client['client_id']]);
    $totalEnquiries = $enquiriesStmt->fetchColumn();
}

$recentFavouritesStmt = $pdo->prepare("
    SELECT 
        f.date_saved,
        p.property_id,
        p.title,
        p.location,
        p.price,
        pi.image_url
    FROM favourites f
    JOIN properties p ON f.property_id = p.property_id
    LEFT JOIN property_images pi 
        ON p.property_id = pi.property_id 
        AND pi.is_primary = TRUE
    WHERE f.user_id = ?
    ORDER BY f.date_saved DESC
    LIMIT 3
");
$recentFavouritesStmt->execute([$user_id]);
$recentFavourites = $recentFavouritesStmt->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header.php";
?>

<section class="section">
    <div class="container dashboard-layout">

        <aside class="dashboard-sidebar">
            <div class="profile-box">
                <div class="profile-icon">👤</div>
                <h3><?= e($user['full_name']); ?></h3>
                <p><?= e($user['email']); ?></p>
            </div>

            <nav class="dashboard-menu">
                <a class="active" href="<?= url('dashboard.php'); ?>">Dashboard</a>
                <a href="<?= url('properties.php'); ?>">Browse Properties</a>
                <a href="<?= url('favourites.php'); ?>">Saved Properties</a>
                <a href="<?= url('contact.php'); ?>">Contact Us</a>
                <a href="<?= url('logout.php'); ?>">Logout</a>
            </nav>
        </aside>

        <div class="dashboard-main">
            <div class="dashboard-welcome">
                <h1>Welcome back, <?= e($user['full_name']); ?>!</h1>
                <p>Here is a quick overview of your property activity.</p>
            </div>

            <div class="admin-grid">
                <div class="stat-card">
                    <h2><?= e($totalFavourites); ?></h2>
                    <p>Saved Properties</p>
                </div>

                <div class="stat-card">
                    <h2><?= e($totalEnquiries); ?></h2>
                    <p>Submitted Enquiries</p>
                </div>

                <div class="stat-card">
                    <h2><?= date("d M Y", strtotime($user['created_at'])); ?></h2>
                    <p>Member Since</p>
                </div>
            </div>

            <div class="panel">
                <h2>Recent Saved Properties</h2>

                <?php if (count($recentFavourites) > 0): ?>
                    <div class="property-grid">
                        <?php foreach ($recentFavourites as $property): ?>
                            <div class="property-card">
                                <?php if (!empty($property['image_url'])): ?>
                                    <img 
                                        src="<?= url($property['image_url']); ?>" 
                                        alt="<?= e($property['title']); ?>"
                                    >
                                <?php endif; ?>

                                <div class="property-card-body">
                                    <h3><?= e($property['title']); ?></h3>
                                    <p><?= e($property['location']); ?></p>
                                    <p class="price">R<?= number_format($property['price'], 2); ?></p>

                                    <a 
                                        class="btn" 
                                        href="<?= url('properties-details.php?id=' . $property['property_id']); ?>"
                                    >
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>You have not saved any properties yet.</p>
                    <br>
                    <a class="btn" href="<?= url('properties.php'); ?>">
                        Browse Properties
                    </a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php require_once "includes/footer.php"; ?>