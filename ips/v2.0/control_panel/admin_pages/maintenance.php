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

	function on_delete_filecache()
	{
		$t = $_REQUEST['min_access_time'];
		$h = $t * 60 * 60; 
		$arr = ips_file_cache_delete($h);
		if ($arr === false)
		{
			$msg =  "Unable to delete the files from the file cache.";
		}
		else
		{
			$num = $arr['num'];
			$size = data_format::pretty_size_decimal($arr['size'], 1);
			$msg =  "$num files deleted ($size).";
		}
		$this->controller->add_view_var('message', $msg);
		return 'submitted';
	}

	function on_display_cache_dump()
	{
		$config = ips_get_config();
		$path = $_REQUEST['path'];
		if (file_exists($path))
		{
			$cont = file_get_contents($path);
			header('Content-Type: text/plain; charset=utf-8');
			echo($cont);
			exit(0);
		}
		else
		{
			$this->controller->add_view_var('message', "The file could not be found.");
			return 'submitted';
		}
	}

	function on_dump_cache()
	{
		$config = ips_get_config();
		$path = @$_REQUEST['export_path'];
		if (empty($path))
		{
			$this->controller->add_view_var('message', "Please enter the path where the cache export file should be saved then try again.");
			return 'submitted';
		}

		if (ips_dump_cache($path))
		{
			$enc = urlencode($path);
			$link = "<a href=\"".$_SERVER['PHP_SELF']."?page=maintenance&amp;cmd=display_cache_dump&amp;path=$enc\" target=\"_blank\" >$path</a>";
			$this->controller->add_view_var('message', "The contents of the cache have been written to the file:<br>$link");
		}
		else
		{
			$this->controller->add_view_var('message', "The cache could not be written to the path:<br>$path");
		}
		return 'submitted';
	}

	function on_reboot_cache()
	{
		$res = ips_reboot_shm();
		if (!$res)
			$msg = "The cache could not be restarted. Please try again in a few seconds.";
		else
			$msg = "The shared memory will be reinitialized the next time the web server software is restarted.";
		$this->controller->add_view_var('message', $msg);
		return 'submitted';
	}

	function on_restart_index()
	{
		$res = ips_restart_shm(false, false);
		if (!$res)
			$msg = "The cache could not be restarted.";
		else
			$msg = "The shared memory has been restarted.";
		$this->controller->add_view_var('message', $msg);
		return 'submitted';
	}

	function on_clean_log()
	{
		$config = new config;
		$log_dir = $config->get_val('log_dir');
		$logpath=$log_dir."ips_log.txt";
		$fp = @fopen($logpath, "w");
		if ($fp != null)
		{
			fclose($fp);
			$msg = "The log file has been cleaned.";
			$this->controller->add_view_var('message', $msg);
			return 'submitted';
		}
		else
		{
			$msg = "The log file could not be opened for writing.";
			$this->controller->add_view_var('message', $msg);
			return 'submitted';
		}
	}

	function on_default()
    {
		return "maintenance_default";
	}

	function get_view()
	{
		$cmd = @$_REQUEST['cmd'];
		if ($cmd == "delete_filecache")
			return $this->on_delete_filecache();
		else if ($cmd == "dump_cache")
			return $this->on_dump_cache();
		else if ($cmd == "display_cache_dump")
			return $this->on_display_cache_dump();
		else if ($cmd == "reboot_cache")
			return $this->on_reboot_cache();
		else if ($cmd == "restart_index")
			return $this->on_restart_index();
		else if ($cmd == "clean_log")
			return $this->on_clean_log();
		return $this->on_default();
	}
}


?>
