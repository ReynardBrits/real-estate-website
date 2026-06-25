<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/image-upload.php";

if (!isAgentOrAdmin()) {
    redirect("login.php");
}

if (empty($_GET['id'])) {
    redirect("admin/properties.php");
}

$property_id = $_GET['id'];
$message = "";
$error = "";

$stmt = $pdo->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->execute([$property_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    redirect("admin/properties.php");
}

$agentsStmt = $pdo->query("
    SELECT a.agent_id, u.full_name
    FROM agents a
    JOIN users u ON a.user_id = u.user_id
");
$agents = $agentsStmt->fetchAll(PDO::FETCH_ASSOC);

function getPropertyImages($pdo, $property_id) {
    $imageStmt = $pdo->prepare("
        SELECT *
        FROM property_images
        WHERE property_id = ?
        ORDER BY is_primary DESC, display_order ASC
    ");

    $imageStmt->execute([$property_id]);
    return $imageStmt->fetchAll(PDO::FETCH_ASSOC);
}

function uploadEditedPropertyImages($files, $property_id, $pdo) {
    $uploadDir = "../assets/images/properties/";
    $dbPathPrefix = "assets/images/properties/";

    $allowedExtensions = ["jpg", "jpeg", "png", "webp"];
    $maxFileSize = 8 * 1024 * 1024;

    if (!isset($files["name"]) || count($files["name"]) === 0) {
        return;
    }

    // Check if this property already has images
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM property_images 
        WHERE property_id = ?
    ");
    $countStmt->execute([$property_id]);
    $existingImageCount = (int) $countStmt->fetchColumn();

    // Get the next display order
    $orderStmt = $pdo->prepare("
        SELECT COALESCE(MAX(display_order), 0) 
        FROM property_images 
        WHERE property_id = ?
    ");
    $orderStmt->execute([$property_id]);
    $displayOrder = (int) $orderStmt->fetchColumn() + 1;

    $imageStmt = $pdo->prepare("
        INSERT INTO property_images
        (property_id, image_url, is_primary, display_order)
        VALUES (?, ?, ?, ?)
    ");

    for ($i = 0; $i < count($files["name"]); $i++) {
        if ($files["error"][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($files["error"][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        if ($files["size"][$i] > $maxFileSize) {
            continue;
        }

        $originalName = $files["name"][$i];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            continue;
        }

        $singleFile = [
            "name" => $files["name"][$i],
            "type" => $files["type"][$i],
            "tmp_name" => $files["tmp_name"][$i],
            "error" => $files["error"][$i],
            "size" => $files["size"][$i]
        ];

        $dbPath = saveCompressedPropertyImage(
            $singleFile,
            $uploadDir,
            $dbPathPrefix,
            1200,
            75
        );

        if ($dbPath !== false) {
            // Only make it primary if the property had no images before
            $isPrimary = $existingImageCount === 0 ? 1 : 0;

            $imageStmt->execute([
                $property_id,
                $dbPath,
                $isPrimary,
                $displayOrder
            ]);

            $existingImageCount++;
            $displayOrder++;
        }
    }
}

function hasNewUploadedImages($files) {
    if (!isset($files["name"]) || count($files["name"]) === 0) {
        return false;
    }

    for ($i = 0; $i < count($files["name"]); $i++) {
        if ($files["error"][$i] === UPLOAD_ERR_OK) {
            return true;
        }
    }

    return false;
}

function deleteExistingPropertyImages($property_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT image_url 
        FROM property_images 
        WHERE property_id = ?
    ");
    $stmt->execute([$property_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($images as $image) {
        $filePath = __DIR__ . "/../" . $image["image_url"];

        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    $deleteStmt = $pdo->prepare("
        DELETE FROM property_images 
        WHERE property_id = ?
    ");
    $deleteStmt->execute([$property_id]);
}

$images = getPropertyImages($pdo, $property_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $update = $pdo->prepare("
            UPDATE properties
            SET 
                title = ?,
                description = ?,
                price = ?,
                property_type = ?,
                listing_type = ?,
                location = ?,
                address = ?,
                bedrooms = ?,
                bathrooms = ?,
                garages = ?,
                floor_size = ?,
                erf_size = ?,
                status = ?,
                agent_id = ?
            WHERE property_id = ?
        ");

        $update->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['price'],
            $_POST['property_type'],
            $_POST['listing_type'],
            $_POST['location'],
            $_POST['address'],
            $_POST['bedrooms'],
            $_POST['bathrooms'],
            $_POST['garages'],
            $_POST['floor_size'] ?: null,
            $_POST['erf_size'] ?: null,
            $_POST['status'],
            $_POST['agent_id'] ?: null,
            $property_id
        ]);

         // Replace images only if new images were uploaded
        if (isset($_FILES["property_images"]) && hasNewUploadedImages($_FILES["property_images"])) {
            deleteExistingPropertyImages($property_id, $pdo);
            uploadEditedPropertyImages($_FILES["property_images"], $property_id, $pdo);
        }

        $pdo->commit();

        $message = "Property updated successfully.";

        $stmt->execute([$property_id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
        $images = getPropertyImages($pdo, $property_id);

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Something went wrong: " . $e->getMessage();
    }
}

require_once "../includes/header.php";
?>

<section class="section">
    <div class="container">
        <div class="form-card">
            <h1>Edit Property</h1>

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

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" name="title" value="<?= e($property['title']); ?>" required>
                </div>

                <div class="form-group">
                    <textarea name="description" required><?= e($property['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <input type="number" step="0.01" name="price" value="<?= e($property['price']); ?>" required>
                </div>

                <div class="form-group">
                    <select name="property_type" required>
                        <option value="House" <?= $property['property_type'] === 'House' ? 'selected' : ''; ?>>House</option>
                        <option value="Apartment" <?= $property['property_type'] === 'Apartment' ? 'selected' : ''; ?>>Apartment</option>
                        <option value="Townhouse" <?= $property['property_type'] === 'Townhouse' ? 'selected' : ''; ?>>Townhouse</option>
                    </select>
                </div>

                <div class="form-group">
                    <select name="listing_type" required>
                        <option value="For Sale" <?= $property['listing_type'] === 'For Sale' ? 'selected' : ''; ?>>For Sale</option>
                        <option value="For Rent" <?= $property['listing_type'] === 'For Rent' ? 'selected' : ''; ?>>For Rent</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="text" name="location" value="<?= e($property['location']); ?>" required>
                </div>

                <div class="form-group">
                    <input type="text" name="address" value="<?= e($property['address']); ?>">
                </div>

                <div class="form-group">
                    <input type="number" name="bedrooms" value="<?= e($property['bedrooms']); ?>" required>
                </div>

                <div class="form-group">
                    <input type="number" name="bathrooms" value="<?= e($property['bathrooms']); ?>" required>
                </div>

                <div class="form-group">
                    <input type="number" name="garages" value="<?= e($property['garages']); ?>" required>
                </div>

                <div class="form-group">
                    <input type="number" name="floor_size" value="<?= e($property['floor_size']); ?>">
                </div>

                <div class="form-group">
                    <input type="number" name="erf_size" value="<?= e($property['erf_size']); ?>">
                </div>

                <div class="form-group">
                    <select name="status" required>
                        <option value="Available" <?= $property['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                        <option value="Sold" <?= $property['status'] === 'Sold' ? 'selected' : ''; ?>>Sold</option>
                        <option value="Rented" <?= $property['status'] === 'Rented' ? 'selected' : ''; ?>>Rented</option>
                    </select>
                </div>

                <div class="form-group">
                    <select name="agent_id">
                        <option value="">No Agent</option>

                        <?php foreach ($agents as $agent): ?>
                            <option 
                                value="<?= e($agent['agent_id']); ?>" 
                                <?= $property['agent_id'] == $agent['agent_id'] ? 'selected' : ''; ?>
                            >
                                <?= e($agent['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h3>Current Images</h3>

                <?php if (count($images) > 0): ?>
                    <div class="image-gallery" style="margin-bottom: 20px;">
                        <?php foreach ($images as $image): ?>
                            <img 
                                src="<?= url($image['image_url']); ?>" 
                                alt="Property image"
                            >
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No images uploaded for this property.</p>
                    <br>
                <?php endif; ?>

                <h3>Replace Images</h3>
                <p style="margin-bottom: 10px;">
                    Leave this empty to keep the current images. Select new images only if you want to replace the existing gallery.
                </p>

                <div class="form-group">
                    <input 
                        type="file" 
                        name="property_images[]" 
                        accept=".jpg,.jpeg,.png,.webp" 
                        multiple
                    >
                </div>

                <button class="btn" type="submit">Update Property</button>

                <a class="btn btn-secondary" href="<?= url('admin/properties.php'); ?>">
                    Back
                </a>
            </form>
        </div>
    </div>
</section>

<?php require_once "../includes/footer.php"; ?>