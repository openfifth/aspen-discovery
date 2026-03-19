<?php

declare(strict_types=1);

namespace League\OAuth2\Server\EventEmitting;

use League\Event\EventDispatcher;
use League\Event\ListenerPriority;

final class EventEmitter extends EventDispatcher
{
    public function addListener(string $eventClass, callable $listener): void
    {
        $this->subscribeTo($eventClass, $listener);
    }
    
    // Legacy method for backward compatibility
    public function addListenerWithPriority(string $event, callable $listener, int $priority = ListenerPriority::NORMAL): self
    {
        $this->subscribeTo($event, $listener, $priority);

        return $this;
    }

    public function emit(object $event): object
    {
        return $this->dispatch($event);
    }
}
