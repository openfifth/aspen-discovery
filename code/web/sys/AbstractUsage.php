<?php /** @noinspection PhpMissingFieldTypeInspection */

abstract class AbstractUsage extends DataObject {
	public function getCurPeriod($timeframe) {
		if ($timeframe == 'day') {
			return "{$this->day}-{$this->month}-{$this->year}";
		}
		if ($timeframe == 'month') {
			return "{$this->month}-{$this->year}";
		}
		if ($timeframe == 'year') {
			return "{$this->year}";
		}
		return "{$this->month}-{$this->year}"; // monthly is the default
	}
}