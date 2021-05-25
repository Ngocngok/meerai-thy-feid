<?php
require __DIR__ . '/vendor/autoload.php';

fetchData('aquaponic/humidity');

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
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
            print($server_output);
            sendResult($server_output);
            // return;
        }
        sleep(1);
    }
    print $server_output;
    $arg1 = "ERROR";
    // return;
}
?>