<?php

function auto_refresh_link_tpl($data)
{
	$content = "";
	$auto = @$data['auto_refresh'];
	$page = @$data['page'];
	if ($auto)
		$auto_refresh_label = "auto_refresh";
	else
		$auto_refresh_label = "stop auto_refresh";

	$content = "<a href=\"".$_SERVER['PHP_SELF']."?page=$page&amp;auto_refresh=$auto\">$auto_refresh_label</a>";
	return $content;
}

?>