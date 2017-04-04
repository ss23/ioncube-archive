<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >

<h2><=domain_name> settings</h2>
<form action="<=php_self>" method="post">
<div>
<:# we use a combo box instead of a checkbox to cope with default values>

<table width="100%">
<tr><td width="210">&nbsp;</td><td>&nbsp;</td></tr>
<:ignore_row>
</table>

<input type="submit" value="Update" name="submit_domain"/>
<input type="hidden" name="domain" value="<=domain>" />
<input type="hidden" name="page" value="settings" />
<input type="hidden" name="restart_keys" value="" />
<input type="hidden" name="initial_values" value="<=initial_values>" />

</div>
</form>
<span class="info"><:default_domain_message></span>