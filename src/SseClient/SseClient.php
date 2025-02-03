<?php

namespace volkerschulz;

class SseClient {

    protected $stream;
    protected $client;
    protected string $url;
    protected array $options = [
        'headers' => [
            'Accept' => 'text/event-stream',
            'Cache-Control' => 'no-cache'
        ],
        'ignore_comments' => false, // Include comments in the event data?
        'use_last_event_id' => true, // Send Last-Event-ID header?
        'reconnect' => true, // Automatically reconnect on stream end / abort?
        'min_wait_for_reconnect' => 100, // Minimum time to wait before reconnecting
        'max_wait_for_reconnect' => 30000 // Maximum time to wait before reconnecting
    ];
    protected ?int $server_retry = null;
    protected ?string $last_event_id = null;

    const END_OF_MESSAGE = "/\r\n\r\n|\n\n|\r\r/";

    public function __construct(string $url, array $options = []) {
        $this->options = array_merge($this->options, $options);
        $this->url = $url;
        $this->client = new \GuzzleHttp\Client();
    }

    public function addHeader(string $key, string $value) {
        $this->options['headers'][$key] = $value;
    }

    public function getEvents() {
        $this->connect_guzzle();
        $buffer = '';
        while(!$this->stream->eof()) {
            $byte = $this->stream->read(1);
            $buffer .= $byte;

            if (preg_match(self::END_OF_MESSAGE, $buffer)) {
                $parts = preg_split(self::END_OF_MESSAGE, $buffer, 2);
                $buffer = $parts[1];
                $event_data = $this->parseEvent($parts[0]);
                if(!empty($event_data)) yield $event_data;
            }

            if($this->stream->eof()) {
                if(!$this->options['reconnect']) {
                    break;
                } else {
                    $this->delay_reconnect();
                    $this->connect_guzzle();
                }
            }
        }
    }

    private function delay_reconnect() {
        $delay_ms = $this->options['min_wait_for_reconnect'];
        if($this->server_retry !== null) {
            if($this->server_retry < $this->options['min_wait_for_reconnect']) {
                $delay_ms = $this->options['min_wait_for_reconnect'];
            } elseif($this->server_retry > $this->options['max_wait_for_reconnect']) {
                $delay_ms = $this->options['max_wait_for_reconnect'];
            } else {
                $delay_ms =  $this->server_retry;
            }
        }
        usleep($delay_ms * 1000);
    }

    private function connect_guzzle() {
        if($this->options['use_last_event_id'] && $this->last_event_id !== null) {
            $this->options['headers']['Last-Event-ID'] = $this->last_event_id;
        }
        $response = $this->client->request('GET', $this->url, [
            'stream' => true,
            'headers' => $this->options['headers']
        ]);
        $this->stream = $response->getBody();
    }

    private function parseEvent($bodyContents) {
        $data = [];
        $lines = explode("\n", $bodyContents);
        foreach($lines as $line) {
            $line = trim($line);
            if(empty($line)) {
                continue;
            }
            if($line[0] == ':' && $this->options['ignore_comments']) {
                continue;
            }
            $parts = explode(':', $line, 2);
            if(count($parts) == 2) {
                if(empty($parts[0])) {
                    $data['comments'][] = trim($parts[1]);
                } elseif($parts[0] == 'data') {
                    $data['data'][] = $parts[1];
                } else {
                    $data[$parts[0]] = $parts[1];
                }
                if($parts[0] == 'retry') {
                    if(is_numeric($parts[1])) {
                        $this->server_retry = (int) $parts[1];
                    }
                }
                if($parts[0] == 'id') {
                    $this->last_event_id = $parts[1];
                }
            } else {
                $data[] = $line;
            }
        }
        if(!empty($data['data']) && is_array($data['data'])) $data['data'] = implode("\n", $data['data']);
        return $data;
    }

}
