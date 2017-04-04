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

	function get_domain_config_val($domain, $key)
	{
		$config = new config($domain);
		return $config->get_val($key);
	}
       
	function setup_domains()
	{
		$model = new table_model("domain_list");
		$restored = $model->session_restore();
		if ($restored)
			$model->process_request();
		
		$headers = array("Domain", "Shared memory used", "Scripts in SHM", "Put/get memory used", "File cache used", "Files in file cache");						
		$links = array( array(	"root" => "index.php?page=status",	"key"  => "domain") );
		$format = array(DF_STRING, DF_STRING, DF_STRING, DF_SIZE_DECIMAL, DF_BIGSIZE_DECIMAL, DF_STRING);
		$align = array(TC_LEFT, TC_RIGHT, TC_RIGHT, TC_RIGHT, TC_RIGHT, TC_RIGHT);
		
		$custom_form_data = array("page" => "status");
		$actions = array();
		
		$model->links = $links;
		$model->custom_form_data = $custom_form_data;
		$model->actions = $actions;
		$model->format = $format;
		$model->headers = $headers;
		$model->alignment = $align;
		$model->reset_commands();
		//$model->add_command("all_domains", "show scripts for all domains");

		if (!$restored || $model->refresh_requested())
		{
			$domains = ips_get_domain_info();
			if (is_array($domains))
			{
				foreach($domains as $k=>$v)
				{
					$summary = ips_summary(0, $v['name']);
					$v['filecache_size'] = $summary['filecache_size'];
					$v['filecache_count'] = $summary['filecache_count'];
					$domains[$k] = $v;

					$max_shm = $this->get_domain_config_val($v['name'], 'max_shm_size');
					if ($max_shm !== null)
						$domains[$k]['shm_used'] = data_format::pretty_size_decimal($domains[$k]['shm_used'])." / ".data_format::pretty_size_decimal($max_shm);
					else
						$domains[$k]['shm_used'] = data_format::pretty_size_decimal($domains[$k]['shm_used']);
				}
				$model->set_assoc_data($domains);
			}
		}

		$model->format_data();

		//update session var after setting the data: so we can remember how many pages there were
		$model->session_save();

		$this->controller->add_view_var('domain_table', $model);
		$panel = $this->controller->fetch('shm_status_domain_panel');
		$this->controller->add_view_var('shm_status_domain_panel', $panel);
	}

	function get_location($entry)
	{
		if ($entry['in_shm'])
			return "Shm";
		else if ($entry['in_filecache'])
			return "File";
		else
			return "-";
	}
			
	function setup_scripts($per_domain)
	{	
	   
		if (!$per_domain || isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == "all_domains")
			$selected_domain = null;
		else
		{
			$selected_domain = @$_REQUEST['domain'];
			if ($selected_domain == null)
				$selected_domain = @$_SESSION['domain'];
			if ($selected_domain == "global" || $selected_domain == "default")
				$selected_domain = null;
		}

		$_SESSION['domain'] = $selected_domain;

		//recall 
		$model = new table_model("script_list");
		$restored = $model->session_restore();
		if ($restored)
			$model->process_request();

		//setup layout stuff specific to this table... but see custom_table_data later
		$links = array();
		$format = array();
		$table_data = array();
		$actions = array("ignore" => "Ignore", "remove" => "Remove");
		$widths = array("100%", "1px", "1px", "1px", "1px");

		$headers = array("Path", "Score", "Hits / s ", "Hits since restart", "Updates", "Location");
		$show_domain_column = (empty($selected_domain) && $per_domain);
		if ($show_domain_column)
			$headers[] = "Domain";

		$show_domain_col_changed = (@$_SESSION['last_show_domain_column'] != $show_domain_column);

		$align = array(TC_LEFT, TC_RIGHT, TC_RIGHT, TC_RIGHT, TC_RIGHT, TC_CENTER, TC_LEFT);

		//headers may change... e.g. displaying list for a particular domain after the list of all domains.
		$model->headers = $headers;
		$model->format = $format;
		$model->alignment = $align;
		$model->widths = $widths;
		$model->links = $links;
		$model->actions = $actions;

		$rpp = prefs::get_val('max_scripts_per_page');
		if ($rpp)
			$model->rows_per_page = $rpp;
		else
			$model->rows_per_page = 15;
		$model->reset_commands();
     
		$show_filecache = 0;
		if (isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == "show_filecache")
			$show_filecache = 1;

		if (@prefs::get_val("show_filecache_always"))
			$show_filecache = 1;
		else
			$model->add_command("show_filecache", "show filecache");

		$domain_change = ($selected_domain != @$model->get_private_data('domain'));

		$full_paths = array();
		$user_names = array();

		$refresh_requested = $model->refresh_requested();
		if (!$restored || $domain_change || $refresh_requested || $show_domain_col_changed)
		{	
		    $_SESSION['last_show_domain_column'] = $show_domain_column;

			$index = ips_get_index();

			//first we need to construct an associative array based on identity of file: path
			$assoc = array();		

			/* Format for rows: 
			 *		name | score | frequency | location (| domain)
			 */

			for ($i = 0;$i<count($index);$i++)
			{
				if (!isset($index[$i]['versions'][0]))
					continue;

				$entry = $index[$i]['versions'][0];
				if (!$entry['in_shm'] && !$entry['in_filecache'])
					continue;	//Don't display index item unless it is in a cache!

				$location = $this->get_location($entry);
				$domain = @$entry['domain'];
				if (!empty($selected_domain))
				{
					if ($domain != $selected_domain)
						continue;
				}
				else if (empty($domain))
				{
					$domain = "<span class=\"gray\">(shared script)</span>";					
				}
				$path = $index[$i]['path'];
				$name = utils::path_ellipsis($path);
				$score = $index[$i]['score'];

				$row = array();
				$row[] = $name;
				if ($score)
					$row[] = $score;
				else
					$row[] = "<span class=\"gray\">pending</span>";

				$row[] = sprintf("%.2f", $index[$i]['frequency']);
				$row[] = $index[$i]['hits'];
				$row[] = $index[$i]['updates'];
				$row['user'] = $entry['user'];
				$row[] = $location;
				
				if (empty($selected_domain) && $per_domain)
					$row[] = $domain;

				$assoc[$path] = $row;	
			}

			if ($show_filecache)
			{
			    $na = "<span class=\"gray\">N/A</span>";
				//echo("FILECACHE");
				//add to assoc array using filecache...
				if (!empty($selected_domain))
				{
					$fcache=ips_get_filecache(0, $selected_domain);
				}
				else
					$fcache=ips_get_filecache(1);

				//utils::pre_dump($fcache);

				foreach($fcache as $item)
				{
					$path = $item['source_path'];
					$domain = $item['domain'];
					if (empty($domain))
					{
						$domain = "<span class=\"gray\">(shared script)</span>";					
					}

					if (!isset($assoc[$path]))
					{
						$name = utils::path_ellipsis($path);

						$row = array();
						$row[] = $name;
						$row[] = $na;		//score;
						$row[] = $na;		//freq;
						$row[] = $na;		//hits;
						$row[] = $na;		//updates
						$row['user'] = $item['user']; //not in final table...
						$row[] = "File cache";
						if (empty($selected_domain) && $per_domain)
							$row[] = $domain;
						$assoc[$path] = $row;
					}
				}
			}
		//	utils::pre_dump($fcache);
			//now use the associative array to build up the table...
			foreach($assoc as $path=>$row)
			{
				$full_paths[] = $path;
				$user_names[] = $row['user']; 
				unset($row['user']);
				$table_data[] = $row;
			}
			
			//setting the data will apply a 'sort'
			$model->set_data($table_data);
			$model->set_custom_column("tooltips", $full_paths);
			$model->set_custom_column("user_names", $user_names);

			$model->format_data();
		}
		else if ($model->clicked())
			$model->format_data();

		

		$full_paths = $model->get_custom_column('tooltips');
		$user_names = $model->get_custom_column('user_names');


	//	utils::pre_dump($user_names);

		$custom_form_data = array("page" => "status");
		$model->custom_form_data = $custom_form_data;

		$model->set_private_data('domain', $selected_domain);

		//update session var after setting the data: so we can remember how many pages there were
		$model->session_save();

		if (empty($selected_domain))
		{
			$full_title = "Scripts cached by all domains";
			$settings_link = "<a href=\"".$_SERVER['PHP_SELF']."?page=settings&amp;domain=global\">[settings]</a>";
		}
		else
		{
			$full_title = "Scripts cached by $selected_domain";
			$settings_link = "<a href=\"".$_SERVER['PHP_SELF']."?page=settings\">[settings]</a>";
		}
	
		$this->controller->add_view_var('script_table', $model);
		$this->controller->add_view_var('scripts_table_title', $full_title);
		$this->controller->add_view_var('settings_link', $settings_link);


		$panel = $this->controller->fetch('shm_status_script_panel');
		$this->controller->add_view_var('shm_status_script_panel', $panel);
	}

	//sel_rows is comma separated list.
	function on_ignore_scripts($selected_domain)
	{
		$sel_rows = $_REQUEST['__selected_rows'];
		$sr_array = explode(",", $sel_rows);

		$model = new table_model("script_list");
		$restored = $model->session_restore(); 
		$full_paths = $model->get_custom_column('tooltips');
		$page_number = $model->page_number;
		$rpp = $model->rows_per_page;
		$offset = $rpp * ($page_number - 1);

		$scripts = array();
		foreach($sr_array as $sr)
			$scripts[] = $full_paths[$offset + $sr];

		$config = new config($selected_domain);
		foreach($scripts as $s)
			$config->add_ignore_pattern($s, true);
		$config->save();

		$outs = "<div style=\"text-align:left;padding:10px;\">".implode("<br>", $scripts)."</div>";

		$this->controller->add_view_var('message', "The following scripts will no longer be cached: <br><br>$outs");
		$this->controller->add_view_var('url', $_SERVER['PHP_SELF']."?page=status&table=script_list&cmd=refresh");
		return 'message';
	}


	function on_remove_scripts($selected_domain)
	{
		$sel_rows = $_REQUEST['__selected_rows'];
		$sr_array = explode(",", $sel_rows);

		$model = new table_model("script_list");
		$restored = $model->session_restore(); 
		$full_paths = $model->get_custom_column('tooltips');
		$user_names = $model->get_custom_column('user_names');
		$page_number = $model->page_number;
		$rpp = $model->rows_per_page;
		$offset = $rpp * ($page_number - 1);

		$scripts = array();
		$uns = array();
		foreach($sr_array as $sr)
		{
			$scripts[] = $full_paths[$offset + $sr];
			$uns[] = $user_names[$offset + $sr];
		}

		$removed = array();
		$failed = array();

		for($i = 0;$i<count($scripts);$i++)
		{
			$s = $scripts[$i];
			$user = $uns[$i];
			if ($selected_domain)
				$res = ips_remove_cached_file($s, false, $selected_domain, $user);
			else
				$res = ips_remove_cached_file($s, true, null, $user);

			if ($res)
				$removed[] = $s;
			else
				$failed[] = $s;
		}

		$message = "";
		if (count($removed))
		{
			$outs = "<div style=\"text-align:left;padding:10px;\">".implode("<br>", $removed)."</div>";
			$message.="The following items have been removed from the cache: <br><br>$outs<br><br";
		}

		if (count($failed))
		{
			$outs = "<div style=\"text-align:left;padding:10px;\">".implode("<br>", $failed)."</div>";
			$message.="The following items could not be removed from the cache: <br><br>$outs<br><br";
		}

		$this->controller->add_view_var('message', $message);
		$this->controller->add_view_var('url', $_SERVER['PHP_SELF']."?page=status&table=script_list&cmd=refresh");
		return 'message';
	}

	function on_table_action()
	{
		$sel_domain = @$_REQUEST['domain'];
		if ($sel_domain == null)
			$sel_domain = @$_SESSION['domain'];
		$sel_table = $_REQUEST['__selected_table'];
		
		if ($sel_table == "script_list")
		{
			if (isset($_REQUEST['ignore']))
				return $this->on_ignore_scripts($sel_domain);
			else if (isset($_REQUEST['remove']))
				return $this->on_remove_scripts($sel_domain);
		}
	}

	function on_default()
    {
		status_base::init();
		$per_domain = config::global_val('per_domain_settings');
		

		if ($per_domain)
			$this->setup_domains();
		$this->setup_scripts($per_domain);
		//$this->setup_putget	($per_domain);
		return 'status_default';
    }

	function get_view()
	{
		
		$cmd = @$_REQUEST['cmd'];
		if(isset($_REQUEST['reset_cache']))
		{
			ips_restart_shm(0, 0);
			$this->controller->add_view_var('message', "Shared memory successfully restarted.");
			$this->controller->add_view_var('url', $_SERVER['PHP_SELF']."?page=status");
			return 'message';
		}
		if ($cmd == "table_action")
			return $this->on_table_action();

		if ($cmd == "view_log")
		{
			$path = config::global_val('log_dir')."ips_log.txt";
			$content = file_get_contents($path);
			die("<pre>\n$content\n</pre>");
		}
		return $this->on_default();
	}
}


?>
