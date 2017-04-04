<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >

<table width="100%" cellspacing="1" class="traffic_lights">
	<tr><td class="<=green_class>">status: enabled</td><td class="<=orange_class>">status: restarting</td><td class="<=red_class>">status: disabled</td></tr>
</table>

<br />
&nbsp;<:auto_refresh_link>
<br />
<table bgcolor=white width="100%"><tr><td>
	<img src="graphs/shm_usage.php">
	<:graphs_domain show=show_domain_pie>
	
	<:graph_image_map type="domain_shm_usage" name="map1">
	<img src="graphs/request_minmeanmax.php">
</td></tr></table>