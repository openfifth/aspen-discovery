<?php

require_once ROOT_DIR . '/services/AJAX/AJAXHandler.php';
require_once ROOT_DIR . '/sys/Grouping/ManuallyGroupedWork.php';
require_once ROOT_DIR . '/sys/Grouping/ManuallyGroupedWorkRecord.php';

class AJAX_ManualGroupedWorks extends JSON_Action {

	/**
	 * Get a list of manual grouped works
	 *
	 * @return array
	 */
	function getManualGroupedWorksList(): array {
		$result = [
			'success' => false,
			'message' => translate([
				'text' => 'Unknown Error',
				'isAdminFacing' => true,
			]),
		];

		if (UserAccount::isLoggedIn() && UserAccount::userHasPermission('Manually Group and Ungroup Works')) {
			$manualGroups = [];

			$manuallyGroupedWork = new ManualGroupedWork();
			$manuallyGroupedWork->orderBy('title');
			$manuallyGroupedWork->find();

			while ($manuallyGroupedWork->fetch()) {
				$manualGroups[] = [
					'id' => $manuallyGroupedWork->id,
					'title' => $manuallyGroupedWork->title,
					'description' => $manuallyGroupedWork->description,
				];
			}

			$result = [
				'success' => true,
				'message' => translate([
					'text' => 'Retrieved manual groups successfully',
					'isAdminFacing' => true,
				]),
				'manualGroups' => $manualGroups,
			];
		} else {
			$result['message'] = translate([
				'text' => 'You do not have permission to perform this action',
				'isAdminFacing' => true,
			]);
		}

		return $result;
	}

	/**
	 * Add a record to a manual group
	 *
	 * @return array
	 */
	function addToManualGroup(): array {
		$result = [
			'success' => false,
			'message' => translate([
				'text' => 'Unknown Error',
				'isAdminFacing' => true,
			]),
		];

		if (UserAccount::isLoggedIn() && UserAccount::userHasPermission('Manually Group and Ungroup Works')) {
			$recordId = $_REQUEST['recordId'] ?? '';
			$recordSource = $_REQUEST['recordSource'] ?? '';
			$manualGroupId = $_REQUEST['manualGroupId'] ?? '';

			if (empty($recordId) || empty($recordSource)) {
				$result['message'] = translate([
					'text' => 'Record ID and source are required',
					'isAdminFacing' => true,
				]);
				return $result;
			}

			if ($manualGroupId === 'new') {
				// Create a new manual group
				$newGroupTitle = $_REQUEST['newGroupTitle'] ?? '';
				$newGroupDescription = $_REQUEST['newGroupDescription'] ?? '';

				if (empty($newGroupTitle)) {
					$result['message'] = translate([
						'text' => 'Group title is required when creating a new group',
						'isAdminFacing' => true,
					]);
					return $result;
				}

				$manualGroup = new ManualGroupedWork();
				$manualGroup->title = $newGroupTitle;
				$manualGroup->description = $newGroupDescription;
				$manualGroup->created_by = UserAccount::getActiveUserId();
				$manualGroup->date_created = time();
				$manualGroup->last_updated = time();

				if (!$manualGroup->insert()) {
					$result['message'] = translate([
						'text' => 'Failed to create new manual group',
						'isAdminFacing' => true,
					]);
					return $result;
				}

				$manualGroupId = $manualGroup->id;
			} else {
				// Verify the manual group exists
				$manualGroup = new ManualGroupedWork();
				$manualGroup->id = $manualGroupId;
				if (!$manualGroup->find(true)) {
					$result['message'] = translate([
						'text' => 'Manual group not found',
						'isAdminFacing' => true,
					]);
					return $result;
				}
			}

			// Check if record is already in the manual group
			$manualGroupRecord = new ManuallyGroupedWorkRecord();
			$manualGroupRecord->manually_grouped_work_id = $manualGroupId;
			$manualGroupRecord->type = $recordSource;
			$manualGroupRecord->identifier = $recordId;

			if ($manualGroupRecord->find(true)) {
				$result['message'] = translate([
					'text' => 'Record is already in this manual group',
					'isAdminFacing' => true,
				]);
				$result['success'] = true;
				return $result;
			}

			// Add the record to the manual group
			$manualGroupRecord->identifier_type = 'record_id';
			$manualGroupRecord->date_added = time();

			if ($manualGroupRecord->insert()) {
				// Trigger reindexing
				$manualGroup->reindexRecords();

				$result = [
					'success' => true,
					'message' => translate([
						'text' => 'Record added to manual group successfully',
						'isAdminFacing' => true,
					]),
				];
			} else {
				$result['message'] = translate([
					'text' => 'Failed to add record to manual group',
					'isAdminFacing' => true,
				]);
			}
		} else {
			$result['message'] = translate([
				'text' => 'You do not have permission to perform this action',
				'isAdminFacing' => true,
			]);
		}

		return $result;
	}

