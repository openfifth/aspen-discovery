<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Simple PSR-7 ServerRequest implementation for OAuth2 authorization flow
 */
class SimpleServerRequest implements ServerRequestInterface {
	private $method;
	private $uri;
	private $headers;
	private $body;
	private $serverParams;
	private $queryParams;
	private $parsedBody;
	private $attributes = [];
	private $protocolVersion = '1.1';

	public function __construct() {
		$this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		$this->serverParams = $_SERVER;
		$this->queryParams = $_GET;
		
		// Normalize headers to lowercase keys for case-insensitive lookup
		$rawHeaders = getallheaders() ?: [];
		$this->headers = [];
		foreach ($rawHeaders as $key => $value) {
			$this->headers[strtolower($key)] = $value;
		}
		
		$this->uri = $_SERVER['REQUEST_URI'] ?? '/';
		
		// Read the body once and cache it
		$this->body = file_get_contents('php://input');
		
		// Handle parsed body for POST requests
		if ($this->method === 'POST') {
			// First priority: use $_POST if it's already populated by PHP
			if (!empty($_POST)) {
				$this->parsedBody = $_POST;
				// Trim whitespace from all POST values
				foreach ($this->parsedBody as $key => $value) {
					if (is_string($value)) {
						$this->parsedBody[$key] = trim($value);
					}
				}
			} elseif (!empty($this->body)) {
				// Parse from the raw body
				$contentType = $this->getHeaderLine('content-type');
				if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
					parse_str($this->body, $this->parsedBody);
					// Trim whitespace from all parsed values
					foreach ($this->parsedBody as $key => $value) {
						if (is_string($value)) {
							$this->parsedBody[$key] = trim($value);
						}
					}
				} elseif (strpos($contentType, 'application/json') !== false) {
					$this->parsedBody = json_decode($this->body, true);
				} else {
					// Default: try to parse as form data
					parse_str($this->body, $this->parsedBody);
					// Trim whitespace from all parsed values
					foreach ($this->parsedBody as $key => $value) {
						if (is_string($value)) {
							$this->parsedBody[$key] = trim($value);
						}
					}
				}
			} else {
				$this->parsedBody = null;
			}
		} else {
			$this->parsedBody = null;
		}
	}

	// Request specific methods
	public function getRequestTarget(): string {
		return $_SERVER['REQUEST_URI'] ?? '/';
	}

	public function withRequestTarget($requestTarget): self {
		return $this;
	}

	public function getMethod(): string {
		return $this->method;
	}

	public function withMethod($method): self {
		return $this;
	}

	public function getUri(): string {
		return $this->uri;
	}

	public function withUri(UriInterface $uri, $preserveHost = false): self {
		return $this;
	}

	// Message methods
	public function getProtocolVersion(): string {
		return $this->protocolVersion;
	}

	public function withProtocolVersion($version): self {
		return $this;
	}

	public function getHeaders(): array {
		return $this->headers;
	}

	public function hasHeader($name): bool {
		$lowerName = strtolower($name);
		return array_key_exists($lowerName, $this->headers);
	}

	public function getHeader($name): array {
		$lowerName = strtolower($name);
		return isset($this->headers[$lowerName]) ? (array)$this->headers[$lowerName] : [];
	}

	public function getHeaderLine($name): string {
		return implode(',', $this->getHeader($name));
	}

	public function withHeader($name, $value): self {
		return $this;
	}

	public function withAddedHeader($name, $value): self {
		return $this;
	}

	public function withoutHeader($name): self {
		return $this;
	}

	public function getBody(): string {
		return $this->body;
	}

	public function withBody(\Psr\Http\Message\StreamInterface $body): self {
		return $this;
	}

	// ServerRequest specific methods
	public function getServerParams(): array {
		return $this->serverParams;
	}

	public function getCookieParams(): array {
		return $_COOKIE;
	}

	public function withCookieParams(array $cookies): self {
		return $this;
	}

	public function getQueryParams(): array {
		return $this->queryParams;
	}

	public function withQueryParams(array $query): self {
		return $this;
	}

	public function getUploadedFiles(): array {
		return [];
	}

	public function withUploadedFiles(array $uploadedFiles): self {
		return $this;
	}

	public function getParsedBody() {
		return $this->parsedBody;
	}

	public function withParsedBody($data): self {
		return $this;
	}

	public function getAttributes(): array {
		return $this->attributes;
	}

	public function getAttribute($name, $default = null) {
		return $this->attributes[$name] ?? $default;
	}

	public function withAttribute($name, $value): self {
		$new = clone $this;
		$new->attributes[$name] = $value;
		return $new;
	}

	public function withoutAttribute($name): self {
		$new = clone $this;
		unset($new->attributes[$name]);
		return $new;
	}
}
