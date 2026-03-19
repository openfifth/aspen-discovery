<?php

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Repositories/ClientRepository.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Repositories/AccessTokenRepository.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Repositories/ScopeRepository.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Repositories/RefreshTokenRepository.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Repositories/UserRepository.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Repositories/AuthCodeRepository.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/OpenIDConnectConfig.php';

class OAuth2ServerConfig {

	private static ?AuthorizationServer $authorizationServer = null;
	private static ?ResourceServer $resourceServer = null;

	/**
	 * @throws Exception
	 */
	public static function getAuthorizationServer(): AuthorizationServer {
		if (self::$authorizationServer === null) {
			$clientRepository = new ClientRepository();
			$accessTokenRepository = new AccessTokenRepository();
			$authCodeRepository = new AuthCodeRepository();
			$refreshTokenRepository = new RefreshTokenRepository();
			$scopeRepository = new ScopeRepository();
			$userRepository = new UserRepository();

			$privateKeyPath = ROOT_DIR . '/services/Authentication/OAuth2Server/private.key';
			$encryptionKey = self::getEncryptionKey();

			self::$authorizationServer = new AuthorizationServer($clientRepository, $accessTokenRepository, $scopeRepository, $privateKeyPath, $encryptionKey);

			$authCodeGrant = new AuthCodeGrant($authCodeRepository, $refreshTokenRepository, new DateInterval('PT10M') // Authorization codes expire after 10 minutes
			);
			$authCodeGrant->setRefreshTokenTTL(new DateInterval('P1M')); // Refresh tokens expire after 1 month

			self::$authorizationServer->enableGrantType($authCodeGrant, new DateInterval('PT1H') // Access tokens expire after 1 hour
			);

			$passwordGrant = new PasswordGrant($userRepository, $refreshTokenRepository);
			$passwordGrant->setRefreshTokenTTL(new DateInterval('P1M')); // Refresh tokens expire after 1 month

			self::$authorizationServer->enableGrantType($passwordGrant, new DateInterval('PT1H') // Access tokens expire after 1 hour
			);

			$refreshTokenGrant = new RefreshTokenGrant($refreshTokenRepository);
			$refreshTokenGrant->setRefreshTokenTTL(new DateInterval('P1M')); // New refresh tokens expire after 1 month

			self::$authorizationServer->enableGrantType($refreshTokenGrant, new DateInterval('PT1H') // Access tokens expire after 1 hour
			);

			$clientCredentialsGrant = new ClientCredentialsGrant($clientRepository, $scopeRepository);
			self::$authorizationServer->enableGrantType($clientCredentialsGrant, new DateInterval('PT4H') // Client credentials tokens last longer (4 hours)
			);
		}

		return self::$authorizationServer;
	}

	public static function getResourceServer(): ResourceServer {
		if (self::$resourceServer === null) {
			$accessTokenRepository = new AccessTokenRepository();
			$publicKeyPath = ROOT_DIR . '/services/Authentication/OAuth2Server/public.key';

			self::$resourceServer = new ResourceServer($accessTokenRepository, $publicKeyPath);
		}

		return self::$resourceServer;
	}

	private static function getEncryptionKey(): string {
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
		$config = [
			"digest_alg" => "sha512",
			"private_key_bits" => 2048,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
		];

		$res = openssl_pkey_new($config);

		openssl_pkey_export($res, $privateKey);
		file_put_contents($privateKeyPath, $privateKey);
		chmod($privateKeyPath, 0600);

		$publicKeyDetails = openssl_pkey_get_details($res);
		$publicKey = $publicKeyDetails["key"];
		file_put_contents($publicKeyPath, $publicKey);
		chmod($publicKeyPath, 0644);
	}

	/**
	 *  Generate ID Token for OpenID Connect
	 */
	public static function generateIDToken(array $user, string $clientId, string $issuer): string {
		$issuedAt = time();
		$claims = OpenIDConnectConfig::buildIDTokenClaims($user, $clientId, $issuedAt, $issuer);

		return self::createJWT($claims);
	}

	/**
	 * Create and sign JWT token for OpenID Connect
	 */
	private static function createJWT(array $claims): string {
		$header = [
			'typ' => 'JWT',
			'alg' => 'RS256',
		];

		$header64 = self::base64urlEncode(json_encode($header));
		$payload64 = self::base64urlEncode(json_encode($claims));

		$privateKeyPath = ROOT_DIR . '/services/Authentication/OAuth2Server/private.key';
		$privateKey = file_get_contents($privateKeyPath);

		$signature = '';
		openssl_sign($header64 . '.' . $payload64, $signature, $privateKey, OPENSSL_ALGO_SHA256);

		$signature64 = self::base64urlEncode($signature);

		return $header64 . '.' . $payload64 . '.' . $signature64;
	}

	private static function base64urlEncode(string $str): string {
		return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
	}
}
