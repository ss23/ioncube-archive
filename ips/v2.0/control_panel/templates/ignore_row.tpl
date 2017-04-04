<?php
/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

function ignore_row_tpl($data)
{	
	$content = "";
	if ($data['domain_name'] != "Default")
		$content = " <tr><td>Scripts to ignore:</td><td><textarea wrap=\"off\" class=\"ignore\" name=\"ignore_string\"><=active_config.ignore_string></textarea></td></tr>";
	return $content;
}


?>