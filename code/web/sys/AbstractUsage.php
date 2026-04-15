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
		$periodStart = $custom['customUsagePeriodStart'] ?? '';
		$periodDuration = (int) ($custom['customUsagePeriodDuration'] ?? 0);

		if (strtotime($periodStart) === false || $periodDuration <= 0) {
			throw new InvalidArgumentException('buildCustomPeriodQuery: invalid start date or non-positive duration');
		}

		$escapedPeriodStart = $this->escape($periodStart);
		$escapedPeriodDuration = $this->escape($periodDuration);

		$selectPeriod = "CONCAT("
			. "DATE_FORMAT(DATE_ADD($escapedPeriodStart, INTERVAL FLOOR(DATEDIFF(STR_TO_DATE(CONCAT(year, '-', month, '-', day), '%Y-%m-%d'), $escapedPeriodStart) / $escapedPeriodDuration) * $escapedPeriodDuration DAY), '%d/%m/%y'),"
			. "' - ',"
			. "DATE_FORMAT(DATE_ADD($escapedPeriodStart, INTERVAL (FLOOR(DATEDIFF(STR_TO_DATE(CONCAT(year, '-', month, '-', day), '%Y-%m-%d'), $escapedPeriodStart) / $escapedPeriodDuration) * $escapedPeriodDuration + ($escapedPeriodDuration - 1)) DAY), '%d/%m/%y')"
			. ") AS period";

		$this->selectAdd($selectPeriod);
		$this->whereAdd(
			"STR_TO_DATE(CONCAT(year, '-', LPAD(month, 2, '0'), '-', LPAD(day, 2, '0')), '%Y-%m-%d') >= $escapedPeriodStart"
		);
		$this->groupBy('period');
		$this->orderBy(['year', 'month', 'day']);
	}
}