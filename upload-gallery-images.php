<?php
// upload-gallery-images.php
// Script to create uploads directory and default images

// Create uploads directory structure
$baseDir = __DIR__;
$uploadsDir = $baseDir . '/uploads';
$galleryDir = $uploadsDir . '/gallery';

if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
    echo "Created uploads directory: $uploadsDir<br>";
}

if (!file_exists($galleryDir)) {
    mkdir($galleryDir, 0777, true);
    echo "Created gallery directory: $galleryDir<br>";
}

// Copy default images to uploads directory
$defaultImages = [
    'worship-service.jpg',
    'youth-camp.jpg', 
    'bishop-teaching.jpg',
    'outreach.jpg',
    'womens-ministry.jpg',
    'baptism.jpg',
    'choir.jpg',
    'mens-prayer.jpg',
    'children-ministry.jpg',
    'church-building.jpg'
];

$sourceDir = $baseDir . '/assets/images/gallery';
$uploadedCount = 0;

foreach ($defaultImages as $image) {
    $sourceFile = $sourceDir . '/' . $image;
    $destFile = $galleryDir . '/' . time() . '_' . $image;
    
    if (file_exists($sourceFile)) {
        if (copy($sourceFile, $destFile)) {
            echo "Copied: $image to gallery directory<br>";
            $uploadedCount++;
        } else {
            echo "Failed to copy: $image<br>";
        }
    } else {
        echo "Source file not found: $sourceFile<br>";
        // Create a placeholder file
        $placeholderText = "Placeholder for: $image\nUploaded: " . date('Y-m-d H:i:s');
        file_put_contents($destFile, $placeholderText);
        echo "Created placeholder for: $image<br>";
        $uploadedCount++;
    }
}

echo "<br>Total images processed: $uploadedCount<br>";
echo "Gallery uploads directory ready!";

// Set proper permissions
chmod($uploadsDir, 0755);
chmod($galleryDir, 0755);