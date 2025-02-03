<?php

use volkerschulz\SseClient;

require_once '../vendor/autoload.php';

$url = 'https://chat.me-local.de/teststream.php';

$client = new SseClient($url);
foreach($client->getEvents() as $data) {
    echo 'Event received: ' . PHP_EOL;
    var_dump($data);
    echo '-----------------' . PHP_EOL;
}
