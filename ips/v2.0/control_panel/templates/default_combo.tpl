<?php
/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

function default_combo_tpl($data)
{	
	$label = $data['label'];
	$val = $data['value'];
	$name = $data['name'];
	$d = $data['default'];
	$class = @$data['class'];

	$s1 = "";
	$s2 = "";
	$s3 = "";
	if ($val)
		$s1 = "selected=\"selected\"";
	else if ($val === null)
		$s3 = "selected=\"selected\"";
	else
		$s2 = "selected=\"selected\"";
	if ($d)
		$def = "on";
	else
		$def = "off";

		if (!empty($class))
			$class = " class=\"$class\" ";
	
		
	$content = "$label
<select name=\"$name\" $class>
<option value=\"1\" $s1>On</option>
<option value=\"0\"  $s2>Off</option>";
if ($data['domain'] != "default")
	$content.="
<option value=\"-1\" $s3>Default ($def)</option>
";
$content.="
</select>
";
	return $content;
}

?>
