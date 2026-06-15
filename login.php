<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";
require_once "includes/functions.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin' || $user['role'] === 'agent') {
            redirect("admin/dashboard.php");
        } else {
            redirect("dashboard.php");
        }
    } else {
        $error = "Invalid email or password.";
    }
}

require_once "includes/header.php";
?>

<div class="container">
    <div class="form-card">
        <h1>Login</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button class="btn" type="submit">Login</button>
        </form>

        <br>

        <p><strong>Demo admin:</strong> admin@britsrealty.co.za / admin123</p>
        <p><strong>Demo agent:</strong> agent@britsrealty.co.za / agent123</p>
        <p><strong>Demo client:</strong> client@test.co.za / user123</p>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>