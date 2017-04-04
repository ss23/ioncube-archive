<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

//check IPS is OK:
if (!ips_started())
{
	echo("IPS was unabled to start correctly.<br><br>
		 For details please see the file <b>ips_log.txt</b> in the log directory of the IPS install directory,<br>
		 and your web server software's error log.");
		exit(1);
}


//initialise the app
//ips_initialise_control_panel();

//configuration and include of common classes
include_once(dirname(__FILE__)."/../lib/include.php");

//IPS extension model
include_once(dirname(__FILE__)."/../model/include.php");
    

class index_controller extends controller
{
	var $page;
	var $auto_refresh;
	
	function index_controller()
	{
		controller::controller(true);
		$page = @$_REQUEST['page'];
		//sanitise:
		if (empty($page))
			$page = "summary";
		$this->page = $page;	
		$this->add_view_var('self', $_SERVER['PHP_SELF']);
		$this->add_view_var('root_link', $_SERVER['PHP_SELF']."?page=$page");
		$this->auto_refresh = 0;
	}

	function setup_pages()
	{
		$per_domain = config::global_val('per_domain_settings');
		if ($per_domain)
		{
			$this->add_page("Summary", "summary");
			$this->add_page("Graphs", "graphs");
			$this->add_page("Domains", "domains");
			$this->add_page("Scripts", "scripts");
			$this->add_page("Domain Settings", "settings_summary");
			$this->add_page("Domain Settings", "settings", array(), 1);
			$this->add_page("Global Settings", "settings_global", array("domain"=>"global"));
		//	$this->add_page("Settings", "settings");
			$this->add_page("Maintenance", "maintenance");
			$this->add_page("Preferences", "preferences");
			$this->add_page("About", "about");
		}
		else
		{
			$this->add_page("Summary", "summary");
			if ($charts_ok)
				$this->add_page("Graphs", "graphs");
			$this->add_page("Scripts", "scripts");
			$this->add_page("Settings", "settings_global");
			$this->add_page("Maintenance", "maintenance");
			$this->add_page("Preferences", "preferences");
			$this->add_page("About", "about");
		}
	}

	function set_menu()
	{
		$html = "";
		$as = array();
		//find the page title which is currently selected
		$sel_title = "";
		foreach($this->pages as $p)
		{
			$link = $p->script;
			if ($link == $this->page)
				$sel_title = $p->title;
		}


		foreach($this->pages as $p)
		{
			$txt = $p->title;
			$link = $p->script;
			if ($txt == $sel_title)
				$txt = "<b>$txt</b>";

			$here = $_SERVER['PHP_SELF'];
			$extra = "";
			foreach($p->params as $k=>$v)
			{
				$extra .= "&amp;$k=$v";
			}
			$a = "<a href = \"$here?page=$link$extra\">$txt</a>";
			if (!$p->hide)
				$as[] = $a;
		}

		$html = implode(" | ", $as);
		$this->add_view_var('menu', $html);
	}

	function set_warnings()
	{
		$warnings = array();

		$sapi_type = php_sapi_name();
		$cgi = (substr($sapi_type, 0, 3) == 'cgi');
		$persist = config::global_val('persistent_shm_cache');
		$shm = config::global_val('global_enable_shm');
		if ($shm && !$persist && $cgi)
		{
			$warning = "PHP is running in CGI mode: please enable the 'persistent cache' for optimal performance.";
			$this->add_view_var('top_warning', $warning);
		}		
	}

	function initialise_view()
	{
		$stats = ips_summary(true);

		//utils::pre_dump($stats);
		$now = time();
		if (isset($stats['last_shm_restart']))
		{
			$config = new config();
			$interval = $config->get_val('shm_restart_interval');
			$diff = $now - $stats['last_shm_restart'];
			$stats['last_shm_restart_date'] = data_format::pretty_time_full($diff);

			$next_restart = $stats['last_shm_restart'] + $interval;
			$future = $next_restart - $now;
			if ($future < 0)
				$future = 0;
			$stats['next_restart'] = data_format::pretty_time_full($future);
		}
		else
		{
			$stats['last_shm_restart_date'] = "[shared memory disabled]";
			$stats['next_restart'] = "[shared memory disabled]";
		}

		if (isset($stats['shm_restart_request']) && $stats['shm_restart_request'])
		{
			$diff = $now - $stats['shm_restart_request'];
			$stats['shm_restart_request_date'] = data_format::pretty_time_full($diff);			
		}

		
		$this->set_menu();
		$this->set_warnings();
		$this->add_view_var('self', $_SERVER['PHP_SELF']);
		$this->add_view_var('stats', $stats);
		$this->add_view_var('page', $this->page);

		//auto refresh stuff....
		$auto = "1";
		if (isset($_REQUEST['auto_refresh']))
		{
			$auto = ($_REQUEST['auto_refresh']?"0":"1");
		}
		$this->add_view_var('auto_refresh', $auto);
		if ($auto == "0")
		{
			$this->add_view_var("meta_refresh_interval", @prefs::get_val("auto_refresh"));	
		}

		$this->auto_refresh = (($auto == "0")?1:0);
	}

	function get_php_ini_path()
	{
		$php_ini_path = "";

		ob_start();
		phpinfo(INFO_GENERAL);
		$php_info = ob_get_contents();
		ob_end_clean();

		foreach (split("\n",$php_info) as $line) {
			if (eregi("configuration file.*(</B></td><TD ALIGN=\"left\">| => |v\">)([^ <]*)(.*</td.*)?", $line, $match)) {
			  $php_ini_path = $match[2];
			}
		}
		return $php_ini_path;
	}

	function do_login()  
	{
		if (!isset($_REQUEST['password']))
			return false;
		$password = @$_REQUEST['password'];
		$_SESSION['ips_password'] = $password;
		return true;
	}

	function get_page_view()
	{
		$this->setup_pages();

		$page = $this->page;

		$this->initialise_view();

		$path = dirname(__FILE__)."/../admin_pages/$page.php";
		
		if (!file_exists($path))
		{
			$msg =  "The page '$page' could not be found.";
			$this->add_view_var('message', $msg);
			return 'submitted';
		}

		if ($page == "summary" || $page == "domains" || $page == "scripts")
			$_SESSION['sticky_page'] = $page;

		include $path;
		$page_obj = new ips_page($this);

		$view = $page_obj->get_view();
		return $view;
	}

	function on_force_restart()
	{
		$res = ips_restart_shm(false, true);	//force a restart
		if ($res)
		{
			$msg = "The shared memory has been restarted.";
		}
		else
		{
			$msg = "The shared memory could not be restarted.";
		}
		$this->add_view_var('message', $msg);
		$this->add_view_var('page', "summary");
		return 'submitted';
	}

	function do_logout()
	{
		// Unset all of the session variables.
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (isset($_COOKIE[session_name()])) {
		   setcookie(session_name(), '', time()-42000, '/');
		}
		// Finally, destroy the session.
		session_destroy();	
	}

	function setup_common_vars()
	{
		$this->add_view_var("app_version", APP_VERSION);
		$this->add_view_var("ips_version", ips_version());	
	}

    function get_view()
    {
		//does IPS exist?
		if (!function_exists('ips_version'))
		{
			$php_ini_path = $this->get_php_ini_path();
			$this->add_view_var('php_ini', " <b>$php_ini_path</b>");
			return 'no_ips';
		}

		$cmd = @$_REQUEST['cmd'];
		$this->setup_common_vars();

		if ($cmd == "logout")
		{			
			$this->do_logout();
			return "logout";
		}

		//have we logged in?
		if (!isset($_SESSION['ips_password']))
		{
			if (!$this->do_login())
				return 'login';
		}

//		utils::pre_dump($_SESSION);
		if (!ips_admin_login($_SESSION['ips_password']))
		{
			$this->do_logout();
			return 'login';
		}
		if ($cmd == "sysinfo")
		{
			phpinfo();
			exit(0);
		}

		if ($cmd == "force_restart")
			return $this->on_force_restart();

	
	
		return $this->get_page_view();		
    }
}

$c = new index_controller();

   

$view = $c->get_view();

$c->display_view($view);

?>
