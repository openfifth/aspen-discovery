<?php
class MilestoneUsersProgress extends DataObject
{
    public $__table = 'ce_milestone_users_progress';
    public $id;
    public $userId;
    public $ce_milestone_id;
    public $progress;
    public $rewardGiven;

    public static function getProgressByMilestoneId($milestoneId, $userId) {
        $milestoneProgress = new Self();

        $milestoneProgress->whereAdd('ce_milestone_id = ' . intval($milestoneId));
        $milestoneProgress->whereAdd('userId = ' . intval($userId));
        $milestoneProgress->find(true);

        return $milestoneProgress->progress ? $milestoneProgress->progress : 0;
    }

    public static function getRewardGivenForMilestone($milestoneId, $userId) {
        $progress = new MilestoneUsersProgress();
        $progress->ce_milestone_id = $milestoneId;
        $progress->userId = $userId;

        if ($progress->find(true)) {
            return $progress->rewardGiven;
        }
        return false;
    } 
}