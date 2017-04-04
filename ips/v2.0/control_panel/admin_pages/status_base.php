<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

class status_base
{
	function init()
	{
		$status = ips_get_status();
		$stats = ips_summary(true);

		$shm = $this->enabled_string($status['request_shm']);
		$fcache = $this->enabled_string($status['request_filecache']);
		$this->controller->add_view_var('fcache', $fcache);
		$this->controller->add_view_var('shm', $shm);
		
		$status = @$stats['cache_status'];

		$s = $this->enabled_string($status);
		$this->controller->add_view_var("cache_status_bar", $s);
	}

	function enabled_string($b)
	{
		if ($b)
			return "<span class=\"enabled\">enabled</span>";
		else
			return "<span class=\"disabled\">disabled</span>";
	}
}

?>