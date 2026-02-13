<?php
/**
 * Dynamically merges the base OpenAPI spec with all per-API spec files.
 * Returns a single combined OpenAPI specification.
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache');

$baseSpecFile = __DIR__ . '/aspen_openapi.json';
$baseSpec = json_decode(file_get_contents($baseSpecFile), true);

if (json_last_error() !== JSON_ERROR_NONE) {
	http_response_code(500);
	echo json_encode(['error' => 'Failed to parse base spec']);
	exit;
}

// Find all per-API spec files (excluding the base spec)
$specFiles = glob(__DIR__ . '/*_openapi.json');

foreach ($specFiles as $specFile) {
	if (basename($specFile) === 'aspen_openapi.json') {
		continue;
	}
	
	$apiSpec = json_decode(file_get_contents($specFile), true);
	if (json_last_error() !== JSON_ERROR_NONE) {
		continue;
	}
	
	// Merge tags
	if (!empty($apiSpec['tags'])) {
		foreach ($apiSpec['tags'] as $tag) {
			$tagExists = false;
			foreach ($baseSpec['tags'] ?? [] as $existingTag) {
				if ($existingTag['name'] === $tag['name']) {
					$tagExists = true;
					break;
				}
			}
			if (!$tagExists) {
				$baseSpec['tags'][] = $tag;
			}
		}
	}
	
	// Merge paths
	if (!empty($apiSpec['paths'])) {
		foreach ($apiSpec['paths'] as $path => $pathDef) {
			if (!isset($baseSpec['paths'][$path])) {
				$baseSpec['paths'][$path] = $pathDef;
			}
		}
	}
	
	// Merge component schemas
	if (!empty($apiSpec['components']['schemas'])) {
		if (!isset($baseSpec['components']['schemas'])) {
			$baseSpec['components']['schemas'] = [];
		}
		foreach ($apiSpec['components']['schemas'] as $name => $schema) {
			if (!isset($baseSpec['components']['schemas'][$name])) {
				$baseSpec['components']['schemas'][$name] = $schema;
			}
		}
	}
	
	// Merge component responses
	if (!empty($apiSpec['components']['responses'])) {
		if (!isset($baseSpec['components']['responses'])) {
			$baseSpec['components']['responses'] = [];
		}
		foreach ($apiSpec['components']['responses'] as $name => $response) {
			if (!isset($baseSpec['components']['responses'][$name])) {
				$baseSpec['components']['responses'][$name] = $response;
			}
		}
	}
}

echo json_encode($baseSpec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
