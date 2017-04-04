<?php

function meta_tags_tpl($data)
{
	$content = "";
	if (isset($data['meta_refresh_interval']))
	{
		$interval = $data['meta_refresh_interval'];
		$content = "<meta http-equiv=\"refresh\" content=\"$interval\">";
	}
	return $content;
}

?>