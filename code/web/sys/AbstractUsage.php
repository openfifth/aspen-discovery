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
	public function buildCustomPeriodQuery(array $custom): void {
		$escapedPeriodDuration = $this->escape($custom['customUsagePeriodDuration']);
		$escapedPeriodStart = $this->escape($custom['customUsagePeriodStart']);
    	$selectPeriod ="CONCAT(DATE_FORMAT(DATE_ADD($escapedPeriodStart, INTERVAL FLOOR(DATEDIFF(STR_TO_DATE(CONCAT(year, '-', month, '-', day), '%Y-%m-%d'), $escapedPeriodStart) / $escapedPeriodDuration) * $escapedPeriodDuration DAY), '%d/%m/%y'),' - ',DATE_FORMAT(DATE_ADD($escapedPeriodStart, INTERVAL (FLOOR(DATEDIFF(STR_TO_DATE(CONCAT(year, '-', month, '-', day), '%Y-%m-%d'), $escapedPeriodStart) / $escapedPeriodDuration) * $escapedPeriodDuration + ($escapedPeriodDuration - 1)) DAY), '%d/%m/%y')) AS period";
		$customPeriodStartYear = date('Y', strtotime($custom['customUsagePeriodStart']));
		$customPeriodStartMonth = date('m', strtotime($custom['customUsagePeriodStart']));
		$customPeriodStartDay = date('d', strtotime($custom['customUsagePeriodStart']));
		$this->selectAdd($selectPeriod);
		$condition = 'year > ' .
			$customPeriodStartYear .
			' OR (year = ' .
			$customPeriodStartYear .
			' AND month >= ' .
			$customPeriodStartMonth .
			')' .
			' OR (year = ' .
			$customPeriodStartYear .
			' AND month = ' .
			$customPeriodStartMonth .
			' AND day >= ' .
			$customPeriodStartDay .
			')';
		$this->whereAdd($condition);
		$this->groupBy('period');
		$this->orderBy(['year', 'month', 'day']);
	}
}