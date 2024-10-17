<?php
require_once ROOT_DIR . '/JSON_Action.php';
require_once ROOT_DIR . '/sys/Community/UserCampaign.php';
require_once ROOT_DIR . '/sys/Community/MilestoneUsersProgress.php';

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

    function milestoneRewardGivenUpdate() {
        ob_start();

        try {
            $userId = $_GET['userId'];
            $milestoneId = $_GET['milestoneId'];

            $milestoneProgress = new MilestoneUsersProgress();
            $milestoneProgress->userId = $userId;
            $milestoneProgress->milestoneId = $milestoneId;

            if ($milestoneProgress->find(true)) {
                $milestoneProgress->rewardGiven = 1;

                if ($milestoneProgress->update()) {
                    ob_end_clean();
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Failed to update reward status');
                }
            } else {
                throw new Exception('Milestone progress record not found.');
            }

        } catch(Exception $e) {
            ob_end_clean();
            error_log("Update failed: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}