<?php
/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

class ips_page
{
	var $controller;
	function ips_page(&$controller)
	{
		$this->controller = &$controller;
	}

	function process_ignore_string($str)
	{
		$pi = null;
		$str = trim($str);
		$ignore = true;
		if (strlen($str) >= 2)
		{
			if ($str[0] == "+" && $str[1] == " ")
			{
				$ignore = false;
				$str = trim(substr($str, 2));
			}			
		}
		if (!empty($str))
			$pi = array("pattern" => $str,	"ignore" => $ignore);
		return $pi;
	}

	function add_ignore_patterns(&$config, $values)
	{
		$str = @$values['ignore_string'];
		$items = explode("\n", $str);
		if (count($items))
		{
			for($i = 0;$i<count($items);$i++)
			{
				$pi = $this->process_ignore_string($items[$i]);
				if ($pi != null)
				{
					$config->add_ignore_pattern($pi['pattern'], $pi['ignore']);
				}
			}
		}
	}

	function set_shared_directories(&$config, $values)
	{
		$str = @$values['shared_string'];
		//utils::pre_dump($str);
		$items = explode("\n", $str);
		$arr = array();
		if (count($items))
		{
			for($i = 0;$i<count($items);$i++)
			{
				$pi = trim($items[$i]);
				if ($pi != null)
				{
					$arr[] = "\"$pi\"";
				}
			}
		}
		$val = implode(",", $arr);
		$config->set_val('shared_script_directories', $val);
	}

	function on_enable_per_domain($enable)
    {
		$config = new config();
		$config->set_val('per_domain_settings', $enable?"1":"0");
		if (!$config->save())
			return $this->on_save_failed();
		$this->controller->add_view_var('message', "The web server must be restarted for this setting to take effect.");
		return 'submitted';
    }


	function set_log_options($log_string)
	{
		$options_raw = explode(",", $log_string);
		$options = array();
		foreach($options_raw as $o)
			$options[$o] = 1;

		$this->controller->add_view_var('log_options', $options);
	}

	function on_submitted($msg)
	{
		$this->controller->add_view_var('message', $msg);
		return 'submitted';
	}

	function on_submitted_tab($tab, $restart_needed)
	{
		if ($restart_needed)
			return $this->on_submitted("$tab settings updated.<br>The web server must be restarted for these settings to take effect.");
		else
			return $this->on_submitted("$tab settings updated.");
	}

	function on_save_failed()
	{
		return $this->on_submitted("The configuration file could not be saved to disk.");
	}

	function append_error(&$errors, $name, $message)
	{
		$error = array("message" => $message);
		$errors[$name] = $error;
	}

	function on_bad_form(&$values, &$errors)
	{
		$this->controller->add_view_var("form_errors", $errors);
		$view = $this->on_default($this->get_current_domain(), $values);
		return $view;
	}

	function on_global_logging_tab(&$values)
	{
		$config = new config();
		
		$log_array = array();
		foreach($values as $k=>$v)
		{
			$sub = substr($k, 0, 4);
			if ($sub == "log_" && $k != "log_dir")
			{
				$remainder = substr($k, 4);
				$log_array[] = $remainder;
			}
		}

		$log_string = implode(",", $log_array);
		$config->set_val('log', $log_string);
		$config->set_val("log_dir", @$values["log_dir"]);

		$config->set_bool("enable_script_checksum", @$values["enable_script_checksum"]);
		$config->set_bool("enable_index_checksum", @$values["enable_index_checksum"]);
		
		if (!$config->save())
			return $this->on_save_failed();
		return $this->on_submitted("Log settings updated.");
	}

	function on_global_filter_tab(&$values)
	{
		$config = new config();
		$config->reset_ignore_patterns();
		$this->set_shared_directories($config, $values);
		$this->add_ignore_patterns($config, $values);
		if (!$config->save())
			return $this->on_save_failed();
		return $this->on_submitted("Filter settings updated.");
	}

	function get_global_putget_errors(&$values)
	{
		$errors = array();
		$size = data_format::parse_size(@$values['global_max_putget_shm']);
		if ($size === null)
			$this->append_error($errors, "global_max_putget_shm", "Limit must be specified in MB or GB, or 'unlimited'");
		return $errors;
	}

