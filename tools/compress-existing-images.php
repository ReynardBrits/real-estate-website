<?php
// Developer utility only.
// This script is used locally to resize and compress existing property images.
// Do not upload this file to the live hosting server.
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!extension_loaded('gd')) {
    die("GD extension is not enabled.");
}

$folder = __DIR__ . "/assets/images/properties/";
$maxWidth = 900;      // stronger resize for website speed
$jpegQuality = 65;   // stronger compression

$allowedExtensions = ['jpg', 'jpeg'];

$files = scandir($folder);

echo "<h1>Image Compression Results</h1>";
echo "<p>Folder: {$folder}</p>";

foreach ($files as $file) {
    $path = $folder . $file;

    if (!is_file($path)) {
        continue;
    }

    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
        continue;
    }

    clearstatcache(true, $path);
    $oldSize = filesize($path);

    $imageInfo = getimagesize($path);

    if ($imageInfo === false) {
        echo "<p>Skipped {$file}: not a valid image.</p>";
        continue;
    }

    $originalWidth = $imageInfo[0];
    $originalHeight = $imageInfo[1];

    $sourceImage = imagecreatefromjpeg($path);

    if (!$sourceImage) {
        echo "<p>Skipped {$file}: could not open image.</p>";
        continue;
    }

    if ($originalWidth > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = intval(($originalHeight / $originalWidth) * $newWidth);
    } else {
        $newWidth = $originalWidth;
        $newHeight = $originalHeight;
    }

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    imagecopyresampled(
        $newImage,
        $sourceImage,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $originalWidth,
        $originalHeight
    );

    imageinterlace($newImage, true);

    $tempPath = $path . ".tmp.jpg";

    $saved = imagejpeg($newImage, $tempPath, $jpegQuality);

    imagedestroy($sourceImage);
    imagedestroy($newImage);

    if (!$saved || !file_exists($tempPath)) {
        echo "<p>Failed to save {$file}</p>";
        continue;
    }

    clearstatcache(true, $tempPath);
    $tempSize = filesize($tempPath);

    // Only replace if the compressed version is actually smaller
    if ($tempSize < $oldSize) {
        unlink($path);
        rename($tempPath, $path);

        clearstatcache(true, $path);
        $newSize = filesize($path);

        echo "<p>
            <strong>{$file}</strong>: 
            {$originalWidth}x{$originalHeight} → {$newWidth}x{$newHeight}<br>
            " . round($oldSize / 1024, 2) . " KB → " . round($newSize / 1024, 2) . " KB
        </p>";
    } else {
        unlink($tempPath);

        echo "<p>
            <strong>{$file}</strong>: skipped because compressed version was not smaller.<br>
            Current size: " . round($oldSize / 1024, 2) . " KB
        </p>";
    }
}

echo "<h2>Done. Delete this file after testing.</h2>";