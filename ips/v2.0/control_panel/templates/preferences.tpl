<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >


<div class="domain_config" style="margin-top:1px;">

<:back_link>

<form action="<=php_self>" method="post">
<table width="100%">
<tr><td width="300">Maximum components shown in paths:</td><td><input class="form-number" type="text" name="max_path_components" value="<=prefs.max_path_components>" /></td></tr>

<tr><td width="300">Number of components to hide in script paths:</td><td><input class="form-number" type="text" name="components_to_hide" value="<=prefs.components_to_hide>" /></td></tr>

<tr><td width="300">Number of scripts to show per page:</td><td><input class="form-number" type="text" name="max_scripts_per_page" value="<=prefs.max_scripts_per_page>" /></td></tr>

<tr><td width="300">Auto refresh interval (seconds):</td><td><input class="form-number" type="text" name="auto_refresh" value="<=prefs.auto_refresh>" /></td></tr>

<tr><td colspan="2"><:checkbox label="Always display file cache in scripts table" name="show_filecache_always" value="1" checked=prefs.show_filecache_always></td></tr>


</table>
<input type="submit" value="Update" name="submit_prefs"/>
<input type="hidden" name="page" value="<=page>" />
<input type="hidden" name="cmd" value="submit" />
</div>
</form>

</div>


