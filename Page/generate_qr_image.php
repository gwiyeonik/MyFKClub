<?php
// Point to your actual library path
require_once "lib/phpqrcode/qrlib.php"; 

// Ensure data is provided
$data = isset($_GET['data']) ? $_GET['data'] : 'No Data';

// CRITICAL: Tells the browser to treat this output as a PNG image
header("Content-Type: image/png");

// Output the raw PNG image
QRcode::png($data); 
?>