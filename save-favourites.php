<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "includes/db.php";
require_once "includes/functions.php";

if (!isLoggedIn()) {
    redirect("login.php");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['property_id'])) {
    $property_id = $_POST['property_id'];

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO favourites (user_id, property_id)
        VALUES (?, ?)
    ");

    $stmt->execute([$_SESSION['user_id'], $property_id]);

    redirect("favourites.php");
}

redirect("properties.php");
?>