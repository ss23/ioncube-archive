<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >

<form action="<=php_self>" method="post">
<div>

Scripts to ignore
<textarea wrap="off" class="ignore" name="ignore_string" title="Paths to scripts that should not be cached. Wildcards and relative paths are allowed."><=active_config.ignore_string></textarea>

<br /><br />
Directories containing shared PHP scripts
<textarea wrap="off" class="ignore" name="shared_string" title="Scripts in these directories are 'shared' and will not contribute to any per-domain quotas"><=active_config.shared_string></textarea>

<input type="hidden" name="tab" value="<=sel_tab>" />
<input type="hidden" name="page" value="<=page>" />
<input type="hidden" name="restart_keys" value="global_enable_shm" />
<input type="submit" value="Update" name="submit_tab"/>

</div>
</form>
