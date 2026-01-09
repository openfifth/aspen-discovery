<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap_aspen.php';

require_once ROOT_DIR . '/services/API/SystemAPI.php';
require_once ROOT_DIR . '/sys/Administration/BackgroundProcess.php';
require_once ROOT_DIR . '/sys/SystemVariables.php';
require_once ROOT_DIR . '/sys/DBMaintenance/hoopla_version2_updates.php';

set_time_limit(0);

$backgroundProcess = null;
if ($argc > 2) {
	$backgroundProcessId = $argv[2];
	$backgroundProcess = new BackgroundProcess();
	$backgroundProcess->id = $backgroundProcessId;
	if (!$backgroundProcess->find(true)) {
		$backgroundProcess = null;
		echo("Could not find the specified background process\n");
		die();
	} elseif (!$backgroundProcess->isRunning) {
		$backgroundProcess->addNote('Error, attempted to restart previously completed background process');
		die();
	}
}

$logMessage = function (string $message) use ($backgroundProcess) : void {
	if ($backgroundProcess !== null) {
		$backgroundProcess->addNote($message);
	} else {
		echo $message . PHP_EOL;
	}
};

$finish = function (string $message) use ($backgroundProcess, $logMessage) : void {
	$logMessage($message);
	if ($backgroundProcess !== null) {
		$backgroundProcess->endProcess(null);
	}
	die();
};

$logMessage('Running Hoopla Version 2 database updates.');

$systemVariables = SystemVariables::getSystemVariables();
if ($systemVariables === false || (int)$systemVariables->hooplaVersion !== 2) {
	$finish('Hoopla Version is not set to 2. No updates will run.');
}

$hooplaUpdates = getHooplaVersion2Updates();
if (count($hooplaUpdates) === 0) {
	$finish('No Hoopla Version 2 DB updates are defined.');
}

$systemAPI = new SystemAPI();
$hooplaUpdates = $systemAPI->checkWhichUpdatesHaveRun($hooplaUpdates);

$pendingUpdates = [];
foreach ($hooplaUpdates as $key => $update) {
	if (empty($update['alreadyRun'])) {
		$pendingUpdates[] = $key;
	}
}

if (count($pendingUpdates) === 0) {
	$finish('No Hoopla Version 2 database updates are needed.');
}

foreach ($pendingUpdates as $pendingUpdateKey) {
	$updateTitle = $hooplaUpdates[$pendingUpdateKey]['title'] ?? $pendingUpdateKey;
	$logMessage("Running update: {$updateTitle}");
	$result = $systemAPI->runDatabaseUpdate($hooplaUpdates, $pendingUpdateKey);
	if (!$result['success']) {
		$finish("Hoopla Version 2 update failed: {$updateTitle}. {$result['message']}");
	}
	$logMessage("Completed: {$updateTitle}");
}

$finish('Hoopla Version 2 database updates completed successfully.');
