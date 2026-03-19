<?php

/**
 * Minimal League\Uri polyfill for OAuth2 Server
 * Provides basic URI parsing functionality without requiring the full League\Uri library
 */

namespace League\Uri;

class Exceptions {
	public static function syntaxError($message = '') {
		return new SyntaxError($message);
	}
}

class SyntaxError extends \Exception {
}

class Uri {
	private $scheme = '';
	private $host = '';
	private $port = null;
	private $path = '';
	private $query = '';
	private $fragment = '';
	private $user = '';
	private $pass = '';

	private function __construct() {
	}

	/**
	 * Create a new Uri instance from a string
	 *
	 * @param string $uri
	 * @return Uri
	 * @throws SyntaxError
	 */
	public static function new(string $uri): self {
		$instance = new self();

		// Parse the URL
		$parts = @parse_url($uri);

		if ($parts === false) {
			throw new SyntaxError("Invalid URI: {$uri}");
		}

		if (isset($parts['scheme'])) {
			$instance->scheme = $parts['scheme'];
		}

		if (isset($parts['host'])) {
			$instance->host = $parts['host'];
		}

		if (isset($parts['port'])) {
			$instance->port = $parts['port'];
		}

		if (isset($parts['path'])) {
			$instance->path = $parts['path'];
		}

		if (isset($parts['query'])) {
			$instance->query = $parts['query'];
		}

		if (isset($parts['fragment'])) {
			$instance->fragment = $parts['fragment'];
		}

		if (isset($parts['user'])) {
			$instance->user = $parts['user'];
		}

		if (isset($parts['pass'])) {
			$instance->pass = $parts['pass'];
		}

		return $instance;
	}

	/**
	 * Get the scheme
	 *
	 * @return string
	 */
	public function getScheme(): string {
		return $this->scheme;
	}

	/**
	 * Get the host
	 *
	 * @return string
	 */
	public function getHost(): string {
		return $this->host;
	}

	/**
	 * Get the port
	 *
	 * @return int|null
	 */
	public function getPort(): ?int {
		return $this->port;
	}

	/**
	 * Get the path
	 *
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}

	/**
	 * Get the query
	 *
	 * @return string
	 */
	public function getQuery(): string {
		return $this->query;
	}

	/**
	 * Get the fragment
	 *
	 * @return string
	 */
	public function getFragment(): string {
		return $this->fragment;
	}

	/**
	 * Get the user info
	 *
	 * @return string
	 */
	public function getUser(): string {
		return $this->user;
	}

	/**
	 * Get the password
	 *
	 * @return string
	 */
	public function getPass(): string {
		return $this->pass;
	}

	/**
	 * Get the authority (user:pass@host:port)
	 *
	 * @return string
	 */
	public function getAuthority(): string {
		$authority = $this->host;

		if ($this->port !== null) {
			$authority .= ':' . $this->port;
		}

		if ($this->user !== '') {
			$userInfo = $this->user;
			if ($this->pass !== '') {
				$userInfo .= ':' . $this->pass;
			}
			$authority = $userInfo . '@' . $authority;
		}

		return $authority;
	}

	/**
	 * Convert back to string
	 *
	 * @return string
	 */
	public function __toString(): string {
		$uri = '';

		if ($this->scheme !== '') {
			$uri .= $this->scheme . ':';
		}

		$authority = $this->getAuthority();
		if ($authority !== '') {
			$uri .= '//' . $authority;
		}

		$uri .= $this->path;

		if ($this->query !== '') {
			$uri .= '?' . $this->query;
		}

		if ($this->fragment !== '') {
			$uri .= '#' . $this->fragment;
		}

		return $uri;
	}
}

