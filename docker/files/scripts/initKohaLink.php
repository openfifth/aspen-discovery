<?php

require_once __DIR__ . '/../logger/DockerLogger.php';
DockerLogger::init('BACKEND');

$enableKoha = strtolower(getenv('ENABLE_KOHA'));

if ($enableKoha !== 'yes') {
	DockerLogger::info("Koha integration not enabled");
	exit(0);
}

DockerLogger::info("Initializing Koha integration");

//Load Koha's Database
$variables = [
	'sitename' => getenv('SITE_NAME'),
	'ilsUrl' => getenv('KOHA_OPAC_URL'),
	'databaseUser' => getenv('DATABASE_USER'),
	'databasePassword' => getenv('DATABASE_PASSWORD'),
	'databaseName' => getenv('DATABASE_NAME'),
	'databaseHost' => getenv('DATABASE_HOST') ?? 'localhost',
	'databasePort' => getenv('DATABASE_PORT') ?? 3306,
	'ilsDatabaseHost' => getenv('KOHA_DATABASE_HOST'),
	'ilsDatabasePort' => getenv('KOHA_DATABASE_PORT'),
	'ilsDatabaseUser' => getenv('KOHA_DATABASE_USER'),
	'ilsDatabasePassword' => getenv('KOHA_DATABASE_PASSWORD'),
	'ilsDatabaseName' => getenv('KOHA_DATABASE_NAME'),
	'ilsDatabaseTimezone' => getenv('KOHA_DATABASE_TIMEZONE') ?? 'US/Central',
	'ilsClientId' => getenv('KOHA_CLIENT_ID'),
	'ilsClientSecret' => getenv('KOHA_CLIENT_SECRET'),
];

// Validate required Koha variables
$requiredKohaVars = ['ilsUrl', 'ilsDatabaseHost', 'ilsDatabaseUser', 'ilsDatabasePassword', 'ilsDatabaseName'];
foreach ($requiredKohaVars as $var) {
	if (empty($variables[$var])) {
		DockerLogger::error("Missing required Koha environment variable: {$var}");
	}
}

$databaseName = $variables['databaseName'];
$databasePort = $variables['databasePort'];
$databaseHost = $variables['databaseHost'];
$databaseUser = $variables['databaseUser'];
$databasePassword = $variables['databasePassword'];
$databaseDsn = "mysql:host=$databaseHost;port=$databasePort;dbname=$databaseName";

$aspenDir = '/usr/local/aspen-discovery';

// Prepare test statement
try {
	$statement = "SELECT driver FROM account_profiles WHERE driver = 'Koha';";
	$aspenDatabase = new PDO($databaseDsn, $databaseUser, $databasePassword);
	$updateUserStmt = $aspenDatabase->prepare($statement);
} catch (PDOException $e) {
	DockerLogger::error("Database connection failed: " . $e->getMessage());
}

// Check if ils tables have already been initialized
try {
	$updateUserStmt->execute();
	$result = $updateUserStmt->fetchAll();
	if (!empty($result)) {
		DockerLogger::info("Koha integration already initialized");
		exit(0);
	}
} catch (PDOException $e) {
	DockerLogger::info("Koha integration not found, initializing...");
}

try {
	// Attempt to get the system's temp directory
	$tmp_dir = rtrim(sys_get_temp_dir(), "/");
	DockerLogger::info("Loading Koha configuration to database");
	
	if (!copy("$aspenDir/install/koha_connection.sql", "$tmp_dir/koha_connection_{$variables['sitename']}.sql")) {
		DockerLogger::error("Failed to copy Koha connection template");
	}
	
	replaceVariables("$tmp_dir/koha_connection_{$variables['sitename']}.sql", $variables);
	
	$mysqlCmd = "mysql -u{$variables['databaseUser']} -p\"{$variables['databasePassword']}\" -h{$variables['databaseHost']} -P{$variables['databasePort']} {$variables['databaseName']} < $tmp_dir/koha_connection_{$variables['sitename']}.sql";
	exec($mysqlCmd, $output, $returnCode);
	
	if ($returnCode !== 0) {
		DockerLogger::error("Failed to load Koha configuration to database");
	}
	
	// Clean up temp file
	unlink("$tmp_dir/koha_connection_{$variables['sitename']}.sql");
	
} catch (Exception $e) {
	DockerLogger::error("Koha initialization failed: " . $e->getMessage());
}

DockerLogger::info("Koha integration established successfully");

function replaceVariables($filename, $variables): void {
	$contents = file($filename);
	$fHnd = fopen($filename, 'w');
	foreach ($contents as $line) {
		foreach ($variables as $name => $value) {
			$line = str_replace('{' . $name . '}', $value, $line);
		}
		fwrite($fHnd, $line);
	}
	fclose($fHnd);
}
?>
