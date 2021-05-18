<?php
require __DIR__ . '/vendor/autoload.php';

$ch = curl_init();

$body = array('clientId' => 'ANormalUser', 'userName' => 'anormaluser', 'password' => 'anormaluser', 'cleanSession' => true);

$body = json_encode($body);
curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/login");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

// In real life you should use something like:
// curl_setopt($ch, CURLOPT_POSTFIELDS, 
//          http_build_query(array('postvar1' => 'value1')));

// Receive server response ...
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//gotta go fast, security later on
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$server_output = curl_exec($ch);
// curl_close ($ch);

$cookies = json_decode($server_output, true);
$cookies = $cookies['tokenId'];
//subcribe
$body = array(
    'subscriptions' => array(
        array('aquaponic/humidity', 1)
    ),
    'tokenId' => $cookies
);
$body = json_encode($body);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/subscribe");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_COOKIE, 'tokenId=' . $cookies);
// curl_setopt($ch, CURLOPT_COOKIESESSION, true);
//gotta go fast, security later on
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);

$server_output = curl_exec($ch);

$body = array('tokenId' => $cookies);
$body = json_encode($body);

print $body;
curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/get-subscriptions");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_COOKIE, 'tokenId=' . $cookies);
// curl_setopt($ch, CURLOPT_COOKIESESSION, true);
//gotta go fast, security later on
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);
var_dump($server_output);

for ($i = 0; $i < 2; $i++) {

    //need to encode payload brfore sent
    $body = array('topic' => 'aquaponic/humidity', 'qos' => 0, 'payload' => base64_encode('400'), 'retain' => true, 'dup' => false, 'tokenId' => $cookies);
    $body = json_encode($body);
    // $body = base64_encode($body);

    print $body;
    curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/publish");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_COOKIE, 'tokenId=' . $cookies);
    // curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    //gotta go fast, security later on
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);


    curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/pull");
    $server_output = curl_exec($ch);
    var_dump($server_output);
    sleep(1);
}

// $server_output = curl_exec($ch);
// var_dump($server_output);
// sleep(5);
// print $server_output;
// $body = array('tokenId' => $cookies);

//need to encode payload brfore sent
$body = array('topic' => 'aquaponic/humidity', 'qos' => 0, 'payload' => base64_encode('99'), 'retain' => true, 'dup' => false, 'tokenId' => $cookies);
$body = json_encode($body);
// $body = base64_encode($body);

print $body;
curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/publish");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_COOKIE, 'tokenId=' . $cookies);
// curl_setopt($ch, CURLOPT_COOKIESESSION, true);
//gotta go fast, security later on
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);

print $server_output;

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



curl_setopt($ch, CURLOPT_URL, "https://node02.myqtthub.com/logout");
$server_output = curl_exec($ch);
var_dump($server_output);
curl_close($ch);
?>