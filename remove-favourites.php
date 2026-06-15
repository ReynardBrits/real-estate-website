<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";
require_once "includes/functions.php";

if (!isLoggedIn()) {
    redirect("login.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['favourite_id'])) {
    $stmt = $pdo->prepare("
        DELETE FROM favourites
        WHERE favourite_id = ? AND user_id = ?
    ");

    $stmt->execute([
        $_POST['favourite_id'],
        $_SESSION['user_id']
    ]);
}

redirect("favourites.php");
?>