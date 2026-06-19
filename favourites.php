<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";
require_once "includes/functions.php";

if (!isLoggedIn()) {
    redirect("login.php");
}

$stmt = $pdo->prepare("
    SELECT 
        p.*,
        f.favourite_id,
        pi.image_url
    FROM favourites f
    JOIN properties p ON f.property_id = p.property_id
    LEFT JOIN property_images pi 
        ON p.property_id = pi.property_id 
        AND pi.is_primary = TRUE
    WHERE f.user_id = ?
    ORDER BY f.date_saved DESC
");

$stmt->execute([$_SESSION['user_id']]);
$favourites = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header.php";
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">My Favourite Properties</h1>

        <?php if (count($favourites) > 0): ?>
            <div class="property-grid">
                <?php foreach ($favourites as $property): ?>
                    <article class="property-card">
                        <img 
                            src="<?= url($property['image_url']); ?>" 
                            alt="<?= e($property['title']); ?>"
                            loading="lazy"
                        >

                        <div class="property-card-content">
                            <span class="badge"><?= e($property['listing_type']); ?></span>

                            <h3><?= e($property['title']); ?></h3>

                            <p><?= e($property['location']); ?></p>

                            <p class="price">
                                R<?= number_format($property['price'], 2); ?>
                            </p>

                            <p>
                                <?= e($property['bedrooms']); ?> Beds |
                                <?= e($property['bathrooms']); ?> Baths |
                                <?= e($property['garages']); ?> Garages
                            </p>

                            <br>

                            <a 
                                class="btn btn-secondary" 
                                href="<?= url('properties-details.php?id=' . $property['property_id']); ?>"
                            >
                                View Details
                            </a>

                            <form action="<?= url('remove-favourites.php'); ?>" method="POST" style="margin-top: 10px;">
                                <input 
                                    type="hidden" 
                                    name="favourite_id" 
                                    value="<?= e($property['favourite_id']); ?>"
                                >

                                <button class="btn" type="submit">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="panel">
                <p>You have not saved any favourite properties yet.</p>
                <br>
                <a class="btn" href="<?= url('properties.php'); ?>">Browse Properties</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>