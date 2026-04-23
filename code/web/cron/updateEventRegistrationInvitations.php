<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap_aspen.php';
require_once ROOT_DIR . '/sys/CronLogEntry.php';
require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceRegistration.php';
require_once ROOT_DIR . '/sys/Events/EventInstance.php';
require_once ROOT_DIR . '/services/EventRegistrationService.php';

$cronLogEntry = new CronLogEntry();
$cronLogEntry->startTime = time();
$cronLogEntry->name = 'Update Event Registration Invites';
$cronLogEntry->insert();

$expiredCount = 0;
$invitedCount = 0;

$expiredRowIds = UserAspenEventInstanceRegistration::getExpiredInvitedRowIds();

$affectedInstanceIds = [];

foreach ($expiredRowIds as $rowId) {
	$row = new UserAspenEventInstanceRegistration();
	$row->id = $rowId;
	if (!$row->find(true)) {
		continue;
	}
	$affectedInstanceIds[$row->eventInstanceId] = true;
	$row->delete();
	$expiredCount++;
}

foreach (array_keys($affectedInstanceIds) as $eventInstanceId) {
	$eventInstance = new EventInstance();
	$eventInstance->id = $eventInstanceId;
	if (!$eventInstance->find(true)) {
		continue;
	}
	if (EventRegistrationService::inviteNextOnWaitingList($eventInstance)) {
		$invitedCount++;
	}
}

$cronLogEntry->notes = "Expired: $expiredCount, Invited: $invitedCount";
$cronLogEntry->endTime = time();
$cronLogEntry->update();
