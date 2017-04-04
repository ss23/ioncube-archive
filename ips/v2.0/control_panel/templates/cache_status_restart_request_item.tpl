<?php

function cache_status_restart_request_item_tpl($data)
{
	$content = "";
	$stats = @$data['stats'];
	$restart_interval = @$stats['shm_restart_request_date'];
	if ($restart_interval !== null)
	{
		$text = "Time since restart requested: <span class=\"disabled\">$restart_interval</span>";
		$content = "$text &nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Force Restart\" style=\"padding:2px;margin:0px;\" />";
		$content .= "<input type=\"hidden\" name=\"cmd\" value=\"force_restart\" />";
	}
	return $content;
}

?>
