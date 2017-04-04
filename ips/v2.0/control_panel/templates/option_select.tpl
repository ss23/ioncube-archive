<?php
/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

function option_select_tpl($data)
{

	$options = $data['opts'];
	$name	= $data['option_name'];
	$select = $data['select'];
	$form_id = $data['form'];

	$content = "
<select name=\"$name\" onchange=\"javascript:on_change_combo('$form_id')\">
";
	foreach ($options as $row)
	{
		if ($select && ($row['value'] === $select) )
		{
			$content .= "
<option selected=\"selected\" value=\"".$row['value']."\">".$row['msg']."</option>
";

		}
		else
			$content .= "
<option value=\"".$row['value']."\">".$row['msg']."</option>
";
	}
	$content .= "
</select>
";
	return $content;
}

?>
