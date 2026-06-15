<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";
require_once "../includes/functions.php";

if (!isAgentOrAdmin()) {
    redirect("login.php");
}

$stmt = $pdo->query("
    SELECT 
        p.*,
        u.full_name AS agent_name,
        pi.image_url
    FROM properties p
    LEFT JOIN agents a ON p.agent_id = a.agent_id
    LEFT JOIN users u ON a.user_id = u.user_id
    LEFT JOIN property_images pi 
        ON p.property_id = pi.property_id 
        AND pi.is_primary = TRUE
    ORDER BY p.created_at DESC
");

$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once "../includes/header.php";
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Manage Properties</h1>

        <p>
            <a class="btn" href="<?= url('admin/add-property.php'); ?>">
                Add New Property
            </a>

            <a class="btn btn-secondary" href="<?= url('admin/dashboard.php'); ?>">
                Back to Dashboard
            </a>
        </p>

        <br>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Listing</th>
                        <th>Status</th>
                        <th>Agent</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($properties) > 0): ?>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($property['image_url'])): ?>
                                        <img 
                                            src="<?= url($property['image_url']); ?>" 
                                            alt="<?= e($property['title']); ?>" 
                                            style="width: 90px; height: 60px; object-fit: cover; border-radius: 6px;"
                                        >
                                    <?php else: ?>
                                        No image
                                    <?php endif; ?>
                                </td>

                                <td><?= e($property['title']); ?></td>
                                <td><?= e($property['location']); ?></td>
                                <td>R<?= number_format($property['price'], 2); ?></td>
                                <td><?= e($property['listing_type']); ?></td>
                                <td><?= e($property['status']); ?></td>
                                <td><?= e($property['agent_name'] ?? 'N/A'); ?></td>

                                <td>
                                    <a href="<?= url('properties-details.php?id=' . $property['property_id']); ?>">
                                        View
                                    </a>
                                    |
                                    <a href="<?= url('admin/edit-property.php?id=' . $property['property_id']); ?>">
                                        Edit
                                    </a>
                                    |
                                    <a 
                                        href="<?= url('admin/delete-property.php?id=' . $property['property_id']); ?>" 
                                        onclick="return confirm('Are you sure you want to delete this property?');"
                                    >
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No properties found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once "../includes/footer.php"; ?>