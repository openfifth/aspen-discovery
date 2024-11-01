<?php

require_once ROOT_DIR . '/sys/Community/Campaign.php';
require_once ROOT_DIR . '/sys/Community/UserCampaign.php';
require_once ROOT_DIR . '/services/Admin/Dashboard.php';

require_once ROOT_DIR . '/sys/Community/CampaignMilestone.php';


class Community_CampaignTable extends Admin_Dashboard {
    
    function launch() {
        global $interface;

        $campaignId = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($campaignId > 0) {
            $campaign = Campaign::getCampaignById($campaignId);

            if ($campaign) {
                $interface->assign('campaign', $campaign);
                
                //Retrieve milestones for the campaign
                $milestones = CampaignMilestone::getMilestoneByCampaign($campaignId);

                //Get users for campaign
                $users = $campaign->getUsersForCampaign();

                $userCampaigns = [];
                foreach ($users as $user) {
                    $userCampaign = new UserCampaign();
                    $userCampaign->campaignId = $campaignId;
                    $userCampaign->userId = $user->id;

                    if ($userCampaign->find(true)) {
                        $isCampaignComplete = $userCampaign->checkCompletionStatus();
                        $userCampaigns[$campaign->id][$user->id] = [
                            'rewardGiven' => (int)$userCampaign->rewardGiven,
                            'isCampaignComplete' =>$isCampaignComplete,
                            'milestones' => []
                        ];

                        //Get milestone completion status
                        $milestoneCompletionStatus = $userCampaign->checkMilestoneCompletionStatus();

                        foreach ($milestones as $milestone) {
                            $milestoneComplete = $milestoneCompletionStatus[$milestone->id] ?? false;
                            $userProgress = MilestoneUsersProgress::getProgressByMilestoneId($milestone->id, $user->id);
                            $totalGoals = CampaignMilestone::getMilestoneGoalCountByCampaign($campaignId, $milestone->id);
                            $milestoneRewardGiven = MilestoneUsersProgress::getRewardGivenForMilestone($milestone->id, $user->id);

                            //Add milestone data for each user
                            $userCampaigns[$campaign->id][$user->id]['milestones'][$milestone->id] = [
                                'milestoneComplete' => $milestoneComplete,
                                'userProgress' => $userProgress,
                                'goal' => $totalGoals,
                                'milestoneRewardGiven' =>$milestoneRewardGiven,
                            ];
                        }
                    }
                }
                $interface->assign('userCampaigns', $userCampaigns);
                $interface->assign('milestones', $milestones);
                $interface->assign('users', $users);

            } else {
                $interface->assign('error', 'Campaign not found.');
            }
        } else {
            $interface->assign('error', 'Invalid campaign ID.');
        }
        $this->display('campaignTable.tpl', 'Campaign Table');
    }

    function canView(): bool {
        return true;
    }

    function getActiveAdminSection(): string
    {
        return 'community';
    }

    function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        return $breadcrumbs;
    }
}