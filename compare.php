<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";
require_once "includes/functions.php";

$ids = $_GET['ids'] ?? [];

if (!is_array($ids)) {
    $ids = [$ids];
}

$ids = array_unique(array_filter(array_map('intval', $ids)));

if (count($ids) > 4) {
    $ids = array_slice($ids, 0, 4);
}

$properties = [];

if (count($ids) >= 2) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            pi.image_url
        FROM properties p
        LEFT JOIN property_images pi 
            ON p.property_id = pi.property_id 
            AND pi.is_primary = TRUE
        WHERE p.property_id IN ($placeholders)
    ");

    $stmt->execute($ids);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once "includes/header.php";
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Compare Properties</h1>

        <?php if (count($properties) < 2): ?>
            <div class="panel">
                <p>Please select at least two properties to compare.</p>
                <br>
                <a class="btn" href="<?= url('properties.php'); ?>">
                    Back to Properties
                </a>
            </div>
        <?php else: ?>
            <div class="compare-table-wrapper">
                <table class="compare-table">
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <?php foreach ($properties as $property): ?>
                                <th><?= e($property['title']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>Image</td>
                            <?php foreach ($properties as $property): ?>
                                <td>
                                    <?php if (!empty($property['image_url'])): ?>
                                        <img 
                                            src="<?= url($property['image_url']); ?>" 
                                            alt="<?= e($property['title']); ?>"
                                            loading="lazy"
                                            style="width: 180px; height: 120px; object-fit: cover; border-radius: 8px;"
                                        >
                                    <?php else: ?>
                                        No image
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td>Price</td>
                            <?php foreach ($properties as $property): ?>
                                <td>R<?= number_format($property['price'], 2); ?></td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td>Location</td>
                            <?php foreach ($properties as $property): ?>
                                <td><?= e($property['location']); ?></td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td>Property Type</td>
                            <?php foreach ($properties as $property): ?>
                                <td><?= e($property['property_type']); ?></td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td>Listing Type</td>
                            <?php foreach ($properties as $property): ?>
                                <td><?= e($property['listing_type']); ?></td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td>Bedrooms</td>
                            <?php foreach ($properties as $property): ?>
                                <td><?= e($property['bedrooms']); ?></td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td>Bathrooms</td>
                            <?php foreach ($properties as $property): ?>
                                <td><?= e($property['bathrooms']); ?></td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td>Garages</td>
                            <?php foreach ($properties as $property): ?>
                                <td><?= e($property['garages']); ?></td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td>Floor Size</td>
                            <?php foreach ($properties as $property): ?>
                                <td><?= e($property['floor_size']); ?> m²</td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td>Erf Size</td>
                            <?php foreach ($properties as $property): ?>
                                <td><?= e($property['erf_size']); ?> m²</td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td>Status</td>
                            <?php foreach ($properties as $property): ?>
                                <td><?= e($property['status']); ?></td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td>Action</td>
                            <?php foreach ($properties as $property): ?>
                                <td>
                                    <a 
                                        class="btn btn-secondary" 
                                        href="<?= url('properties-details.php?id=' . $property['property_id']); ?>"
                                    >
                                        View Details
                                    </a>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>

            <br>

            <a class="btn" href="<?= url('properties.php'); ?>">
                Back to Properties
            </a>
        <?php endif; ?>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>