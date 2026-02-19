<?php

class AspenMobileSetting extends DataObject {
	public $__table = 'aspen_mobile_settings';
	public $id;
	public $name;
	public $shortName;
	public $description;
	public $themeId;
	public $autoRotateCard;
	public $enableSelfRegistration;
	public $showMoreInfoBtn;
	public $manifestID;
	public $startURL;
	public $slug;
	public $sha256CertFingerprint;
	public $firebaseAPIKey;
	public $firebaseAuthDomain;
	public $firebaseProjectID;
	public $firebaseStorageBucket;
	public $firebaseMessagingSenderID;
	public $firebaseAppID;
	public $firebaseMeasurementID;
	public $vapidKey;
	public $serviceAccount;

	private $_libraries;

	function getEncryptedFieldNames(): array {
		return ['serviceAccount'];
	}

	static function getObjectStructure($context = ''): array {

		require_once ROOT_DIR . '/sys/Theming/Theme.php';
		$theme = new Theme();
		$availableThemes = [];
		$theme->orderBy('themeName');
		$theme->find();
		while ($theme->fetch()) {
			$availableThemes[$theme->id] = $theme->themeName;
		}
		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'name' => [
				'property' => 'name',
				'type' => 'text',
				'label' => 'Name',
				'description' => 'The name to use in the app manifest',
				'maxLength' => 50,
				'required' => true,
				'default' => 'Aspen Mobile'
			],
			'shortName' => [
				'property' => 'shortName',
				'type' => 'text',
				'label' => 'Short Name',
				'description' => 'The short_name to use in the app manifest',
				'maxLength' => 50,
				'required' => true,
				'default' => 'Aspen Mobile'
			],
			'description' => [
				'property' => 'description',
				'type' => 'text',
				'label' => 'Description',
				'description' => 'The description to use in the app manifest',
				'maxLength' => 200,
				'required' => true,
				'default' => 'A progressive web application for Aspen Discovery'
			],
			'themeId' => [
				'property' => 'themeId',
				'type' => 'enum',
				'label' => 'Theme',
				'values' => $availableThemes,
				'description' => 'The theme which should be used for the application',
			],
			'manifestID' => [
				'property' => 'manifestID',
				'type' => 'text',
				'label' => 'Manifest ID',
				'description' => 'A Unique identifier for your web application: https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Manifest/Reference/id',
				'maxLength' => 50,
				'required' => true,
			],
			'startURL' => [
				'property' => 'startURL',
				'type' => 'text',
				'label' => 'Start URL',
				'description' => 'URL for the application to start at.',
				'maxLength' => 50,
				'required' => true,
				'default' => '/',
			],
			'slug' => [
				'property' => 'slug',
				'type' => 'text',
				'label' => 'Slug',
				'description' => 'slug to identify the application',
				'maxLength' => 50,
				'required' => true,
			],
			'sha256CertFingerprint' => [
				'property' => 'sha256CertFingerprint',
				'type' => 'text',
				'label' => 'Sha 256 Cert Fingerprint',
				'description' => 'Provided by Google Play after initial upload; proves that App and the website are authorized. (https://support.google.com/googleplay/android-developer/answer/16641489?hl=en)',
				'maxLength' => 200,
				'default' => strtoupper(implode(":", str_split(hash('sha256', "REPLACE ME"), 2))),
				'required' => true,
			],
			'firebaseAPIKey' => [
				'property' => 'firebaseAPIKey',
				'type' => 'text',
				'label' => 'Firebase API Key',
				'description' => 'API key generated from within Firebase. (withing your firebase project on google cloud > APIs & Services > Credentials',
				'maxLength' => 50,
				'required' => true,
			],
			'firebaseAuthDomain' => [
				'property' => 'firebaseAuthDomain',
				'type' => 'text',
				'label' => 'Firebase Authorization Domain',
				'description' => 'description here',
				'maxLength' => 50,
				'required' => true,
			],
			'firebaseProjectID' => [
				'property' => 'firebaseProjectID',
				'type' => 'text',
				'label' => 'Firebase project ID',
				'description' => 'description here',
				'maxLength' => 50,
				'required' => true,

			],
			'firebaseStorageBucket' => [
				'property' => 'firebaseStorageBucket',
				'type' => 'text',
				'label' => 'Firebase Storage Bucket',
				'description' => 'URL for firebase Storage',
				'maxLength' => 50,
				'required' => true,

			],
			'firebaseMessagingSenderID' => [
				'property' => 'firebaseMessagingSenderID',
				'type' => 'text',
				'label' => 'Firebase Messaging Sender ID',
				'description' => 'Sender ID in the Cloud Messaging tab of your Firebase Project. Should be the same as the Project Number.',
				'maxLength' => 50,
				'required' => true,

			],
			'firebaseAppID' => [
				'property' => 'firebaseAppID',
				'type' => 'text',
				'label' => 'Firebase application ID',
				'description' => 'description here',
				'maxLength' => 50,
				'required' => true,

			],
			'firebaseMeasurementID' => [
				'property' => 'firebaseMeasurementID',
				'type' => 'text',
				'label' => 'Firebase Measurement ID',
				'description' => 'description here',
				'maxLength' => 50,
				'required' => true,

			],
			'vapidKey' => [
				'property' => 'vapidKey',
				'type' => 'text',
				'label' => 'Vapid Key',
				'description' => 'description here',
				'maxLength' => 100,
				'required' => true,

			],
			'serviceAccount' => [
				'property' => 'serviceAccount',
				'type' => 'storedPassword',
				'label' => 'Service Account',
				'description' => 'Contents of your Service Account json file from firebase. Service Accounts > Generate new private key > copy contents of the downloaded file into this field.',
				'maxLength' => 5000,
				'required' => false,
			]
		];

		return $structure;
	}

	function getFirebaseSettings(){
		return [
			'apiKey' => $this->firebaseAPIKey,
			'authDomain' =>$this->firebaseAuthDomain,
			'projectId' => $this->firebaseProjectID,
			'storageBucket' => $this->firebaseStorageBucket,
			'messagingSenderId' => $this->firebaseMessagingSenderID,
			'appId' => $this->firebaseAppID,
			'measurementId' => $this->firebaseMeasurementID,
			'vapidKey' => $this->vapidKey
		];
	}

	function getServiceAccount() {
		return json_decode($this->serviceAccount, true);
	}
}
?>