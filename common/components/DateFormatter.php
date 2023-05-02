<?php

namespace common\components;
use yii;

/*
	This meant to be a convenience class for formatting dates. Consider using this when displaying dates to 
	help keep dates and times consistent across the site.
	
*/

class DateFormatter
{
	const FORMAT_SHORT_DATE = "d-m-y";
	const FORMAT_ABBR_DATE = "j M, Y";
	
	const FORMAT_SHORT_TIME = "H:i a";
	const FORMAT_FULL_TIME = "H:i:s a";
	
	const FORMAT_SHORT_DATETIME = DateFormatter::FORMAT_SHORT_DATE . " " . DateFormatter::FORMAT_SHORT_TIME;
	const FORMAT_SHORT_DATETIME_W_TIMEZONE = DateFormatter::FORMAT_SHORT_DATETIME . " O";
	
	const FORMAT_ABBR_DATETIME = DateFormatter::FORMAT_ABBR_DATE . " " . DateFormatter::FORMAT_SHORT_TIME;
	const FORMAT_ABBR_DATETIME_W_TIMEZONE = DateFormatter::FORMAT_ABBR_DATETIME . " e";
	
	// shortDate()
	// Returns a date string of the format "dd-mm-yy"  (ie 15-03-19 )
	// If $include_title_text is true, it wraps the date text in a span, and includes
	// the full date and time ( dd-mm-yy hh:ii a +zzzz ) as a title attribute.
	
	public static function shortDate($timestamp, $include_title_text = true)
	{
		return DateFormatter::dateString($timestamp, DateFormatter::FORMAT_SHORT_DATE, $include_title_text ? DateFormatter::FORMAT_SHORT_DATETIME_W_TIMEZONE : null);
	}
	
	public static function shortDateTime($timestamp)
	{
		return DateFormatter::dateString($timestamp, FORMAT_SHORT_DATETIME);
	}
	
	public static function shortDateTimeWithTimezone($timestamp)
	{
		return DateFormatter::dateString($timestamp, FORMAT_SHORT_DATETIME . " e");
	}
	
	
	
	// abbreviatedDate()
	// Returns a date string of the format "d Mon, yyyy" (ie 1 Jan, 2019)
	// If $include_title_text is true, it wraps the date text in a span, and includes
	// the full date and time ( d Mon, yyyy hh:ii a eee ) as a title attribute.
	
	public static function abbreviatedDate($timestamp, $include_title_text = true)
	{
		return DateFormatter::dateString($timestamp, DateFormatter::FORMAT_ABBR_DATE, $include_title_text ? DateFormatter::FORMAT_ABBR_DATETIME_W_TIMEZONE : null);
	}
	
	public static function abbreviatedDateTime($timestamp)
	{
		return DateFormatter::dateString($timestamp, FORMAT_ABBR_DATETIME);
	}
	
	public static function abbreviatedDateTimeWithTimezone($timestamp)
	{
		return DateFormatter::dateString($timestamp, FORMAT_ABBR_DATETIME_W_TIMEZONE);
	}
	
	
	
	
	public static function dateString($timestamp, $dateFormat, $dateTimeFormat = null)
	{
		$date = date($dateFormat, $timestamp);
		
		if($dateTimeFormat != null)
		{
			$datetime = date($dateTimeFormat, $timestamp);
			return "<span title=\"$datetime\">$date</span>";
		}
		else
		{
			return $date;
		}
	}
}
	
?>