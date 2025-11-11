<?php

// DRAFT !!!

define('ROOT_DIR', str_replace('/scripts', '', __DIR__));

spl_autoload_register('aspen_autoloader', true, false);


require_once ROOT_DIR . '/sys/DB/DataObject.php';
require_once ROOT_DIR . '/services/Pay360/Client.php';
require_once ROOT_DIR . '/services/Pay360/Poller.php';
require_once ROOT_DIR . '/sys/ConfigArray.php';
require_once ROOT_DIR . '/sys/IP/IPAddress.php';
require_once ROOT_DIR . '/sys/Smarty/Autoloader.php';
require_once ROOT_DIR . '/sys/Utils/EncryptionUtils.php';
require_once ROOT_DIR . '/sys/Timer.php';
require_once ROOT_DIR . '/sys/Logger.php';

global $timer;
$timer = new Timer();

global $configArray;
$configArray = readConfig();

global $logger;
$logger = new Logger();

initDatabase();

$pay360SettingsId = intval($argv[2]) ?? null;
if (!$pay360SettingsId) {
	die("Pay360 settings ID required\n");
}

$paymentId = $argv[3] ?? null;
if (!$paymentId) {
	die("Payment ID required\n");
}

$apiClient = new Pay360_Client($pay360SettingsId, $paymentId);
$poller = new Pay360_Poller($apiClient);

$poller->poll();

// TEMPORARY - Copied from /code/web/bootstrap_aspen.php 
function aspen_autoloader($class) {
	if (substr($class, 0, 4) == 'CAS_') {
		if (CAS_autoload($class)) {
			return;
		}
	}
	// Don't get involved if we're being called for a SimpleSAML method
	if (substr($class, 0, 10) == 'SimpleSAML' || substr($class, 0, 6) == 'sspmod') {
		return;
	}
	if (strpos($class, '.php') > 0) {
		$class = substr($class, 0, strpos($class, '.php'));
	}
	$nameSpaceClass = str_replace('_', '/', $class) . '.php';
	try {
		if (strpos($class, 'Smarty_') === 0) {
			Smarty_Autoloader::autoload($class);
			return;
		} elseif (strpos($class, 'PHPUnit') === 0) {
			return;
		} elseif (file_exists(ROOT_DIR . '/sys/' . $class . '.php')) {
			$className = ROOT_DIR . '/sys/' . $class . '.php';
			require_once $className;
		} elseif (file_exists(ROOT_DIR . '/sys/Account/' . $class . '.php')) {
			$className = ROOT_DIR . '/sys/Account/' . $class . '.php';
			require_once $className;
		} elseif (file_exists(ROOT_DIR . '/Drivers/' . $class . '.php')) {
			$className = ROOT_DIR . '/Drivers/' . $class . '.php';
			require_once $className;
		} elseif (file_exists(ROOT_DIR . '/services/MyAccount/lib/' . $class . '.php')) {
			$className = ROOT_DIR . '/services/MyAccount/lib/' . $class . '.php';
			require_once $className;
		} else {
			require_once $nameSpaceClass;
		}
	} catch (Exception $e) {
		AspenError::raiseError("Error loading class $class");
	}
}

function initDatabase() {
	global $configArray;
	/** @var PDO */ global $aspen_db;

	try {
		$aspen_db = new PDO($configArray['Database']['database_dsn'], $configArray['Database']['database_user'], $configArray['Database']['database_password']);
		$aspen_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$aspen_db->exec("SET NAMES utf8mb4");
	} catch (PDOException $e) {
		global $serverName;
		echo("Server name: $serverName<br>\r\n");
		if ($configArray['System']['debug']) {
			echo("Could not connect to database {$configArray['Database']['database_dsn']}, define database connection information in config.pwd.ini<br>\r\n");
		} else {
			echo("Could not connect to database\r\n");
		}
		die();
	}
}


