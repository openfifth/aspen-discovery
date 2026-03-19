<?php

namespace League\Event;

interface HasEventName
{
    public function eventName(): string;
}
