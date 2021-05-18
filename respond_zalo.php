<?php
require __DIR__ . '/vendor/autoload.php';

define('OA_SECRET_KEY', "oaZrye5aiC46utQaKC5K");
$json = file_get_contents("php://input");

$fp = fopen('data.txt', 'a');//opens file in append mode  
fwrite($fp, $json);  
fclose($fp);  

// $headers = getallheaders();
// if (0 < strlen($json) && isset($headers["X-ZEvent-Signature"])) :
//     $data = json_decode($json, false, 512, JSON_BIGINT_AS_STRING);
//     if ($data) :
//         // Calculate the MAC value from received data       
//         $mac_1 = "mac=" . hash("sha256", $data->app_id . $json . $data->timestamp . OA_SECRET_KEY);
//         $mac_2 = $headers["X-ZEvent-Signature"];
//         if (0 === strcmp($mac1, $mac2)) : // data verified
//         // TODO: Process the received data
//         // or forward to another process.
//             callZaloAPI($data);
//         else : 
//             print "ERROR!";
//         endif;
        
//     endif;
// else :
//     print "No X-ZEvent specified!";
// endif;



function callZaloAPI($data)
{
    $parsed = json_decode($data, true);


    // Create a new cURL resource
    $ch = curl_init();

    // Setup request to send json via POST
    $sent = array(
        'recipient' => array(
            'message_id' => $parsed['message']['msg_id']
            // 'user_id' => '2174132164291926302'
        ),
        'message' => array(
            'text' => "Hi there!"
        )
    );
    
    $payload = json_encode($sent);
    // print_r($payload);
    // Attach encoded JSON string to the POST fields
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    // Set URL
    curl_setopt($ch, CURLOPT_URL, 'https://openapi.zalo.me/v2.0/oa/message?access_token=gigFH8RvGpgjpO5EZ_DFEwp2X4cFwqXMrwNyLlZDQo6LcTW_tjjzJQssiqVGq0Hog9EiGeV753BIXRitYiq7EQMVk23O_1WMZCdI2UxZHYwIb_WPyAHDCjdjvokpgqitsSxY19A4DNUujlO7xDTwTAsZmLtbyMbHjxpSLTt3T7wDgxvwe-WtTzAPZclypI1ej8k9VEAy4al8YeT5cFiJJD6EsMIbm1fHuP2CEf3aHox3jlWIxkPsPuokraBv_4TyrBVn3QVXCZhGgwKmgiyr0EE6WH2erWe1Gni7-aQ2uqOo');
    
    // Fix curl with https security hole later
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

    // Set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 

    // Return response instead of outputting
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Set POST
    curl_setopt($ch, CURLOPT_POST, true);

    // Execute the POST request
    $result = curl_exec($ch);
    var_dump($result);
    // Close cURL resource
    curl_close($ch);
}

callZaloAPI($json);
?>