<?php

use volkerschulz\SseClient;

require_once '../vendor/autoload.php';

$url = 'https://chat.me-local.de/teststream.php';

$client = new SseClient($url, [
    'reconnect' => false,
]);
$client->setReadTimeout(6);

foreach($client->getEvents($options) as $data) {
    if($data === null) {
        // Probably a read timeout
        $error = $client->getLastError();
        echo 'Error: ' . $error . PHP_EOL;
        echo '-----------------' . PHP_EOL;
        $client->abort();
        break;
        continue;
    }
    echo 'Event received: ' . PHP_EOL;
    var_dump($data);
    echo '-----------------' . PHP_EOL;
}
echo 'Connection closed' . PHP_EOL;
sleep(10);

$options = [
    'form_params' => [
        'testkey' => 'testvalue',
    ],
];
foreach($client->getEvents($options) as $data) {
    if($data === null) {
        // Probably a read timeout
        $error = $client->getLastError();
        echo 'Error: ' . $error . PHP_EOL;
        echo '-----------------' . PHP_EOL;
        continue;
    }
    echo 'Event received: ' . PHP_EOL;
    var_dump($data);
    echo '-----------------' . PHP_EOL;
}
echo 'Connection closed' . PHP_EOL;
$client = null;
sleep(20);
