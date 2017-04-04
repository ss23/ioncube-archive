<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >

<div class="maintenance">

<:back_link>
<form method="get" action="<=php_self>">
	<div>
	<table style="margin:0px; width:100%;height:100px; border-bottom:1px solid white;"><tr><td>
		<h3>Cache tidy</h3>
		
		Delete entries not accessed for <input  type="text" name="min_access_time" value="6" /> hours &nbsp;&nbsp;&nbsp;<input type="submit" value="Delete" />
		</td></tr></table>

	<input type="hidden" name="cmd" value="delete_filecache" />
	<input type="hidden" name="page" value="<=page>" />
	</div>
</form>
</div>

<div class="maintenance">
<form method="get" action="<=php_self>">
	<div>
	<table style="margin:0px; width:100%;height:100px; border-bottom:1px solid white;"><tr><td>
		<h3>Export the contents of the index to a file</h3>
		
		Path: <input type="text" name="export_path" style="width:300px" /> <input type="submit" value="Export" />
		</td></tr></table>
	<input type="hidden" name="cmd" value="dump_cache" />
	<input type="hidden" name="page" value="<=page>" />
	</div>
</form>
</div>


<div class="maintenance">
<form method="get" action="<=php_self>">
	<div>
	<table style="margin:0px; width:100%;height:100px; border-bottom:1px solid white;"><tr><td>
		<h3>Recreate the shared memory after the next web server software restart</h3>
		
		<input type="submit" value="Set restart flag" />
		</td></tr></table>

	<input type="hidden" name="cmd" value="reboot_cache" />
	<input type="hidden" name="page" value="<=page>" />
	</div>
</form>

</div>


<div class="maintenance">
<form method="get" action="<=php_self>">
	<div>
	<table style="margin:0px; width:100%;height:100px; border-bottom:1px solid white;"><tr><td>
		<h3>Restart the cache index</h3>
		
		<input type="submit" value="Restart" />
		</td></tr></table>

	<input type="hidden" name="cmd" value="restart_index" />
	<input type="hidden" name="page" value="<=page>" />
	</div>
</form>
</div>

<div class="maintenance">
<form method="get" action="<=php_self>">
	<div>
	<table style="margin:0px; width:100%;height:100px; border-bottom:1px solid white;"><tr><td>
		<h3>Clean the IPS log file</h3>
		
		<input type="submit" value="Clean" />
		</td></tr></table>

	<input type="hidden" name="cmd" value="clean_log" />
	<input type="hidden" name="page" value="<=page>" />
	</div>
</form>
</div>