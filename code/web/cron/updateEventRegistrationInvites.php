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
$waitingList = new UserAspenEventInstanceWaitingList();
$waitingList->status = 'notified';
$waitingList->whereAdd("expiresAt < '$now'");

$waitingList->find();

$expiredUsers = [];
while ($waitingList->fetch()) {
    $expiredUsers[] = [
        'id' => $waitingList->id,
        'eventInstanceId' => $waitingList->eventInstanceId,
        'position' => $waitingList->position,
    ];

    $waitingList->canRegister = 0;
    $waitingList->status = 'waiting';
    $waitingList->update();
    $expiredCount++;
}

foreach ($expiredUsers as $expiredUser) {
    $eventInstanceId = $expiredUser['eventInstanceId'];
    $removedPosition = $expiredUser['position'];
    $expiredUserId = $expiredUser['id'];

    $wlShift = new UserAspenEventInstanceWaitingList();
    $wlShift->eventInstanceId = $eventInstanceId;
    $wlShift->whereAdd("position >  $removedPosition");
    $wlShift->find();

    while ($wlShift->fetch()) {
        $wlShift->position = $wlShift->position -1;
        $wlShift->update();
    }

    $wlExpired = new UserAspenEventInstanceWaitingList();
    $wlExpired->id = $expiredUserId;
    if ($wlExpired->find(true)) {
        $wlMax = new UserAspenEventInstanceWaitingList();
        $wlMax->eventInstanceId = $eventInstanceId;
        $wlMax->selectAdd();
        $wlMax->selectAdd('MAX(position) AS maxPos');
        $wlMax->find(true);

        $maxPosition = (int) $wlMax->maxPos;
        $wlExpired->position = $maxPosition + 1;
        $wlExpired->update();
    }
}

$cronLogEntry->notes = "Updated $expiredCount waiting list entires";
$cronLogEntry->endTime = time();
$cronLogEntry->update();

//will also need to reorder waiting list

