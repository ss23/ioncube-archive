<?php
/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

$link_info = array(	"root" => "index.php?page=status",
							"key"  => "domain",
							"column" => 0
							 );					//link will

function do_clone($obj)
{


}

function wrap_link($val, $link_info)
{
	if (!empty($link_info))
	{
		$root = $link_info['root'];
		$key = $link_info['key'];
		$uri = "$root&amp;$key=$val";
		$val = "<a href=\"$uri\">$val</a>";
	}
	return $val;
}

function wrap_header(&$current_table_model, $tb, $text, $link, $i, $rev, $sorted_on_this_idx, $on_right)
{
    $on_right = false;
	if ($sorted_on_this_idx)
	{
		if (!$rev)
			$img = "<img src=\"images/sort_down.gif\" alt=\"down_arrow\" style=\"margin:2px;\" />";
		else
			$img = "<img src=\"images/sort_up.gif\"  alt=\"up_arrow\" style=\"margin:2px;\" />";
	}
	else
		$img = "<img src=\"images/sort_empty.gif\"  alt=\"empty_arrow\" style=\"margin:2px;\" />";

	$new_table_model = clone($current_table_model);
	$new_table_model->sort_idx = $i;
	$new_table_model->page_number = 1;

	$uri = $new_table_model->get_uri($link, 1);

	//$escape = htmlspecialchars($text);
	$escape = $text;
	if ($sorted_on_this_idx || !$on_right)
		$text = "<a href=\"$uri\">$escape</a>$img";
	else
		$text = "$img<a href=\"$uri\">$escape</a>";
	return $text;
}

function get_page_digit(&$current_table_model, $i, $txt, $link, $class, $as_link)
{
	$new_table_model = clone($current_table_model);
	$new_table_model->page_number = $i;
	$uri = $new_table_model->get_uri($link, 0);

	if ($as_link)
		$ret = "<a href=\"$uri\" class=\"$class\" ><b>$txt</b></a> &nbsp; ";
	else
		$ret = "<span class=\"disabled_arrow\" ><b>$txt</b></span> &nbsp; ";
	return $ret;
}
		

function get_page_list(&$current_table_model, $table_name, $data_count, $rows_per_page, $max_pages, $page_number, $link)
{
	
	$html = "<div style=\"text-align:left\">";
	$pages = (int)($data_count / $rows_per_page);
	if ($data_count % $rows_per_page != 0)
		$pages++;

	if ($pages < 2)
		return "&nbsp;";

	$show_left_arrow = ($page_number > 1);
	$show_right_arrow = ($page_number < $pages);

	$overflow = ($pages > $max_pages);
	if ($overflow)
	{
		$min = ((int)(($page_number - 1)/ $max_pages)) * $max_pages + 1;
		$max = min($pages, $min + $max_pages - 1);
	}
	else
	{
		$min = 1;
		$max = $pages;
	}

	$overflow_up = ($pages > $max);
	$overflow_down = ($min != 1);

	$html.=get_page_digit($current_table_model, $page_number-1, "&lt;&lt;", $link, "page_number", $show_left_arrow);

	if ($overflow_down)
		$html.=get_page_digit($current_table_model, $min - $max_pages, "... ", $link, "page_number", true);

	for ($i = $min;$i<$max + 1;$i++)
	{
		if ($page_number == $i)
		{
			$class = "page_number_sel";
			$txt="<b>$i</b>";
		}
		else
		{
			$class = "page_number";
			$txt="<b>$i</b>";
		}
		$html.=get_page_digit($current_table_model, $i, $txt, $link, $class, true);
	}

	if ($overflow_up)
		$html.=get_page_digit($current_table_model, $max+1, "... ", $link, "page_number", true);
	

	$html.=get_page_digit($current_table_model, $page_number+1, "&gt;&gt;", $link, "page_number", $show_right_arrow);
	

	$html.="</div>";
	return $html;
}

function get_align_string($idx, $arr)
{
	$val = @$arr[$idx];
	if ($val === null)
		return "";
	$ret = "";

	
	switch($val)
	{
		case TC_LEFT:
			$ret = " align=\"left\" ";
			break;
		case TC_CENTER:
			$ret = " align=\"center\" ";
			break;
		case TC_RIGHT:
			$ret = " align=\"right\" ";
			break;
	}
	return $ret;
}

