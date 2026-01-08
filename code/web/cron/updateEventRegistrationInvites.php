<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap_aspen.php';
require_once ROOT_DIR . '/sys/Email/EmailTemplate.php';
require_once ROOT_DIR . '/sys/CronLogEntry.php';
require_once ROOT_DIR . '/sys/Events/UserAspenEventInstanceWaitingList.php';

$cronLogEntry = new CronLogEntry();
$cronLogEntry->startTime = time();
$cronLogEntry->name = 'Update Event Registration Invites';
$cronLogEntry->insert();

$now = date('Y-m-d H:i:s');

$expiredCount = 0;

// Find all expired notified users
$expiredUsers = [];
$wl = new UserAspenEventInstanceWaitingList();
$wl->status = 'notified';
$wl->whereAdd("expiresAt < '$now'");
$wl->find();

while ($wl->fetch()) {
    $expiredUsers[] = [
        'id' => $wl->id,
        'eventInstanceId' => $wl->eventInstanceId,
        'position' => $wl->position,
    ];
    $expiredCount++;
}

// Group expired users by event
$expiredByEvent = [];
foreach ($expiredUsers as $user) {
    $expiredByEvent[$user['eventInstanceId']][] = $user;
}

// Process each event separately
foreach ($expiredByEvent as $eventInstanceId => $users) {

    // Sort expired users by original position ascending
    usort($users, fn($a, $b) => $a['position'] <=> $b['position']);

    // Number of expired users for this event
    $numExpired = count($users);

    // Shift all users who are below the first expired user up
    $firstExpiredPos = $users[0]['position'];
    $wlShift = new UserAspenEventInstanceWaitingList();
    $wlShift->eventInstanceId = $eventInstanceId;
    $wlShift->whereAdd("position > $firstExpiredPos");
    $wlShift->find();

    while ($wlShift->fetch()) {
        $wlShift->position -= $numExpired; // shift up by number of expired users
        $wlShift->update();
    }

    // Move expired users to bottom positions
    // Get current max position after shifting
    $wlMax = new UserAspenEventInstanceWaitingList();
    $wlMax->eventInstanceId = $eventInstanceId;
    $wlMax->selectAdd();
    $wlMax->selectAdd('MAX(position) AS maxPos');
    $wlMax->find(true);
    $maxPosition = (int)$wlMax->maxPos;

    foreach ($users as $expiredUser) {
        $wlExpired = new UserAspenEventInstanceWaitingList();
        $wlExpired->id = $expiredUser['id'];
        if ($wlExpired->find(true)) {
            $wlExpired->status = 'waiting';
            $wlExpired->canRegister = 0;
            $maxPosition++;
            $wlExpired->position = $maxPosition;
            $wlExpired->update();
        }
    }
}

$cronLogEntry->notes = "Updated $expiredCount waiting list entires";
$cronLogEntry->endTime = time();
$cronLogEntry->update();
