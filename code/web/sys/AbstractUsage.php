<?php /** @noinspection PhpMissingFieldTypeInspection */

abstract class AbstractUsage extends DataObject {
	public function getCurPeriod($timeframes) {
		if (in_array('day', $timeframes)) {
			return "{$this->day}-{$this->month}-{$this->year}";
		}
		if (in_array('month', $timeframes)) {
			return "{$this->month}-{$this->year}";
		}
		if (in_array('year', $timeframes)) {
			return "{$this->year}";
		}
		return "{$this->month}-{$this->year}"; // monthly is the default
	}
}