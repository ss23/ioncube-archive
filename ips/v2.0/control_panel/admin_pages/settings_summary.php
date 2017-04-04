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

	function get_domain_config_val($domain, $key)
	{
		$config = new config($domain);
		return $config->get_val($key);
	}

	function get_settings_column(&$config, &$domain_config, $key)
	{
		$val = $domain_config->get_val($key);
		if ($val === null)
			$val = $config->get_val($key);
		return $val;
	}

	function get_settings_row(&$config, &$domain_config)
	{
		$row = array();
		$row[] = $this->get_settings_column($config, $domain_config, 'enable');
		$row[] = $this->get_settings_column($config, $domain_config, 'enable_shm');
		$row[] = $this->get_settings_column($config, $domain_config, 'enable_filecache');
		$row[] = $this->get_settings_column($config, $domain_config, 'enable_putget');
		return $row;
	}

	function get_domain_settings()
	{
		$ret = array();
		$config = new config();
		$domains = $config->get_val('domains');
		foreach($domains as $d)
		{
			$domain_config = new config($d);
			$row = $this->get_settings_row($config, $domain_config);
			$ret[$d] = $row;
		}
	//	utils::pre_dump($ret);
		return $ret;
	}

	function get_default_domain_settings()
	{
		$ret = array();
		$config = new config();
		
		$row = $this->get_settings_row($config, $config);
		$ret['default'] = $row;

	//	utils::pre_dump($ret);
		return $ret;
	}

	function setup_domains()
	{

		$model = new table_model("domain_settings_list");
		$restored = $model->session_restore();
		if ($restored)
			$model->process_request();
		
		$headers = array("Domain", "Enabled", "SHM enabled", "File cache enabled", "Put/get enabled");						
		$links = array( array(	"root" => "index.php?page=settings",	"key"  => "domain") );
		$format = array(DF_STRING, DF_BOOL, DF_BOOL, DF_BOOL, DF_BOOL);
		$align = array(TC_LEFT, TC_CENTER, TC_CENTER, TC_CENTER, TC_CENTER);
		
		$custom_form_data = array("page" => "settings_summary");
		$actions = array();
		
		$model->selectable_rows = false;
		$model->links = $links;
		$model->custom_form_data = $custom_form_data;
		$model->actions = $actions;
		$model->format = $format;
		$model->headers = $headers;
		$model->alignment = $align;
		$model->reset_commands();
		//$model->add_command("all_domains", "show scripts for all domains");

		$sel_table = @$_REQUEST['table'];
		$some_table_selected = !empty($sel_table);

		if (!$some_table_selected || !$restored || $model->refresh_requested())
		{
			$domains = ips_get_domain_info();
			$settings = $this->get_domain_settings();
			$def = $this->get_default_domain_settings();

			$model->set_keyed_assoc_data($settings);
			$model->set_keyed_assoc_data($def, true);

			/*
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
			*/
		}


		$model->format_data();

		//update session var after setting the data: so we can remember how many pages there were
		$model->session_save();
		$this->controller->add_view_var('domain_table', $model);
	}

	function on_default()
    {
		$this->setup_domains();		
		return 'settings_summary';
    }

	function get_view()
	{
		return $this->on_default();
	}
}

?>