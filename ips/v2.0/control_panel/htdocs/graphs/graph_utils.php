<?php

//initialise the app
//ips_initialise_control_panel();

class graph_utils
{
	function do_admin_login()
	{
		if (!ips_admin_login(@$_SESSION['ips_password']))
		{
			utils::redirect("index.php");
			return false;
		}
		return true;
	}
}

?>
