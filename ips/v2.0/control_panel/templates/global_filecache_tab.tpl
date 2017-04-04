<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >

<form action="<=php_self>" method="post">
<div>
<table width="100%">
	<tr><td width="210">Filecache path:</td><td>
		<input type="text" class="form-dir" name="cache_dir" value="<=active_config.cache_dir>" title="Path to the top level directory of the file cache." />
	</td></tr>
	<tr><td width="210">Maximum file cache size:</td><td>
		<input type="text" class="form-number"  name="global_max_filecache_size" value="<=active_config.global_max_filecache_size>" title="The size should be given in MB or GB." />
		<:error_displayer name="global_max_filecache_size" errors=form_errors>
	</td></tr>
	<tr><td width="210">Maximum number of files in file cache:</td><td>
		<input type="text" class="form-number"  name="global_max_filecache_files" value="<=active_config.global_max_filecache_files>" title="The maximum number of files that should be stored in the file cache." />
		<:error_displayer name="global_max_filecache_files" errors=form_errors>
	</td></tr>
</table>

<input type="hidden" name="tab" value="<=sel_tab>" />
<input type="hidden" name="page" value="<=page>" />
<input type="hidden" name="restart_keys" value="global_enable_shm" />
<input type="submit" value="Update" name="submit_tab" />

</div>
</form>
