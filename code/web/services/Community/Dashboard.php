<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/Dashboard.php';
require_once ROOT_DIR . '/sys/Community/CampaignData.php';
require_once ROOT_DIR . '/sys/Community/Campaign.php';


class Community_Dashboard extends Admin_Dashboard {
    function launch() {
        global $interface;

        // $instanceName = $this->loadInstanceInformation('Campaign');
        $campaigns = $this->getAllCampaigns();
        $interface->assign('campaigns', $campaigns);

        $campaignsThisMonth = $this->getCampaignsStartedThisMonth();
        $interface->assign('campaignsThisMonth', $campaignsThisMonth);

        $campaignsEndingThisMonth = $this->getCampaignsEndingThisMonth();
        $interface->assign('campaignsEndingThisMonth', $campaignsEndingThisMonth);
        
        $activeCampaigns = $this->getActiveCampaigns();
        $interface->assign('activeCampaigns', $activeCampaigns);

        $upcomingCampaigns = $this->getUpcomingCampaigns();
        $interface->assign('upcomingCampaigns', $upcomingCampaigns);
        // $this->loadDates();

    

        $this->display('dashboard.tpl', 'Dashboard');
    }

    /**
     * @return array 
     */
    public function getAllCampaigns(): array {
        $campaignList = [];
        $campaign = new Campaign();
        $campaign->find();
        while ($campaign->fetch()) {
            $campaignList[] = clone $campaign;
        }
        return $campaignList;
    }

    public function getCampaignsStartedThisMonth(): array {
        $campaignList = [];
        $campaign = new Campaign();

        $currentMonth = date('m');
        $currentYear = date('Y');

        $campaign->whereAdd("MONTH(startDate) = $currentMonth AND YEAR(startDate) = $currentYear");
        $campaign->find();
        while ($campaign->fetch()) {
            $campaignList[] = clone $campaign;
        }
        return $campaignList;
    }

    public function getCampaignsEndingThisMonth(): array {
        $campaignList = [];
        $campaign = new Campaign();

        $currentMonth = date('m');
        $currentYear = date('Y');

        $campaign->whereAdd("MONTH(endDate) = $currentMonth AND YEAR(endDate) = $currentYear");
        $campaign->find();
        while ($campaign->fetch()) {
            $campaignList[] = clone $campaign;
        }
        return $campaignList;
    }

    public function getActiveCampaigns(): array {
        $activeCampaigns = [];
        $today = date('Y-m-d');

        $campaign = new Campaign();
        $campaign->whereAdd("startDate <= '$today'");
        $campaign->whereAdd("endDate >= '$today'");
        $campaign->find();

        while ($campaign->fetch()) {
            $activeCampaigns[] = clone $campaign;
        }

        return $activeCampaigns;
    }

    public function getUpcomingCampaigns(): array {
        $upcomingCampaigns = [];
        $today = date('Y-m-d');

        $campaign = new Campaign();
        $campaign->whereAdd("startDate > '$today'");
        $campaign->find();

        while ($campaign->fetch()) {
            $upcomingCampaigns[] = clone $campaign;
        }
        return $upcomingCampaigns;
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