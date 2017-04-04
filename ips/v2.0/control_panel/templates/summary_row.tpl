<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

function summary_row_tpl($data)
{
	$content = "";
	$val = @$data['value'];
	if ($val !== null)
	{
		$name = @$data['name'];
		$row = "<tr><td align=\"left\">$name</td><td align=\"right\">$val</td></tr>";
		$content = $row;
	}
	return $content;
}

?>

