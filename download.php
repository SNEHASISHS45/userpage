<?php
if (isset($_GET['file'])) {
    $file_url = urldecode($_GET['file']);
    $filename = basename($file_url);

    // Set proper headers for download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($file_url));
    
    // Download file from Cloudinary
    readfile($file_url);
    exit();
}
?>
