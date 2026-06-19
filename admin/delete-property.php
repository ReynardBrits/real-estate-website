<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";
require_once "../includes/functions.php";

if (!isAgentOrAdmin()) {
    redirect("login.php");
}

if (!empty($_GET['id'])) {
    $property_id = $_GET['id'];

    try {
        $pdo->beginTransaction();

        // Get all image paths for this property before deleting the property
        $imageStmt = $pdo->prepare("
            SELECT image_url
            FROM property_images
            WHERE property_id = ?
        ");
        $imageStmt->execute([$property_id]);
        $images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);

        // Delete the actual image files from the project folder
        foreach ($images as $image) {
            $imagePath = $image['image_url'];

            if (!empty($imagePath) && !filter_var($imagePath, FILTER_VALIDATE_URL)) {
                $fullPath = "../" . $imagePath;

                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        }

        // Delete the property from the database
        $deleteStmt = $pdo->prepare("
            DELETE FROM properties
            WHERE property_id = ?
        ");
        $deleteStmt->execute([$property_id]);

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Could not delete property: " . $e->getMessage());
    }
}

redirect("admin/properties.php");
?>