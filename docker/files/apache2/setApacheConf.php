<?php

require_once __DIR__ . '/../scripts/DockerLogger.php';
DockerLogger::init('APACHE');

if (count($argv) < 2) {
	DockerLogger::error("Apache configuration file required as argument");
} elseif (count($argv) > 2) {
	DockerLogger::error("Too many arguments");
}

$apacheConfFile = $argv[1];

if (is_dir($apacheConfFile)) {
	DockerLogger::error("Path is a directory, expected file: {$apacheConfFile}");
}

if (!file_exists($apacheConfFile)) {
	DockerLogger::error("Apache configuration file not found: {$apacheConfFile}");
}

DockerLogger::info("Configuring Apache with: {$apacheConfFile}");

try {
	//Copy the httpd conf file
	$apacheDir = "/etc/apache2";
	$dockerDir = "/usr/local/aspen-discovery/docker";
	$fileName = basename($apacheConfFile);
	
	if (!copy($apacheConfFile, "$apacheDir/sites-enabled/$fileName")) {
		DockerLogger::error("Failed to copy Apache configuration");
	}
	
	DockerLogger::info("Apache configuration copied successfully");

	// Copy data-alias.conf to allow apache accessing files that are not in the served path
	if (file_exists("$dockerDir/files/apache2/data-alias.conf")) {
		if (!copy("$dockerDir/files/apache2/data-alias.conf", "$apacheDir/conf-enabled/data-alias.conf")) {
			DockerLogger::warn("Failed to copy data-alias configuration");
		} else {
			DockerLogger::info("Data alias configuration copied");
		}
	}

	// Copy PHP-FPM configuration if it exists
	$phpFpmSource = "$dockerDir/files/php_fpm/php-fpm.conf";
	if (file_exists($phpFpmSource)) {
		$phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
		$phpFpmTarget = "/etc/php/$phpVersion/fpm/pool.d/www.conf";
		
		// Read template and replace variables
		$phpFpmContent = file_get_contents($phpFpmSource);
		$phpFpmPort = getenv('PHP_FPM_PORT') ?: '9000';
		$phpFpmContent = str_replace('{phpFpmPort}', $phpFpmPort, $phpFpmContent);
		
		if (file_put_contents($phpFpmTarget, $phpFpmContent) === false) {
			DockerLogger::warn("Failed to write PHP-FPM configuration");
		} else {
			DockerLogger::info("PHP-FPM configuration updated");
		}
	}

	// Validate Apache configuration
	$output = [];
	$returnCode = 0;
	exec("apache2 -t 2>&1", $output, $returnCode);
	
	if ($returnCode !== 0) {
		DockerLogger::warn("Apache configuration validation failed:");
		foreach ($output as $line) {
			DockerLogger::warn($line);
		}
		DockerLogger::error("Apache configuration is invalid");
	}
	
	DockerLogger::info("Apache configuration setup completed successfully");

} catch (Exception $e) {
	DockerLogger::error("Apache configuration failed: " . $e->getMessage());
}
?>
