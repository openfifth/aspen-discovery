<?php
require_once ROOT_DIR . '/services/MyAccount/MyAccount.php';
require_once ROOT_DIR . '/sys/Community/Campaign.php';
require_once ROOT_DIR . '/sys/Community/CampaignMilestone.php';
require_once ROOT_DIR . '/sys/Community/Milestone.php';

class MyCampaigns extends MyAccount {

    function launch() {
        global $interface;
        global $library;

        $campaignList = $this->getCampaigns();
        $interface->assign('campaignList', $campaignList);

        $userId = $this->getUserId();
        $interface->assign('userId', $userId);
        $this->display('../MyAccount/myCampaigns.tpl', 'My Campaigns');
    }

    function getUserId() {
        $user = UserAccount::getLoggedInUser();
        $userId = $user->id;
        return $userId;
    }

    function getCampaigns() {
        $campaign = new Campaign();
        $campaignList = [];

        if (!UserAccount::isLoggedIn()) {
            return $campaignList;
        }
        $user = UserAccount::getLoggedInUser();
        $userId = $user->id;

        $campaign->find();
        while ($campaign->fetch()) {
            $campaignId = $campaign->id;
            //Keep logic for milestone progress
            $isEnrolled = $this->checkUserEnrollment($userId, $campaignId);
            $campaign->enrolled = $isEnrolled;
            //Total Number of Milestones for campaign - display completed number to user as a fraction. 
            $campaign->numCampaignMilestones = $this->getNumCampaignMilestones($campaignId);
            $completedMilestones = UserCompletedMilestone::getCompletedMilestones($userId, $campaignId);
            $campaign->numCompletedMilestones = count($completedMilestones);

            //Fetch milestones for campaign
            $milestones = CampaignMilestone::getMilestoneByCampaign($campaignId);
            $campaign->milestones = $milestones;
            $milestoneGoalCounts = [];
            foreach ($milestones as $milestone) {
                $milestoneId = $milestone->milestoneId;
                //Calculate milestone progress
                $progressResult = $this->calculateMilestoneProgress($campaignId, $userId, $milestoneId);
                $milestoneProgress[$milestoneId] = $progressResult['progress'];
                $milestoneCompletedGoals[$milestoneId] = $progressResult['completed'];
                //Get goal count for milestone
                $milestoneGoalCount = CampaignMilestone::getMilestoneGoalCountByCampaign($campaignId, $milestoneId);
                $milestoneGoalCounts[$milestoneId] = $milestoneGoalCount;
            }
            $campaign->milestoneProgress = $milestoneProgress;
            $campaign->milestoneGoalCount = $milestoneGoalCounts;
            $campaign->milestoneCompletedGoals = $milestoneCompletedGoals;
            $campaignList[] = clone $campaign;
        }
        return $campaignList;
    }

    function checkUserEnrollment($userId, $campaignId) {
        global $aspen_db;
        $query = "SELECT COUNT(*) AS count FROM user_campaign WHERE userId = :userId AND campaignId = :campaignId";
        $stmt = $aspen_db->prepare($query);
    
        // Execute the query with bound parameters
        $stmt->execute([
            ':userId' => $userId,
            ':campaignId' => $campaignId,
        ]);
    
        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Check if the count is greater than 0
        return $result['count'] > 0;
    }
//Keep logic for milestone progress
    function calculateMilestoneProgress($campaignId, $userId, $milestoneId) {
        require_once ROOT_DIR . '/sys/Community/MilestoneUsersProgress.php';
     
        $goal = CampaignMilestone::getMilestoneGoalCountByCampaign($campaignId, $milestoneId);

        if ($goal ==0) {
            return 0;
        }

        $userCompletedGoalCount = MilestoneUsersProgress::getProgressByMilestoneId($milestoneId, $userId);
        $progress = ($userCompletedGoalCount / $goal) * 100;
        return [
            'progress' => round($progress, 2),
            'completed' => $userCompletedGoalCount,
        ];
    }

    function getNumCampaignMilestones($campaignId) {
        require_once ROOT_DIR . '/sys/Community/UserCompletedMilestone.php';
        //Fetch milestones for campaign
        $campaignMilestone = new CampaignMilestone();
        $campaignMilestone->campaignId = $campaignId;
        $numCampaignMilestones = $campaignMilestone->count();

        return $numCampaignMilestones;
    }

    function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'Campaigns');
		return $breadcrumbs;
    }
}