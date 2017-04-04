<?
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

	function on_default()
    {
		
		return 'putget_default';
    }

	function translate_location($code)
	{
		static $locations = array("Unknown", "Shared memory", "File store");
		return $locations[$code];
	}

	function get_view()
	{
		$putget_status = ips_putget_status(true);
		$config = ips_get_config();

		$per_domain = $config['per_domain_settings'];
		if ($per_domain)
		{
			$content_title = "Content for the current domain";
			$content = $putget_status['current_content'];
		}
		else
		{
			$content_title = "Global content";
			$content = $putget_status['global_content'];
		}
		if (!is_array($content))
		{
			$this->controller->add_view_var('message', "Shared memory is not currently enabled, so the put/get store cannot be accessed.");
			$putget_status_content = $this->controller->fetch('gray_message');
		}
		else
		{

			$default_date = " - ";
			$cont = array();
			foreach($content as $k => $c)
			{
				$item = array(	"key" => $k,
								"location" => $this->translate_location($c['location']), 
								"size" => data_format::pretty_size($c['size'], 1),
								"last_updated" => data_format::pretty_date($c['last_updated'], $default_date),
								"expiry" => data_format::pretty_date($c['expiry'], $default_date)
							);

				$cont[]  = $item;
			}

			$this->controller->add_view_var('content_title', $content_title);
			$this->controller->add_view_var('putget_content', $cont);
			$putget_status_content = $this->controller->fetch('putget_status_content');
			
		}

		$this->controller->add_view_var('putget_status_content', $putget_status_content);
	
	//	echo("<pre>");
	//	var_dump($content);
	//	echo("</pre>");

		$cmd = @$_REQUEST['cmd'];
		return $this->on_default();
	}
}


?>