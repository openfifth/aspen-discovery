<?php

require_once ROOT_DIR . '/JSON_Action.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/OpenIDConnectConfig.php';

/**
 * JSON Web Key Set (JWKS) Endpoint
 *
 * Returns public keys for verifying ID token signatures
 *
 * Usage:
 * GET /.well-known/jwks.json
 */
class Authentication_OAuth2_JWKS extends JSON_Action {

	function launch($method = null): void {
		$publicKeyPath = ROOT_DIR . '/services/Authentication/OAuth2Server/public.key';
		$jwks = OpenIDConnectConfig::getJWKS($publicKeyPath);

		header('Content-Type: application/json');
		header('Cache-Control: public, max-age=3600');  // Cache for 1 hour

		echo json_encode($jwks);
	}
}
