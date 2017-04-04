<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

function negative_tpl($data)
{	
	$val = $data['val'];
	if ($val)
		return "";
	else
		return " disabled=\"1\" ";
	
}

?>
