<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */



class utils
{

	function _do_sanitise_magic_quotes(&$arr)
	{
	    foreach($arr as $k=>$v)
	    {
		$arr[$k] = stripslashes($v);
	    }		
	}

	function entry_state_string($code)
	{
		$arr = array("active", "marked for deletion", "being written", "old version");
		if (isset($arr[$code]))
			return $arr[$code];
		else
			return "unknown";
	}

	function sanitise_magic_quotes()
	{
	    $mq = ini_get('magic_quotes_gpc');
	    if ($mq)
	    {
		utils::_do_sanitise_magic_quotes($_REQUEST);
		utils::_do_sanitise_magic_quotes($_GET);
		utils::_do_sanitise_magic_quotes($_POST);
	    }
	}

	function debug_array($data)
	{
		foreach($data as $k => $ds)
		{
			utils::pre_dump($k);
		}
	}

	function redirect($url)
	{
		header("location:$url");
		exit(0);
	}

	function plurify($v, $word)
	{
		if ($v != 1)
			return $word."s";
		else
			return $word;
	}

	function putget_error_str($code)
	{
		$errs = array(	"success",
						"invalid key",
						"shared memory full",
						"file store full",
						"item expired",
						"item not found",
						"corrupt file",
						"corrupt item",
						"wrong argument count",
						"bad location",
						"unable to create directory",
						"access denied",
						"write error",
						"insufficent shared memory to create a new domain", 
						"invalid path",
						"put/get API disabled");
		if (isset($errs[$code]))
			return $errs[$code];
		else
			return "unknown error";
	}


	function pre_dump($var)
	{
		echo("<pre>");
		var_dump($var);
		echo("</pre>");
	}

	function path_ellipsis($path)
	{
		$removed = 0;
		$max = @prefs::get_val("max_path_components");
		$comps_to_hide = @prefs::get_val("components_to_hide");

		if (empty($path))
		return $path;
		$is_absolute_unix = ($path[0] == '/');
		if ($is_absolute_unix)
		{
		    $path = substr($path, 1);
		    }

		$components = explode(DIRECTORY_SEPARATOR, $path);
		if ($comps_to_hide)
		{
			$removed = 1;
			$components = array_slice($components, $comps_to_hide);
		}
		if ($max && count($components) > $max)
		{
			$removed = 1;
			$to_remove = count($components) - $max;
			$components = array_slice($components, $to_remove);
		}
		
		if ($removed)
			$ret = "...".DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $components);
		else
		{
		    if ($is_absolute_unix)
		    $ret = "/$path";
		    else
			$ret = $path;
			}

		return $ret;
	}
}

?>
