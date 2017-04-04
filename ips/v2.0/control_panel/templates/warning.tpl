<?php

function warning_tpl($data)
{
    $msg = @$data['warn'];
    $content = "";
    if (!empty($msg))
	$content = "<div class=\"warning\">Warning: $msg</div>";
    return $content;
}

?>

