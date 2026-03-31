<?php

namespace Psr\EventDispatcher;

interface EventDispatcherInterface
{
    public function dispatch(object $event);
}

interface ListenerProviderInterface
{
    public function getListenersForEvent(object $event): iterable;
}

interface StoppableEventInterface
{
    public function isPropagationStopped(): bool;
}
