<?php
    require_once __DIR__ . '/../bootstrap.php';
    require_once __DIR__ . '/../bootstrap_aspen.php';
    if (array_key_exists('Community Engagement', $enabledModules)) {
        $userCampaigns = new UserCampaign();
		//adding 1 day to catch things that were enrolled today
        $userCampaigns->whereAdd("enrollmentDate < '" . date("Y-m-d", strtotime("+1 day")) . "'");
        $userCampaigns->unenrollmentDate = null;
        $userCampaigns->completed = 0;
        $userIds = $userCampaigns->fetchAll("userId");
        foreach($userIds as $userID)
        {
            $user = new User();
            $user->id = $userID;
            if($user->find(true))
            {
                echo "\nupdating checkouts/holds for user: ".$userID."\n\n";
                $user->getCheckouts();
				$user->getHolds();
            }
			// TODO do we need to do a similar check for holds or other milestone criteria?
        }
    }
?>