<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../includes/db.php";
require_once "../includes/functions.php";

if (!isAgentOrAdmin()) {
    redirect("login.php");
}

$message = "";
$error = "";

$agentsStmt = $pdo->query("
    SELECT a.agent_id, u.full_name
    FROM agents a
    JOIN users u ON a.user_id = u.user_id
");
$agents = $agentsStmt->fetchAll(PDO::FETCH_ASSOC);

function uploadPropertyImages($files, $property_id, $pdo) {
    $uploadDir = "../assets/images/properties/";
    $dbPathPrefix = "assets/images/properties/";

    $allowedExtensions = ["jpg", "jpeg", "png", "webp"];
    $maxFileSize = 5 * 1024 * 1024;

    if (!isset($files["name"]) || count($files["name"]) === 0) {
        return;
    }

    $imageStmt = $pdo->prepare("
        INSERT INTO property_images
        (property_id, image_url, is_primary, display_order)
        VALUES (?, ?, ?, ?)
    ");

    $displayOrder = 1;

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
        $tmpName = $files["tmp_name"][$i];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            continue;
        }

        $newFileName = "property_" . $property_id . "_" . uniqid() . "." . $extension;
        $targetPath = $uploadDir . $newFileName;
        $dbPath = $dbPathPrefix . $newFileName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $isPrimary = $displayOrder === 1 ? 1 : 0;

            $imageStmt->execute([
                $property_id,
                $dbPath,
                $isPrimary,
                $displayOrder
            ]);

            $displayOrder++;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $propertyStmt = $pdo->prepare("
            INSERT INTO properties
            (
                title,
                description,
                price,
                property_type,
                listing_type,
                location,
                address,
                bedrooms,
                bathrooms,
                garages,
                floor_size,
                erf_size,
                status,
                agent_id
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $propertyStmt->execute([
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
            $_POST['agent_id'] ?: null
        ]);

        $property_id = $pdo->lastInsertId();

        uploadPropertyImages($_FILES["images"], $property_id, $pdo);

        $pdo->commit();

        $message = "Property added successfully.";
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
            <h1>Add Property</h1>

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
                    <input type="text" name="title" placeholder="Property Title" required>
                </div>

                <div class="form-group">
                    <textarea name="description" placeholder="Property Description" required></textarea>
                </div>

                <div class="form-group">
                    <input type="number" step="0.01" name="price" placeholder="Price" required>
                </div>

                <div class="form-group">
                    <select name="property_type" required>
                        <option value="">Property Type</option>
                        <option value="House">House</option>
                        <option value="Apartment">Apartment</option>
                        <option value="Townhouse">Townhouse</option>
                    </select>
                </div>

                <div class="form-group">
                    <select name="listing_type" required>
                        <option value="">Listing Type</option>
                        <option value="For Sale">For Sale</option>
                        <option value="For Rent">For Rent</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="text" name="location" placeholder="Location e.g. Pretoria East" required>
                </div>

                <div class="form-group">
                    <input type="text" name="address" placeholder="Full Address">
                </div>

                <div class="form-group">
                    <input type="number" name="bedrooms" placeholder="Bedrooms" required>
                </div>

                <div class="form-group">
                    <input type="number" name="bathrooms" placeholder="Bathrooms" required>
                </div>

                <div class="form-group">
                    <input type="number" name="garages" placeholder="Garages" required>
                </div>

                <div class="form-group">
                    <input type="number" name="floor_size" placeholder="Floor Size in m²">
                </div>

                <div class="form-group">
                    <input type="number" name="erf_size" placeholder="Erf Size in m²">
                </div>

                <div class="form-group">
                    <select name="status" required>
                        <option value="Available">Available</option>
                        <option value="Sold">Sold</option>
                        <option value="Rented">Rented</option>
                    </select>
                </div>

                <div class="form-group">
                    <select name="agent_id">
                        <option value="">No Agent</option>

                        <?php foreach ($agents as $agent): ?>
                            <option value="<?= e($agent['agent_id']); ?>">
                                <?= e($agent['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <h3>Upload Property Images</h3>
                <p style="margin-bottom: 10px;">
                    You can select multiple images at once. Accepted formats: JPG, JPEG, PNG, WEBP.
                </p>

                <div class="form-group">
                    <input 
                        type="file" 
                        name="images[]" 
                        accept=".jpg,.jpeg,.png,.webp" 
                        multiple
                    >
                </div>

                <button class="btn" type="submit">Add Property</button>

                <a class="btn btn-secondary" href="<?= url('admin/properties.php'); ?>">
                    Back
                </a>
            </form>
        </div>
    </div>
</section>

<?php require_once "../includes/footer.php"; ?>
