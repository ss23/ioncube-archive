<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

define ("TC_LEFT", 1);
define ("TC_CENTER", 0);
define ("TC_RIGHT", 2);


//urghh!!! But usort's callback cannot take user_data....
$current_sort_idx = -1;
$reverse_sort = 0;

function table_model_sorter($a, $b)
{
	global $current_sort_idx;
	global $reverse_sort;
	$a0 = $a["row"][$current_sort_idx];
	$b0 = $b["row"][$current_sort_idx];
	if ($a0 == $b0)
        {
		$a0 = $a["row"][0];
		$b0 = $b["row"][0];
		if ($a0 == $b0)
		    return 0;

		return ($b0 < $a0) ? 1 : -1;
	}

	if ($reverse_sort)
		return ($b0 < $a0) ? -1 : 1;
	else
		return ($a0 < $b0) ? -1 : 1;
}

class table_model
{
	var $sort_idx;
	var $reverse_states;	//sort reverse states: for each column index
	var $data;				//main data... paginated
	var $fixed_data;		//extra data: not paginated.
	var $headers;
	var $format;
	var $table_name;
	var $links;
	var $root_link;
	var $rows_per_page;
	var $max_pages;
	var $page_number;
	var $custom_form_data;
	var $ids;
	var $actions;	//translated into buttons
	var $widths;
	var $cmds;		//translated into links at bottom of table...
	var $private_data;
	var $selectable_rows;
	var $auto_refresh = null;

	function table_model($table_name)
	{
		$this->table_name = $table_name;

		$this->data = array();
		$this->custom_form_data = array();
		$this->ids = array();
		$this->actions = array();
		$this->reverse_states = array();
		$this->widths = array();

		$this->sort_idx = 0;
		$this->rows_per_page = 15;
		$this->page_number = 1;
		$this->max_pages = 5;

		$this->reset_commands();
		$this->private_data = array();
		$this->selectable_rows = true;
	}

	function set_private_data($key, $val)
	{
		$this->private_data[$key] = $val;
	}

	function get_private_data($key)
	{
		return @$this->private_data[$key];
	}

	function get_last_update_time()
	{
		$val = @$_SESSION['tables'][$this->table_name]['update_time'];
		return $val;
	}

	function _on_updated()
	{
		$_SESSION['tables'][$this->table_name]['update_time'] = time();
	}

	function session_save()
	{
		$this->fix_page_number();

		$arr = array();
		foreach($this as $k => $v)
		{
			$arr[$k] = $v;
		}
		$_SESSION['tables'][$this->table_name]['data'] = serialize($arr);
	}

	function reset_commands()
	{
		$refresh = array("label"=>"refresh", "cmd"=>"refresh");
		$this->cmds = array($refresh);
	}

	function add_command($cmd, $label)
	{
		$c = array("cmd"=>$cmd, "label"=>$label);
		$this->cmds[] = $c;
	}

	function session_restore()
	{
	//	return false;
		$ret = false;
		
		if (isset($_SESSION['tables'][$this->table_name]) && is_array($_SESSION['tables'][$this->table_name]))
		{
			$ser_array = unserialize($_SESSION['tables'][$this->table_name]['data']);
			foreach($this as $k => $v)
			{
				if (isset($ser_array[$k]))
				{
					$sval = $ser_array[$k];
					$this->$k = $sval;
				}
			}
			$ret = true;
		}

		$this->fix_page_number();
		return $ret;
	}

	function clicked()
	{
		$selected_table = @$_REQUEST['table'];
		$clicked_table = ($selected_table == $this->table_name);
		return $clicked_table;
	}

	function refresh_requested()
	{
		$res = false;
		if ($this->clicked())
		{
			if (isset($_REQUEST['cmd']))
			{
				$res = true;
			}
		}
		return $res;
	}

	function process_request()
	{
		$selected_table = @$_REQUEST['table'];
		$clicked_table = ($selected_table == $this->table_name);
		if ($clicked_table)
		{
			if (isset($_REQUEST['cmd']) && $_REQUEST['cmd'] == "refresh")
			{

			}
			if (isset($_REQUEST['h']))
			{
				//header click
				$h = $_REQUEST['h'];

				//if we clicked a header then the model must exist!
				if ($this->sort_idx == $_REQUEST['h'])
				{
					$this->toggle_sort_order($h);
				}					
				$this->page_number = 1;
				$this->sort_idx = $h;
			}
	
			if (isset($_REQUEST['pn']))
			{
				//page number click
				$this->page_number = $_REQUEST['pn'];
			}
		}
	}

