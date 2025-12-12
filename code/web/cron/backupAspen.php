<?php
require_once __DIR__ . '/../bootstrap.php';

set_time_limit(0);
ini_set('memory_limit', '2G');
global $configArray;
global $serverName;

global $aspen_db;

require_once ROOT_DIR . '/sys/CronLogEntry.php';
$cronLogEntry = new CronLogEntry();
$cronLogEntry->startTime = time();
$cronLogEntry->name = 'Backup Aspen';
$cronLogEntry->insert();

$debug = false;

$dbUser = $configArray['Database']['database_user'];
$dbPassword = $configArray['Database']['database_password'];
$dbName = $configArray['Database']['database_aspen_dbname'];
$dbHost = $configArray['Database']['database_aspen_host'];
$dbPort = $configArray['Database']['database_aspen_dbport'];

//Make sure our backup directory exists
$backupDir = "/data/aspen-discovery/$serverName/sql_backup";
if (!file_exists($backupDir)) {
	mkdir($backupDir, 700, true);
}

//Remove any backups older than 2 days
$cronLogEntry->notes .= date('g:i:s A') . " Removing old backups.<br/>";
$currentFilesInBackup = scandir($backupDir);
$earliestTimeToKeep = time() - (2 * 24 * 60 * 60);
foreach ($currentFilesInBackup as $file) {
	$okToProcess = false;
	if (strlen($file) > 4) {
		$last4 = substr($file, -4);
		if ($last4 == ".sql" || $last4 == ".tar") {
			$okToProcess = true;
		}
	}
	if (!$okToProcess && strlen($file) > 7) {
		$last4 = substr($file, -7);
		if ($last4 == ".tar.gz" || $last4 == ".sql.gz") {
			$okToProcess = true;
		}
	}
	if ($okToProcess) {
		//Backup files we should delete after 3 days
		$lastModified = filemtime($backupDir . '/'. $file);
		if ($lastModified !== false && $lastModified < $earliestTimeToKeep) {
			unlink($backupDir . '/'. $file);
		}
	}
}

//Create the tar file
$curDateTime = date('ymdHis');
$todaysBackupDir = $backupDir . '/' . $curDateTime;
if (!file_exists($todaysBackupDir)) {
	mkdir($todaysBackupDir, 700, true);
}

$backupFile = "$backupDir/aspen.$serverName.$curDateTime.tar.gz";
$compressCommand = '';
$dumpScript = 'mysqldump';
if ($configArray['System']['operatingSystem'] != 'windows') {
	exec_advanced("cd $backupDir", $debug);
	$compressCommand = '| pigz';
	$dumpScript = 'mariadb-dump';
}

//Create the export files
$listTablesStmt = $aspen_db->query("SHOW TABLES");
$allTables = $listTablesStmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($allTables as $table) {
	$exportData = true;
	//Ignore
	if ($table == 'session' || $table == 'cached_values') {
		$exportData = false;
	}

	$exportFile = "$serverName.$curDateTime.$table.sql";

	$tableExtension = '.sql';
	if ($configArray['System']['operatingSystem'] != 'windows') {
		$exportFile .= '.gz';
	}
	$fullExportFilePath = "$backupDir/$exportFile";
	$cronLogEntry->notes .= date('g:i:s A') . " Exporting $table.<br/>";
	$cronLogEntry->update();
	if ($exportData) {
		$dumpCommand = "$dumpScript -u$dbUser -p$dbPassword -h$dbHost -P$dbPort --opt --hex-blob --no-autocommit --extended-insert --net-buffer-length=1M --max-allowed-packet=16M --single-transaction --quick $dbName $table $compressCommand > $todaysBackupDir/$exportFile";
	}else{
		$dumpCommand = "$dumpScript -u$dbUser -p$dbPassword -h$dbHost -P$dbPort --no-data $dbName $table $compressCommand > $todaysBackupDir/$exportFile";
	}
	exec_advanced($dumpCommand, $debug);

	//Do not add the file to the archive now, we will compress them all later
}
$listTablesStmt->closeCursor();
$cronLogEntry->notes .= date('g:i:s A') . " All tables have been exported<br/>";
$cronLogEntry->update();

//zip up the archive
$cronLogEntry->notes .= date('g:i:s A') . " Creating tarball.<br/>";
$cronLogEntry->update();
if ($configArray['System']['operatingSystem'] != 'windows') {
	exec_advanced("tar -cf - -C $todaysBackupDir . $compressCommand > $backupFile", $debug);
}else{
	exec_advanced("tar -czf $backupFile -C $todaysBackupDir .", $debug);
}
$cronLogEntry->notes .= date('g:i:s A') . " Finished creating tarball.<br/>";
$cronLogEntry->update();

//Clean up exported files
if (file_exists($todaysBackupDir)) {
	require_once ROOT_DIR . '/sys/Utils/SystemUtils.php';
	SystemUtils::recursive_rmdir($todaysBackupDir);
	$cronLogEntry->notes .= date('g:i:s A') . " Cleaned up individual backup files.<br/>";
	$cronLogEntry->update();
}

//Optionally, move the file to the Google backup bucket
// Load the system settings
require_once ROOT_DIR . '/sys/SystemVariables.php';
$systemVariables = new SystemVariables();

// See if we have a bucket to back up to
if ($systemVariables->find(true) && !empty($systemVariables->googleBucket)) {
	//Perform the backup
	$cronLogEntry->notes .= date('g:i:s A') . " Sending backup to Google bucket.<br/>";
	$bucketName = $systemVariables->googleBucket;
	exec_advanced("gsutil cp $backupFile gs://$bucketName/", $debug);
}

$cronLogEntry->notes .= date('g:i:s A') . " Finished backup.";
$cronLogEntry->endTime = time();
$cronLogEntry->update();

$aspen_db = null;
$configArray = null;
die();

/////// END OF PROCESS ///////

function exec_advanced($command, $log) : void {
	if ($log) {
		console_log($command, 'RUNNING: ');
	}
	$result = exec($command);
	if ($log) {
		console_log($result, 'RESULT: ');
	}
}
function console_log($message, $prefix = '') : void {
	$STDERR = fopen("php://stderr", "w");
	fwrite($STDERR, $prefix.$message."\n");
	fclose($STDERR);
}