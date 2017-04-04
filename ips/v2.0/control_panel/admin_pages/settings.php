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

	

    function get_domain_config($domain, $config)
    {
	    if ($domain == null || $domain == "global")
	    {
		    $config['domain_name'] = "Global";
		    return $config;
	    }
	    foreach($config['domains'] as $d)
	    {
		    if ($d['domain_name'] == $domain)
			    return $d;
	    }
	    return $config;
    }

    function get_active_form($domain)
    {
	    if ($domain == null || $domain == "global")
		    return "global_form";
	    else if ($domain == "default")
		    return "domain_default_form";
		else
			return "domain_form";
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
	
	function convert_domain_values(&$values)
	{
		foreach($values as $k=>$v)
		{
			if ($v == -1)
				$values[$k] = null;
			if (empty($v))
				$values[$k] = null;
		}
	}

	function get_domain_errors(&$values)
	{
		

		$errors = array();
		$size = data_format::parse_big_size(@$values['max_filecache_size']);

		if ($size === null)
			$this->append_error($errors, "max_filecache_size", "'Maximum file cache size' must be specified in MB or GB, or 'unlimited'");
		$count = data_format::parse_integer(@$values['max_filecache_files']);
		if ($count === null)
			$this->append_error($errors, "max_filecache_files", "'Maximum number of files in cache' must be an integer or 'unlimited'");

		$shm0 = @$values['max_shm_size'];
		if (!empty($shm0))
		{
		    $shm = data_format::parse_size(@$values['max_shm_size']);
		    if ($shm === null || $shm < 100 * 1024)
			    $this->append_error($errors, "max_shm_size", "'Shared memory limit' must be at least 100KB");
		}

		$size = data_format::parse_size(@$values['max_putget_shm']);
		if ($size === null)
			$this->append_error($errors, "max_putget_shm", "Limit must be specified in MB or GB, or 'unlimited'");

		return $errors;
	}

	function on_delete_domain($domain, $values)
	{
		//utils::pre_dump($domain);
		$msg = "Reset configuration settings for the domain <b>$domain</b>?";
		$link = $_SERVER['PHP_SELF']."?page=settings&cmd=confirm_delete_domain&domain=$domain";
		$here = $_SERVER['PHP_SELF']."?page=settings&domain=$domain";
		$this->controller->add_view_var('yes_link', $link);
		$this->controller->add_view_var('message', $msg);
		$this->controller->add_view_var('no_link', $here);
		return 'confirmation_page';
	}

	function on_confirm_delete_domain($domain, $values)
	{
		ips_remove_config_domain($domain);
		utils::redirect($_SERVER['PHP_SELF']."?page=settings&domain=global");
		exit(0);
	}

	//currently only used for nonempty domains, or default domain: not global domain
    function on_update_domain($domain, $values)
    {
		if (empty($domain) || $domain == "global")
		{	
			return $this->on_submitted("Error: on_update_domain called with no domain.");
		}

		if ($domain == "default")
			$domain = null;

		$status = ips_get_status();
		$to_api = array();
		$config = new config($domain);
		$config->reset_ignore_patterns();

		$values["max_filecache_size"] = $this->convert_unlimited(@$values["max_filecache_size"]);
		$values["max_filecache_files"] = $this->convert_unlimited(@$values["max_filecache_files"]);
		$values["max_putget_shm"] = $this->convert_unlimited(@$values["max_putget_shm"]);

		$errors = $this->get_domain_errors(&$values);
		if (count($errors))
		{
			$this->convert_domain_values($values);
			return $this->on_bad_form($values, $errors);
		}

		$config->set_domain_bool("enable", @$values["enable"]);
		$config->set_domain_bool("enable_shm", @$values["enable_shm"]);
		$config->set_domain_bool("enable_filecache", @$values["enable_filecache"]);
		$config->set_domain_bool("enable_optimiser", @$values["enable_optimiser"]);
		$config->set_domain_bool("enable_putget", @$values["enable_putget"]);
		$config->set_domain_bool("enable_api", @$values["enable_api"]);
		
		$config->set_domain_long("max_shm_size", @$values["max_shm_size"]);
		$config->set_domain_long("max_putget_shm", @$values["max_putget_shm"]);

		$config->set_domain_long("max_filecache_size", @$values["max_filecache_size"]);
		$config->set_domain_long("max_filecache_files", @$values["max_filecache_files"]);
		/* currently disabled.
		$config->set_domain_long("max_putget_file_store", @$values["max_putget_file_store"]);
		$config->set_domain_long("max_putget_file_store_files", @$values["max_putget_file_store_files"]);
		*/

		$this->add_ignore_patterns($config, $values);
		
		$config->save();

		$restart_keys = explode(",", $_REQUEST['restart_keys']);
		$restart_required = 0;

		$initial = $this->get_initial_values();

		foreach($restart_keys as $k)
		{
			if ($k == "global_enable_shm")
			{
				if (@$values["global_enable_shm"] && !$status["use_shared_memory_global"])
					$restart_required = 1;
			}
			else if (@$initial[$k] != @$values[$k])
				$restart_required = 1;
		}

		$msg =  "Configuration for domain $domain updated.";
		if ($restart_required)
			$msg .= "<br>The web server software must be restarted for the changes to take effect.";
		$this->controller->add_view_var('message', $msg);
		return 'submitted';
    }

	function on_new_domain()
	{
		$new_domain = trim(@$_REQUEST['new_domain_string']);
		if (!empty($new_domain))
		{
			if (IPS_SUCCESS == ips_add_config_domain($new_domain))
			{
				$msg =  "New domain added.";
			}
			else
			{
				$msg =  "The new domain either existed previously, or was invalid.";
			}
			$this->controller->add_view_var('message', $msg);
			return 'submitted';
		}
		else
		{
			$msg .= "<br>Please enter a valid domain name and try again.";
			$this->controller->add_view_var('message', $msg);
			return 'submitted';
		}
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

	function set_domain_name($domain)
	{
		$domain_name = $domain;
		if ($domain_name == "global")
			$domain_name = "Global";
		else if ($domain_name == "default")
			$domain_name = "Default";
		$this->controller->add_view_var('domain_name', $domain_name);
		if ($domain == "default")
			$this->controller->add_view_var('domain_title', "Default per-domain settings");
		else
			$this->controller->add_view_var('domain_title', $domain_name);	
	}

	function set_log_options($log_string)
	{
		$options_raw = explode(",", $log_string);
		$options = array();
		foreach($options_raw as $o)
			$options[$o] = 1;

		$this->controller->add_view_var('log_options', $options);
	}

	function is_real_domain($domain)
	{
		if (strcasecmp($domain, "global") == 0 || strcasecmp($domain, "default") == 0 || empty($domain))
			return 0;
		return 1;
	}

	function domain_exists($config, $domain)
	{
		$ds = $config['domains'];
		foreach($ds as $k => $d)
		{
			if ($d == $domain)
				return true;
		}
		return false;
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
			if ($sub == "log_" && $k != "log_path" && $k != "log_dir")
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
		$this->set_domain_name($domain);
		$full_config = ips_get_config();
		//utils::pre_dump($full_config);
		
		$domain_exists = (!$this->is_real_domain($domain) || $this->domain_exists($full_config, $domain));
		if (!$domain_exists)
		{
			ips_add_config_domain($domain);
			utils::redirect($_SERVER['PHP_SELF']."?page=settings");
			exit(0);

		//	$msg =  "A new settings page has been created for the domain $domain.";
		//	$this->controller->add_view_var('message', $msg);
		//	return 'submitted';
		}
		if ($domain == "global" || $domain == "default")
			$config = ips_get_config();
		else
			$config = ips_get_config($domain);
			
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
		$form_name = $this->get_active_form($domain);
		$this->controller->add_view_var('active_config', $config);
		$this->controller->add_view_var('config', $full_config);


		
		$form = $this->controller->fetch($form_name);

		$domain_list = $this->controller->get_domain_combo($full_config, $domain, 1);
		$this->controller->add_view_var('domain_list', $domain_list);
		$this->controller->add_view_var('active_form', $form);

		$per_domain = $full_config['per_domain_settings'];
		if ($per_domain)
			$ds = $this->controller->fetch('domain_selector');
		else
			$ds = $this->controller->fetch('domain_selector_no_per_domain');

		$this->controller->add_view_var('domain_selector', $ds);
		
		return 'settings';
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
