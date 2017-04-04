<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

define("PREFS_PATH", dirname(__FILE__)."/../prefs/admin.txt");


//Models the preferences file for this web app

class prefs
{
	//API:
	/* static */ function get_val($key)
	{
		$the_prefs = &prefs::_instance();
		return @$the_prefs->_vals[$key];
	}

	/* static */ function get_all_vals()
	{
		$the_prefs = &prefs::_instance();
		return $the_prefs->_vals;
	}

	/* static */ function save_vals($vals)
	{
		$the_prefs = &prefs::_instance();
		$the_prefs->_vals = $vals;
		return $the_prefs->_save();
	}

	//Rest is implementation

	function prefs()
	{
		$this->_load();
	}

	function &_instance()
	{
		static $the_prefs;
		if (!isset($the_prefs))
			$the_prefs = & new prefs;
		return $the_prefs;
	}

	var $_vals;

	function _load_line($line)
	{
		$pos = strpos($line, "=");
		$key = trim(substr($line, 0, $pos));
		$val = trim(substr($line, $pos+1));
		if (!empty($key))
			$this->_vals[$key] = $val;
	}

	function _load()
	{
		$this->_vals = array(	'max_path_components'=>5,
								'max_scripts_per_page'=>30,
								'auto_refresh'=>30,
								'components_to_hide'=>'');

		if (file_exists(PREFS_PATH))
		{
			$contents = @file_get_contents(PREFS_PATH);
			if (!empty($contents))
			{
				$lines = explode("\n", $contents);
				foreach($lines as $l)
					$this->_load_line($l);
			}
		}
	}

	function _save()
	{
		$content = "";
		foreach($this->_vals as $k => $v)
		{
			$content.="$k=$v\r\n";
		}
		return @file_put_contents(PREFS_PATH, $content);
	}
}

?>