	/**
	 * Remove a record from a manual group
	 *
	 * @return array
	 */
	function removeFromManualGroup(): array {
		$result = [
			'success' => false,
			'message' => translate([
				'text' => 'Unknown Error',
				'isAdminFacing' => true,
			]),
		];

		if (UserAccount::isLoggedIn() && UserAccount::userHasPermission('Manually Group and Ungroup Works')) {
			$recordId = $_REQUEST['recordId'] ?? '';
			$recordSource = $_REQUEST['recordSource'] ?? '';
			$manualGroupId = $_REQUEST['manualGroupId'] ?? '';

			if (empty($recordId) || empty($recordSource) || empty($manualGroupId)) {
				$result['message'] = translate([
					'text' => 'Record ID, source, and manual group ID are required',
					'isAdminFacing' => true,
				]);
				return $result;
			}

			// Verify the manual group exists
			$manualGroup = new ManualGroupedWork();
			$manualGroup->id = $manualGroupId;
			if (!$manualGroup->find(true)) {
				$result['message'] = translate([
					'text' => 'Manual group not found',
					'isAdminFacing' => true,
				]);
				return $result;
			}

			// Find the record in the manual group
			$manualGroupRecord = new ManuallyGroupedWorkRecord();
			$manualGroupRecord->manually_grouped_work_id = $manualGroupId;
			$manualGroupRecord->type = $recordSource;
			$manualGroupRecord->identifier = $recordId;

			if (!$manualGroupRecord->find(true)) {
				$result['message'] = translate([
					'text' => 'Record not found in this manual group',
					'isAdminFacing' => true,
				]);
				return $result;
			}

			// Delete the record from the manual group
			if ($manualGroupRecord->delete()) {
				// Trigger reindexing
				$manualGroup->reindexRecords();

				$result = [
					'success' => true,
					'message' => translate([
						'text' => 'Record removed from manual group successfully',
						'isAdminFacing' => true,
					]),
				];
			} else {
				$result['message'] = translate([
					'text' => 'Failed to remove record from manual group',
					'isAdminFacing' => true,
				]);
			}
		} else {
			$result['message'] = translate([
				'text' => 'You do not have permission to perform this action',
				'isAdminFacing' => true,
			]);
		}

		return $result;
	}

	/**
	 * Check if a record is part of a manual group
	 *
	 * @return array
	 */
	function getManualGroupForRecord(): array {
		$result = [
			'success' => false,
			'message' => translate([
				'text' => 'Unknown Error',
				'isAdminFacing' => true,
			]),
		];

		if (UserAccount::isLoggedIn() && UserAccount::userHasPermission('Manually Group and Ungroup Works')) {
			$recordId = $_REQUEST['recordId'] ?? '';
			$recordSource = $_REQUEST['recordSource'] ?? '';

			if (empty($recordId) || empty($recordSource)) {
				$result['message'] = translate([
					'text' => 'Record ID and source are required',
					'isAdminFacing' => true,
				]);
				return $result;
			}

			// Check if record is in a manual group
			$manualGroupRecord = new ManuallyGroupedWorkRecord();
			$manualGroupRecord->type = $recordSource;
			$manualGroupRecord->identifier = $recordId;

			if ($manualGroupRecord->find(true)) {
				$manualGroup = new ManualGroupedWork();
				$manualGroup->id = $manualGroupRecord->manually_grouped_work_id;
				if ($manualGroup->find(true)) {
					$result = [
						'success' => true,
						'message' => translate([
							'text' => 'Record is part of a manual group',
							'isAdminFacing' => true,
						]),
						'isInManualGroup' => true,
						'manualGroup' => [
							'id' => $manualGroup->id,
							'title' => $manualGroup->title,
							'description' => $manualGroup->description,
						],
					];
				} else {
					$result['message'] = translate([
						'text' => 'Manual group not found',
						'isAdminFacing' => true,
					]);
				}
			} else {
				$result = [
					'success' => true,
					'message' => translate([
						'text' => 'Record is not part of a manual group',
						'isAdminFacing' => true,
					]),
					'isInManualGroup' => false,
				];
			}
		} else {
			$result['message'] = translate([
				'text' => 'You do not have permission to perform this action',
				'isAdminFacing' => true,
			]);
		}

		return $result;
	}
}