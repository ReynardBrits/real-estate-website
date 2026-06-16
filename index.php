<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";
require_once "includes/header.php";

$stmt = $pdo->query("
    SELECT 
        p.*,
        pi.image_url
    FROM properties p
    LEFT JOIN property_images pi 
        ON p.property_id = pi.property_id 
        AND pi.is_primary = TRUE
    WHERE p.status = 'Available'
    ORDER BY p.created_at DESC
    LIMIT 3
");

$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="hero">
    <div class="container hero-content">
        <h1>Find Your Dream Home</h1>
        <p>Browse houses, apartments and townhouses available for sale or rent.</p>
        <a href="<?= url('properties.php'); ?>" class="btn">View Properties</a>
    </div>
</section>

<div class="container">
    <div class="search-box">
        <form class="search-form" action="<?= url('properties.php'); ?>" method="GET">
            <input type="text" name="location" placeholder="Location">

            <select name="property_type">
                <option value="">Property Type</option>
                <option value="House">House</option>
                <option value="Apartment">Apartment</option>
                <option value="Townhouse">Townhouse</option>
            </select>

            <select name="listing_type">
                <option value="">Listing Type</option>
                <option value="For Sale">For Sale</option>
                <option value="For Rent">For Rent</option>
            </select>

            <input type="number" name="min_price" placeholder="Min Price">
            <input type="number" name="max_price" placeholder="Max Price">
            <input type="number" name="bedrooms" placeholder="Bedrooms">
            <button class="btn" type="submit">Search</button>
        </form>
    </div>
</div>

<section class="section">
    <div class="container">
        <h2 class="section-title">Featured Properties</h2>

        <?php if (count($properties) > 0): ?>
            <div class="property-grid">
                <?php foreach ($properties as $property): ?>
                    <article class="property-card">
                        <img 
                            src="<?= url($property['image_url']); ?>" 
                            alt="<?= e($property['title']); ?>"
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
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="panel">
                <p>No featured properties are available yet.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section about-section">
    <div class="container">
        <div class="about-content">
            <div>
                <h2 class="section-title">About Brits Realty</h2>

                <p>
                    At Brits Realty, we help clients find properties that match their lifestyle, 
                    budget and long-term goals. Whether you are buying your first home, looking 
                    for an investment property, or searching for a rental, our platform makes it 
                    easier to explore available listings and connect with the right property professionals.
                </p>

                <a class="btn" href="<?= url('properties.php'); ?>">
                    Browse Properties
                </a>
            </div>

            <div class="about-card">
                <h3>Why Choose Brits Realty?</h3>

                <ul>
                    <li>Detailed property listings with multiple images</li>
                    <li>Easy search and filtering options</li>
                    <li>Saved favourites for registered users</li>
                    <li>Direct property enquiries</li>
                    <li>Built-in mortgage repayment calculator</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>