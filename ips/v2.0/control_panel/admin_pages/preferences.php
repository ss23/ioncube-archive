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
		//$prev_page = 
		$prefs = prefs::get_all_vals();
		$this->controller->add_view_var("prefs", $prefs);
		return "preferences";
	}

	function on_submit()
	{
		$new_vals = $_REQUEST;
		$vals = prefs::get_all_vals();
		$vals['max_path_components'] = trim(@$new_vals['max_path_components']);
		$vals['auto_refresh'] = trim(@$new_vals['auto_refresh']);
		$vals['max_scripts_per_page'] = trim(@$new_vals['max_scripts_per_page']);
		$vals['components_to_hide'] = trim(@$new_vals['components_to_hide']);
		$vals['show_filecache_always'] = trim((@$new_vals['show_filecache_always']?"1":"0"));
		$res = prefs::save_vals($vals); 
		if ($res !== false)
		{
			$msg =  "Preferences updated.";
		}
		else
		{
			$msg =  "The preferences file could not be saved.<br />Please check the permissions of the file <b>".PREFS_PATH."</b> and the directory containing it.";
		}
		$this->controller->add_view_var('message', $msg);
		return 'submitted';
	}

	function get_view()
	{
		$cmd = @$_REQUEST['cmd'];
		if ($cmd == "submit")
			return $this->on_submit();
		return $this->on_default();
	}
}


?>