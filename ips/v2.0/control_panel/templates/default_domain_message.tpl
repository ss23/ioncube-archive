<?php
/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

function default_domain_message_tpl($data)
{	
	$content = "";
	if ($data['domain_name'] != "Default")
		$content = "Leave a field blank to set the value to the default domain value.";
	return $content;
}

?>
