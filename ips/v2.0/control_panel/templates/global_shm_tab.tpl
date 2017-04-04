<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >

<form action="<=php_self>" method="post">
<div>

<table>

<tr><td class="settings_label">Number of cache locks:</td><td>
	<input class="form-number" type="text" name="lock_count" value="<=active_config.lock_count>" title="The number of locks used in the shared memory cache index. Advanced use only." />  
	<:error_displayer name="lock_count" errors=form_errors><br />
</td></tr>
<tr><td>Number of cache slots:</td><td>
	<input class="form-number" type="text" name="bucket_count" value="<=active_config.bucket_count>" title="The number of slots used in the shared memory cache index hash table. Advanced use only." />
	<:error_displayer name="bucket_count" errors=form_errors><br />
</td></tr>
<tr><td>Shared memory restart interval:</td><td>
	<input class="form-number" type="text" name="shm_restart_interval" value="<=active_config.shm_restart_interval>" title="The time between restarts of the shared memory cache (units are s, m, h, or d)" />
	<:error_displayer name="shm_restart_interval" errors=form_errors><br />
</td></tr>
<tr><td>Shared memory limit:</td><td>
	<input type="text" class="form-number" name="global_max_shm_size" value="<=active_config.global_max_shm_size>" title="The total shared memory that should be used by IPS." />
	<:error_displayer name="global_max_shm_size" errors=form_errors><br />
</td></tr>
<tr><td>Memory to reserve for cache index:</td><td>
	<input type="text" class="form-number" name="cache_reserve" value="<=active_config.cache_reserve>" title="The memory that should be reserved for the cache index, and not used to cache PHP scripts." />
	<:error_displayer name="cache_reserve" errors=form_errors><br />
</td></tr>
</table>
<input type="hidden" name="tab" value="<=sel_tab>" />
<input type="hidden" name="page" value="<=page>" />
<input type="hidden" name="restart_keys" value="global_enable_shm" />
<input type="hidden" name="initial_values" value="<=initial_values>" />
<input type="submit" value="Update" name="submit_tab"/>
</div>

</form>
