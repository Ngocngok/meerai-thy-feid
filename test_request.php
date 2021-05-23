<?php
require __DIR__ . '/vendor/autoload.php';

$data = json_decode(file_get_contents('php://input'), true);

// API URL
$url = 'https://openapi.zalo.me/v2.0/oa/message?access_token=gigFH8RvGpgjpO5EZ_DFEwp2X4cFwqXMrwNyLlZDQo6LcTW_tjjzJQssiqVGq0Hog9EiGeV753BIXRitYiq7EQMVk23O_1WMZCdI2UxZHYwIb_WPyAHDCjdjvokpgqitsSxY19A4DNUujlO7xDTwTAsZmLtbyMbHjxpSLTt3T7wDgxvwe-WtTzAPZclypI1ej8k9VEAy4al8YeT5cFiJJD6EsMIbm1fHuP2CEf3aHox3jlWIxkPsPuokraBv_4TyrBVn3QVXCZhGgwKmgiyr0EE6WH2erWe1Gni7-aQ2uqOo';

// Create a new cURL resource
$ch = curl_init($url);

// Setup request to send json via POST
$data = array(
    'recipient' => array(
        'message_id' => $data['sender']['id']
    ),
    'message' => array(
        'text' => "Hi there!"
        )
);
$payload = json_encode($data);

// Attach encoded JSON string to the POST fields
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

// Set the content type to application/json
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

// Return response instead of outputting
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


// Execute the POST request
$result = curl_exec($ch);
print $result;
$result = curl_exec($ch);
print $result;
// Close cURL resource
curl_close($ch);
?>