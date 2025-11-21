<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifier
 */
/**
 * Smarty format_time_range_locale modifier plugin
 * Type:     modifier
 * Name:     format_time_range_locale
 * Purpose:  format time ranges using locale-aware formatting, avoiding redundant AM/PM
 * Input:
 *          - start_time: start time (DateTime object, string, or timestamp)
 *          - end_time: end time (DateTime object, string, or timestamp)
 *          - format: '12' for 12-hour format with AM/PM (default), '24' for 24-hour format
 *
 * @param mixed  $start_time start time (DateTime object, string, or timestamp)
 * @param mixed  $end_time   end time (DateTime object, string, or timestamp)
 * @param string $format     time format ('12' or '24')
 *
 * @return string formatted time range
 */
function smarty_modifier_format_time_range_locale($start_time, $end_time, $format = '12')
{
	require_once ROOT_DIR . '/sys/Utils/DateUtils.php';
	return DateUtils::formatTimeRange($start_time, $end_time, $format);
}
