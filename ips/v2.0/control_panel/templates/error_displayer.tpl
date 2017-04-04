<?php
/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

function error_displayer_tpl($data)
{	
	$content = "";
	$errors = @$data['errors'];
	$name = @$data['name'];
	if (isset($errors[$name]))
	{
		$error_info = $errors[$name];
		$message = $error_info['message'];
		$content = "<span class=\"form-error\">$message</span>";
	}
	return $content;
}
?>