	function on_global_putget_tab(&$values)
	{
		$values["global_max_putget_shm"] = $this->convert_unlimited(@$values["global_max_putget_shm"]);

		$errors = $this->get_global_putget_errors(&$values);
		if (count($errors))
			return $this->on_bad_form($values, $errors);

		$config = new config();
		$config->set_val("putget_dir", @$values["putget_dir"]);
		$config->set_val("global_max_putget_shm", @$values["global_max_putget_shm"]);
		
		/*
		$config->set_val("global_max_putget_file_store", @$values["global_max_putget_file_store"]);
		$config->set_val("global_max_putget_file_store_files", @$values["global_max_putget_file_store_files"]);
		*/
		if (!$config->save())
			return $this->on_save_failed();
		return $this->on_submitted("Put/get API settings updated.");
	}

	function convert_unlimited($str)
	{
		$str0 = $str;
		if ($str === null)
			return null;
		$str = trim($str);
		$str = strtolower($str);
		if ($str == "unlimited")
			return 0;
		else
			return $str0;
	}

	function get_global_filecache_errors(&$values)
	{
		$errors = array();
		$size = data_format::parse_big_size(@$values['global_max_filecache_size']);
		if ($size === null)
			$this->append_error($errors, "global_max_filecache_size", "'Maximum file cache size' must be specified in MB or GB, or 'unlimited'");
		$count = data_format::parse_integer(@$values['global_max_filecache_files']);
		if ($count === null)
			$this->append_error($errors, "global_max_filecache_files", "'Maximum number of files in cache' must be an integer or 'unlimited'");
		return $errors;
	}

	function on_global_filecache_tab(&$values)
	{
		$values["global_max_filecache_size"] = $this->convert_unlimited(@$values["global_max_filecache_size"]);
		$values["global_max_filecache_files"] = $this->convert_unlimited(@$values["global_max_filecache_files"]);

		$errors = $this->get_global_filecache_errors(&$values);
		if (count($errors))
			return $this->on_bad_form($values, $errors);

		$config = new config();
		$config->set_val("cache_dir", @$values["cache_dir"]);
		$config->set_val("global_max_filecache_size", @$values["global_max_filecache_size"]);
		$config->set_val("global_max_filecache_files", @$values["global_max_filecache_files"]);
		if (!$config->save())
			return $this->on_save_failed();
		return $this->on_submitted("File cache settings updated.");
	}

	function get_global_shm_errors(&$values)
	{
		$errors = array();

		$lc = data_format::parse_integer(@$values['lock_count']);
		if ($lc == null || $lc < 1 || $lc > 32)
			$this->append_error($errors, "lock_count", "'Number of cache locks' must be an integer between 1 and 32");

		$shm0 = trim(@$values['global_max_shm_size']);
		$shm = data_format::parse_size(@$values['global_max_shm_size']);
		if ($shm === null || $shm < 1024 * 1024)
			$this->append_error($errors, "global_max_shm_size", "'Shared memory limit' must be at least 1MB");

		$buckets = data_format::parse_integer(@$values['bucket_count']);	//buckets have size less than 64 bytes: require space take by buckets to be 
		$bucket_struct_size = 64;
		$max_bucket_space = $shm /  128;	//max proportion of the shared memory we want to have filled with buckets.
		$max_number_buckets = (int)($max_bucket_space / $bucket_struct_size);

		if ($buckets === null || $buckets < 256)
			$this->append_error($errors, "bucket_count", "'Number of cache slots' must be at least 256");
		else if ($buckets > $max_number_buckets)
			$this->append_error($errors, "bucket_count", "'Number of cache slots' must be at most $max_number_buckets, based on the requested shared memory size of $shm0");

		$restart = data_format::parse_period(@$values['shm_restart_interval']);
		if ($restart === null || $restart < 1)
			$this->append_error($errors, "shm_restart_interval", "'Shared memory restart interval' must be at least 1s");

		$reserve = data_format::parse_size(@$values['cache_reserve']);
		if ($reserve === null || $reserve < 0)
			$this->append_error($errors, "cache_reserve", "'Cache index reserve' must be a positive integer");

		return $errors;
	}
	function on_global_shm_tab(&$values)
	{
		$errors = $this->get_global_shm_errors(&$values);
		if (count($errors))
			return $this->on_bad_form($values, $errors);

		$restart = false;
		$initial = $this->get_initial_values();
		if ($initial['global_max_shm_size'] != @$values["global_max_shm_size"])
			$restart = true;
		if ($initial['lock_count'] != @$values["lock_count"])
			$restart = true;
		if ($initial['bucket_count'] != @$values["bucket_count"])
			$restart = true;

		$config = new config();

		$config->set_val("shm_restart_interval", @$values["shm_restart_interval"]);
		$config->set_val("lock_count", @$values["lock_count"]);
		$config->set_val("global_max_shm_size", @$values["global_max_shm_size"]);
		$config->set_val("cache_reserve", @$values["cache_reserve"]);
		$config->set_val("bucket_count", @$values["bucket_count"]);

		if (!$config->save())
			return $this->on_save_failed();

		return $this->on_submitted_tab("Shared memory cache", $restart);
	}

