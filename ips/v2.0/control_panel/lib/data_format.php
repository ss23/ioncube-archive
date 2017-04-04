<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

define("DF_STRING", 1);
define("DF_SIZE",	2);
define("DF_DATE",	3);
define("DF_TIME",	4);
define("DF_SIZE_DECIMAL",	5);
define("DF_BIGSIZE",	6);
define("DF_BIGSIZE_DECIMAL",	7);
define("DF_BOOL",	8);


class data_format
{
	function format_data($d, $f)
	{
		if ($f === null)
			$f = DF_STRING;
		$ret = "";
		switch($f)
		{
			case DF_STRING:
				$ret = $d;
				break;
			case DF_SIZE:
				$ret = data_format::pretty_size($d, 1);
				break;
			case DF_BIGSIZE:
				$ret = data_format::pretty_big_size($d, 1);
				break;
			case DF_SIZE_DECIMAL:
				$ret = data_format::pretty_size_decimal($d, 1);
				break;
			case DF_BIGSIZE_DECIMAL:
				$ret = data_format::pretty_bigsize_decimal($d, 1);
				break;
			case DF_DATE:
				$ret = data_format::pretty_date($d);
				break;
			case DF_TIME:
				$ret = data_format::pretty_time($d);
				break;
			case DF_BOOL:
				$ret = data_format::pretty_boolean($d);
				break;
			default:
				trigger_error("Unknown data format: $f");
				$ret = $d;
				break;
		}
		return $ret;
	}

	function parse_integer($s)
	{
		$s = trim($s);
		$s = strtoupper($s);
		if (empty($s) || $s == "UNLIMITED")
			return 0;

		if (!is_numeric($s))
			return null;
		return $s;
	}

	function parse_time($s)
	{
		$s = trim($s);
		$bits = explode(":", $s);
		if (!is_array($bits) || count($bits) != 2)
			return null;
		$mins = trim($bits[1]);
		$hours = trim($bits[0]);
		if (!is_numeric($hours) || !is_numeric($mins))
			return null;
		if ($mins < 0 || $mins > 59)
			return null;
		if ($hours < 0 || $hours > 23)
			return null;
		return ($hours * 60 * 60 + $mins * 60);
	}

	function parse_period($s)
	{
		$s = trim($s);
		$s = strtolower($s);
		$len = strlen($s);
		if (empty($s))
			return null;
		$c = $s[$len-1];

		--$len;
		$mult = 1;
		if ($c == "d")
			$mult = 60 * 60 * 24;
		else if ($c == "h")
			$mult = 60 * 60;
		else if ($c == "m")
			$mult = 60;
		else if ($c == "s")
			$mult = 1;
		else 
			++$len;
		$num = trim(substr($s, 0, $len));
		if (!is_numeric($num))
			return null;
		$num *= $mult;
		return $num;
	}

	function parse_size($s)
	{
		$s = trim($s);
		$s = strtoupper($s);
		if (empty($s) || $s == "UNLIMITED")
			return 0;

		$len = strlen($s);
		if (empty($s))
			return null;
		$c = $s[$len-1];
		if ($c == "B")
		{
			if ($len == 1)
				return null;
			--$len;
			$c = $s[$len-1];
		}
		--$len;
		$mult = 1;
		if ($c == "G")
			$mult = 1024 * 1024 * 1024;
		else if ($c == "M")
			$mult = 1024 * 1024;
		else if ($c == "K")
			$mult = 1024;
		else 
			++$len;
		$num = trim(substr($s, 0, $len));
		if (!is_numeric($num))
			return null;
		$num *= $mult;
		return $num;
	}

	function parse_big_size($s)
	{
		$s = trim($s);
		$s = strtoupper($s);
		if (empty($s) || $s == "UNLIMITED")
			return 0;
		
		$len = strlen($s);
		if (empty($s))
			return null;
		$c = $s[$len-1];
		if ($c == "B")
		{
			if ($len == 1)
				return null;
			--$len;
			$c = $s[$len-1];
		}
		--$len;
		$mult = 1;
		if ($c == "P")
			$mult = 1024 * 1024 * 1024;
		else if ($c == "T")
			$mult = 1024 * 1024;
		else if ($c == "G")
			$mult = 1024;
		else if ($c == "M")
			$mult = 1;
		else 
			++$len;
		$num = trim(substr($s, 0, $len));
		if (!is_numeric($num))
			return null;
		$num *= $mult;
		return $num;
	}

