<?php

namespace Psr\Clock;

interface ClockInterface {
	public function now(): \DateTimeImmutable;
}
