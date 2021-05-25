<?php
require __DIR__ . '/vendor/autoload.php';
ignore_user_abort(true);
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
} else if (str_contains(strtolower($data['message']['text']), "#airtemperature")) {
    fetchData('aquaponic/airtemperature');
} else if (str_contains(strtolower($data['message']['text']), "#watertemperature")) {
    fetchData('aquaponic/watertemperature');
} else if (str_contains(strtolower($data['message']['text']), "#waterdistance")) {
    fetchData('aquaponic/waterdistance');
} else if (str_contains(strtolower($data['message']['text']), "#level")) {
    fetchData('aquaponic/level');
} else if (str_contains(strtolower($data['message']['text']), "#turnonpump")) {
    remoteControll('pump', 0);
} else if (str_contains(strtolower($data['message']['text']), "#turnoffpump")) {
    remoteControll('pump', 1);
} else if (str_contains(strtolower($data['message']['text']), "#turnonvalve")) {
    remoteControll('valve', 0);
} else if (str_contains(strtolower($data['message']['text']), "#turnoffvalve")) {
    remoteControll('valve', 1);
} else {
    helpFunction();
}

function helpFunction()
{
    $rep = 'Some common usage: #airtemperature, #watertemperature, #waterdistance, #level, #turnonpump, #turnoffpump, #turnonvalve, #turnoffvalve';
    sendToZaloClient($rep);
    return;
}

function remoteControll($equipment, $option)
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

    //-------------------------------publish----------------------------------------------
    $body = array(
        'topic' => 'aquaponic/' . $equipment,
        'qos' => 1,
        'payload' => base64_encode($option),
        'retain' => false,
        'dup' => false,
        'tokenId' => $cookies
    );
    $body = json_encode($body);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/publish");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_COOKIE, 'tokenId=' . $cookies);
    //gotta go fast, security later on
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $server_output = curl_exec($ch);
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

    //-------------------------------pull------------------------------------------------
        curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/pull");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);

        // fwrite($GLOBALS['fp'], $server_output);
        if (strlen($server_output) != 2) {
            sendResult($server_output);
            // return;
        }
        sleep(5);
    }
    $arg1 = "ERROR";
    sendResult($arg1);
    // return;
}

function sendResult($content)
{
    $rep = "";
    if (strcmp("ERROR", $content) == 0) {
        $rep = "The system encountered an error while fetching data! Maybe the aquaponic system is offline!";
    } else {

        $content = json_decode($content, true);
        $rep = "The " . substr($content[0]['topic'], 10) . " is " . base64_decode($content[0]['payload']) . ".";
    }

    sendToZaloClient($rep);
}

// fclose($fp);


function sendToZaloClient($payload)
{
    $sent = array(
        'recipient' => array(
            'message_id' => $GLOBALS['data']['message']['msg_id']
        ),
        'message' => array(
            'text' => $payload
        )
    );
    $sent = json_encode($sent);
    $ch = curl_init();
    // print_r($payload);
    // Attach encoded JSON string to the POST fields
    curl_setopt($ch, CURLOPT_POSTFIELDS, $sent);
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
    // Close cURL resource
    curl_close($ch);
}
