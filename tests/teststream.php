<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
set_time_limit(86400);

$start_time = time();
echo ": Comment\n\n";
echo ": Setting timeout\n";
echo "retry: 1000\n\n";
echo ":\n\n";
$headers = getallheaders();
if(!empty($headers['Last-Event-ID'])) {
    echo ": GOT HEADER FOR Last-Event-ID: {$headers['Last-Event-ID']}\n";
    echo ": That was seconds ago: " . (time() - $headers['Last-Event-ID']) . "\n\n";
}
while(true) {
    echo "id: " . time() . "\n";
    echo "event: ping\n";
    echo "data: " . json_encode(['message' => 'Hello, world at ' . date("Y-m-d H:i:s") . '!']) . "\n\n";
    ob_flush();
    flush();
    if(time() - $start_time > 900) {
        break;
    }
    sleep(1);
}
