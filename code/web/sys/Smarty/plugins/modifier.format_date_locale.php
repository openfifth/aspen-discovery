<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifier
 */
/**
 * Smarty format_date_locale modifier plugin
 * Type:     modifier
 * Name:     format_date_locale
 * Purpose:  format dates using locale-aware formatting via IntlDateFormatter
 * Input:
 *          - string: input date string or timestamp
 *          - style: date style (short, medium, long, full)
 *          - time_style: time style (none, short, medium, long, full)
 *
 * @param mixed  $string     input date string or timestamp
 * @param string $style      date style (short, medium, long, full)
 * @param string $time_style time style (none, short, medium, long, full)
 *
 * @return string formatted date
 */
function smarty_modifier_format_date_locale($string, $style = 'medium', $time_style = 'none')
{
	global $activeLanguage;

	if (empty($string) || $string === '0000-00-00' || $string === '0000-00-00 00:00:00') {
		return '';
	}

	// Convert input to timestamp
	if (is_numeric($string)) {
		$timestamp = (int)$string;
	} else {
		$timestamp = strtotime($string);
	}

	if ($timestamp === false || $timestamp === -1) {
		return '';
	}

	// Map style strings to IntlDateFormatter constants
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

	// Get style constants
	$dateStyle = $dateStyleMap[strtolower($style)] ?? IntlDateFormatter::MEDIUM;
	$timeStyle = $timeStyleMap[strtolower($time_style)] ?? IntlDateFormatter::NONE;

	// Get locale from active language, fallback to system default
	$locale = $activeLanguage->locale ?? 'en_US';

	// Get timezone
	$timezone = date_default_timezone_get();

	// Create formatter
	$formatter = new IntlDateFormatter(
		$locale,
		$dateStyle,
		$timeStyle,
		$timezone
	);

	return $formatter->format($timestamp);
}
