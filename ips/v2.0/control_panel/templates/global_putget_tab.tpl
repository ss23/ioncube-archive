<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >

<form action="<=php_self>" method="post">
<div>

<table width="100%">
	<tr><td width="180">Shared memory limit for put/get API:</td><td>
		<input type="text" class="form-number"  name="global_max_putget_shm" value="<=active_config.global_max_putget_shm>" title="Maximum shared memory that IPS can use for the put/get functions. Specify the size in KB or MB " />
		<:error_displayer name="global_max_putget_shm" errors=form_errors>
	</td></tr>
	<!--
	<tr><td>File store limit for put/get API:</td><td><input type="text" class="form-number"  name="global_max_putget_file_store" value="<=active_config.global_max_putget_file_store>"/></td></tr>
	<tr><td>Max number of files in put/get file store:</td><td><input type="text" class="form-number"  name="global_max_putget_file_store_files" value="<=active_config.global_max_putget_file_store_files>"/></td></tr> -->
	<tr><td>Put/get file store path:</td><td><input type="text" class="form-dir" name="putget_dir" value="<=active_config.putget_dir>" title="The path to the top-level directory containing files used by the put/get API functions." /></td></tr>

</table>

<input type="hidden" name="tab" value="<=sel_tab>" />
<input type="hidden" name="page" value="<=page>" />
<input type="hidden" name="restart_keys" value="global_enable_shm" />
<input type="submit" value="Update" name="submit_tab"/>


</div>


</form>
