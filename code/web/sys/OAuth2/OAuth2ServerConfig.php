<?php

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/OAuth2/ClientRepository.php';
require_once ROOT_DIR . '/sys/OAuth2/TokenRepositories.php';
require_once ROOT_DIR . '/sys/OAuth2/ScopeUserRepositories.php';

class OAuth2ServerConfig {

	private static $authorizationServer = null;
	private static $resourceServer = null;

	public static function getAuthorizationServer(): \League\OAuth2\Server\AuthorizationServer {
		if (self::$authorizationServer === null) {
			// Initialize repositories
			$clientRepository = new ClientRepository();
			$accessTokenRepository = new AccessTokenRepository();
			$authCodeRepository = new AuthCodeRepository();
			$refreshTokenRepository = new RefreshTokenRepository();
			$scopeRepository = new ScopeRepository();
			$userRepository = new UserRepository();

			// Path to private key for signing tokens
			$privateKeyPath = ROOT_DIR . '/services/Authentication/OAuth2Server/private.key';
			$encryptionKey = self::getEncryptionKey();

			// Create authorization server
			self::$authorizationServer = new \League\OAuth2\Server\AuthorizationServer(
				$clientRepository,
				$accessTokenRepository,
				$scopeRepository,
				$privateKeyPath,
				$encryptionKey
			);

			// Enable the authorization code grant
			$authCodeGrant = new \League\OAuth2\Server\Grant\AuthCodeGrant(
				$authCodeRepository,
				$refreshTokenRepository,
				new DateInterval('PT10M') // Authorization codes expire after 10 minutes
			);
			$authCodeGrant->setRefreshTokenTTL(new DateInterval('P1M')); // Refresh tokens expire after 1 month

			self::$authorizationServer->enableGrantType(
				$authCodeGrant,
				new DateInterval('PT1H') // Access tokens expire after 1 hour
			);

			// Enable the password grant
			$passwordGrant = new \League\OAuth2\Server\Grant\PasswordGrant(
				$userRepository,
				$refreshTokenRepository
			);
			$passwordGrant->setRefreshTokenTTL(new DateInterval('P1M')); // Refresh tokens expire after 1 month

			self::$authorizationServer->enableGrantType(
				$passwordGrant,
				new DateInterval('PT1H') // Access tokens expire after 1 hour
			);

			// Enable the refresh token grant
			$refreshTokenGrant = new \League\OAuth2\Server\Grant\RefreshTokenGrant($refreshTokenRepository);
			$refreshTokenGrant->setRefreshTokenTTL(new DateInterval('P1M')); // New refresh tokens expire after 1 month

			self::$authorizationServer->enableGrantType(
				$refreshTokenGrant,
				new DateInterval('PT1H') // Access tokens expire after 1 hour
			);
		}

		return self::$authorizationServer;
	}

	public static function getResourceServer(): \League\OAuth2\Server\ResourceServer {
		if (self::$resourceServer === null) {
			$accessTokenRepository = new AccessTokenRepository();
			$publicKeyPath = ROOT_DIR . '/services/Authentication/OAuth2Server/public.key';

			self::$resourceServer = new \League\OAuth2\Server\ResourceServer(
				$accessTokenRepository,
				$publicKeyPath
			);
		}

		return self::$resourceServer;
	}

	private static function getEncryptionKey(): string {
		// Generate a secure encryption key for this installation
		// In production, this should be stored securely and consistently
		$keyFile = ROOT_DIR . '/services/Authentication/OAuth2Server/encryption.key';
		
		if (!file_exists($keyFile)) {
			$key = base64_encode(random_bytes(32));
			file_put_contents($keyFile, $key);
			chmod($keyFile, 0600);
		}
		
		return base64_decode(file_get_contents($keyFile));
	}

	public static function generateKeyPairIfNeeded(): void {
		$privateKeyPath = ROOT_DIR . '/services/Authentication/OAuth2Server/private.key';
		$publicKeyPath = ROOT_DIR . '/services/Authentication/OAuth2Server/public.key';

		if (!file_exists($privateKeyPath) || !file_exists($publicKeyPath)) {
			self::generateKeyPair($privateKeyPath, $publicKeyPath);
		}
	}

	private static function generateKeyPair(string $privateKeyPath, string $publicKeyPath): void {
		// Generate a new private/public key pair
		$config = [
			"digest_alg" => "sha512",
			"private_key_bits" => 2048,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
		];

		$res = openssl_pkey_new($config);
		
		// Extract the private key
		openssl_pkey_export($res, $privateKey);
		file_put_contents($privateKeyPath, $privateKey);
		chmod($privateKeyPath, 0600);

		// Extract the public key
		$publicKeyDetails = openssl_pkey_get_details($res);
		$publicKey = $publicKeyDetails["key"];
		file_put_contents($publicKeyPath, $publicKey);
		chmod($publicKeyPath, 0644);
	}
}
