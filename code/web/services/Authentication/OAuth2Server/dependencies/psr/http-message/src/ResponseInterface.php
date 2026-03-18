<?php

namespace Psr\Http\Message;

interface ResponseInterface extends MessageInterface {
	public function getStatusCode(): int;

	public function withStatus(int $code, string $reasonPhrase = '');

	public function getReasonPhrase(): string;
}
