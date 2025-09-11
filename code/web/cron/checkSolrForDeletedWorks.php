<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap_aspen.php';
require_once __DIR__ . '/../sys/SolrUtils.php';

require_once ROOT_DIR . '/sys/CronLogEntry.php';
$cronLogEntry = new CronLogEntry();
$cronLogEntry->startTime = time();
$cronLogEntry->name = 'Check Solr For Deleted Works';
$cronLogEntry->insert();

//Do a quick search to see how many results we have
/** @var SearchObject_AbstractGroupedWorkSearcher $searchObject */
$searchObject = SearchObjectFactory::initSearchObject();
$searchObject->init();
$searchObject->disableSpelling();
$searchObject->disableScoping();
$searchObject->disableBoosting();
$searchObject->disableDefaultAvailabilityToggle();
$searchObject->disableEditionLimiters();
$searchObject->setFieldsToReturn('id');
$searchObject->setLimit(1);
$solrConnection = $searchObject->getIndexEngine();
$result = $searchObject->processSearch();

$recordsToDeleteFromSolr = [];
$numRecordsDeleted = 0;
if (!$result instanceof AspenError && empty($result['error'])) {
	$numResults = $searchObject->getResultTotal();
	$cronLogEntry->notes .= date('h:i:s') . " There are $numResults records in Solr.<br/>";
	$solrBatchSize = 250;
	$searchObject->setTimeout(60);
	$searchObject->setLimit($solrBatchSize);
	$searchObject->clearFacets();
	$numBatches = (int)ceil($numResults / $solrBatchSize);
	$cronLogEntry->notes .= date('h:i:s') . " Processing in $numBatches batches.<br/>";
	for ($batchIndex = 1; $batchIndex <= $numBatches; $batchIndex++) {
		if ($batchIndex % 100 == 0) {
			$cronLogEntry->notes .= date('h:i:s') . " Processing batch $batchIndex.<br/>";
		}
		$cronLogEntry->lastUpdate = time();
		$cronLogEntry->update();
		$searchObject->setPage($batchIndex);
		$result = $searchObject->processSearch(true, false, false);
		if (!$result instanceof AspenError && empty($result['error'])) {
			$recordsInBatch = [];
			foreach ($result['response']['docs'] as $doc) {
				$recordsInBatch[] = $doc['id'];
			}
			//Check the database to see if the IDs exist.
			require_once ROOT_DIR . '/sys/Grouping/GroupedWork.php';
			$groupedWork = new GroupedWork();
			$groupedWork->selectAdd();
			$groupedWork->selectAdd('permanent_id');
			$groupedWork->whereAddIn('permanent_id', $recordsInBatch, true);
			$allResultsFromDB = $groupedWork->fetchAll('permanent_id', 'permanent_id');
			if (count($allResultsFromDB) != count($recordsInBatch)) {
				//Loop through to figure out which record(s) are missing
				foreach ($recordsInBatch as $groupedWorkId) {
					if (!isset($allResultsFromDB[$groupedWorkId])) {
						$cronLogEntry->notes .= date('h:i:s') . " $groupedWorkId does not exist in the database and needs to be deleted.<br/>";
						$recordsToDeleteFromSolr[] = $groupedWorkId;
						$cronLogEntry->update();
					}
				}
			}
		}
	}

	foreach ($recordsToDeleteFromSolr as $groupedWorkId) {
		if (!$solrConnection->deleteRecord($groupedWorkId)) {
			$cronLogEntry->notes .= date('h:i:s') . " ERROR $groupedWorkId could not be deleted.<br/>";
			$cronLogEntry->numErrors++;
		}else{
			$numRecordsDeleted++;
		}
	}
}else{
	$cronLogEntry->notes .= date('h:i:s') . " Could not connect to Solr.<br/>";
}
if ($numRecordsDeleted > 0) {
	$solrConnection->commit();
}

$cronLogEntry->notes .= date('h:i:s') . " Deleted $numRecordsDeleted records.";
$cronLogEntry->endTime = time();
$cronLogEntry->update();
