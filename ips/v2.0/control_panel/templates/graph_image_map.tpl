<?php

function graph_image_map_tpl($data)
{
	$graph_name = $data['type'];
	$name = $data['name'];

	$path = dirname(__FILE__)."/../htdocs/graphs/$graph_name.php";
	if (!file_exists($path))
		return "";
	include_once($path);
	$class_name = "${graph_name}_graph";
	$graph = new $class_name;
	$map = $graph->get_map();

	$content = "<map name=\"$name\">$map</map>";
	return $content;
}

?>
