<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >

<form action="<=php_self>" method="post">
<div>
<table width="800">
<tr valign="top">


<td>
<:checkbox label="Log startups" name="log_startups" value="1" checked=log_options.startups><br />
<:checkbox label="Log shutdowns" name="log_shutdowns" value="1" checked=log_options.shutdowns><br />
<:checkbox label="Log requests" name="log_requests" value="1" checked=log_options.requests><br />
<:checkbox label="Log restarts" name="log_restarts" value="1" checked=log_options.restarts><br />
</td>
<td>
<:checkbox label="Log permissions errors" name="log_permissions" value="1" checked=log_options.permissions><br />
<:checkbox label="Log shared memory overflow errors" name="log_shm_overflows" value="1" checked=log_options.shm_overflows><br />
<:checkbox label="Log putget" name="log_putget" value="1" checked=log_options.putget><br />
<:checkbox label="Log lookups" name="log_lookups" value="1" checked=log_options.lookups><br />
<:checkbox label="Log updates" name="log_updates" value="1" checked=log_options.updates><br />
</td>
</tr>
<tr><td colspan="2">
<:checkbox label="Enable cache index checksums" name="enable_index_checksum" value="1" checked=active_config.enable_index_checksum><br />
<:checkbox label="Enable PHP script checksums" name="enable_script_checksum" value="1" checked=active_config.enable_script_checksum><br />
</td></tr>
</table>
<br /><br />
<table width="100%">
	<tr><td width="100">IPS log directory:</td><td><input type="text" class="form-dir" name="log_dir" style="width:100%" value="<=active_config.log_dir>" /></td></tr>
</table>

<input type="hidden" name="tab" value="<=sel_tab>" />
<input type="hidden" name="page" value="<=page>" />
<input type="hidden" name="restart_keys" value="global_enable_shm" />
<input type="submit" value="Update" name="submit_tab"/>

</div>

</form>
