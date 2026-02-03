<?php /** @noinspection PhpMissingFieldTypeInspection */

class UserAspenEventNotification extends DataObject {
	public $__table = 'user_aspen_event_notifications';
	public $id;
	public $userId;
	public $eventId;
	public $eventInstanceId;
	public $notificationType;
	public $changeType;
	public $toastShown;
	public $emailSent;
	public $createdAt;
}