	function pretty_boolean($s)
	{
		return $s?"yes":"no";
	}

	function pretty_time($s)
	{
		$min = 60;
		$hour = $min * 60;
		$day = $hour * 24;
		if ($s % $day == 0)
			return ($s / $day)."d";
		else if ($s % $hour == 0)
			return ($s / $hour)."h";
		else if ($s % $min == 0)
			return ($s / $min)."m";
		else
			return $s."s";
	}

	function pretty_time_full($s)
	{
		$min = 60;
		$hour = $min * 60;
		$day = $hour * 24;

		$days = (int)($s / $day);
		$r = $s - $days * $day;
		$hours = (int)($r / $hour);
		$r -= $hours * $hour;
		$mins = (int)($r / $min);
		$r -= $mins * $min;
		$secs = $r;
		$arr = array();
		if ($days)
			$arr[] = "${days} ".utils::plurify($days, "day");
		if ($hours)
			$arr[] = "${hours} ".utils::plurify($hours, "hour");
		if ($mins)
			$arr[] = "${mins} ".utils::plurify($mins, "min");
		
		$arr[] = "${secs} ".utils::plurify($secs, "sec");

		$ret = implode(", ", $arr);
		return $ret;
	}

	function pretty_hours_mins($s)
	{
		$mins = (int)($s / 60);
		$secs = $s % 60;
		$val = sprintf("%02d:%02d", $mins, $secs);
		return $val;
	}

	function pretty_size($s, $show_bytes = 1)
	{
		if ($s === null)
			return null;

		$KB = 1024;
		$MB = $KB * 1024;
		$GB = $MB * 1024;
		
		if ($s == 0)
		{
			if ($show_bytes)
				return $s." bytes";
			else
				return $s;
		}
		else if ($s % $GB == 0)
			return ($s / $GB)." GB";
		else if ($s % $MB == 0)
			return ($s / $MB)." MB";
		else if ($s % $KB == 0)
			return ($s / $KB)." KB";
		else if ($show_bytes)
			return $s." bytes";
		else
			return $s;
	}

	function pretty_bigsize($s, $show_mb = 1)
	{
		if ($s === null)
			return null;

		$GB = 1024;
		$TB = $GB * 1024;
		$PB = $TB * 1024;
		
		if ($s == 0)
		{
			if ($show_mb)
				return "0 bytes";
			else
				return $s;
		}
		else if ($s % $PB == 0)
			return ($s / $PB)." PB";
		else if ($s % $TB == 0)
			return ($s / $TB)." TB";
		else if ($s % $GB == 0)
			return ($s / $GB)." GB";
		else
			return $s." MB";
	}
	
	function pretty_size_decimal($s, $show_bytes = 1)
	{
		$KB = 1024;
		$MB = $KB * 1024;
		$GB = $MB * 1024;

		$dp = 1;
		
		if ($s == 0)
		{
			if ($show_bytes)
				return $s." bytes";
			else
				return $s;
		}
		else if ($s / $GB >=1 )
			return sprintf("%.".$dp."f", $s / $GB)." GB";
		else if ($s / $MB >=1)
			return sprintf("%.".$dp."f", $s / $MB)." MB";
		else if ($s / $KB >= 1)
			return sprintf("%.".$dp."f", $s / $KB)." KB";
		else if ($show_bytes)
			return $s." bytes";
		else
			return $s;
	}

	function pretty_bigsize_decimal($s, $show_mb = 1)
	{
		$GB = 1024;
		$TB = $GB * 1024;
		$PB = $TB * 1024;

		$dp = 1;  
		if ($s == 0)
		{
			if ($show_mb)
				return "0 bytes";
			else
				return $s;
		}
		else if ($s / $PB >= 1 )
			return sprintf("%.".$dp."f", $s / $PB)." PB";
		else if ($s / $TB >= 1)
			return sprintf("%.".$dp."f", $s / $TB)." TB";
		else if ($s / $GB >= 1)
			return sprintf("%.".$dp."f", $s / $GB)." GB";
		else if ($s >= 1)
			return sprintf("%.".$dp."f", $s / 1)." MB";
		else//$s may be floating point
			return sprintf("%.".$dp."f", $s * 1024)." KB";
		
	}

	function pretty_date($s, $default = "")
	{
		if ($s == 0)
			return $default;
		return date("Y/n/j H:i:s", $s);
	}
}

?>