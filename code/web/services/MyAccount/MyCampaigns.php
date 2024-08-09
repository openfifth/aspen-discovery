<?php
require_once ROOT_DIR . '/services/MyAccount/MyAccount.php';
require_once ROOT_DIR . '/sys/Community/Campaign.php';

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
            $campaign->progress = $this->calculateProgress($campaign->id, $userId);
            $isEnrolled = $this->checkUserEnrollment($userId, $campaignId);
            $campaign->enrolled = $isEnrolled;

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

    function calculateProgress() {
        return 75;
    }

    function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'Campaigns');
		return $breadcrumbs;
    }
}