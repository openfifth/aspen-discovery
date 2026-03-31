<?php

namespace Psr\Http\Message;

interface RequestInterface extends MessageInterface {
	public function getRequestTarget(): string;

	public function withRequestTarget(string $requestTarget);

	public function getMethod(): string;

	public function withMethod(string $method);

	public function getUri();

	public function withUri(UriInterface $uri, bool $preserveHost = false);
}
