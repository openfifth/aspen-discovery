<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Simple PSR-7 Stream implementation
 */
class SimpleStream implements StreamInterface {
	private $body = '';

	public function __construct(string $body = '') {
		$this->body = $body;
	}

	public function __toString(): string {
		return $this->body;
	}

	public function close(): void {
		$this->body = '';
	}

	public function detach() {
		return null;
	}

	public function getSize(): ?int {
		return strlen($this->body);
	}

	public function tell(): int {
		return 0;
	}

	public function eof(): bool {
		return true;
	}

	public function isSeekable(): bool {
		return false;
	}

	public function seek($offset, $whence = SEEK_SET): void {
		// No-op for this simple implementation
	}

	public function rewind(): void {
		// No-op for this simple implementation
	}

	public function isWritable(): bool {
		return true;
	}

	public function write($string): int {
		$this->body .= $string;
		return strlen($string);
	}

	public function isReadable(): bool {
		return true;
	}

	public function read($length): string {
		return $this->body;
	}

	public function getContents(): string {
		return $this->body;
	}

	public function getMetadata($key = null) {
		return null;
	}
}

/**
 * Simple PSR-7 Response implementation for OAuth2 authorization flow
 */
class SimpleResponse implements ResponseInterface {
	private $statusCode = 200;
	private $headers = [];
	private $body;
	private $protocolVersion = '1.1';
	private $reasonPhrase = 'OK';

	private static $phrases = [
		200 => 'OK',
		201 => 'Created',
		302 => 'Found',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		500 => 'Internal Server Error',
	];

	public function __construct() {
		$this->body = new SimpleStream();
	}

	// Response specific methods
	public function getStatusCode(): int {
		return $this->statusCode;
	}

	public function withStatus($code, $reasonPhrase = ''): self {
		$new = clone $this;
		$new->statusCode = $code;
		$new->reasonPhrase = $reasonPhrase ?: (self::$phrases[$code] ?? '');
		return $new;
	}

	public function getReasonPhrase(): string {
		return $this->reasonPhrase;
	}

	// Message methods
	public function getProtocolVersion(): string {
		return $this->protocolVersion;
	}

	public function withProtocolVersion($version): self {
		$new = clone $this;
		$new->protocolVersion = $version;
		return $new;
	}

	public function getHeaders(): array {
		return $this->headers;
	}

	public function hasHeader($name): bool {
		return array_key_exists(strtolower($name), array_change_key_case($this->headers));
	}

	public function getHeader($name): array {
		$headers = array_change_key_case($this->headers);
		return $headers[strtolower($name)] ?? [];
	}

	public function getHeaderLine($name): string {
		return implode(',', $this->getHeader($name));
	}

	public function withHeader($name, $value): self {
		$new = clone $this;
		$new->headers[$name] = is_array($value) ? $value : [$value];
		return $new;
	}

	public function withAddedHeader($name, $value): self {
		$new = clone $this;
		if (!isset($new->headers[$name])) {
			$new->headers[$name] = [];
		}
		if (!is_array($new->headers[$name])) {
			$new->headers[$name] = [$new->headers[$name]];
		}
		$new->headers[$name][] = $value;
		return $new;
	}

	public function withoutHeader($name): self {
		$new = clone $this;
		unset($new->headers[$name]);
		return $new;
	}

	public function getBody(): StreamInterface {
		return $this->body;
	}

	public function withBody(StreamInterface $body): self {
		$new = clone $this;
		$new->body = $body;
		return $new;
	}

	/**
	 * Set body content directly (non-PSR-7 method for convenience)
	 */
	public function setBody(string $body): self {
		$this->body = new SimpleStream($body);
		return $this;
	}
}
