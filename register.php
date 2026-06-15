<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";
require_once "includes/functions.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];

    if ($full_name && $email && $password) {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = "This email address is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $pdo->beginTransaction();

            $insertUser = $pdo->prepare("
                INSERT INTO users (full_name, email, password, phone, role)
                VALUES (?, ?, ?, ?, 'client')
            ");
            $insertUser->execute([$full_name, $email, $hashed_password, $phone]);

            $user_id = $pdo->lastInsertId();

            $insertClient = $pdo->prepare("
                INSERT INTO clients (user_id, address)
                VALUES (?, ?)
            ");
            $insertClient->execute([$user_id, $address]);

            $pdo->commit();

            $_SESSION['user_id'] = $user_id;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['role'] = 'client';

            redirect("properties.php");
        }
    } else {
        $error = "Please complete all required fields.";
    }
}

require_once "includes/header.php";
?>

<div class="container">
    <div class="form-card">
        <h1>Register</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="text" name="full_name" placeholder="Full Name" required>
            </div>

            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="form-group">
                <input type="text" name="phone" placeholder="Phone Number">
            </div>

            <div class="form-group">
                <input type="text" name="address" placeholder="Address">
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button class="btn" type="submit">Create Account</button>
        </form>

        <br>

        <p>Already registered? <a href="<?= url('login.php'); ?>">Login here</a>.</p>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>