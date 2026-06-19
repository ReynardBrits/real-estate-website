<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";
require_once "../includes/functions.php";

if (!isAdmin()) {
    redirect("admin/dashboard.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $role = $_POST['role'] ?? null;

    $allowedRoles = ['client', 'agent', 'admin'];

    if ($user_id && in_array($role, $allowedRoles)) {
        try {
            $pdo->beginTransaction();

            // Do not allow the logged-in admin to change their own role
            if ($user_id == $_SESSION['user_id']) {
                $pdo->rollBack();
                redirect("admin/users.php");
            }

            // Update role in users table
            $updateUser = $pdo->prepare("
                UPDATE users
                SET role = ?
                WHERE user_id = ?
            ");
            $updateUser->execute([$role, $user_id]);

            if ($role === 'agent') {
                // If user becomes an agent, create agent profile if it does not exist
                $checkAgent = $pdo->prepare("
                    SELECT agent_id
                    FROM agents
                    WHERE user_id = ?
                ");
                $checkAgent->execute([$user_id]);

                if (!$checkAgent->fetch(PDO::FETCH_ASSOC)) {
                    $insertAgent = $pdo->prepare("
                        INSERT INTO agents
                        (user_id, agency_name, position, bio)
                        VALUES (?, ?, ?, ?)
                    ");

                    $insertAgent->execute([
                        $user_id,
                        'Brits Realty',
                        'Property Agent',
                        'Agent profile created by administrator.'
                    ]);
                }
            }

            if ($role === 'client') {
                // If user becomes client, remove agent profile if it exists
                $deleteAgent = $pdo->prepare("
                    DELETE FROM agents
                    WHERE user_id = ?
                ");
                $deleteAgent->execute([$user_id]);

                // Create client profile if it does not exist
                $checkClient = $pdo->prepare("
                    SELECT client_id
                    FROM clients
                    WHERE user_id = ?
                ");
                $checkClient->execute([$user_id]);

                if (!$checkClient->fetch(PDO::FETCH_ASSOC)) {
                    $insertClient = $pdo->prepare("
                        INSERT INTO clients
                        (user_id, address)
                        VALUES (?, ?)
                    ");

                    $insertClient->execute([
                        $user_id,
                        null
                    ]);
                }
            }

            if ($role === 'admin') {
                // Admins do not need an agent profile unless they are also acting as agents.
                // To keep it clean, remove agent profile when promoted to admin.
                $deleteAgent = $pdo->prepare("
                    DELETE FROM agents
                    WHERE user_id = ?
                ");
                $deleteAgent->execute([$user_id]);
            }

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Could not update user role: " . $e->getMessage());
        }
    }
}

redirect("admin/users.php");
?>