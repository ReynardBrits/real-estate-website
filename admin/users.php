<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";
require_once "../includes/functions.php";

if (!isAdmin()) {
    redirect("admin/dashboard.php");
}

$stmt = $pdo->query("
    SELECT 
        user_id,
        full_name,
        email,
        phone,
        role,
        created_at
    FROM users
    ORDER BY created_at DESC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once "../includes/header.php";
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">Manage Users</h1>

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
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Current Role</th>
                        <th>Registered</th>
                        <th>Change Role</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= e($user['full_name']); ?></td>
                                <td><?= e($user['email']); ?></td>
                                <td><?= e($user['phone']); ?></td>
                                <td><?= e(ucfirst($user['role'])); ?></td>
                                <td><?= e($user['created_at']); ?></td>

                                <td>
                                    <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                                        Cannot change own role
                                    <?php else: ?>
                                        <form 
                                            method="POST" 
                                            action="<?= url('admin/update-user-role.php'); ?>"
                                            style="display: flex; gap: 8px; align-items: center;"
                                        >
                                            <input 
                                                type="hidden" 
                                                name="user_id" 
                                                value="<?= e($user['user_id']); ?>"
                                            >

                                            <select name="role" required>
                                                <option value="client" <?= $user['role'] === 'client' ? 'selected' : ''; ?>>
                                                    Client
                                                </option>

                                                <option value="agent" <?= $user['role'] === 'agent' ? 'selected' : ''; ?>>
                                                    Agent
                                                </option>

                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : ''; ?>>
                                                    Admin
                                                </option>
                                            </select>

                                            <button class="btn" type="submit">
                                                Update
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once "../includes/footer.php"; ?>