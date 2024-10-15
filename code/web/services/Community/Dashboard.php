<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/Dashboard.php';
require_once ROOT_DIR . '/sys/Community/CampaignData.php';
require_once ROOT_DIR . '/sys/Community/Campaign.php';
require_once ROOT_DIR . '/sys/Community/UserCampaign.php';
require_once ROOT_DIR . '/sys/Community/UserCompletedMilestone.php';


class Community_Dashboard extends Admin_Dashboard {
    function launch() {
        global $interface;

        $campaign = new Campaign();
        $userCampaign = new UserCampaign();

        $campaigns = $campaign->getAllCampaigns();
        $interface->assign('campaigns', $campaigns);

        $campaignsEndingThisMonth = $campaign->getCampaignsEndingThisMonth();
        $interface->assign('campaignsEndingThisMonth', $campaignsEndingThisMonth);
        
        $activeCampaigns = $campaign->getActiveCampaignsList();
        $interface->assign('activeCampaigns', $activeCampaigns);

        $upcomingCampaigns = $campaign->getUpcomingCampaigns();
        $interface->assign('upcomingCampaigns', $upcomingCampaigns);

        $userCampaigns = [];
        foreach ($campaigns as $campaign) {
            $users = $campaign->getUsersForCampaign();
            foreach ($users as $user) {
                $userCampaign = new UserCampaign();
                $userCampaign->userId = $user->id;
                $userCampaign->campaignId = $campaign->id;
            
            if ($userCampaign->find(true)) {
                $numMilestones = count(CampaignMilestone::getMilestoneByCampaign($campaign->id));
                $userCampaigns[$campaign->id][$user->id] = [
                    'rewardGiven' => (int)$userCampaign->rewardGiven,
                ];
            }
            }
        }
        $interface->assign('userCampaigns', $userCampaigns);
        $this->display('dashboard.tpl', 'Dashboard');
    }

   
    function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        return $breadcrumbs;
    }

    function canView(): bool {
        return UserAccount::userHasPermission([
            'View Community Dashboard',
        ]);
    }

    function getActiveAdminSection(): string
    {
        return 'community';
    }
}