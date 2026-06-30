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

	static function formatHumanDate(string $date): string {
		$dt = DateTimeImmutable::createFromFormat('Y-m-d', $date);
		if (!$dt) {
			return $date;
		}
		return self::formatDate($dt) ?: $date;
	}

	static function formatHumanDateTime(string $dateTime): string {
		$dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateTime);
		if (!$dt) {
			return $dateTime;
		}
		return self::formatDateTime($dt) ?: $dateTime;
	}

	static function formatDate(DateTimeImmutable $date): string|false {
		global $locale;
		$formatter = new IntlDateFormatter(self::formatLocale($locale), IntlDateFormatter::FULL, IntlDateFormatter::NONE);
		return $formatter->format($date);
	}

	static function formatDateTime(DateTimeImmutable $dateTime): string|false {
		global $locale;
		$formatter = new IntlDateFormatter(self::formatLocale($locale), IntlDateFormatter::FULL, IntlDateFormatter::SHORT);
		return $formatter->format($dateTime);
	}

	static function formatLocale(?string $locale): string {
		if (!$locale) {
			return 'en_US';
		}
		return str_replace('-', '_', $locale);
	}

	static function formatDateLocale($string, $dateStyle = 'medium', $timeStyle = 'none', $pattern = null, $skeleton = null): string|false {
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

		if ($skeleton !== null) {
			$pattern = (new IntlDatePatternGenerator($locale))->getBestPattern($skeleton);
		}

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