<?php

function graphs_domain_tpl($data)
{
	$content = "";
	if (@$data['show'])
	{
		$content = "<img src=\"graphs/domain_shm_usage.php?show_image=1\" usemap=\"#map1\">";

	}
	return $content;
}

?>