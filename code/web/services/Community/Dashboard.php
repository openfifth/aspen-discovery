<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/Dashboard.php';
require_once ROOT_DIR . '/sys/Community/CampaignData.php';
require_once ROOT_DIR . '/sys/Community/Campaign.php';


class Community_Dashboard extends Admin_Dashboard {
    function launch() {
        global $interface;

        $campaign = new Campaign();

        $campaigns = $campaign->getAllCampaigns();
        $interface->assign('campaigns', $campaigns);

        $campaignsEndingThisMonth = $campaign->getCampaignsEndingThisMonth();
        $interface->assign('campaignsEndingThisMonth', $campaignsEndingThisMonth);
        
        $activeCampaigns = $campaign->getActiveCampaignsList();
        $interface->assign('activeCampaigns', $activeCampaigns);

        $upcomingCampaigns = $campaign->getUpcomingCampaigns();
        $interface->assign('upcomingCampaigns', $upcomingCampaigns);

        $this->display('dashboard.tpl', 'Dashboard');
    }

   
    function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        return $breadcrumbs;
    }

    function canView(): bool {
        return true;
    }

    function getActiveAdminSection(): string
    {
        return 'community';
    }
}