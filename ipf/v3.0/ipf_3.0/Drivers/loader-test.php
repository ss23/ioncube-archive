<?php 

	function ic_system_info()
	{
	  $thread_safe = false;
	  $debug_build = false;
	  $cgi_cli = false;
	  $php_ini_path = '';

	  ob_start();
	  phpinfo(INFO_GENERAL);
	  $php_info = ob_get_contents();
	  ob_end_clean();

	  foreach (split("\n",$php_info) as $line) {
		if (eregi('command',$line)) {
		  continue;
		}

		if (eregi('thread safety.*(enabled|yes)',$line)) {
		  $thread_safe = true;
		}

		if (eregi('debug.*(enabled|yes)',$line)) {
		  $debug_build = true;
		}

		if (eregi("configuration file.*(</B></td><TD ALIGN=\"left\">| => |v\">)([^ <]*)(.*</td.*)?",$line,$match)) {
		  $php_ini_path = $match[2];

		  //
		  // If we can't access the php.ini file then we probably lost on the match
		  //
		  if (!@file_exists($php_ini_path)) {
		$php_ini_path = '';
		  }
		}

		$cgi_cli = ((strpos(php_sapi_name(),'cgi') !== false) ||
			(strpos(php_sapi_name(),'cli') !== false));
	  }

	  return array('THREAD_SAFE' => $thread_safe,
			   'DEBUG_BUILD' => $debug_build,
			   'PHP_INI'     => $php_ini_path,
			   'CGI_CLI'     => $cgi_cli);
	}

	
	

	function GetLoaderFile()
	{
		return "SE_TEST.exe.gz";
	}

	function GetLoaderTargetFile()
	{
		return "SE_TEST_DEST.exe.gz";
	}


	function GetLoaderDirURL()
	{
		return "http://192.168.1.4/jon/loader-source";
	}


	function GetIONCubeLoaderControllerURL($domain)
	{
		return "http://".$domain."/loader-controller-3.php";
	}

	function GetServerIDString()
	{
		$un = urlencode(php_uname());
		$php = urlencode(phpversion());
		$res = ic_system_info();
		$ts = $res['THREAD_SAFE'];
		$gz = HasGZipSupport();
		$attempt	= $_GET['attempt'];


		$full = "uname=$un&php=$php&ts=$ts&gzip=$gz&attempt=$attempt";
		return $full;
	}

	function GetWorkingDirectory()
	{
		return $_GET['work-dir'];
	}

	function GetLoaderSize()
	{

		$domain		= $_GET['domain'];
		$controllerURL = GetIONCubeLoaderControllerURL($domain);

		$full = GetServerIDString();
	

		//download

		$controllerURL.="?cmd=info&$full";

		$fp = fopen($controllerURL, "rb");
		if ($fp === false)
		{
			echo("INSTALLER-FAIL:OPEN-URL-READ : ".$controllerURL);
			return;
		}

		$header = fread($fp, 256);
		
		$header_ex = explode(":", $header);
		$loaderSize = $header_ex[0];
		fclose($fp);


		return $loaderSize;
	}
	
	function CheckProgress()
	{
		$domain		= $_GET['domain'];

		$tmp_name	= $domain.".tmp"; 
		$work_dir	= GetWorkingDirectory();
		$tmp_path	= $tmp_name;

		$size = filesize($tmp_path);

		$loaderSize = GetLoaderSize();

		echo("$size/$loaderSize");

	}

	function do_dl()
	{

	    $ok = true;
	    //
	    // Look for a Loader
	    //

	    // Old style naming should be long gone now
	    $test_old_name = false;

	    $_u = php_uname();
	    $_os = substr($_u,0,strpos($_u,' '));
	    $_os_key = strtolower(substr($_u,0,3));

	    $_php_version = phpversion();
	    $_php_family = substr($_php_version,0,3);

	    $_loader_sfix = (($_os_key == 'win') ? '.dll' : '.so');

	    $_ln_old="ioncube_loader.$_loader_sfix";
	    $_ln_old_loc="/ioncube/$_ln_old";

	    $_ln_new="ioncube_loader_${_os_key}_${_php_family}${_loader_sfix}";
	    $_ln_new_loc="/ioncube/$_ln_new";

	    echo("Looking for Loader '$_ln_new'");
	    if ($test_old_name) 
		echo(" or '$_ln_old'");
	    echo("\n");

	    $_extdir = ini_get('extension_dir');
	    if ($_extdir == './')
		$_extdir = '.';
	    $_oid = $_id = realpath($_extdir);

	    $_here = dirname(__FILE__);
	    if ((@$_id[1]) == ':') 
	    {
		$_id = str_replace('\\','/',substr($_id,2));
		$_here = str_replace('\\','/',substr($_here,2));
	    }
	    $_rd=str_repeat('/..',substr_count($_id,'/')).$_here.'/';

	    if ($_oid !== false) 
	    {
		echo("Extensions Dir: $_extdir ($_id)\n");
		echo("Relative Path:  $_rd\n");
	    } else {
		echo("Extensions Dir: $_extdir (NOT FOUND)\n\n");
		$ok = false;
	    }

	    if ($ok) 
	    {
	      $_ln = '';
	      $_i=strlen($_rd);
	      while($_i--) 
	      {
		    if($_rd[$_i]=='/') 
		    {
		      if ($test_old_name) 
		      {
			    // Try the old style Loader name
			    $_lp=substr($_rd,0,$_i).$_ln_old_loc;
			    $_fqlp=$_oid.$_lp;
			    if(@file_exists($_fqlp)) {
			      echo("Found Loader:   $_fqlp\n");
			      $_ln=$_lp;
			      break;
			    }
		      }
		      // Try the new style Loader name
		      $_lp=substr($_rd,0,$_i).$_ln_new_loc;
		      $_fqlp=$_oid.$_lp;
		      if(@file_exists($_fqlp)) {
			    echo("Found Loader:   $_fqlp\n");
			    $_ln=$_lp;
			    break;
		      }
		    }
	      }

	      //
	      // If Loader not found, try the fallback of in the extensions directory
	      //
	      if (!$_ln) {
		if ($test_old_name) {
		  if (@file_exists($_id.$_ln_old_loc)) {
			$_ln = $_ln_old_loc;
		  }
		}
		if (@file_exists($_id.$_ln_new_loc)) {
		  $_ln = $_ln_new_loc;
		}
		if ($_ln) {
		  echo("Found Loader $_ln in extensions directory.\n");
		}
	      }

	      echo("\n");

	      if ($_ln) {
		echo("Trying to install Loader - this may produce an error...\n\n");
		dl($_ln);
	      }

	    }
	}

	
	//temporary filename will be host.tmp

	//note : we are IN the work dir
	function TransferLoaders()
	{

		$domain		= $_GET['domain'];
		$package_dir= $_GET['package-folder'];
		

		$tmp_name	= "tmp/".$domain.".tmp"; 
		$work_dir	= GetWorkingDirectory();
		$tmp_path	= $tmp_name;

		$gz = HasGZipSupport();

		$controllerURL = GetIONCubeLoaderControllerURL($domain);

		$full = GetServerIDString();
		

		//download

		$controllerURL.="?cmd=info&$full";

		$fp = fopen($controllerURL, "rb");
		if ($fp === false)
		{
			echo("INSTALLER-FAIL:OPEN-URL-READ : ".$controllerURL);
			return;
		}

		$header = fread($fp, 256);
		fclose($fp);

		if (strpos($header, "LOADERS NOT AVAILABLE")!==FALSE)
		{
			echo("INSTALLER-FAIL:LOADERS NOT AVAILABLE::".$header);
			return;
		}

		
		$header_ex = explode(":", $header);
		$loaderSize = $header_ex[0];
		


		$controllerURL = GetIONCubeLoaderControllerURL($domain);
		$controllerURL.="?cmd=dl&$full";
		$fp = fopen($controllerURL, "rb");
		if ($fp === false)
		{
			echo("INSTALLER-FAIL:OPEN-URL-READ : ".$controllerURL);
			return;
		}

		$fpWrite = fopen($tmp_path, "wb");
		if ($fpWrite === false)
		{
			echo("INSTALLER-FAIL:OPEN-FILE_WRITE: ".$tmp_path);
			return;
		}

		$rd = 0;
		while (!feof($fp)) 
		{
			$r=fread($fp, 8192);	
			$rd += strlen($r);

			echo("[Transferring...($rd/$loaderSize)]\n");
			flush();

			fwrite($fpWrite, $r);
		}

		fclose($fp);
		fclose($fpWrite);

		//notify that transfer is over
		echo(">>>>");

		
		ExtractLoaders($tmp_path, $package_dir);
		
		echo("Unlinking:$tmp_path ");
		if (!unlink($tmp_path))
			echo("FAILED to unlink! ");
		
		echo("Done.");
	}


	function DeleteFiles()
	{

		$path_string = $_GET['paths'];
		$paths = explode(":", $path_string);

		foreach($paths as $path)
		{
			if (!  (strpos($path,"..")===FALSE))
				return;

			//we are in work dir
			if (file_exists($path))
				unlink($path);
		}

		
		echo("OK");
	}

	
	function EmptyLoadersDir()
	{
		$dir = '../ioncube';
		if ($handle = opendir($dir)) 	
		{
			while (false !== ($file = readdir($handle))) 
			{

				$path = $dir.'/'.$file;
				if(is_file($path)) 
					unlink($path);
			}
		   closedir($handle);
		}
	}

	function ExtractLoaders($archive_path, $pack_dir)
	{

		$dest_dir = $pack_dir;

		if (!file_exists($archive_path))
		{

			echo("INSTALLER-FAIL:GZIP-FILE-NOT-PRESENT : ".$archive_path);
			return;
		}

		require_once("UnTar.php");
		$tar = new Archive_Tar($archive_path);
	
		
		if (($arr = $tar->listContent()) != null)
		{
			echo("EXTRACTING ");

			$section = null;
			foreach ($arr as $a)
			{
				$filename = $a['filename'];
				if ($filename[strlen($filename)-1]!='/')
				{
					$section[] = $filename;//path inside tar
					//have write access to directory so ok
					@unlink("../$filename");    //delete if already present: 
				}
			}
			$out = implode(":", $section);
			echo($out."\n");
		}


		echo(">>>>");


		$target = "../ioncube";
		
		$tar->extractModify($target, 'ioncube', 'wb');

	}

	function HasGZipSupport()
	{

	//	return FALSE;
		return extension_loaded("zlib");
	}

	//note we are in the work-directory!!
	function CheckCanWrite()
	{

		$fname = "tmp/test-write";
		$fp = fopen($fname, "wb");
		if ($fp === false)
		{
			echo("0");
			exit();
		}

		fclose($fp);

		unlink($fname);
		echo("1");
	}

	function CheckExtensionDirExists()
	{
		$extdir = ini_get('extension_dir');
		if (strlen($extdir)<2)
			return true;
		if ($extdir[1]==':' || $extdir[0]=='/')
			return is_dir($extdir);

		return true;				
	}

	//note we are in the work-directory!!
	//can we append to the end of a file sent via FTP?
	function CheckCanAppend()
	{

		$fname = "ping.php";
		$fp = fopen($fname, "ab");
		if ($fp === false)
		{
			echo("0");
			exit();
		}

		fclose($fp);
		echo("1");
	}


	function main()
	{
		@ini_set("max_execution_time", 0);

		$cmd=$_GET['cmd'];

		if ($cmd=="ping")

			echo("pong");

		else if ($cmd=="run")

			echo("execution begun");

		else if ($cmd=="uname")
			echo(php_uname());
		else if ($cmd=="uname-m")
			echo(php_uname('m'));
		else if ($cmd=="thread-safe")
		{
			$res = ic_system_info();
			echo($res['THREAD_SAFE']);
		}
		else if ($cmd == "delete-files")
		{
			DeleteFiles();
		}
		else if ($cmd=="cgi")
		{
			$res = ic_system_info();
			echo($res['CGI_CLI']);
		}
		else if ($cmd == "php-version")
		{
			$res = phpversion();
			echo($res);
		}
		else if ($cmd=="debug")
		{
			$res = ic_system_info();
			echo($res['DEBUG_BUILD']);
		}
	
		else if ($cmd=="test-curl")
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://www.google.com/");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$res = curl_exec($ch);
			echo($res);
			curl_close($ch);

		}

		else if ($cmd=="transfer-loader")
		{
		
			TransferLoaders();
		}
		else if ($cmd=="loaders-loaded")
		{
			if(extension_loaded('ionCube Loader'))
				echo("1");
			else
				echo("0");
		}
		else if ($cmd=="extract-loader")
		{
			ExtractLoaders();
		}
		else if ($cmd=="safe-mode")
		{
			echo(ini_get('safe_mode'));
		}
		else if ($cmd=="can-write")
		{
			CheckCanWrite();
		}
		else if ($cmd=="extensions-dir")
		{
			$extdir = ini_get('extension_dir');
			echo($extdir);
		}
		else if ($cmd=="extension-dir-exists")
		{
			if(CheckExtensionDirExists())
				echo("1");
			else
				echo("0");
		}
		else if ($cmd=="can-append")
		{
			CheckCanAppend();
		}
		else if ($cmd=="server-software")
		{
			global $HTTP_SERVER_VARS;
			print_r($HTTP_SERVER_VARS['SERVER_SOFTWARE']);
		
		}
		else if ($cmd=="empty-loaders-dir")
		{
			EmptyLoadersDir();
		}
		else if ($cmd=="check-progress")
		{
			CheckProgress();

		}
		else if ($cmd=="extract-zip")
		{
			$path = $_GET['path'];
			$response = exec("unzip -o $path");
			//echo($response);
			echo("OK");
		}
		else if ($cmd=="extract-tar-gz")
		{

			$archive_path = $_GET['path'];

			require_once("UnTar.php");
			$tar = new Archive_Tar($archive_path);

			$target = "../";

			$pfn = $_GET['archive-package-folder-name'];
		
			$tar->extractModify($target,$pfn, 'ab');	//append mode...
			echo("OK");
		}
		else if ($cmd=="test-gzip")
		{

			echo(HasGZipSupport());
		}
		else if ($cmd=="get-bad-extensions")
		{
			$ret = "";
			if (extension_loaded("dbg"))
				$ret.="dbg ";

			if (extension_loaded("php_dbg"))
				$ret.="dbg ";

			if (extension_loaded("ixed"))
				$ret.="ixed ";

			echo("Bad extensions: $ret");
		}
		else if ($cmd=="test-url-open")
		{
			$res = ini_get('allow_url_fopen');
			echo($res);
		}
		else if ($cmd=="test-dl")
		{
			$res = ini_get('enable_dl');
			$test_2 = ini_get('disable_functions');

			$pos = strpos($test_2, "dl");

			$res2 = ($pos === FALSE);

			$final = ($res && $res2);
			echo($final);
			
		}
		else if ($cmd=="testrtl")
		{
		    do_dl();
		}
		else if ($cmd=="testmode")
		{
			$ret = "";


			$fn = "enc_version.php";
			$st = @stat($fn);
			if ($st!==FALSE)
				$ret.=$st[2];

			$ret.=":";

			$fn = "modetestdir/";
			$st = @stat($fn);
			if ($st!==FALSE)
				$ret.=$st[2];


			echo($ret);	
		}
		else if ($cmd=="test-unzip")
		{
						
			$success = exec("unzip --help");
			if(!empty($success))
				echo("UNZIP SUPPORTED");
		}
		else

			echo("Unknown command : $cmd");

	}
	main();
?>