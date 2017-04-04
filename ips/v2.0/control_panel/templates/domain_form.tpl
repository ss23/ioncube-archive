<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >

<h2><=domain_title> settings</h2>
<form action="<=php_self>" method="post">
<div>
<:# we use a combo box instead of a checkbox to cope with default values>

<table width="100%">
<tr><td width="210">Enable cache</td><td><:default_combo class="form" label="" name="enable" value=active_config.enable default=config.enable ></td></tr>
<tr><td width="210">Enable shared memory</td><td><:default_combo class="form" label="" name="enable_shm" value=active_config.enable_shm default=config.enable_shm ></td></tr>
<tr><td>Enable file cache</td><td><:default_combo class="form" label="" name="enable_filecache" value=active_config.enable_filecache default=config.enable_filecache></td></tr>
<tr><td>Enable optimiser</td><td><:default_combo class="form" label="" name="enable_optimiser" value=active_config.enable_optimiser default=config.enable_optimiser><td></tr>
<tr><td width="210">Enable put/get API</td><td><:default_combo class="form" label="" name="enable_putget" value=active_config.enable_putget default=config.enable_putget ></td></tr>
<tr><td width="210">Enable web interface</td><td><:default_combo class="form" label="" name="enable_api" value=active_config.enable_api default=config.enable_api ></td></tr>

<tr><td>Shared memory limit</td><td>
	<input class="form-number" type="text" name="max_shm_size" value="<=active_config.max_shm_size>" />
	<:error_displayer name="max_shm_size" errors=form_errors>
</td></tr>
<tr><td>Shared memory limit for put/get API</td><td>
	<input  class="form-number" type="text" name="max_putget_shm" value="<=active_config.max_putget_shm>" />
	<:error_displayer name="max_putget_shm" errors=form_errors>
</td></tr>

<tr><td>Maximum file cache size:</td><td>
	<input type="text" class="form-number"  name="max_filecache_size" value="<=active_config.max_filecache_size>" />
	<:error_displayer name="max_filecache_size" errors=form_errors>
</td></tr>
<tr><td>Max number of files in file cache:</td><td>
	<input type="text" class="form-number"  name="max_filecache_files" value="<=active_config.max_filecache_files>" />
	<:error_displayer name="max_filecache_files" errors=form_errors>
</td></tr>

<tr><td>Domain password:</td><td>
	<input type="text" class="form-number"  name="domain_password" value="" />
	<:error_displayer name="domain_password" errors=form_errors>
</td></tr>

<!--
<tr><td>File store limit for put/get API:</td><td><input type="text" class="form-number"  name="max_putget_file_store" value="<=active_config.max_putget_file_store>" /></td></tr>
<tr><td>Max number of files in put/get file store:</td><td><input type="text" class="form-number"  name="max_putget_file_store_files" value="<=active_config.max_putget_file_store_files>" /></td></tr> -->

<:ignore_row>
</table>

<input type="submit" value="Update" name="submit_domain"/>
<input type="submit" value="Delete" name="delete_domain"/>
<input type="hidden" name="domain" value="<=domain>" />
<input type="hidden" name="page" value="settings" />
<input type="hidden" name="restart_keys" value="" />
<input type="hidden" name="initial_values" value="<=initial_values>" />

</div>
</form>
<span class="info"><:default_domain_message></span>