<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

 function combo_sorter($a, $b)
 {
	 $ma = $a['msg'];
	 $mb = $b['msg'];
	 return strcmp($ma, $mb);
 }

class page
{
	var $title;
	var $script;
	var $params;
	var $hide;
}


//derive from this class for custom behaviour
class controller
{
	var $admin_mode;
	var $opts = array();

	var $pages;

	function add_view_var($k, $v)
	{
		$this->opts[$k] = $v;
	}

	function get_view_var($k)
	{
		return @$this->opts[$k];
	}

	function get_domain_combo(&$config, $selected, $settings_version)
    {
		if ($selected == null)
			$selected = "global";

	    
	    $domain_list['on'] = "domain";
	    $domain_list['select'] = $selected;

		if ($settings_version)
		{
			$domain_list['opts'] = array( 
			//	array("value"=>"global",	"msg"=>"Global settings"),
				array("value"=>"default",	"msg"=>"Default domain settings"));

			//$sofar = array();
			$domains = @$config['domains'];
			if ($domains !== null)
			{
				foreach($domains as $d)
				{
					$domain_list['opts'][] = array("value"=> $d,  "msg"=> $d);
					//$sofar[$d] = true;
				}
			}
		}
		else
		{
			$domain_list['opts'] = array( 
				array("value"=>"global",	"msg"=>"All domains"));

			$dis = ips_get_domain_info();
			if (is_array($dis))
			{
				foreach($dis as $k=>$v)
				{
					$d = $v['name'];
					//if (!isset($sofar[$d]))
						$domain_list['opts'][] = array("value"=> $d,  "msg"=> $d);
				}
			}
		}

		usort($domain_list['opts'], 'combo_sorter');
	    return $domain_list;
    }

	//Note: Need to enter a password for admin_mode to actually work!
	function controller($admin_mode = false)
	{
	    utils::sanitise_magic_quotes();
		$this->tf = new TF(TEMPLATES_DIR);
		$title = "ionCube Performance System";
		$this->opts['title'] = $title;
		$this->opts['php_self'] = $_SERVER['PHP_SELF'];
		$this->opts['site_root'] = SITE_ROOT;		

		$this->admin_mode = $admin_mode;

		$this->pages = array();
	}

	function add_page($title, $script, $params = array(), $hide = false)
	{
		$new_page = new page;
		$new_page->title = $title;
		$new_page->script = $script;
		$new_page->params = $params;
		$new_page->hide = $hide;
		$this->pages[] = $new_page;
	}

	function display_view($tplate)
	{		
		$page_content = $this->tf->render($tplate, $this->opts);
		$this->add_view_var('page_content', $page_content);

		if ($this->admin_mode)
			echo($this->tf->render('top_layout',$this->opts)); 
		else
			echo($this->tf->render('top_layout_user',$this->opts)); 
	}

	function fetch($tplate)
	{
		$vals = $this->opts;
		$res = $this->tf->render($tplate, $vals); 
		return $res;
	}
}