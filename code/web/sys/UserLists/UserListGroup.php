<?php /** @noinspection PhpMissingFieldTypeInspection */

class UserListGroup extends DataObject {
	public $__table = 'user_list_group';
	public $id;
	public $title;
	public $parentGroupId;
	public $userId;

	public function supportsSoftDelete(): bool {
		return true;
	}
}