	function toggle_sort_order($idx)
	{
		$old = @$this->reverse_states[$idx];
		$this->reverse_states[$idx] = !$old;
	}

	function get_sort_order($idx)
	{
		return @$this->reverse_states[$idx];
	}

	function _sort()
	{
		global $current_sort_idx;
		global $reverse_sort;

		$current_sort_idx = $this->sort_idx;
		$reverse_sort = @$this->reverse_states[$current_sort_idx];

		usort($this->data, 'table_model_sorter');
	}

	function set_fixed_data($vals)
	{
		$this->fixed_data = array();
		foreach($vals as $v)
		{
			$item = array("row" => $v);
			$this->fixed_data[] = $item;
		}
		$this->_on_updated();
	}

	function set_data($vals)
	{
		$this->data = array();
		foreach($vals as $v)
		{
			$item = array("row" => $v);
			$this->data[] = $item;
		}
		$this->_on_updated();
	}

	function set_assoc_data($data)
	{
		$vals = array();
		foreach($data as $info)
		{
			$item = array();
			foreach($info as $in)
				$item[] = $in;
			$vals[] = $item;
		}
		return $this->set_data($vals);
	}

	function set_keyed_data($data)
	{
		$vals = array();
		foreach($data as $d => $info)
		{
			$item = array();
			$item[] = $d;
			$item[] = $info;
			$vals[] = $item;
		}
		return $this->set_data($vals);
	}

	function set_keyed_assoc_data($data, $fixed = false)
	{
		$vals = array();
		foreach($data as $d => $info)
		{
			$item = array();
			$item[] = $d;
			foreach($info as $i)
			{
				$item[] = $i;
			}
			$vals[] = $item;
		}
		if ($fixed)
			return $this->set_fixed_data($vals);
		else
			return $this->set_data($vals);
	}

	function format_data()
	{
		$this->_sort();	
	}

	function set_custom_column($colid, $data)
	{
		
		for($i = 0;$i<count($this->data);$i++)
		{
			$this->data[$i][$colid] = $data[$i];
		}
	}


	function get_custom_column($colid)
	{	
		$ret = array();
		for($i = 0;$i<count($this->data);$i++)
		{
			$ret[] = $this->data[$i][$colid];
		}
		return $ret;
	}

	function get_column_number($num)
	{
		$ret = array();
		for($i = 0;$i<count($this->data);$i++)
		{
			$ret[] = $this->data[$i]["row"][$num];
		}
		return $ret;

	}

	function remove_column($colid)
	{
		for ($i = 0;$i<count($this->data);$i++)
			unset($this->data[$i][$colid]);
	}

	function is_empty()
	{
		return (count($this->data) == 0);
	}

	function get_start_row()
	{
		$start_idx = ($this->page_number - 1) * $this->rows_per_page;
		return $start_idx;
	}

	function fix_page_number()
	{
		$data_count = count($this->data);
		$start_idx = $this->get_start_row();
		if ($start_idx >= $data_count)
		{
			$this->page_number = 1;
		}
	}

	function get_current_page()
	{	
		$ret = array();
		$start_idx = $this->get_start_row();
		$ret = array_slice($this->data, $start_idx, $this->rows_per_page);
		return $ret;
	}

	function get_row_count()
	{
		return count($this->data);
	}

/*
	function get_refresh_uri($root_link)
	{
		$uri = $root_link."&amp;table=".$this->table_name."&amp;cmd=refresh";
		return $uri;
	}
*/

	function get_uri($root_link, $header_link)
	{
		$sort = "";
		$rpp = "";
		$pn = "";

		if ($header_link && $this->sort_idx !== null)
			$sort = "&amp;h=".$this->sort_idx;
		if (!$header_link && $this->page_number !== null)
			$pn = "&amp;pn=".$this->page_number;

		$uri = $root_link."&amp;table=".$this->table_name.$sort.$rpp.$pn;
		return $uri;
	}
}
?>