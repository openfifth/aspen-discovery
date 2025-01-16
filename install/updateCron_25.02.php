<?php

if (count($_SERVER['argv']) > 1) {   
	$serverName = $_SERVER['argv'][1];
	$fhnd = fopen("/usr/local/aspen-discovery/sites/$serverName/conf/crontab_settings.txt", 'a+');
	
	if (!$fhnd) {
		echo("- Could not find cron settings file\n");
		exit();
	}

	while (($line = fgets($fhnd)) !== false) {
		if (strpos($line, 'updateOCLCILLRequests') > 0) {
			echo("- No cron expression added: is already found in /sites/$serverName/conf/crontab_settings.txt \n");
			exit();
		}
	}

	fwrite($fhnd, "\n#########################\n");
	fwrite($fhnd, "# fetch active OCLC ILL Requests and update them in Aspen DB #\n");
	fwrite($fhnd, "#########################\n");
	fwrite($fhnd, "0 0 * * * aspen php /usr/local/aspen-discovery/code/web/cron/updateOCLCILLRequests.php $serverName\n");
	exit();

} else {
	echo 'Must provide servername as first argument';
	exit();
}
