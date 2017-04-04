<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

       
include dirname(__FILE__)."/status_base.php";


$a = "";

class ips_page extends status_base
{
   
	var $controller;
	function ips_page(&$controller)
	{
		$this->controller = &$controller;
	}

	function get_domain_config_val($domain, $key)
	{
		$config = new config($domain);
		return $config->get_val($key);
	}
       
	function setup_domains()
	{
		$model = new table_model("domain_list");
		$restored = $model->session_restore();
		if ($restored)
			$model->process_request();
		
		$headers = array("Domain", "Shared memory used", "Scripts in SHM", "Put/get memory used", "File cache used", "Files in file cache");						
		$links = array( array(	"root" => "index.php?page=scripts",	"key"  => "domain") );
		$format = array(DF_STRING, DF_STRING, DF_STRING, DF_SIZE_DECIMAL, DF_BIGSIZE_DECIMAL, DF_STRING);
		$align = array(TC_LEFT, TC_RIGHT, TC_RIGHT, TC_RIGHT, TC_RIGHT, TC_RIGHT);
		
		$custom_form_data = array("page" => "status");
		$actions = array();
		
		$model->selectable_rows = false;
		$model->links = $links;
		$model->custom_form_data = $custom_form_data;
		$model->actions = $actions;
		$model->format = $format;
		$model->headers = $headers;
		$model->alignment = $align;
		$model->reset_commands();

		$model->auto_refresh = $this->controller->auto_refresh;

		//$model->add_command("all_domains", "show scripts for all domains");

		$sel_table = @$_REQUEST['table'];
		$some_table_selected = !empty($sel_table);

		if (!$some_table_selected || !$restored || $model->refresh_requested())
		{
			$domains = ips_get_domain_info();
			if (is_array($domains))
			{
				foreach($domains as $k=>$v)
				{
					$summary = ips_summary(0, $v['name']);
					$v['filecache_size'] = $summary['filecache_size'];
					$v['filecache_count'] = $summary['filecache_count'];
					$domains[$k] = $v;

					$max_shm = $this->get_domain_config_val($v['name'], 'max_shm_size');
					if ($max_shm !== null)
						$domains[$k]['shm_used'] = data_format::pretty_size_decimal($domains[$k]['shm_used'])." / ".data_format::pretty_size_decimal($max_shm);
					else
						$domains[$k]['shm_used'] = data_format::pretty_size_decimal($domains[$k]['shm_used']);
				}
				$model->set_assoc_data($domains);
			}
		}

		$model->format_data();

		//update session var after setting the data: so we can remember how many pages there were
		$model->session_save();

		$this->controller->add_view_var('domain_table', $model);
		$panel = $this->controller->fetch('shm_status_domain_panel');
		$this->controller->add_view_var('shm_status_domain_panel', $panel);
	}

	function on_table_action()
	{
		$sel_domain = @$_REQUEST['domain'];
		if ($sel_domain == null)
			$sel_domain = @$_SESSION['domain'];
		$sel_table = $_REQUEST['__selected_table'];
		
		if ($sel_table == "script_list")
		{
			if (isset($_REQUEST['ignore']))
				return $this->on_ignore_scripts($sel_domain);
			else if (isset($_REQUEST['remove']))
				return $this->on_remove_scripts($sel_domain);
		}
	}

	function on_default()
    {
		status_base::init();
		$this->setup_domains();
		return 'domains_default';
    }

	function get_view()
	{
		
		$cmd = @$_REQUEST['cmd'];
		if(isset($_REQUEST['reset_cache']))
		{
			ips_restart_shm(0, 0);
			$this->controller->add_view_var('message', "Shared memory successfully restarted.");
			$this->controller->add_view_var('url', $_SERVER['PHP_SELF']."?page=status");
			return 'message';
		}
		if ($cmd == "table_action")
			return $this->on_table_action();
		return $this->on_default();
	}
}

?>
