<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

include dirname(__FILE__)."/status_base.php";

class ips_page extends status_base
{

	var $controller;
	function ips_page(&$controller)
	{
		$this->controller = &$controller;
	}

	function on_default()
    {
	    $config = new config;
	    $log = $config->get_val('log_dir')."ips_log.txt";
	    $fcache = $config->get_val('cache_dir');
	    $home = $config->get_val('home');

	    $this->controller->add_view_var("ips_home", $home);
	    $this->controller->add_view_var("file_cache_path", $fcache);

		$page = "summary";
		$link = "<a href = \"".$_SERVER['PHP_SELF']."?page=$page&amp;cmd=view_log\" onclick=\"window.open(this.href); return false;\" onkeypress=\"window.open(this.href); return false;\">$log</a>";
	    $this->controller->add_view_var("log_path", $link);


		$num_cpus = ips_cpu_count();

		status_base::init();
		$domain_info = ips_get_domain_info();
		if ($domain_info !== null)
			$this->controller->add_view_var("domain_count", count($domain_info));
		$stats = ips_summary(true);
		//utils::pre_dump($stats);

		$this->controller->add_view_var("cpu_count", $num_cpus);

		if (isset($stats['cache_status']))
			$this->controller->add_view_var("cache_status", $stats['cache_status']);
		if (isset($stats['shm_free']))
			$this->controller->add_view_var("shm_free", data_format::pretty_size_decimal($stats['shm_free'], 1));
		if (isset($stats['shm_used']))
			$this->controller->add_view_var("shm_used", data_format::pretty_size_decimal($stats['shm_used'], 1));
		if (isset($stats['shm_refcount']))
			$this->controller->add_view_var("shm_refcount", $stats['shm_refcount']);
		if (isset($stats['filecache_size']))
			$this->controller->add_view_var("filecache_size", data_format::pretty_bigsize_decimal($stats['filecache_size'], 1));
		if (isset($stats['filecache_count']))
			$this->controller->add_view_var("filecache_count",$stats['filecache_count']);
		if (isset($stats['putget_shm_used']))
			$this->controller->add_view_var("putget_shm_used", data_format::pretty_size_decimal($stats['putget_shm_used'], 1));
		if (isset($stats['min_items_per_bucket']))
			$this->controller->add_view_var("min_items_per_bucket", $stats['min_items_per_bucket']);
		if (isset($stats['max_items_per_bucket']))
			$this->controller->add_view_var("max_items_per_bucket", $stats['max_items_per_bucket']);
		if (isset($stats['mean_items_per_bucket']))
		{
			$min = $stats['min_items_per_bucket'];
			$mean = sprintf("%.2f", $stats['mean_items_per_bucket']);
			$max = $stats['max_items_per_bucket'];
			$this->controller->add_view_var("mean_items_per_bucket", $mean);

			$this->controller->add_view_var("minmeanmax", "$min / $mean / $max");
		}
		
		if (isset($stats['nonempty_bucket_ratio']))
			$this->controller->add_view_var("nonempty_bucket_percent", (int)($stats['nonempty_bucket_ratio'] * 100)."%");
		if (isset($stats['index_size']))
			$this->controller->add_view_var("index_size", data_format::pretty_size_decimal($stats['index_size']));
		if (isset($stats['shm_entries']))
			$this->controller->add_view_var("index_shm", $stats['shm_entries']);
		if (isset($stats['filecache_entries']))
			$this->controller->add_view_var("index_filecache", $stats['filecache_entries']);
		if (isset($stats['no_location_entries']))
			$this->controller->add_view_var("index_no_location", $stats['no_location_entries']);
		$server = "";
		if (isset($stats['max_request_server']) && !empty($stats['max_request_server']))
		{
			$server = $stats['max_request_server'];
			$url = $stats['max_request_url'];
			if ($server == "CLI")
			{
				$url = utils::path_ellipsis($url);
				$link = "CLI: $url";
			}
			else
			{
				$full_url = "http://$server$url";
				$link = "<a href=\"$full_url\" target=\"_blank\">$full_url</a>";
			}
			$this->controller->add_view_var("maxrequesturl", $link);

			if (isset($stats['mean_request_time']))
			{
				$min = $stats['min_request_time'];
				$mean = sprintf("%.2f", $stats['mean_request_time']);
				$max = $stats['max_request_time'];
				$this->controller->add_view_var("request_minmeanmax", "$min / $mean / $max");			
			}
		}


		$load_avg = ips_load_average();
		if ($load_avg !== false)
		{
		    $this->controller->add_view_var("load_avg_one", sprintf("%.2f", $load_avg[0]));
		    $this->controller->add_view_var("load_avg_five", sprintf("%.2f", $load_avg[1]));
		    $this->controller->add_view_var("load_avg_fifteen", sprintf("%.2f", $load_avg[2]));
			$load_row = $this->controller->fetch('load_average_row');
			$this->controller->add_view_var("load_average_row", $load_row);
		}
		return 'summary_default';
    }  

	function get_view()
	{    
		$cmd = @$_REQUEST['cmd'];
		if ($cmd == "view_log")
		{
		    $path = config::global_val('log_dir')."ips_log.txt";
		    if (!is_file($path))
		    {

			$this->controller->add_view_var('message', "The log file does not exist at <b>$path</b>");
			return 'submitted';
		    }
		    else
		    {
			$content = file_get_contents($path);
			die("<pre>\n$content\n</pre>");
		    }
		}
		
		return $this->on_default();
	}
}

?>
