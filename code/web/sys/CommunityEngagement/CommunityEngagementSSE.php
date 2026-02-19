<?php /** @noinspection PhpMissingFieldTypeInspection */

class CommunityEngagementSSE {

    private bool $debug = false;

    public function __construct(bool $debug = false) {
        $this->debug = $debug;
    }

    /**
     * Main Endpoint: Handles the SSE lifecycle.
     */
    public function CommunityEngagementSSE() {
        if (!UserAccount::isLoggedIn()) {
            exit();
        }

        $patron = UserAccount::getActiveUserObj();
        $interval = 10;

        // 1. Send initial connection headers
        $this->sendHeaders();
        $this->sendSSEEvent('established', 'connection established');
        $this->flushOutput();

        // 2. Main Execution Loop
        while (true) {
            // Check if the client is still there
            if ($this->shouldStop()) {
                exit();
            }

            if ($this->debug) {
                global $logger;
                $logger->log("RUNNING SSE ", Logger::LOG_ERROR);
            }

            // Get the data (The part we can now test easily)
            $notifications = $this->fetchLatestNotifications($patron, $interval);

            if (empty($notifications)) {
                $this->sendSSEEvent('heart_beat', 'No notifications found');
            } else {
                foreach ($notifications as $payload) {
                    $this->sendSSEEvent('ce_notification', json_encode($payload));
                }
            }

            $this->flushOutput();
            sleep($interval);
        }
    }

    /**
     * REFACTORED LOGIC: This method contains all the business rules.
     * It returns an array of notification payloads based on priority:
     * 1. Campaign Completion (Highest - suppresses all others)
     * 2. Milestone Completion (Suppresses progress updates)
     * 3. Progress Updates (Lowest)
     */
    public function fetchLatestNotifications($patron, $interval) {
        $campaignCompletions = [];
        $milestoneCompletions = [];
        $progressUpdates = [];

        // Trackers to prevent duplicate notifications for the same ID in this batch
        $campaignsNotified = [];
        $milestonesNotified = [];
        $progressNotified = [];

        // Load necessary files
        require_once ROOT_DIR . '/sys/CommunityEngagement/CampaignMilestoneProgressEntry.php';
        require_once ROOT_DIR . '/sys/CommunityEngagement/CampaignMilestoneUsersProgress.php';
        require_once ROOT_DIR . '/sys/CommunityEngagement/CampaignMilestone.php';
        require_once ROOT_DIR . '/sys/CommunityEngagement/Campaign.php';
        require_once ROOT_DIR . '/sys/CommunityEngagement/Milestone.php';
        require_once ROOT_DIR . '/sys/CommunityEngagement/UserCampaign.php';

        $entry = new CampaignMilestoneProgressEntry();
        $entry->userId = $patron->id;

        if (!$this->debug) {
            $entry->whereAdd("timestamp >= DATE_SUB(NOW(), INTERVAL " . $interval . " SECOND)");
        }

        if ($entry->find()) {
            while ($entry->fetch()) {
                $campaignMilestone = new CampaignMilestone();
                $campaignMilestone->id = $entry->ce_campaign_milestone_id;
                $campaignMilestone->find(true);

                $campaign = new Campaign();
                $campaign->id = $campaignMilestone->campaignId;
                $campaign->find(true);

                $milestone = new Milestone();
                $milestone->id = $campaignMilestone->milestoneId;
                $milestone->find(true);

                $usersProgress = new CampaignMilestoneUsersProgress();
                $usersProgress->id = $entry->ce_campaign_milestone_users_progress_id;
                $usersProgress->find(true);

                $userCampaign = new UserCampaign();
                $userCampaign->userId = $patron->id;
                $userCampaign->campaignId = $campaignMilestone->campaignId;
                $userCampaign->find(true);

                $unwantedOverflow = $usersProgress->progress > $campaignMilestone->goal && !$milestone->progressBeyondOneHundredPercent;
                $wantedOverflow = $usersProgress->progress > $campaignMilestone->goal && $milestone->progressBeyondOneHundredPercent;

                if ($unwantedOverflow) {
                    continue;
                }

                // 1. Categorize: Campaign Completion
                if ($userCampaign->completed && !$wantedOverflow) {
                    if (in_array($campaign->id, $campaignsNotified)) continue;
                    $campaignsNotified[] = $campaign->id;
                    
                    $campaignCompletions[] = [
                        'id' => $entry->id . '_ce_campaign_completed',
                        'title' => translate(['text' => 'Campaign completed! Awesome!', 'isPublicFacing' => true]),
                        'body' => $campaign->name,
                        'icon' => "fa-medal",
                        'link' => ['href' => '/MyAccount/MyCampaigns', 'text' => translate(['text' => 'View all campaigns', 'isPublicFacing' => true])]
                    ];

                // 2. Categorize: Milestone Completion
                } elseif ($usersProgress->progress >= $campaignMilestone->goal && !$wantedOverflow) {
                    if (in_array($campaignMilestone->id, $milestonesNotified)) continue;
                    $milestonesNotified[] = $campaignMilestone->id;

                    $milestoneCompletions[] = [
                        'id' => $entry->id . '_ce_milestone_completed',
                        'title' => translate(['text' => 'Milestone completed! Well done!', 'isPublicFacing' => true]),
                        'body' => $milestone->name,
                        'icon' => "fa-clipboard-check",
                        'link' => ['href' => '/MyAccount/MyCampaigns', 'text' => translate(['text' => 'View all campaigns', 'isPublicFacing' => true])]
                    ];

                // 3. Categorize: General Progress
                } else {
                    if (in_array($campaignMilestone->id, $progressNotified)) continue;
                    $progressNotified[] = $campaignMilestone->id;

                    $progressUpdates[] = [
                        'id' => $entry->id . '_ce_milestone_progress',
                        'title' => translate(['text' => 'Milestone progress! Good job!', 'isPublicFacing' => true]),
                        'body' => $usersProgress->progress . '/' . $campaignMilestone->goal . ' ' . $milestone->name,
                        'icon' => "fa-chart-line",
                        'link' => ['href' => '/MyAccount/MyCampaigns', 'text' => translate(['text' => 'View all campaigns', 'isPublicFacing' => true])]
                    ];
                }
            }
        }

        // --- APPLY PRIORITY HIERARCHY ---

        // Priority 1: If any campaigns were completed, only return those.
        if (!empty($campaignCompletions)) {
            return $campaignCompletions;
        }

        // Priority 2: If any milestones were completed, return those and ignore progress.
        if (!empty($milestoneCompletions)) {
            return $milestoneCompletions;
        }

        // Priority 3: Otherwise, return whatever progress updates we found.
        return $progressUpdates;
    }

    /**
     * Helper to format SSE strings
     */
    protected function sendSSEEvent($event, $data) {
        echo "event: {$event}\n";
        echo "data: {$data}\n\n";
    }

    /**
     * Helper for headers
     */
    protected function sendHeaders() {
        header("X-Accel-Buffering: no");
        header("Content-Type: text/event-stream");
        header("Cache-Control: no-cache");
    }

    /**
     * Helper to check connection status
     */
    protected function shouldStop() {
        return connection_status() != CONNECTION_NORMAL || connection_aborted();
    }

    /**
     * Helper for buffer flushing
     */
    protected function flushOutput() {
        if (ob_get_length()) {
            ob_end_flush();
        }
        flush();
    }
}