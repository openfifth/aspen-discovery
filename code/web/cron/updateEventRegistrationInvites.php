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

while ($waitingList->fetch()) {
    $waitingList->canRegister = 0;
    $waitingList->status = 'waiting';
    $waitingList->update();
    $expiredCount++;
}

$cronLogEntry->notes = "Updated $expiredCount waiting list entires";
$cronLogEntry->endTime = time();
$cronLogEntry->update();

//will also need to reorder waiting list

