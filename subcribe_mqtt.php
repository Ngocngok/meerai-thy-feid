<?php
require __DIR__ . '/vendor/autoload.php';
$fp = fopen('data.txt', 'a'); //opens file in append mode  




$data = file_get_contents("php://input");
fwrite($fp, $data . '');
$data = json_decode($data, true);




if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}
// handlle message
if (str_contains(strtolower($data['message']['text']), "#humidity")) {
    fetchData('aquaponic/humidity');
}


function fetchData($topic)
{
    $ch = curl_init();
    //-------------------------------login----------------------------------------------
    $body = array('clientId' => 'ANormalUser', 'userName' => 'anormaluser', 'password' => 'anormaluser', 'cleanSession' => true);
    $body = json_encode($body);
    curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/login");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    // Receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //gotta go fast, security later on
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $server_output = curl_exec($ch);
    //cookies for myqtthub api
    $cookies = json_decode($server_output, true);
    $cookies = $cookies['tokenId'];

    //-------------------------------subscribe----------------------------------------------
    $body = array(
        'subscriptions' => array(
            array($topic, 0)
        ),
        'tokenId' => $cookies
    );
    $body = json_encode($body);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/subscribe");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_COOKIE, 'tokenId=' . $cookies);
    //gotta go fast, security later on
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $server_output = curl_exec($ch);

    for ($i = 0; $i < 6; $i++) {
        curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/pull");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        fwrite($GLOBALS['fp'], $server_output);
        if (strlen($server_output) != 2) {
            sendResult($server_output);
            return;
        }
        sleep(5);
    }
    sendResult("ERROR");
    return;
}

function sendResult($content)
{
    $rep = "";
    if ($content == "ERROR") {
        $rep = "The system encountered an error while fetching data!";
    } else {

        $content = json_decode($content, true);
        $rep = "The " . substr($content[0]['topic'], 10) . " is " . base64_decode($content[0]['payload']) . ".";
    }

    // Create a new cURL resource
    $ch = curl_init();
    // Setup request to send json via POST
    $sent = array(
        'recipient' => array(
            'message_id' => $GLOBALS['data']['message']['msg_id']
        ),
        'message' => array(
            'text' => $rep
        )
    );
    $payload = json_encode($sent);

    fwrite($GLOBALS['fp'], $payload);
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
    fwrite($GLOBALS['fp'], $result);
    // Close cURL resource
    curl_close($ch);
}

return;



// curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/get-subscriptions");
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
// curl_setopt($ch, CURLOPT_COOKIE, 'tokenId=' . $cookies);
// // curl_setopt($ch, CURLOPT_COOKIESESSION, true);
// //gotta go fast, security later on
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// $server_output = curl_exec($ch);
// var_dump($server_output);





// //need to encode payload brfore sent
// $body = array('topic' => 'aquaponic/pump', 'qos' => 0, 'payload' => base64_encode($i), 'retain' => false, 'dup' => false, 'tokenId' => $cookies);
// $body = json_encode($body);
// // $body = base64_encode($body);

// print $body;
// curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/publish");
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
// curl_setopt($ch, CURLOPT_COOKIE, 'tokenId=' . $cookies);
// // curl_setopt($ch, CURLOPT_COOKIESESSION, true);
// //gotta go fast, security later on
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// $server_output = curl_exec($ch);


//need to encode payload brfore sent
// $body = array('topic' => 'aquaponic/airtemperature', 'qos' => 1, 'payload' => base64_encode('99'), 'retain' => true, 'dup' => false, 'tokenId' => $cookies);
// $body = json_encode($body);

// print $body;
// curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/publish");
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
// curl_setopt($ch, CURLOPT_COOKIE, 'tokenId=' . $cookies);
// //gotta go fast, security later on
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// $server_output = curl_exec($ch);

// print $server_output;



// $body = array('tokenId' => $cookies);
// $body = json_encode($body);

// print $body;
// curl_setopt($ch, CURLOPT_URL,"https://node02.myqtthub.com/pull");
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
// curl_setopt($ch, CURLOPT_COOKIE, 'tokenId=' . $cookies);
// // curl_setopt($ch, CURLOPT_COOKIESESSION, true);
// //gotta go fast, security later on
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// $server_output = curl_exec($ch);
// var_dump($server_output);



// curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/logout");
// $server_output = curl_exec($ch);
// var_dump($server_output);


fwrite($fp, time() . "---------------------------------------------------------------\n");
fclose($fp);

curl_close($ch);
