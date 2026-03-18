<?php

namespace Psr\Http\Message;

interface UriInterface {
	public function getScheme(): string;

	public function getAuthority(): string;

	public function getUserInfo(): string;

	public function getHost(): string;

	public function getPort(): ?int;

	public function getPath(): string;

	public function getQuery(): string;

	public function getFragment(): string;

	public function withScheme(string $scheme);

	public function withUserInfo(string $user, ?string $password = null);

	public function withHost(string $host);

	public function withPort(?int $port);

	public function withPath(string $path);

	public function withQuery(string $query);

	public function withFragment(string $fragment);

	public function __toString(): string;
}