function get_form_custom_data($cds)
{
	$html = "";
	foreach($cds as $key => $val)
		$html.= "<input type=\"hidden\" name=\"$key\" value=\"$val\" />\r\n";
	return $html;
}


function get_id_string($id)
{
	if (empty($id))
		return "";
	else
		return "id=\"$id\"";
}


function get_buttons($actions)
{
	$html = "";
	foreach($actions as $k=>$a)
	{

		$html.= "<input type=\"submit\" name=\"$k\" disabled=\"disabled\" value=\"$a\" />\r\n";
	}
	return $html;
}

function get_width_string($i, $arr)
{
	$w = @$arr[$i];
	if ($w != null)
	{
		$w = " style=\"width:$w\" ";
	}
	return $w;
}

function plurify($number, $txt)
{
	if ($number != 1)
		return $txt."s";
	else
		return $txt;

}

function get_manifest($count, $rpp)
{
	$pages = (int)($count / $rpp);
	if ($count % $rpp != 0)
		$pages++;

	$msg = "$count ".plurify($count, "item")." / $pages ".plurify($pages, "page");
	$cont = "<span class=\"gray\">$msg</span>";
	return $cont;

}

function get_empty_table_string($cmds_html)
{

	$content = "<span class=\"gray\">Empty table</span>"
	.			"<table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>"
	.			"<td align=\"left\" width=\"33%\">&nbsp;</td>"
	.			"<td align=\"center\" width=\"33%\">$cmds_html</td>"
	.			"<td align=\"right\" width=\"33%\">&nbsp;</td>"
	.			"</tr></table>";
	return $content;

}

function get_commands_html($root_link, $table_name, $cmds, $auto_refresh)
{
	$as = array();
	foreach($cmds as $c)
	{
		$label = str_replace(" ", "&nbsp;", $c['label']);
		$cmd = $c['cmd'];
		$uri = $root_link."&amp;table=".$table_name."&amp;cmd=$cmd";
		$a = "<a href=\"$uri\">$label</a> ";
		$as[] = $a;
	}

	if ($auto_refresh !== null)
	{	
		if ($auto_refresh)
		{
			$label = "stop auto refresh";
			$uri = $root_link."&amp;auto_refresh=0";
		}
		else
		{
			$label = "auto refresh";
			$uri = $root_link."&amp;auto_refresh=1";
		}
		$a = "<a href=\"$uri\">$label</a> ";
		$as[] = $a;
	}
	return implode(" | ", $as);
}

function get_rows_html($vals, &$model, $fixed_rows = false)
{
	$rows = "";
	$ids = @$model->ids;
	$align = @$model->alignment;
	$widths = @$model->widths;
	$formats = @$model->format;
	$table_name = $model->table_name;

	//if (!$fixed_rows)
		$links = @$model->links;

	for($j = 0; $j<count($vals); $j++)
	{
		$val_row = $vals[$j]["row"];
		$tooltip = @$vals[$j]["tooltips"];
		$idstring = get_id_string(@$ids[$j]);
		$js = "";
		if ($model->selectable_rows)
			$js = "onclick=\"javascript:on_select_row('$table_name', this);\"";
		$r = "<tr $idstring $js  >\r\n";
		for ($i = 0;$i<count($val_row);$i++)
		{
			$tt = "";
			if ($i == 0 && !empty($tooltip))
			{
				//$escape = htmlspecialchars($tooltip);
				$escape = $tooltip;
				$tt = " title=\"$escape\" ";
			}

			$al = get_align_string($i, $align);
			$v = data_format::format_data($val_row[$i], @$formats[$i]);
			//$escpape = htmlspecialchars($v);
			$escpape = $v;
			$v = wrap_link($escpape, @$links[$i]);
			$wid = get_width_string($i, $widths);
			$r.="\t<td $tt $wid $al>$v</td>\r\n";
		}
		$r.= "</tr>\r\n";
		$rows .= $r;
	}

	return $rows;
}

