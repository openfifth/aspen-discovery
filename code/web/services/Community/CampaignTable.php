<?php

require_once ROOT_DIR . '/sys/Community/Campaign.php';
require_once ROOT_DIR . '/sys/Community/CampaignMilestone.php';


class Community_CampaignTable extends Action {
    
    function launch() {
        global $interface;

        $campaignId = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($campaignId > 0) {
            $campaign = Campaign::getCampaignById($campaignId);
            if ($campaign) {
                $interface->assign('campaign', $campaign);
                $milestones = CampaignMilestone::getMilestoneByCampaign($campaignId);
                $interface->assign('milestones', $milestones);
                


            } else {
                $interface->assign('error', 'Campaign not found.');
            }
        } else {
            $interface->assign('error', 'Invalid campaign ID.');
        }
        $this->display('campaignTable.tpl', 'Campaign Table');
    }



    function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        return $breadcrumbs;
    }
}