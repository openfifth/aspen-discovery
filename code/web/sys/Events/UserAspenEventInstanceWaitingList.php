<?php /** @noinspection PhpMissingFieldTypeInspection */

class UserAspenEvevntInstanceWaitingList extends DataObject {
	public $__table = 'user_aspen_event_instance_waiting_list';
	public $id;
	public $userId;
	public $position;
	public $status;
	public $joinedAt;
	public $notifiedAt;
	public $expiresAt;
}