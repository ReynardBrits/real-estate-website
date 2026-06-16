<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";
require_once "../includes/functions.php";

if (!isAgentOrAdmin()) {
    redirect("login.php");
}

$user_id = $_SESSION['user_id'];

if (isAdmin()) {
    // Admin sees all enquiries
    $stmt = $pdo->query("
        SELECT 
            e.*,
            p.title AS property_title,
            p.location AS property_location,
            u.full_name AS agent_name
        FROM enquiries e
        LEFT JOIN properties p ON e.property_id = p.property_id
        LEFT JOIN agents a ON p.agent_id = a.agent_id
        LEFT JOIN users u ON a.user_id = u.user_id
        ORDER BY e.date_submitted DESC
    ");
} else {
    // Agent only sees enquiries for properties assigned to them
    $stmt = $pdo->prepare("
        SELECT 
            e.*,
            p.title AS property_title,
            p.location AS property_location,
            u.full_name AS agent_name
        FROM enquiries e
        JOIN properties p ON e.property_id = p.property_id
        JOIN agents a ON p.agent_id = a.agent_id
        JOIN users u ON a.user_id = u.user_id
        WHERE a.user_id = ?
        ORDER BY e.date_submitted DESC
    ");

    $stmt->execute([$user_id]);
}

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
                        <th>Assigned Agent</th>
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

                                <td>
                                    <?php if (!empty($enquiry['property_title'])): ?>
                                        <strong><?= e($enquiry['property_title']); ?></strong>
                                        <br>
                                        <small><?= e($enquiry['property_location']); ?></small>
                                    <?php else: ?>
                                        General Enquiry
                                    <?php endif; ?>
                                </td>

                                <td><?= e($enquiry['agent_name'] ?? 'N/A'); ?></td>
                                <td><?= e($enquiry['name']); ?></td>
                                <td><?= e($enquiry['email']); ?></td>
                                <td><?= e($enquiry['phone']); ?></td>
                                <td><?= e($enquiry['message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No enquiries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once "../includes/footer.php"; ?>