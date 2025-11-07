<?php /** @noinspection PhpMissingFieldTypeInspection */


class GroupedWorkDisplayInfo extends DataObject {
	public $__table = 'grouped_work_display_info';
	public $id;
	public $permanent_id;
	public $title;
	public $author;
	public $seriesName;
	public $seriesDisplayOrder;
	public $description;
	public $addedBy;
	public $dateAdded;

	public function insert(string $context = '') : int|bool {
		if (empty($this->seriesDisplayOrder)) {
			$this->seriesDisplayOrder = 0;
		}
		require_once ROOT_DIR . '/sys/Grouping/GroupedWork.php';
		$groupedWork = new GroupedWork();
		$groupedWork->permanent_id = $this->permanent_id;
		if ($groupedWork->find(true)) {
			$groupedWork->forceReindex(true);
		}
		return parent::insert();
	}

	public function update(string $context = '') : int|bool {
		if (empty($this->seriesDisplayOrder)) {
			$this->seriesDisplayOrder = 0;
		}
		require_once ROOT_DIR . '/sys/Grouping/GroupedWork.php';
		$groupedWork = new GroupedWork();
		$groupedWork->permanent_id = $this->permanent_id;
		if ($groupedWork->find(true)) {
			$groupedWork->forceReindex(true);
		}
		return parent::update();
	}

	public function delete(bool $useWhere = false, bool $hardDelete = false) : bool|int {
		require_once ROOT_DIR . '/sys/Grouping/GroupedWork.php';
		$groupedWork = new GroupedWork();
		$groupedWork->permanent_id = $this->permanent_id;
		if ($groupedWork->find(true)) {
			$groupedWork->forceReindex(true);
		}
		return parent::delete($useWhere, $hardDelete);
	}
}