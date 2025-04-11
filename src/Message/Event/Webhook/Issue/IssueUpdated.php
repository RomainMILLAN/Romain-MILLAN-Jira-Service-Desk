<?php

namespace App\Message\Event\Webhook\Issue;

use Symfony\Component\RemoteEvent\RemoteEvent;

class IssueUpdated extends RemoteEvent
{
    public function __construct(
        array $payload,
        string $id = '',
        string $name = 'issue-updated',
    ) {
        parent::__construct($name, uniqid($id), $payload);
    }
}
