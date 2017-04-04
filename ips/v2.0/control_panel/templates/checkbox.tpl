<?php
/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

function checkbox_tpl($data)
{	
	$name = $data['name'];
	$label = $data['label'];
	$value = $data['value'];
	$checked = $data['checked']?"checked=\"checked\"":" ";
	$tt = @$data['tt'];
	$tool = "";
	if (!empty($tt))
		$tool = " title=\"$tt\" ";
	$link_to_fields = @$data['link_to_fields'];
	if (!$link_to_fields)
		$content = "<input type=\"checkbox\" name=\"$name\" $tool value=\"$value\" $checked /> <span $tool>$label</span>";
	else
		$content = "<input type=\"checkbox\" name=\"$name\" $tool value=\"$value\" $checked onclick=\"javascript:on_click_checkbox(this, '$link_to_fields');\" /> <span $tool>$label</span>";
	return $content;
}

?>
