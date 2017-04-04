<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >
<div class="ds_outer">
<form action="<=php_self>" method="get" class="domain_selector" id="domain_selector">
<div class="domain_selector">
<table width="100%" cellpadding="0" cellspacing="0"><tr>

<td align="left" nowrap="nowrap" width="140">
Select domain to configure:
</td>

<td align="left" nowrap="nowrap">
	<:option_select option_name=domain_list.on opts=domain_list.opts select=domain_list.select form="domain_selector">
	<input type="submit" name="change_domain" value="Go" />
	<input type="hidden" name="page" value="<=page>" />	
</td>

<td align="right" nowrap="nowrap">
	<a href="<=php_self>?page=settings&amp;cmd=disable_per_domain_settings">Disable per-domain settings</a>	
</td></tr></table>
</div>
</form>
</div>