function ips_table_tpl($data)
{	
	$model = @$data['model'];
	$this_page	= $data['root_link'];
	$table_name = $model->table_name;
	$cmds = $model->cmds;
	$auto_refresh = @$model->auto_refresh;
	$cmds_html = get_commands_html($this_page, $table_name, $cmds, $auto_refresh);
	if ($model->is_empty())
	{
		return get_empty_table_string($cmds_html);
	}

	$data_count = $model->get_row_count();
	$headers = $model->headers;
	$vals = $model->get_current_page();
	$fixed_vals = $model->fixed_data;
	$last_update = $model->get_last_update_time();

	$sort_idx = @$model->sort_idx;
	$rev_sort = $model->get_sort_order($sort_idx);
	$rows_per_page = $model->rows_per_page;
	$page_number = $model->page_number;
	$max_pages = $model->max_pages;
	$align = @$model->alignment;
	

	$now = time();
	if ($last_update == 0)
		$last_update = $now;

	//$lu = date("Y/m/d H:i:s", $last_update);
	$lu = data_format::pretty_time_full($now - $last_update);
	if ($rev_sort)
		$r = 0;
	else
		$r = 1;

	$head = "<thead><tr valign=\"bottom\">\r\n";
	for($i = 0;$i<count($headers);$i++)
	{
		$al = get_align_string($i, $align);
		$header = $headers[$i];
		$right_aligned = 0;
		if (isset($align[$i]))
			$right_aligned = ($align[$i] == TC_RIGHT);
		$h = wrap_header($model, $table_name, $header, $this_page, $i, $r, $sort_idx == $i, $right_aligned);
		$adjust = $h;
		if (@$align[$i] == TC_CENTER)
			$adjust = "<span style=\"position:relative;left:5px;\">$h</span>";
		$head.="\t<th $al>$adjust</th>\r\n";
	}
	$head.= "</tr></thead>\r\n";

	$rows = "";
	
	if (count($fixed_vals))
	{
		$width = count($fixed_vals[0]["row"]);
		$rows .= "<tbody class=\"fixed_rows\">";
		$rows .= get_rows_html($fixed_vals, $model, true);
		$rows .= "<tr class=\"row_gap\"><td colspan=\"$width\"></td></tr>";
		$rows .="</tbody>";
	}

	$rows .= "<tbody>";
	$rows .= get_rows_html($vals, $model);
	$rows.="</tbody>";
	
	$form_custom_data = get_form_custom_data($model->custom_form_data);
	$buttons = get_buttons($model->actions);

	$page_list = get_page_list($model, $table_name, $data_count, $rows_per_page, $max_pages, $page_number, $this_page);
	$manifest = get_manifest($data_count, $rows_per_page);
	
	$form_id = "form_$table_name";
	$selected_rows_id = "selected_rows_$table_name";
	
	//$top = "<div style=\"text-align:right\"><span style=\"text-align:left\" class=\"info\">Click the headers to sort, and the rows to select.</span></div>";
	$top = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\"><tr valign=\"bottom\"><td align=\"left\">$page_list</td><td align=\"right\" class=\"info\">Click the headers to sort, and the rows to select.</td></tr></table>";

	$content = "\r\n$top\r\n"
	.			"<form id=\"$form_id\" class=\"table_form\" action=\"".$_SERVER['PHP_SELF']."\">\r\n"
	.			"<div>"
	.			"<table id=\"$table_name\" class=\"ips\" width=\"100%\">\r\n"
	.			"$head"
	.			"$rows"
	.			"</table>\r\n"
	.			"<table class=\"ips_table_footer\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr valign=\"top\">\r\n"
	.			"<td  align=\"left\" >&nbsp;</td>\r\n"
	.			"<td align=\"center\" ><span class=\"gray\">updated: $lu</span><br />$cmds_html </td>\r\n"
	.			"<td align=\"right\" >$manifest</td>\r\n"
	.			"</tr></table>\r\n"
	.			"$form_custom_data\r\n"
	.			"<input id=\"$selected_rows_id\" type=\"hidden\" name=\"__selected_rows\" value=\"\" />\r\n"
	.			"<input type=\"hidden\" name=\"__selected_table\" value=\"$table_name\" />\r\n"
	.			"<input type=\"hidden\" name=\"cmd\" value=\"table_action\" />\r\n"
	.			"<br /><div style=\"text-align:left\">$buttons</div>\r\n"
	.			"</div>\r\n"
	.			"</form>\r\n";
	return $content;
}

?>
