<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

session_start();

//configuration and include of common classes
include_once(dirname(__FILE__)."/../lib/include.php");

//IPS extension model
include_once(dirname(__FILE__)."/../model/include.php");


class index_controller extends controller
{
	var $page;
	function index_controller()
	{
		controller::controller();
		$page = @$_REQUEST['page'];
		//sanitise:
		if (!isset($page) || strlen($page) > 10)
			$page = "status";
		$this->page = $page;	

		$this->add_view_var('self', $_SERVER['PHP_SELF']);
		$this->add_view_var('root_link', $_SERVER['PHP_SELF']."?page=$page");
	}

	function set_menu()
	{
		$pages = array("Settings", "Status");
		$html = "";
		$as = array();
		foreach($pages as $p)
		{
			$txt = $p;
			if (strcasecmp($p, $this->page) == 0)
				$txt = "<b>$txt</b>";

			$lower = strtolower($p);
			$here = $_SERVER['PHP_SELF'];
			$a = "<a href = \"$here?page=$lower\">$txt</a>";
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
	
		if (isset($stats['last_shm_restart']))
			$stats['last_shm_restart_date'] = date("Y/n/j H:i:s", $stats['last_shm_restart']);
		else
			$stats['last_shm_restart_date'] = "[shared memory disabled for this request]";
		
		$this->set_menu();
		$this->set_warnings();
		$this->add_view_var('self', $_SERVER['PHP_SELF']);
		$this->add_view_var('stats', $stats);
		$this->add_view_var('page', $this->page);
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
		//two cases: we have already submitted a login, or we have not.
		$password = trim(@$_REQUEST['password']);
		
		//validate the username and password.
		$config = new config($_SERVER['SERVER_NAME']);
		$hash = $config->get_val('domain_password_hash');
		if (empty($hash))
		{
		    $msg = "No password has been set for this domain. Please contact the server administrator and request a new password.";
		    $this->add_view_var('message', $msg);
		    return 'submitted';
		}
		
		$_SESSION['shared_password'] = $password;
		return true;
	}

	function get_page_view()
	{
		$this->initialise_view();
		$path = dirname(__FILE__)."/../shared_user_pages/".$this->page.".php";
		if (!file_exists($path))
		{
			$msg =  "The page '".$this->page."' could not be found.";
			$this->add_view_var('message', $msg);
			return 'submitted';
		}
		include $path;
		
		$page_obj = new ips_page($this);
		
		$view = $page_obj->get_view();
		
		return $view;
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
		$domain = $_SERVER['SERVER_NAME'];
		$config = new config($domain);
		if ($config === false)
		{
			$config = new config($domain);
		}
		if (!$config->get_val('enable_api'))
		{
			$msg =  "The ionCube Performance System API is not enabled for the current domain.";
			$this->add_view_var('message', $msg);
			return 'message_no_link';
		}


		$cmd = @$_REQUEST['cmd'];

		$this->setup_common_vars();

		//does IPS exist?
		if (!function_exists('ips_version'))
		{
			$php_ini_path = $this->get_php_ini_path();
			$this->add_view_var('php_ini', " <b>$php_ini_path</b>");
			return 'no_ips';
		}

		if ($cmd == "logout")
		{			
			$this->do_logout();
			return "logout";
		}

		
		//have we logged in?
		if (!isset($_SESSION['shared_username']))
		{
		    $ret = $this->do_login();
		    if ($ret !== true)
			return $ret;
		}

		
		return $this->get_page_view();		
    }
}

$c = new index_controller();
$view = $c->get_view();

$c->display_view($view);

?>