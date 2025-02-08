<?php

namespace volkerschulz\SseClient;

class Event {
    public readonly ?string $id;
    public readonly ?string $event;
    public readonly mixed $data;
    public readonly ?array $comments;
    public readonly ?int $retry;


    public function __construct(array $event_data) {
        $this->id = $event_data['id'] ?? null;
        $this->event = $event_data['event'] ?? null;
        $this->data = $event_data['data'] ?? null;
        $this->comments = $event_data['comments'] ?? null;
        $this->retry = $event_data['retry'] ?? null;
    }

    public function getId() : ?string {
        return $this->id;
    }

    public function getEvent() : ?string {
        return $this->event;
    }

    public function getData() : mixed {
        return $this->data;
    }

    public function getComments() : ?array {
        return $this->comments;
    }

    public function getRetry() : ?int {
        return $this->retry;
    }
    
}
