<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><=title></title>
<link href="styles.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="script.js"></script>
</head>
<body>

<div class="menu">
<table width="100%"><tr><td><=menu></td><td><a href="<=self>?cmd=logout">logout</a></td></tr></table>
</div>
<:warning warn=top_warning>

<div class="content">
<=page_content>
</div>

<table width="100%" class="footer" style="position:absolute;bottom:15px;"><tr>
    <td align="left">IPS web app version: <=app_version></td><td align="right">IPS version: <=ips_version></td>
</tr></table>

</body>
</html>