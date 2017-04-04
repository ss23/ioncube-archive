<?php

function read_file($fname)
{

	$handle = fopen($fname, "rb");
	if ($handle === FALSE)
		return "";

	$contents = '';
	while (!feof($handle)) {
	  $contents .= fread($handle, 8192);
	}
	fclose($handle);

	return $contents;
}

$contents	= read_file("config.txt");
$pos		= strpos($contents, "=")+1;
if (!($pos===FALSE))
	$width		= substr($contents, $pos);

echo(	"<html>"
.		"<title> ionCube Package Foundry Sample</title>"
.		"<body style = 'font: 8pt Tahoma, Arial'>"
.		"<br><br>"
.		"<div align = center>"
.		"<div align = left style='width:400px;border:1px solid #808080;padding:40px;background-color:#F0F0F0'>"
	);


echo(	"You have successfully installed the ionCube Package Foundry sample application.<br><br>\n"
.		"You can run the installer package again to configure the application.<br>\n"
.		"In this example the dimensions of the following image can be configured.<br><br><br>\n"
.		"<div align=center>"
.		"<img src = 'image.png' style='border:1px solid #808080;'");

if (!empty($width) && $width!="?")
	echo("width = $width");
echo(">");
echo("</div>");


echo(	
		"</div>"
.		"</div>"
.		"</body>"
.		"</html>"
);


?>