	function get_global_basic_errors(&$values)
	{
		$errors = array();
		if (@$values['enable_scheduling'])
		{
			$start = data_format::parse_time(@$values['schedule_start']);
			if ($start === null)
				$this->append_error($errors, "schedule_start", "'Schedule start time' must be a time of the form HH:MM");

			$stop = data_format::parse_time(@$values['schedule_stop']);
			if ($stop === null)
				$this->append_error($errors, "schedule_stop", "'Schedule stop time' must be a time of the form HH:MM");
		}
		$values['admin_password2'] = trim($values['admin_password2']);
		$values['admin_password'] = trim($values['admin_password']);

		$p1 = $values['admin_password'];
		$p2 = $values['admin_password2'];
		if (!empty($p1) || !empty($p2))
		{
			if ($p1 != $p2)
				$this->append_error($errors, "admin_password2", "The two passwords must match - please try again");
		}
		return $errors;
	}
	
	function on_global_basic_tab(&$values)
	{
		$errors = $this->get_global_basic_errors(&$values);
		if (count($errors))
			return $this->on_bad_form($values, $errors);
		
		$restart = false;
		$initial = $this->get_initial_values();
		if ($initial['global_enable_shm'] != @$values["global_enable_shm"])
			$restart = true;
		if ($initial['per_domain_settings'] != @$values["per_domain_settings"])
			$restart = true;

		$config = new config();
		
		if (!empty($values['admin_password']))
			$config->set_val('admin_password_hash', md5($values['admin_password']));

		$config->set_bool("global_enable", @$values["global_enable"]);
		$config->set_bool("global_enable_shm", @$values["global_enable_shm"]);
		$config->set_bool("global_enable_putget", @$values["global_enable_putget"]);
		$config->set_bool("global_enable_filecache", @$values["global_enable_filecache"]);
		$config->set_bool("enable_scheduling", @$values["enable_scheduling"]);
		$config->set_bool("persistent_shm_cache", @$values["persistent_shm_cache"]);
		$config->set_bool("expose_in_phpinfo", @$values["expose_in_phpinfo"]);
		$config->set_bool("enable_optimiser", @$values["enable_optimiser"]);
		$config->set_bool("per_domain_settings", @$values["per_domain_settings"]);

		$config->set_val("schedule_start", @$values["schedule_start"]);
		$config->set_val("schedule_stop", @$values["schedule_stop"]);

		if (!$config->save())
			return $this->on_save_failed();
		return $this->on_submitted_tab("Basic", $restart);
	}

	function setup_tabs()
	{
		if (isset($_REQUEST['tab']))
			$sel_tab = $_REQUEST['tab'];
		else if (isset($_SESSION['tab']))
			$sel_tab = $_SESSION['tab'];
		else
			$sel_tab = "global_basic_tab";
		$_SESSION['tab'] = $sel_tab;

		$tabs = array("Basic" => "global_basic_tab", "Filter" => "global_filter_tab", "SHM cache" => "global_shm_tab", "File cache" => "global_filecache_tab", "Put/get API" => "global_putget_tab", "Logging" => "global_logging_tab");
		$this->controller->add_view_var('tabs', $tabs);
		$this->controller->add_view_var('sel_tab', $sel_tab);
	}

	function get_current_domain()
	{
		$domain = @$_REQUEST['domain'];
		if ($domain == null)
		{
			$domain = @$_SESSION['domain'];
			if ($domain == null)
				$domain = "global";
		}
		return $domain;
	}

	function get_initial_values()
	{
		$ret = array();
		$ser = @$_REQUEST['initial_values'];
		if (!empty($ser))
			$ret = unserialize($ser);
		return $ret;
	}

	function set_initial_values(&$config, $keys)
	{

		$sub = array();
		foreach($keys as $k)
			$sub[$k] = $config[$k];
		$str = htmlspecialchars(serialize($sub));
		$this->controller->add_view_var('initial_values', $str);
	}

	

