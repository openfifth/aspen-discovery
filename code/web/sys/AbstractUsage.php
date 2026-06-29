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
	public function getCustomPeriod(): string {
		require_once ROOT_DIR . '/sys/Utils/DateUtils.php';
		return DateUtils::formatDateLocale($this->periodStart, 'short') . ' - ' . DateUtils::formatDateLocale($this->periodEnd, 'short');
	}
	public function buildCustomPeriodQuery(array $custom): void {
		$escapedPeriodDuration = $this->escape($custom['customUsagePeriodDuration']);
		$escapedPeriodStart = $this->escape($custom['customUsagePeriodStart']);
		$selectPeriodStart = "DATE_FORMAT(DATE_ADD($escapedPeriodStart, INTERVAL FLOOR(DATEDIFF(STR_TO_DATE(CONCAT(year, '-', month, '-', day), '%Y-%m-%d'), $escapedPeriodStart) / $escapedPeriodDuration) * $escapedPeriodDuration DAY), '%Y-%m-%d') AS periodStart";
		$selectPeriodEnd = "DATE_FORMAT(DATE_ADD($escapedPeriodStart, INTERVAL (FLOOR(DATEDIFF(STR_TO_DATE(CONCAT(year, '-', month, '-', day), '%Y-%m-%d'), $escapedPeriodStart) / $escapedPeriodDuration) * $escapedPeriodDuration + ($escapedPeriodDuration - 1)) DAY), '%Y-%m-%d') AS periodEnd";
		$customPeriodStartYear = date('Y', strtotime($custom['customUsagePeriodStart']));
		$customPeriodStartMonth = date('m', strtotime($custom['customUsagePeriodStart']));
		$customPeriodStartDay = date('d', strtotime($custom['customUsagePeriodStart']));
		$this->selectAdd($selectPeriodStart);
		$this->selectAdd($selectPeriodEnd);
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
		$this->groupBy('periodStart');
		$this->orderBy(['year', 'month', 'day']);
	}
}