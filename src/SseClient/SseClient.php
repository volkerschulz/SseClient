<?php

namespace volkerschulz;

use volkerschulz\SseClient\Event;

class SseClient {

    /* Living standard: https://html.spec.whatwg.org/multipage/server-sent-events.html */

    protected $stream;
    protected $client;
    protected $response;
    protected string $url;
    protected array $options = [
        'headers' => [
            'Accept' => 'text/event-stream',
            'Cache-Control' => 'no-cache'
        ],
        'ignore_comments' => false, // Include comments in the event data?
        'use_last_event_id' => true, // Send Last-Event-ID header?
        'always_return_last_event_id' => true, // Always return the last event id?
        'reconnect' => false, // Automatically reconnect on stream end / abort?
        'min_wait_for_reconnect' => 100, // Minimum time to wait before reconnecting
        'max_wait_for_reconnect' => 30000, // Maximum time to wait before reconnecting
        'read_timeout' => 0, // Read timeout for the stream
        'associative' => true, // Return events as associative arrays
        'concatenate_data' => true, // Concatenate data lines into a single string, inserting newlines
        'line_delimiter' => "/\r\n|\n|\r/", // Delimiter for splitting lines
        'message_delimiter' => "/\r\n\r\n|\n\n|\r\r/", // Delimiter for splitting messages
        'respect_204' => true, // Respect 204 No Content status code to stop reconnecting
    ];
    protected ?int $server_retry = null;
    protected ?string $last_event_id = null;
    protected string $last_error = '';
    protected ?int $last_status_code = null;
    protected bool $abort_requested = false;

    public function __construct(string $url, array $options = []) {
        $this->options = array_merge($this->options, $options);
        $this->url = $url;
        $this->client = new \GuzzleHttp\Client();
    }

    public function addHeader(string $key, string $value) : void {
        $this->options['headers'][$key] = $value;
    }

    public function setReadTimeout(int $seconds) : void {
        $this->options['read_timeout'] = $seconds;
    }

    public function abort() : void {
        $this->abort_requested = true;
    }

    public function getEvents(array $client_options = [], ?string $client_method = null) : \Generator {
        $this->connect_guzzle($client_options, $client_method);
        $buffer = '';
        $this->abort_requested = false;
        while(!$this->stream->eof() && !$this->abort_requested) {
            try { 
                $byte = $this->stream->read(1); 
            } catch(\Exception $e) { 
                $this->last_error = $e->getMessage();
                yield null;
                continue; 
            }
            $buffer .= $byte;
            if (preg_match($this->options['message_delimiter'], $buffer)) {
                $parts = preg_split($this->options['message_delimiter'], $buffer, 2);
                $buffer = $parts[1];
                $event_data = $this->parseEvent($parts[0]);
                if(!empty($event_data)) yield $event_data;
            }

            if($this->stream->eof()) {
                if(!$this->options['reconnect'] || $this->abort_requested) {
                    break;
                } elseif($this->options['respect_204'] && $this->last_status_code === 204) {
                    break;
                } else {
                    $this->delay_reconnect();
                    $this->connect_guzzle($client_options, $client_method);
                }
            }
        }
        $this->stream->close();
    }

    public function getLastError() : string {
        return $this->last_error;
    }

    private function delay_reconnect() : void {
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

    private function connect_guzzle(array $client_options, ?string $client_method) : void {
        if($this->options['use_last_event_id'] && $this->last_event_id !== null) {
            $this->options['headers']['Last-Event-ID'] = $this->last_event_id;
        }
        
        if($client_method === null) {
            $client_method = 'GET';
            if(!empty($client_options['json']) 
                || !empty($client_options['form_params'])
                || !empty($client_options['multipart'])
                || !empty($client_options['body'])) {
                    $client_method = 'POST';
            }
        }

        $client_options['stream'] = true;

        if(empty($client_options['read_timeout']))
            $client_options['read_timeout'] = $this->options['read_timeout'];

        if(empty($client_options['headers']))
            $client_options['headers'] = $this->options['headers'];
        else
            $client_options['headers'] = array_merge($this->options['headers'], $client_options['headers']);

        $this->last_status_code = null;
        $this->response = $this->client->request($client_method, $this->url, $client_options);
        $this->last_status_code = $this->response->getStatusCode() ?? null;
        $this->stream =  $this->response->getBody();
    }

    private function parseEvent(string $event) : array | Event {
        $data = [
            'event' => 'message',
        ];
        $lines = preg_split($this->options['line_delimiter'], $event);
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
                $parts[0] = trim($parts[0]);
                $parts[1] = trim($parts[1]);
                if(empty($parts[0])) {
                    $data['comments'][] = $parts[1];
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
        if(!empty($data['data']) 
            && is_array($data['data'])
            && $this->options['concatenate_data']) {
                $data['data'] = implode("\n", $data['data']);
        } elseif(empty($data['data']) 
            && $this->options['concatenate_data']) {
                $data['data'] = '';
        }
        if(empty($data['id']) && $this->options['always_return_last_event_id']) {
            $data['id'] = $this->last_event_id;
        }
        return $this->options['associative'] ? $data : new Event($data);
    }

}
