<?php

class OCLCRSFGRequest extends DataObject {
	public $__table = 'user_oclc_resource_sharing_for_groups_request';
	public $id;
	public $oclcRequesterRegistryId;
	public $oclcRequestId;
	public $requestStatus;
	public $requestStatusDescription;
	public $createdDate;
	public $verification; // specifies the request comes from an Aspen Discovery site
	public $needed;
	public $serviceType;
	public $userId;
	public $email;
	public $isbn;
	public $issn;
	public $oclcNumber;
	public $mediaType;
	public $title;
	public $author;
	public $edition;
	public $publisher;
	public $language;
	public $feeAccepted;
	public $maximumFeeAmount;
	public $catalogKey;
	public $note;
	public $pickupLocation;
	public $datePlaced;
}
