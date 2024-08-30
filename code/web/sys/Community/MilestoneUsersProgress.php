<?php
class MilestoneUsersProgress extends DataObject
{
    public $__table = 'ce_milestone_users_progress';
    public $id;
    public $userId;
    public $ce_milestone_id;
    public $progress;

    public static function getProgressByMilestoneId($milestoneId, $userId) {
        $milestoneProgress = new Self();

        $milestoneProgress->whereAdd('ce_milestone_id = ' . intval($milestoneId));
        $milestoneProgress->whereAdd('userId = ' . intval($userId));
        $milestoneProgress->find(true);

        return $milestoneProgress->progress ? $milestoneProgress->progress : 0;
    }
}