	function on_default($domain, $old_values = array())
    {
		$full_config = ips_get_config();
		$config = $full_config;
		$this->setup_tabs();		
		
		//prettify the config
		$config['shm_restart_interval'] = data_format::pretty_time($config['shm_restart_interval']);
		$config['global_max_shm_size'] = data_format::pretty_size($config['global_max_shm_size']);
		$config['cache_reserve'] = data_format::pretty_size($config['cache_reserve']);
		$config['global_max_putget_shm'] = data_format::pretty_size($config['global_max_putget_shm']);
		$config['global_max_putget_file_store'] = data_format::pretty_bigsize($config['global_max_putget_file_store']);

		if ($config['global_max_filecache_size'])
			$config['global_max_filecache_size'] = data_format::pretty_bigsize($config['global_max_filecache_size']);
		else
			$config['global_max_filecache_size'] = "unlimited";

		if (!$config['global_max_filecache_files'])
			$config['global_max_filecache_files'] = "unlimited";

		if (!$config['global_max_putget_shm'])
			$config['global_max_putget_shm'] = "unlimited";

		$config['schedule_start'] = data_format::pretty_hours_mins($config['schedule_start']);
		$config['schedule_stop'] = data_format::pretty_hours_mins($config['schedule_stop']);

		$this->set_log_options($config['log']);
		if (count($config['shared_script_directories']))
		{
			$shared_string = "";
			foreach($config['shared_script_directories'] as $dr)
			{
				$shared_string.=trim($dr)."\n";
			}
			$config['shared_string'] = $shared_string;
		}
		$this->set_initial_values($config, array("global_max_shm_size", "global_enable_shm", "lock_count", "bucket_count", "per_domain_settings"));
	

		//utils::pre_dump($config);
		$config['max_shm_size'] = data_format::pretty_size($config['max_shm_size']);
		$config['max_putget_shm'] = data_format::pretty_size($config['max_putget_shm']);
		$config['max_putget_file_store'] = data_format::pretty_bigsize($config['max_putget_file_store']);

		if ($config['max_filecache_size'])
			$config['max_filecache_size'] = data_format::pretty_bigsize($config['max_filecache_size']);
		else
			$config['max_filecache_size'] = "unlimited";

		if (!$config['max_filecache_files'])
				$config['max_filecache_files'] = "unlimited";

		//var_dump($config['ignore']);
		if (count($config['ignore']))
		{
			$ignore = "";
			foreach($config['ignore'] as $ignore_item)
			{
				$ig = $ignore_item['ignore'];
				if (!$ig)
					$ignore.="+ ";
				$ignore.=$ignore_item['pattern']."\n";
			}
			$config['ignore_string'] = $ignore;
		}

		//old_values may come from a posted, invalid, form:
		foreach($old_values as $k=>$v)
		{
			$config[$k] = $v;
		}
		if (@$_SESSION['restart_required'])
		{
			$warning = "A web server restart is required for the settings to take effect.";
			$this->controller->add_view_var('warning', $warning);
		}
		$form_name = "global_form";
		$this->controller->add_view_var('active_config', $config);
		$this->controller->add_view_var('config', $full_config);

		$form = $this->controller->fetch($form_name);

		$domain_list = $this->controller->get_domain_combo($full_config, $domain, 1);
		$this->controller->add_view_var('domain_list', $domain_list);
		$this->controller->add_view_var('active_form', $form);
		return 'settings_global';
    }

	function get_view()
	{
		
		$domain = $this->get_current_domain();
		$this->controller->add_view_var('domain', $domain);

		$cmd = @$_REQUEST['cmd'];
		if ($cmd == "disable_per_domain_settings")
			return $this->on_enable_per_domain(false);
		else if ($cmd == "enable_per_domain_settings")
			return $this->on_enable_per_domain(true);
		else if ($cmd == "confirm_delete_domain")
			return $this->on_confirm_delete_domain($domain, $_REQUEST);

		if (isset($_REQUEST['new_domain']))
		{	
			return $this->on_new_domain();
		}

		/* Naming convention: the handler for the tab 'mytab' must be called 'on_mytab' */
		if (isset($_REQUEST['submit_tab']))
		{
			
			$sel_tab = $_REQUEST['tab'];
			$method_name = "on_$sel_tab";
			if (!method_exists($this, $method_name))
				return $this->on_submitted("<b>Error:</b> no handler has been defined for the tab '$sel_tab'");
			return $this->$method_name($_REQUEST);
		}
		
		if (isset($_REQUEST['submit_domain']))
			return $this->on_update_domain($domain, $_REQUEST);
		else if (isset($_REQUEST['delete_domain']))
			return $this->on_delete_domain($domain, $_REQUEST);

		$_SESSION['domain'] = $domain;
		return $this->on_default($domain);
	}
}

?>