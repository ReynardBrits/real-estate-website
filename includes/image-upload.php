<?php

function saveCompressedPropertyImage($file, $uploadDir, $relativeDir, $maxWidth = 1200, $quality = 75)
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $imageInfo = getimagesize($file['tmp_name']);

    if ($imageInfo === false) {
        return false;
    }

    $mimeType = $imageInfo['mime'];

    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($file['tmp_name']);
            break;

        case 'image/png':
            $sourceImage = imagecreatefrompng($file['tmp_name']);
            break;

        case 'image/webp':
            $sourceImage = imagecreatefromwebp($file['tmp_name']);
            break;

        default:
            return false;
    }

    if (!$sourceImage) {
        return false;
    }

    $originalWidth = imagesx($sourceImage);
    $originalHeight = imagesy($sourceImage);

    if ($originalWidth > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = intval(($originalHeight / $originalWidth) * $newWidth);
    } else {
        $newWidth = $originalWidth;
        $newHeight = $originalHeight;
    }

    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

    $white = imagecolorallocate($resizedImage, 255, 255, 255);
    imagefill($resizedImage, 0, 0, $white);

    imagecopyresampled(
        $resizedImage,
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

    $newFileName = uniqid('property_', true) . '.jpg';

    $destinationPath = rtrim($uploadDir, '/') . '/' . $newFileName;
    $databasePath = rtrim($relativeDir, '/') . '/' . $newFileName;

    $saved = imagejpeg($resizedImage, $destinationPath, $quality);

    imagedestroy($sourceImage);
    imagedestroy($resizedImage);

    if (!$saved) {
        return false;
    }

    return $databasePath;
}