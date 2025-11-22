<?php
/**
 * Load Initial Reading History for users who haven't had their reading history loaded yet.
 *
 * This is run as a cron job to prevent AJAX timeouts. The logic has been
 * transferred from the getReadingHistory() method in CatalogConnection.php.
 *
 * If the process is terminated in the command-line at a point when the CurlWrapper is running,
 * the command-line will return an error, but it is inconsequential.
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap_aspen.php';
require_once ROOT_DIR . '/sys/ReadingHistoryEntry.php';
require_once ROOT_DIR . '/sys/CronLogEntry.php';
$cronLogEntry = new CronLogEntry();
$cronLogEntry->startTime = time();
$cronLogEntry->name = 'Load Initial Reading History';
$cronLogEntry->insert();

global $configArray;
global $serverName;
global $aspen_db;
global $logger;

set_time_limit(0);

$staleIntervalMinutes = 30; // Configurable: Interval after which an import is considered stale.

// Look for users who need their initial reading history loaded and are not currently being processed.
$selectIdSql = "
	SELECT id FROM user
	WHERE initialReadingHistoryLoaded = 0
		AND forceReadingHistoryLoad = 1
		AND trackReadingHistory = 1
		AND (readingHistoryImportStartedAt IS NULL
			OR readingHistoryImportStartedAt < UTC_TIMESTAMP() - INTERVAL :staleInterval MINUTE)
	ORDER BY id
";

try {
	$stmt = $aspen_db->prepare($selectIdSql);
	$stmt->bindValue(':staleInterval', $staleIntervalMinutes, PDO::PARAM_INT);
	$stmt->execute();
	$usersToProcess = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
	$cronLogEntry->numErrors++;
	$cronLogEntry->notes .= "Error fetching users for reading history import: " . $e->getMessage() . ".";
	$cronLogEntry->endTime = time();
	$cronLogEntry->update();
	exit(1);
}

$loadedCount = 0;
$errorCount = 0;

$cronLogEntry->notes .= "<br/>Starting initial reading history load. Found ". count($usersToProcess) ." potential users to process.";

foreach ($usersToProcess as $userId) {

	// Attempt to atomically claim the user.
	$claimSql = "
		UPDATE user
		SET readingHistoryImportStartedAt = UTC_TIMESTAMP()
		WHERE id = :user_id
		AND initialReadingHistoryLoaded = 0 -- Re-check conditions atomically.
		AND forceReadingHistoryLoad = 1
		AND (readingHistoryImportStartedAt IS NULL
		OR readingHistoryImportStartedAt < UTC_TIMESTAMP() - INTERVAL :staleInterval MINUTE)
	";

	try {
		$claimStmt = $aspen_db->prepare($claimSql);
		$claimStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
		$claimStmt->bindValue(':staleInterval', $staleIntervalMinutes, PDO::PARAM_INT);
		$claimStmt->execute();

		if ($claimStmt->rowCount() === 0) {
			$cronLogEntry->numErrors++;
			$cronLogEntry->notes .= "<br/>User $userId already claimed by another process or state changed. Skipping.";
			continue;
		}
	} catch (Exception $e) {
		$cronLogEntry->numErrors++;
		$cronLogEntry->notes .= "<br/>Error claiming user $userId: " . $e->getMessage() . ".";
		$errorCount++;
		continue;
	}

	// Successfully claimed, now load the full User object for processing.
	$user = new User();
	$user->id = $userId;
	if (!$user->find(true)) {
		$cronLogEntry->numErrors++;
		$cronLogEntry->notes .= "<br/>Failed to load claimed user object for $userId. Skipping.";
		// Note: The timestamp remains set, will be retried later if needed.
		$errorCount++;
		continue;
	}

	$cronLogEntry->notes .= "<br/>Processing initial reading history for user: $user->displayName ($userId).";

	try {
		$catalog = $user->getCatalogDriver();

		if ($catalog) {
			if ($catalog->driver->hasNativeReadingHistory()) {
				$result = $catalog->driver->getReadingHistory($user, -1, -1, "checkedOut");
				if ($result['numTitles'] > 0) {
					$cronLogEntry->notes .= "<br/>Found {$result['numTitles']} titles to load for $user->displayName ($user->id).";

					foreach ($result['titles'] as $title) {
						$userReadingHistoryEntry = new ReadingHistoryEntry();
						$userReadingHistoryEntry->userId = $user->id;
						$userReadingHistoryEntry->groupedWorkPermanentId = $title['permanentId'] ?? null;
						$userReadingHistoryEntry->source = $catalog->accountProfile->recordSource;
						$userReadingHistoryEntry->sourceId = $title['sourceId'];
						$userReadingHistoryEntry->barcode = $title['barcode'] ?? null;
						$userReadingHistoryEntry->title = substr($title['title'], 0, 150);
						$userReadingHistoryEntry->author = substr($title['author'], 0, 75);
						$userReadingHistoryEntry->format = $title['format'];
						$userReadingHistoryEntry->checkOutDate = $title['checkout'];

						if (!empty($title['checkin'])) {
							$userReadingHistoryEntry->checkInDate = $title['checkin'];
						} else {
							$userReadingHistoryEntry->checkInDate = null;
						}

						if (empty($title['isIll'])) {
							$userReadingHistoryEntry->isIll = 0;
						} else {
							$userReadingHistoryEntry->isIll = 1;
						}

						$userReadingHistoryEntry->deleted = 0;
						if (!$userReadingHistoryEntry->insert()) {
							//$cronLogEntry->numErrors++;
							//$cronLogEntry->notes .= "<br/>Error inserting reading history entry for user $user->id: " . $userReadingHistoryEntry->getLastError();
							//$errorCount++;
						}
					}

				}

				// Mark that the initial reading history has been loaded and clear the timestamp.
				$updateSql = "
					UPDATE user
					SET initialReadingHistoryLoaded = 1,
					forceReadingHistoryLoad = 0,	-- initialReadingHistoryLoaded determines if it should be imported; this just determines when, so reset it.
					readingHistoryImportStartedAt = NULL
					WHERE id = :user_id
				";
				$updateStmt = $aspen_db->prepare($updateSql);
				$updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
				$updateStmt->execute();

				$loadedCount++;
				$cronLogEntry->notes .= "<br/>Successfully loaded initial reading history for $user->displayName ($user->id).";
			} else {
				// Mark the attempted load even if the ILS doesn't support it and clear timestamp.
				$updateSql = "
					UPDATE user
					SET initialReadingHistoryLoaded = 1,
					forceReadingHistoryLoad = 0,	-- initialReadingHistoryLoaded determines if it should be imported; this just determines when, so reset it.
					readingHistoryImportStartedAt = NULL
					WHERE id = :user_id
				";
				$updateStmt = $aspen_db->prepare($updateSql);
				$updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
				$updateStmt->execute();

				$loadedCount++;
				$cronLogEntry->notes .= "<br/>Marked user $user->id as having reading history loaded, ILS does not support native reading history.";
			}
		} else {
			$cronLogEntry->numErrors++;
			$cronLogEntry->notes .= "<br/>Could not get catalog driver for $user->displayName ($user->id).";
			$errorCount++;
		}
	} catch (Exception $e) {
		$cronLogEntry->numErrors++;
		$cronLogEntry->notes .= "<br/>Error loading reading history for $user->displayName ($user->id): " . $e->getMessage() . ".";
		$errorCount++;
	}

	$cronLogEntry->notes .= "<br/>Processed $loadedCount users so far, with $errorCount errors.";
}

$cronLogEntry->notes .= "<br/>Finished initial reading history load process. Processed $loadedCount users with $errorCount errors.";

$cronLogEntry->endTime = time();
$cronLogEntry->update();