<?php

class Pay360RequestParameter extends DataObject {
	public $__table = 'pay360_request_parameter';
	public $id;
	public $name;
	public $multiline;
	public $optional;
	public $defaultValue;

	static function getPay360UrlParamFields() {
		$urlParamsArr = [];
		$urlParam = new Pay360RequestParameter();
		$urlParam = $urlParam->fetchAll();
		
		foreach($urlParam as $param) {
			$urlParamsArr[] = [
				'id' => $param->id,
				'property' => $param->name,
				'type' => 'section',
				'label' => $param->name,
				'description' => '',
				'maxLength' => 10,
				'properties' => [],
			];
		}
		return $urlParamsArr;
	}
}
