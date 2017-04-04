<?php
function back_link_tpl($data)
{
	$content = "";
	$sticky = $_SESSION['sticky_page'];
	$link = $_SERVER['PHP_SELF']."?page=$sticky";
	if (!empty($sticky))
		$sticky[0] = strtoupper($sticky[0]);
    $content = "<a href=\"$link\">Back to <b>$sticky</b> page</a>";
    return $content;
}

?>