<?php

namespace Psr\Http\Message;

interface ServerRequestInterface extends RequestInterface {
	public function getServerParams(): array;

	public function getCookieParams(): array;

	public function withCookieParams(array $cookies);

	public function getQueryParams(): array;

	public function withQueryParams(array $query);

	public function getUploadedFiles(): array;

	public function withUploadedFiles(array $uploadedFiles);

	public function getParsedBody();

	public function withParsedBody($data);

	public function getAttributes(): array;

	public function getAttribute(string $name, $default = null);

	public function withAttribute(string $name, $value);

	public function withoutAttribute(string $name);
}
