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

$availableProperties = $pdo->query("
    SELECT COUNT(*) 
    FROM properties 
    WHERE status = 'Available'
")->fetchColumn();

$totalPropertyValue = $pdo->query("
    SELECT COALESCE(SUM(price), 0)
    FROM properties
")->fetchColumn();

$recentEnquiriesStmt = $pdo->query("
    SELECT 
        e.name,
        e.email,
        e.message,
        e.date_submitted,
        p.title AS property_title
    FROM enquiries e
    LEFT JOIN properties p ON e.property_id = p.property_id
    ORDER BY e.date_submitted DESC
    LIMIT 4
");

$recentEnquiries = $recentEnquiriesStmt->fetchAll(PDO::FETCH_ASSOC);

require_once "../includes/header.php";
?>

<section class="admin-dashboard-page">
    <div class="container admin-dashboard-container">

        <aside class="admin-sidebar">
            <div class="admin-sidebar-title">
                <h3>Admin Panel</h3>
                <p><?= e($_SESSION['full_name'] ?? 'Admin User'); ?></p>
            </div>

            <nav class="admin-menu">
                <a class="active" href="<?= url('admin/dashboard.php'); ?>">Dashboard</a>
                <a href="<?= url('admin/properties.php'); ?>">Properties</a>
                <a href="<?= url('admin/add-property.php'); ?>">Add Property</a>
                <a href="<?= url('admin/enquiries.php'); ?>">Enquiries</a>

                <?php if (isAdmin()): ?>
                    <a href="<?= url('admin/users.php'); ?>">Users</a>
                <?php endif; ?>

                <a href="<?= url('logout.php'); ?>">Logout</a>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-welcome-panel">
                <h1>Admin Dashboard</h1>
                <p>Manage your real estate platform, users, properties and enquiries.</p>
            </div>

            <div class="admin-stat-grid">
                <div class="admin-stat-card">
                    <span class="stat-icon">🏠</span>
                    <h2><?= e($totalProperties); ?></h2>
                    <p>Total Properties</p>
                </div>

                <div class="admin-stat-card">
                    <span class="stat-icon">✅</span>
                    <h2><?= e($availableProperties); ?></h2>
                    <p>Available Properties</p>
                </div>

                <div class="admin-stat-card">
                    <span class="stat-icon">👥</span>
                    <h2><?= e($totalUsers); ?></h2>
                    <p>Total Users</p>
                </div>

                <div class="admin-stat-card">
                    <span class="stat-icon">💬</span>
                    <h2><?= e($totalEnquiries); ?></h2>
                    <p>Total Enquiries</p>
                </div>
            </div>

            <div class="admin-content-grid">
                <div class="admin-panel">
                    <h2>Platform Summary</h2>

                    <div class="summary-row">
                        <span>Total Property Value</span>
                        <strong>R<?= number_format($totalPropertyValue, 2); ?></strong>
                    </div>

                    <div class="summary-row">
                        <span>Saved Favourites</span>
                        <strong><?= e($totalFavourites); ?></strong>
                    </div>

                    <div class="summary-row">
                        <span>Database Integration</span>
                        <strong>Active</strong>
                    </div>

                    <div class="summary-row">
                        <span>Image Uploads</span>
                        <strong>Enabled</strong>
                    </div>
                </div>

                <div class="admin-panel">
                    <h2>Quick Actions</h2>

                    <div class="quick-actions">
                        <a class="btn" href="<?= url('admin/properties.php'); ?>">
                            Manage Properties
                        </a>

                        <a class="btn btn-secondary" href="<?= url('admin/add-property.php'); ?>">
                            Add Property
                        </a>

                        <a class="btn btn-secondary" href="<?= url('admin/enquiries.php'); ?>">
                            View Enquiries
                        </a>

                        <?php if (isAdmin()): ?>
                            <a class="btn btn-secondary" href="<?= url('admin/users.php'); ?>">
                                Manage Users
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="admin-panel">
                <h2>Recent Enquiries</h2>

                <?php if (count($recentEnquiries) > 0): ?>
                    <div class="recent-list">
                        <?php foreach ($recentEnquiries as $enquiry): ?>
                            <div class="recent-item">
                                <div>
                                    <strong><?= e($enquiry['name']); ?></strong>
                                    <p>
                                        <?= e($enquiry['property_title'] ?? 'General Enquiry'); ?>
                                        ·
                                        <?= e($enquiry['email']); ?>
                                    </p>
                                    <small><?= e(substr($enquiry['message'], 0, 100)); ?>...</small>
                                </div>

                                <span><?= date("d M Y", strtotime($enquiry['date_submitted'])); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No enquiries have been submitted yet.</p>
                <?php endif; ?>
            </div>
        </main>

    </div>
</section>

<?php require_once "../includes/footer.php"; ?>