<?php
require_once ROOT_DIR . '/RecordDrivers/RecordInterface.php';

class BmjBpRecordDriver extends RecordInterface {
	private $record;
	/**
	 * @param array|File_MARC_Record|string Record data to construct the driver from
	 * @access  public
	 */
	public function __construct($record) {
		$this->record = $record;
	}

	public function getBmjBpRecordId(): string {
		if (!isset($this->record['id'])) {
			return '';
		}
		return $this->record['id'];
	}

	public function getBmjBpRecordUrl(): string {
		if (!isset($this->record['id'])) {
			return '';
		}
		return "https://bestpractice.bmj.com/topics/en-gb/" . $this->record['id'];
	}

	public function getCombinedResult(): string {
		global $interface;
		
		$interface->assign('summId', $this->getBmjBpRecordId());
		$interface->assign('module', $this->getModule());
		$interface->assign('summFormats', $this->getFormats());
		$interface->assign('summUrl', $this->getBmjBpRecordUrl());
		$interface->assign('summTitle', $this->getTitle());
		$interface->assign('summDescription', $this->getDescription());

		return 'RecordDrivers/BmjBp/combinedResult.tpl';
	}

	public function getDescription(): string {
		if(!isset($this->record['highlight'])) {
			return '';
		} 
		return $this->record['highlight'];
	}

	public function getFormats(): string {
		if(isset($this->record['ContentType'][0])){
			return (string)$this->record['ContentType'][0];
		}
		$sourceType = 'Unknown Source';
		return $sourceType;
	}

	public function getModule(): string {
		return 'BmjBp';
	}

	public function getSearchResult($view = 'list', $showListsAppearingOn = true) {
		global $interface;
		$interface->assign('summId', $this->getBmjBpRecordId());
		$interface->assign('summUrl', $this->getBmjBpRecordUrl());
		$interface->assign('summTitle', $this->getTitle());
		$interface->assign('summDescription', $this->getDescription());
		return 'RecordDrivers/BmjBp/result.tpl';
	}

	public function getTitle(): string {
		if (!isset($this->record['title'])) {
			return '';
		}
		return $this->record['title'];;
	}

	/** required by RecordInterface, irrelevant to BMJ BP POC */
	public function getBookcoverUrl($size='large', $absolutePath = false) {}
	public function getStaffView() {} // only relevant if there is a detail view page
	public function getMoreDetailsOptions() {}
	public function getUniqueID() {} // irrelevant as it only pertains to Solr
}