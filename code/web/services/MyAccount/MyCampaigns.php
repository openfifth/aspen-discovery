<?php
require_once ROOT_DIR . '/services/MyAccount/MyAccount.php';
require_once ROOT_DIR . '/sys/Community/Campaign.php';

class MyCampaigns extends MyAccount {

    function launch() {
        global $interface;
        global $library;

        $campaignList = $this->getCampaigns();
        $interface->assign('campaignList', $campaignList);

        $this->display('../MyAccount/myCampaigns.tpl', 'My Campaigns');
    }

    function getCampaigns() {
        $campaign = new Campaign();
        $campaignList = [];

        $campaign->find();
        while ($campaign->fetch()) {
            $campaignList[] = clone $campaign;
        }
        return $campaignList;
    }

    function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'Campaigns');
		return $breadcrumbs;
    }
}