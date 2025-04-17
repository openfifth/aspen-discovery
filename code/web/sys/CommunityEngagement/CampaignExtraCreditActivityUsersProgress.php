<?php
class CampaignExtraCreditActivityUsersProgress extends DataObject {

    public $__table = 'ce_campaign_extra_credit_activity_users_progress';
    public $id;
    public $userId;
	public $campaignId;
	public $extraCreditId;
	public $progress;
	public $rewardGiven;

    public static function getProgressByExtraCreditId($extraCreditId, $campaignId, $userId) {
        $extraCreditProgress  = new CampaignExtraCreditActivityUsersProgress();

        $extraCreditProgress->whereAdd('extraCreditId = ' . intval($extraCreditId));
        $extraCreditProgress->whereAdd('campaignId = ' . intval($campaignId));
        $extraCreditProgress->whereAdd('userId = ' . intval($userId));
		$extraCreditProgress->find(true);
        return $extraCreditProgress->progress ? $extraCreditProgress->progress : 0;
    }

    public static function getRewardGivenForExtraCreditActivity($extraCreditId, $userId, $campaignId) {
		$progress = new CampaignExtraCreditActivityUsersProgress();
		$progress->extraCreditId = $extraCreditId;
		$progress->userId = $userId;
		$progress->campaignId = $campaignId;

		if ($progress->find(true)) {
			return $progress->rewardGiven;
		}
		return false;
	} 
}