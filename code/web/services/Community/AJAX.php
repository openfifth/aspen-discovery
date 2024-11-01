<?php
require_once ROOT_DIR . '/JSON_Action.php';
require_once ROOT_DIR . '/sys/Community/Campaign.php';
require_once ROOT_DIR . '/sys/Community/UserCampaign.php';
require_once ROOT_DIR . '/sys/Community/MilestoneUsersProgress.php';
require_once ROOT_DIR . '/sys/UserAccount.php';


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

    function filterCampaigns() {
        global $interface;

        $campaignId = isset($_REQUEST['campaignId']) ? intval($_REQUEST['campaignId']) : 0;

        if ($campaignId > 0) {

            $campaign = Campaign::getCampaignById($campaignId);
            if ($campaign) {

                $html = '<div class="dashboardCategory row" style="border: 1px solid #3174AF; padding: 0 10px 10px 10px; margin-bottom: 10px;">';
                $html .= '<div class="col-sm-12">';
                $html .= "<h2 class=\"dashboardCategoryLabel\"><a href=\"/Community/CampaignTable?id={$campaignId}\">" . htmlspecialchars($campaign->name) . "</a></h2>";
                $html .= '<div style="border-bottom: 2px solid #3174AF; padding: 10px; margin-bottom: 10px;">';
                $html .= '<div class="dashboardLabel">Number of Patrons Enrolled:</div>';
                $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->currentEnrollments) . '</div>';
                $html .= '<div class="dashboardLabel">Total Number of Enrollments:</div>';
                $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->enrollmentCounter) . '</div>';
                $html .= '<div class="dashboardLabel">Total Number of Unenrollments:</div>';
                $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->unenrollmentCounter) . '</div>';
                $html .= '</div>'; // End of campaign div
                $html .= '</div>'; // End of col-sm-12
                $html .= '</div>'; // End of dashboardCategory




                $response['html'] = $html;
                $response['success'] = true;
            } else {
                $response['message'] = 'Campaign not found';
            }   
        }
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}