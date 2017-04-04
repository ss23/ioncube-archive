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
		return 'about';
    }

	function get_view()
	{
		return $this->on_default();
	}
}


?>