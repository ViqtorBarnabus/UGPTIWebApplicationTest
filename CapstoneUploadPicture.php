<?php
// Uploads the file to the specific location for this test site
$upload_dir = "CapstoneImages/";
$img = $_POST['hidden_data'];
$img = str_replace('data:image/png;base64,', '', $img);
$img = str_replace(' ', '+', $img);
$data = base64_decode($img);
$file = $upload_dir . $_POST['hidden_name'] . ".png";
$success = file_put_contents($file, $data);
print $success ? $file : 'Unable to save the file.';
?>