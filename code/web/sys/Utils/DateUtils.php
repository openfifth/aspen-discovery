<?php


class DateUtils {
	static function addDays($givendate, $day, $newDateFormat = 'Y-m-d H:i:s') {
		$cd = strtotime($givendate);
		$newdate = date($newDateFormat, mktime(date('H', $cd), date('i', $cd), date('s', $cd), date('m', $cd), date('d', $cd) + $day, date('Y', $cd)));
		return $newdate;
	}

	static function addMinutes($givendate, $minutes) {
		$cd = strtotime($givendate);
		$newdate = date('Y-m-d H:i:s', mktime(date('H', $cd), date('i', $cd) + $minutes, date('s', $cd), date('m', $cd), date('d', $cd), date('Y', $cd)));
		return $newdate;
	}

	static function formatTimeRange($startTime, $endTime, $format = '12') {
		if (empty($startTime) || empty($endTime)) {
			return '';
		}

		if ($startTime instanceof DateTime) {
			$startTimestamp = $startTime->getTimestamp();
		} elseif (is_numeric($startTime)) {
			$startTimestamp = (int)$startTime;
		} else {
			$startTimestamp = strtotime($startTime);
		}

		if ($endTime instanceof DateTime) {
			$endTimestamp = $endTime->getTimestamp();
		} elseif (is_numeric($endTime)) {
			$endTimestamp = (int)$endTime;
		} else {
			$endTimestamp = strtotime($endTime);
		}

		if ($startTimestamp === false || $startTimestamp === -1 ||
			$endTimestamp === false || $endTimestamp === -1) {
			return '';
		}

		if ($format === '24') {
			return date('H:i', $startTimestamp) . ' - ' . date('H:i', $endTimestamp);
		} else {
			$startHour = (int)date('G', $startTimestamp);
			$endHour = (int)date('G', $endTimestamp);
			$samePeriod = ($startHour < 12) === ($endHour < 12);

			if ($samePeriod) {
				return date('g:i', $startTimestamp) . ' - ' . date('g:i A', $endTimestamp);
			} else {
				return date('g:i A', $startTimestamp) . ' - ' . date('g:i A', $endTimestamp);
			}
		}
	}

	static function formatDateLocale($string, $dateStyle = 'medium', $timeStyle = 'none', $pattern = null) {
		global $activeLanguage;

		if (empty($string) || $string === '0000-00-00' || $string === '0000-00-00 00:00:00') {
			return '';
		}

		if ($string instanceof DateTime) {
			$timestamp = $string->getTimestamp();
		} elseif (is_numeric($string)) {
			$timestamp = (int)$string;
		} else {
			$timestamp = strtotime($string);
		}

		if ($timestamp === false || $timestamp === -1) {
			return '';
		}

		$dateStyleMap = [
			'none'   => IntlDateFormatter::NONE,
			'short'  => IntlDateFormatter::SHORT,
			'medium' => IntlDateFormatter::MEDIUM,
			'long'   => IntlDateFormatter::LONG,
			'full'   => IntlDateFormatter::FULL,
		];

		$timeStyleMap = [
			'none'   => IntlDateFormatter::NONE,
			'short'  => IntlDateFormatter::SHORT,
			'medium' => IntlDateFormatter::MEDIUM,
			'long'   => IntlDateFormatter::LONG,
			'full'   => IntlDateFormatter::FULL,
		];

		$dateStyleConstant = $dateStyleMap[strtolower($dateStyle)] ?? IntlDateFormatter::MEDIUM;
		$timeStyleConstant = $timeStyleMap[strtolower($timeStyle)] ?? IntlDateFormatter::NONE;

		$locale = $activeLanguage->locale ?? 'en_US';
		$timezone = date_default_timezone_get();

		$formatter = new IntlDateFormatter(
			$locale,
			$dateStyleConstant,
			$timeStyleConstant,
			$timezone
		);

		if ($pattern !== null) {
			$formatter->setPattern($pattern);
		}

		return $formatter->format($timestamp);
	}
}
