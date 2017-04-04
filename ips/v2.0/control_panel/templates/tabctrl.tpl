<?php
/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

function tabctrl_tpl($data)
{	
	$tabs = @$data['tabs'];
	$selected_tab = @$data['selected_tab'];
	$tab_tplate = $selected_tab;
	$tf = new TF(TEMPLATES_DIR);
	$tab_content = $tf->render($tab_tplate, $data); 
	$this_page	= $data['root_link'];

	if ($tabs == null)
		return "";

	$content = "<div class=\"tab_total\">\r\n"
	.			"<table cellspacing=\"0\" class=\"tab\" width=\"100%\"><tr>";
	foreach($tabs as $label => $tpl)
	{
		$link = "<a href=\"$this_page&amp;tab=$tpl\">$label</a>";
		if ($tpl == $selected_tab)
			$content.="<td class=\"sel_tab\">$link</td>";
		else
			$content.="<td class=\"unsel_tab\">$link</td>";

	}
	$content.="<td class=\"spare\">&nbsp;</td>";
	$content.="</tr></table>";

	$content.=	"<div class=\"tab\">\r\n"
	.			"$tab_content\r\n"
	.			"</div>\r\n"
	.			"</div>\r\n";

	return $content;

}

?>