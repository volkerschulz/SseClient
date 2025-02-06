<?php

namespace volkerschulz\SseClient;

class Event {
    public readonly ?string $id;
    public readonly ?string $event;
    public readonly mixed $data;
    public readonly ?array $comments;
    public readonly ?int $retry;


    public function __construct(?string $id, ?string $event, mixed $data, ?array $comments, ?int $retry) {
        $this->id = $id;
        $this->event = $event;
        $this->data = $data;
        $this->comments = $comments;
        $this->retry = $retry;
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
