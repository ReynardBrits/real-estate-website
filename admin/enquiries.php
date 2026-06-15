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
        e.*,
        p.title AS property_title
    FROM enquiries e
    LEFT JOIN properties p ON e.property_id = p.property_id
    ORDER BY e.date_submitted DESC
");

$enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once "../includes/header.php";
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Customer Enquiries</h1>

        <p>
            <a class="btn btn-secondary" href="<?= url('admin/dashboard.php'); ?>">
                Back to Dashboard
            </a>
        </p>

        <br>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Property</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($enquiries) > 0): ?>
                        <?php foreach ($enquiries as $enquiry): ?>
                            <tr>
                                <td><?= e($enquiry['date_submitted']); ?></td>
                                <td><?= e($enquiry['property_title'] ?? 'General Enquiry'); ?></td>
                                <td><?= e($enquiry['name']); ?></td>
                                <td><?= e($enquiry['email']); ?></td>
                                <td><?= e($enquiry['phone']); ?></td>
                                <td><?= e($enquiry['message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No enquiries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once "../includes/footer.php"; ?>