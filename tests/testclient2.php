<?php

use volkerschulz\SseClient;

require_once '../vendor/autoload.php';

$url = 'https://sse.dev/test';

$client = new SseClient($url);
$client->setReadTimeout(10);

foreach($client->getEvents() as $data) {
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
