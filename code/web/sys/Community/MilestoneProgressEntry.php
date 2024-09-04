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

    /**
     * Initializes a new MilestoneProgressEntry object by setting its ce_milestone_id to the provided milestone object
     *
     * @param Milestone $milestone The milestone associated with this progress entry.
     * @param mixed $args Optional arguments to further configure the progress entry. Expects the following structure:
     * 
     *  [
     *     "object"                 => An object param, may be of any of the allowed milestone types e.g. 'Checkout', 'Hold', etc.
     *     "userId"                 => A user id param
     *     "milestoneUsersProgress" => A MilestoneUsersProgress object param to be associated with this progress entry
     *  ]
     * 
     * @return void
     */
    public function initialize(Milestone $milestone, $args = null)
    {

        $this->ce_milestone_id = $milestone->id;

        if (!$args)
            return;

        $this->userId = $args['userId'];
        $this->ce_milestone_users_progress_id = $args['milestoneUsersProgress']->id;
        $this->processed = 0;
        $this->tableName = $args['object']->__table;
        $this->object = json_encode($args['object']);
        $this->insert();
    }
}