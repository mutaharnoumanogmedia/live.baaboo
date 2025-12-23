<?php

namespace App\Data;

class NotificationPayload
{
    public function __construct(
        public string $title,
        public string $message,
        public array $data = []
    ) {}
}
