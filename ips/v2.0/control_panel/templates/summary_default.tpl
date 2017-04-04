<:# Copyright (C) 2006 ionCube Ltd. This file is subject to the ionCube Performance System License. All rights reserved. >
<:cache_status_banner>
<div class="domain_panel" align="center" style="padding:20px;margin:0px;">
	<table class="ips" id="summary" width="400px">
		<:summary_row name="Cache status:" value=cache_status>
		<:summary_row name="IPS home:" value=ips_home>
		<:summary_row name="File cache path:" value=file_cache_path>
		<:summary_row name="IPS log path:" value=log_path>
		<:summary_row name="Number of active domains:" value=domain_count>
		<:summary_row name="Shared memory free:" value=shm_free>
		<:summary_row name="Shared memory used:" value=shm_used>
		<:summary_row name="Shared memory used by index:" value=index_size>
		<:summary_row name="Number of processes using shared memory:" value=shm_refcount>
		<:summary_row name="Scripts per used slot (min/mean/max):" value=minmeanmax>
		<:summary_row name="Percentage of slots used:" value=nonempty_bucket_percent>
		<:summary_row name="Indexed scripts in shared memory cache:" value=index_shm>
		<:summary_row name="Indexed scripts in file cache:" value=index_filecache>
		<:summary_row name="Indexed scripts in no cache:" value=index_no_location>
		<:summary_row name="Put/get shared memory used:" value=putget_shm_used>
		<:summary_row name="File cache size:" value=filecache_size>
		<:summary_row name="Files in file cache:" value=filecache_count>
		<:summary_row name="Request duration (min/mean/max) in milliseconds:" value=request_minmeanmax>
		<:summary_row name="URL of longest running request:" value=maxrequesturl>
		<=load_average_row>
		<:summary_row name="Number of CPUs:" value=cpu_count>
	</table>
	<div style="text-align:center; margin-top:5px;"><a href="<=self>?page=<=page>">refresh</a> | <:auto_refresh_link></div>
	</div>

	<br /><br /><br /><br />
</div>



