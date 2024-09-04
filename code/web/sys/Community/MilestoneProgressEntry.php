<?php
require_once ROOT_DIR . '/sys/Community/Milestone.php';

class MilestoneProgressEntry extends DataObject
{
    public $__table = 'ce_milestone_progress_entries';
    public $id;
    public $userId;
    public $ce_milestone_id;
    public $ce_milestone_users_progress_id;
    public $tableName;
    public $processed;
    public $object;
}