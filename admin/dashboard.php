<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";
require_once "../includes/functions.php";

if (!isAgentOrAdmin()) {
    redirect("login.php");
}

$totalProperties = $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalEnquiries = $pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn();
$totalFavourites = $pdo->query("SELECT COUNT(*) FROM favourites")->fetchColumn();

require_once "../includes/header.php";
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Admin Dashboard</h1>

        <div class="admin-grid">
            <div class="stat-card">
                <h2><?= e($totalProperties); ?></h2>
                <p>Properties</p>
            </div>

            <div class="stat-card">
                <h2><?= e($totalUsers); ?></h2>
                <p>Users</p>
            </div>

            <div class="stat-card">
                <h2><?= e($totalEnquiries); ?></h2>
                <p>Enquiries</p>
            </div>

            <div class="stat-card">
                <h2><?= e($totalFavourites); ?></h2>
                <p>Favourites</p>
            </div>
        </div>

        <br>

        <div class="panel">
            <h2>Management</h2>
            <br>

            <a class="btn" href="<?= url('admin/properties.php'); ?>">
                Manage Properties
            </a>

            <a class="btn btn-secondary" href="<?= url('admin/add-property.php'); ?>">
                Add Property
            </a>

            <a class="btn btn-secondary" href="<?= url('admin/enquiries.php'); ?>">
                View Enquiries
            </a>
        </div>
    </div>
</section>

<?php require_once "../includes/footer.php"; ?>