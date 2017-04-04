<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

session_start();
//ips_initialise_control_panel();



define("TEMPLATES_DIR",		dirname(__FILE__)."/../templates");
define("SITE_ROOT",			"http://localhost/app/htdocs");


if (version_compare(phpversion(), '5.0') < 0)
	include_once(dirname(__FILE__)."/cloner.php");

include_once(dirname(__FILE__)."/charts.php");
include_once(dirname(__FILE__)."/app_version.php");
include_once(dirname(__FILE__)."/prefs.php");
include_once(dirname(__FILE__)."/utils.php");
include_once(dirname(__FILE__)."/data_format.php");
include_once(dirname(__FILE__)."/table_model.php");
include_once(dirname(__FILE__)."/compat.php");
include_once(dirname(__FILE__)."/template.php");
include_once(dirname(__FILE__)."/controller.php");

?>
