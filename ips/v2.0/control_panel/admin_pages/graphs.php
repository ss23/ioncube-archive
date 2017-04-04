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

	function on_default()
    {
		status_base::init();
		$stats = ips_summary(true);		
		$status = @$stats['cache_status'];

		if ($status == "enabled")
		{
			$this->controller->add_view_var('green_class', "green");
			$this->controller->add_view_var('orange_class', "gray");
			$this->controller->add_view_var('red_class', "gray");
		}
		else if ($status =="restarting")
		{
			$this->controller->add_view_var('green_class', "gray");
			$this->controller->add_view_var('orange_class', "orange");
			$this->controller->add_view_var('red_class', "gray");
		}
		else 
		{
			$this->controller->add_view_var('green_class', "gray");
			$this->controller->add_view_var('orange_class', "gray");
			$this->controller->add_view_var('red_class', "red");
		}

		$domains = ips_get_domain_info();
		if (is_array($domains) && count($domains) > 0)
		{
			$this->controller->add_view_var('show_domain_pie', 1);	
		}
		return 'graphs_default';
    }

	function get_view()
	{
		$cmd = @$_REQUEST['cmd'];	
		return $this->on_default();
	}
}

?>