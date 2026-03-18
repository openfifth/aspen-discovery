<?php

namespace Laminas\Diactoros;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactory {
	public static function fromGlobals(?array $server = null, ?array $query = null, ?array $body = null, ?array $cookies = null, ?array $files = null): ServerRequestInterface {
		// Use existing Laminas implementation if available
		if (class_exists('\Laminas\Diactoros\ServerRequestFactory', false)) {
			return \Laminas\Diactoros\ServerRequestFactory::fromGlobals($server, $query, $body, $cookies, $files);
		}

		// Fallback to a simple implementation
		return new ServerRequest($server ?? $_SERVER, $files ?? $_FILES, $server['REQUEST_URI'] ?? '/', $server['REQUEST_METHOD'] ?? 'GET', 'php://input', $server ?? $_SERVER);
	}
}
