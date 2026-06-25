<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";
require_once "../includes/functions.php";

if (!isAgentOrAdmin()) {
    redirect("login.php");
}

$user_id = $_SESSION['user_id'];

$message = "";
$error = "";

$allowedStatuses = [
    'New',
    'Contacted',
    'Viewing Scheduled',
    'Closed',
    'Lost'
];

// Update enquiry status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $enquiryId = (int) ($_POST['enquiry_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if ($enquiryId > 0 && in_array($status, $allowedStatuses)) {
        try {
            if (isAdmin()) {
                // Admin can update any enquiry
                $updateStmt = $pdo->prepare("
                    UPDATE enquiries
                    SET status = ?
                    WHERE enquiry_id = ?
                ");

                $updateStmt->execute([$status, $enquiryId]);
            } else {
                // Agent can only update enquiries linked to assigned properties
                $updateStmt = $pdo->prepare("
                    UPDATE enquiries e
                    JOIN properties p ON e.property_id = p.property_id
                    JOIN agents a ON p.agent_id = a.agent_id
                    SET e.status = ?
                    WHERE e.enquiry_id = ?
                    AND a.user_id = ?
                ");

                $updateStmt->execute([$status, $enquiryId, $user_id]);
            }

            $message = "Enquiry status updated successfully.";
        } catch (Exception $e) {
            $error = "Could not update enquiry status.";
        }
    } else {
        $error = "Invalid enquiry status selected.";
    }
}

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

        <?php if ($message): ?>
            <div class="alert alert-success">
                <?= e($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?= e($error); ?>
            </div>
        <?php endif; ?>

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
                        <th>Status</th>
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

                                <td>
                                    <form method="POST">
                                        <input 
                                            type="hidden" 
                                            name="enquiry_id" 
                                            value="<?= e($enquiry['enquiry_id']); ?>"
                                        >

                                        <select name="status">
                                            <?php foreach ($allowedStatuses as $statusOption): ?>
                                                <option 
                                                    value="<?= e($statusOption); ?>"
                                                    <?= ($enquiry['status'] ?? 'New') === $statusOption ? 'selected' : ''; ?>
                                                >
                                                    <?= e($statusOption); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <br><br>

                                        <button 
                                            class="btn btn-secondary" 
                                            type="submit" 
                                            name="update_status"
                                        >
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No enquiries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once "../includes/footer.php"; ?>