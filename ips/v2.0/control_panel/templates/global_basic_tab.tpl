<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >

<form action="<=php_self>" method="post">
<div>
<table width="800">
	<tr valign="top">
		<td>		
		<:checkbox label="Enable IPS" name="global_enable" value="1" tt="Enable/disable caching of PHP scrips." checked=active_config.global_enable><br />
		<:checkbox label="Enable per-domain settings" name="per_domain_settings" value="1" tt="Per-domain settings allow the independent IPS configuration of multiple domains." checked=active_config.per_domain_settings><br />
		<:checkbox label="Enable shared memory" name="global_enable_shm" value="1" tt="The shared memory cache is the fastest cache and should be enabled if possible." checked=active_config.global_enable_shm><br />
		<:checkbox label="Enable file cache" name="global_enable_filecache" value="1" tt="The file cache will be used if the IPS shared memory cache is disabled or full." checked=active_config.global_enable_filecache><br />
		<:checkbox label="Enable persistent cache" name="persistent_shm_cache" value="1" tt="The persistent cache allows scripts to remain in shared memory even when the server is restarted." checked=active_config.persistent_shm_cache><br />
		<:checkbox label="Enable put/get API" name="global_enable_putget" value="1" tt="PHP scripts may store values in files or shared memory using the put/get API." checked=active_config.global_enable_putget><br />
		
		<:checkbox label="Expose settings in phpinfo" name="expose_in_phpinfo" value="1" tt="Check this box to display certain IPS status values and options in the output of the 'phpinfo' function." checked=active_config.expose_in_phpinfo><br />
		<:checkbox label="Enable optimiser" name="enable_optimiser" value="1" tt="Enable the optimiser to dramatically reduce the space scripts take in shared memory." checked=active_config.enable_optimiser><br />

		<:# Must be the last checkbox: associated with scheduling text input tags>
		<:checkbox label="Enable scheduling" link_to_fields="schedule_start,schedule_stop" name="enable_scheduling" value="1" tt="Scheduling allows IPS to be run only between certain times of day." checked=active_config.enable_scheduling><br />
		</td>
	</tr>
</table>
<table width="100%">
	<tr><td width="180">Schedule start time:</td><td>
		<input id="schedule_start" type="text" class="form-dir" name="schedule_start" <:negative val=active_config.enable_scheduling>" value="<=active_config.schedule_start>" />
		<:error_displayer name="schedule_start" errors=form_errors>
	</td></tr>
	<tr><td>Schedule stop time:</td><td>
		<input id="schedule_stop" type="text" class="form-dir" name="schedule_stop" <:negative val=active_config.enable_scheduling> value="<=active_config.schedule_stop>" />
		<:error_displayer name="schedule_stop" errors=form_errors>
	</td></tr>
	<tr><td>New admin password:</td><td><input type="password" class="form-dir" name="admin_password" value="" /></td></tr>
	<tr><td>Confirm new admin password:</td><td>
		<input type="password" class="form-dir" name="admin_password2" value="" />
		<:error_displayer name="admin_password2" errors=form_errors>
	</td></tr>
</table>
</div>

<input type="hidden" name="tab" value="<=sel_tab>" />
<input type="hidden" name="page" value="<=page>" />
<input type="hidden" name="restart_keys" value="global_enable_shm" />
<input type="submit" value="Update" name="submit_tab" />
<input type="hidden" name="initial_values" value="<=initial_values>" />

</form>
