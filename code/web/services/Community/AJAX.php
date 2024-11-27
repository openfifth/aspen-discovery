<?php
require_once ROOT_DIR . '/JSON_Action.php';
require_once ROOT_DIR . '/sys/Community/Campaign.php';
require_once ROOT_DIR . '/sys/Community/UserCampaign.php';
require_once ROOT_DIR . '/sys/Community/CampaignMilestoneUsersProgress.php';
require_once ROOT_DIR . '/sys/UserAccount.php';


class Community_AJAX extends JSON_Action {
    function campaignRewardGivenUpdate() {
        $userId = $_GET['userId'];
        $campaignId = $_GET['campaignId'];
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

            $campaignMilestoneProgress = new CampaignMilestoneUsersProgress();
            #TODO: Add a campaignId check
            $campaignMilestoneProgress->userId = $userId;
            $campaignMilestoneProgress->ce_milestone_id = $milestoneId;

            if ($campaignMilestoneProgress->find(true)) {
                $campaignMilestoneProgress->rewardGiven = 1;

                if ($campaignMilestoneProgress->update()) {
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
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    function filterCampaigns() {

        $campaignId = isset($_REQUEST['campaignId']) ? intval($_REQUEST['campaignId']) : 0;
        $userId = isset($_REQUEST['userId']) ? intval($_REQUEST['userId']) : 0;

        $response = [];

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
                $html .= '</div>'; 
                $html .= '</div>'; 
                $html .= '</div>'; 

                $response['html'] = $html;
                $response['success'] = true;
            } else {
                $response['message'] = 'Campaign not found';
            }   
        } elseif ($userId > 0) {
            $userCampaigns = Campaign::getUserEnrolledCampaigns($userId);

            if (!empty($userCampaigns)) {
                foreach ($userCampaigns as $campaign) {
                    $html = '<div class="dashboardCategory row" style="border: 1px solid #3174AF; padding: 0 10px 10px 10px; margin-bottom: 10px;">';
                    $html .= '<div class="col-sm-12">';
                    $html .= "<h5 style=\"font-weight:bold;\"><a href=\"/Community/CampaignTable?id={$campaign->id}\">" .htmlspecialchars($campaign->name) . "</a></h5>";
                    $html .= '<div style="border-bottom: 2px solid #3174AF; padding: 10px; margin-bottom: 10px;">';
                    $html .= '<div class="dashboardLabel">Number of Patrons Enrolled: </div>';
                    $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->currentEnrollments) . '</div>';
                    $html .= '<div class="dashboardLabel">Number of Enrollments: </div>';
                    $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->enrollmentCounter) . '</div>';
                    $html .= '<div class="dashboardLabel">Number of UnEnrollments: </div>';
                    $html .= '<div class="dashboardValue">' . htmlspecialchars($campaign->unenrollmentCounter) . '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '</div>';
                }

                $response['html'] = $html;
                $response['success'] = true;
            } else {
                $response['message'] = 'No campaigns found for this user.';
            }
        } else {
            $response['message'] = 'No valid campaign or user specified.';
        }

        

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}