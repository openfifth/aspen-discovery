<?php
require_once ROOT_DIR . '/JSON_Action.php';
require_once ROOT_DIR . '/sys/Community/UserCampaign.php';

class Community_AJAX extends JSON_Action {
    function campaignRewardGivenUpdate() {
        error_log("Started campaignRewardGivenUpdate");
        $userId = $_GET['userId'];
        $campaignId = $_GET['campaignId'];
        error_log("User ID: $userId, Campaign ID: $campaignId");
        $userCampaign = new UserCampaign();
        $userCampaign->userId = $userId;
        $userCampaign->campaignId = $campaignId;

        if ($userCampaign->find(true)) {
            $userCampaign->rewardGiven = 1;
            if ($userCampaign->update()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update reward status.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User campaign record not found.']);
        }
        exit;
    }
}