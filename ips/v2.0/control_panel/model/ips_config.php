<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */


/* API:

class config
{
	function config($domain);
	function save();
	function add_ignore_pattern($pattern, $ignore);
	function reset_ignore_patterns();
	function set_bool($key, $val);		//does some translation
	function set_val($key, $val);

	//Take into account nulls when setting the default domain settings
	function set_domain_bool($key, $val);
	function set_domain_long($key, $val);

	static function global_val($key);
}

*/

class config
{
	var $_vals;
	var $_initial_ignore_list;
	var $_domain;

	function config($domain = null)
	{
		if (empty($domain) || strcasecmp($domain, "global") == 0)
		{
			$this->_vals = ips_get_config();
			$this->_domain = null;
		}
		else
		{
			$this->_vals = ips_get_config($domain);
			$this->_domain = $domain;
		}

		$this->_initial_ignore_list = $this->_vals['ignore'];
	}

	/* static */ function global_val($key)
	{
		$config = ips_get_config();
		return $config[$key];
	}

	function _unset_bad_vals()
	{
		unset($this->_vals['domain_name']);
		unset($this->_vals['home']);
	}

	function save()
	{
		

		$changed_ignore = ($this->_initial_ignore_list != $this->_vals['ignore']);

		$this->_update_config_bools();
		$this->_update_patterns();		//make sure they are quoted.
		$this->_update_shared_directories();
		$this->_unset_bad_vals();

		
		if (empty($this->_domain))
		{
			$res = ips_put_config($this->_vals);
		}
		else
			$res = ips_put_config($this->_vals, $this->_domain);
		if ($changed_ignore)
		{
			if (!empty($this->_domain))
				ips_apply_ignore_patterns($this->_domain);
			else
				ips_apply_ignore_patterns();
		}
		return $res;
	}

	function reset_ignore_patterns()
	{
		$this->_vals['ignore'] = array();
	}


	function add_ignore_pattern($pattern, $ignore)
	{
		$np = array("pattern" => "\"".$pattern."\"", 
					"ignore" => $ignore);

		//does this pattern already exist?
		foreach($this->_vals['ignore'] as $p)
		{
			if ($p['pattern'] == $pattern && $p['ignore'] == $ignore)
				return false;

		}
		$this->_vals['ignore'][] = $np;
	}

	function _update_config_bools()
	{
		foreach($this->_vals as $k=>$v)
		{
			if ($v === true)
				$this->_vals[$k] = "1";
			else if ($v === false)
				$this->_vals[$k] = "0";
		}
	}

	function _update_patterns()
	{

		for ($i = 0;$i<count($this->_vals['ignore']);$i++)
		{
			$pattern = $this->_vals['ignore'][$i]['pattern'];
			if ($pattern[0] != '"')
				$this->_vals['ignore'][$i]['pattern'] = "\"$pattern\"";
		}
	}

	function _update_shared_directories()
	{
		if (isset($this->_vals['shared_script_directories']) && is_array($this->_vals['shared_script_directories']))
		{
			for ($i = 0;$i<count($this->_vals['shared_script_directories']);$i++)
			{
				$pattern = $this->_vals['shared_script_directories'][$i];
				if ($pattern[0] != '"')
					$this->_vals['shared_script_directories'][$i] = "\"$pattern\"";
			}
		}
	}

	function set_val($key, $val)
	{
		$this->_vals[$key] = $val;
	}

	function set_bool($key, $val)
	{
		$t = $val?"1":"0";
		$this->set_val($key, $t);
	}	

	function set_domain_bool($key, $val)
	{
		if ($val !== null && $val != -1)
		{
			$this->_vals[$key] = $val;
		}
		else
			$this->_vals[$key] = null;
	}

	function set_domain_long($key, $val)
	{
		if (empty($val))
			$this->_vals[$key] = null;
		else
			$this->_vals[$key] = $val;
	}

	function set_domain_path($key, $val)
	{
		if (empty($val))
			$this->_vals[$key] = null;
		else if ($val[0] != '"')
			$this->_vals[$key] = "\"".$val."\"";
		else
			$this->_vals[$key] = $val;
	}

	function get_val($key)
	{
		$ret = @$this->_vals[$key];
		return $ret;
	}
}

?>