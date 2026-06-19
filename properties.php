<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";
require_once "includes/header.php";

$sql = "
    SELECT 
        p.*,
        pi.image_url
    FROM properties p
    LEFT JOIN property_images pi 
        ON p.property_id = pi.property_id 
        AND pi.is_primary = TRUE
    WHERE p.status = 'Available'
";

$params = [];

if (!empty($_GET['location'])) {
    $sql .= " AND p.location LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
}

if (!empty($_GET['property_type'])) {
    $sql .= " AND p.property_type = ?";
    $params[] = $_GET['property_type'];
}

if (!empty($_GET['listing_type'])) {
    $sql .= " AND p.listing_type = ?";
    $params[] = $_GET['listing_type'];
}

if (!empty($_GET['min_price'])) {
    $sql .= " AND p.price >= ?";
    $params[] = $_GET['min_price'];
}

if (!empty($_GET['max_price'])) {
    $sql .= " AND p.price <= ?";
    $params[] = $_GET['max_price'];
}

if (!empty($_GET['bedrooms'])) {
    $sql .= " AND p.bedrooms >= ?";
    $params[] = $_GET['bedrooms'];
}

if (!empty($_GET['bathrooms'])) {
    $sql .= " AND p.bathrooms >= ?";
    $params[] = $_GET['bathrooms'];
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

$suggestionStmt = $pdo->query("
    SELECT Distinct location
    FROM properties
    ORDER BY location ASC
");

$locationSuggestions = $suggestionStmt->fetchAll(PDO::FETCH_COLUMN);
?>



<section class="section">
    <div class="container">
        <h1 class="section-title">Available Properties</h1>

        <div class="panel">
            <form class="search-form" method="GET">
                <input 
                    type="text" 
                    name="location" 
                    list="locationSuggestions"
                    placeholder="Location"
                    value="<?= e($_GET['location'] ?? ''); ?>"
                >

                <datalist id="locationSuggestions">
                    <?php foreach ($locationSuggestions as $suggestion): ?>
                        <option value="<?= e($suggestion); ?>">
                    <?php endforeach; ?>
                </datalist>

                <select name="property_type">
                    <option value="">Property Type</option>
                    <option value="House" <?= ($_GET['property_type'] ?? '') === 'House' ? 'selected' : ''; ?>>House</option>
                    <option value="Apartment" <?= ($_GET['property_type'] ?? '') === 'Apartment' ? 'selected' : ''; ?>>Apartment</option>
                    <option value="Townhouse" <?= ($_GET['property_type'] ?? '') === 'Townhouse' ? 'selected' : ''; ?>>Townhouse</option>
                </select>

                <select name="listing_type">
                    <option value="">Listing Type</option>
                    <option value="For Sale" <?= ($_GET['listing_type'] ?? '') === 'For Sale' ? 'selected' : ''; ?>>For Sale</option>
                    <option value="For Rent" <?= ($_GET['listing_type'] ?? '') === 'For Rent' ? 'selected' : ''; ?>>For Rent</option>
                </select>

                <input 
                    type="number" 
                    name="min_price" 
                    placeholder="Min Price"
                    value="<?= e($_GET['min_price'] ?? ''); ?>"
                >

                <input 
                    type="number" 
                    name="max_price" 
                    placeholder="Max Price"
                    value="<?= e($_GET['max_price'] ?? ''); ?>"
                >

                <input 
                    type="number" 
                    name="bedrooms" 
                    placeholder="Bedrooms"
                    value="<?= e($_GET['bedrooms'] ?? ''); ?>"
                >

                <input 
                    type="number" 
                    name="bathrooms" 
                    placeholder="Bathrooms"
                    value="<?= e($_GET['bathrooms'] ?? ''); ?>"
                >

                <button class="btn" type="submit">Filter</button>
            </form>
        </div>

        <div class="live-search">
            <input type="text" id="liveSearch" placeholder="Search visible results...">
        </div>

        <form method="GET" action="<?= url('compare.php'); ?>" id="compareForm">

            <?php if (count($properties) > 0): ?>
                <div class="property-grid">
                    <?php foreach ($properties as $property): ?>
                        <article class="property-card">

                            <label class="compare-option">
                                <input 
                                    type="checkbox" 
                                    name="ids[]" 
                                    value="<?= e($property['property_id']); ?>"
                                >
                                    Compare
                                </label>
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

                                <p>
                                    <?= e($property['floor_size']); ?> m² floor
                                    <?php if (!empty($property['erf_size'])): ?>
                                    <?= e($property['erf_size']); ?> m² erf
                                    <?php endif; ?>
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

                <br>
                <button class="btn" type="submit">
                    Compare Selected
                </button>
        </form>

        <?php else: ?>
            <div class="panel">
                <p>No properties matched your search. Try changing the filters.</p>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once "includes/footer.php"; ?>