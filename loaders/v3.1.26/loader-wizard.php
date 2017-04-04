<?php // -*- c++ -*-

/** 
 * ionCube Loader install Wizard v2.3
 *
 * ionCube is a registered trademark of ionCube Ltd. 
 *
 * Copyright (c) ionCube Ltd. 2002-2010
 */


 

define ('ERROR_UNKNOWN_OS',1);
define ('ERROR_UNSUPPORTED_OS',2);
define ('ERROR_UNKNOWN_ARCH',3);
define ('ERROR_UNSUPPORTED_ARCH',4);
define ('ERROR_UNSUPPORTED_ARCH_OS',5);
define ('ERROR_WINDOWS_64_BIT',6);

define ('HAS_PHP_INI',1);
define ('SERVER_SHARED',2); 
define ('SERVER_SHARED_INI',3);
define ('SERVER_VPS',5); 
define ('SERVER_DEDICATED',7); 
define ('SERVER_LOCAL',9);

define ('LOADERS_PAGE',                                                 // Main webpage from where loaders can be downloaded.
            'http://loaders.ioncube.com/');                                 
define ('SUPPORT_SITE',                                                 // Support helpdesk URL. 
            'http://support.ioncube.com/');                                 
define ('LOADER_FORUM_URL',                                             // URL to forum about the ionCube Loader.
            'http://forum.ioncube.com/viewforum.php?f=4');                  
define ('LOADERS_FAQ_URL',                                              // URL for the Loaders FAQ.
            'http://www.ioncube.com/faqs/loaders.php');                     
define ('UNIX_ERRORS_URL',                                              // Help for errors with the Loader on Unix systems.
            'http://www.ioncube.com/loaders/unix_startup_errors.php');      
define ('LOADER_WIZARD_URL',                                            // URL from where this wizard script can be downloaded.
            LOADERS_PAGE);                                                  
define ('ENCODER_URL',                                                  // URL for the Encoder. 
            'http://www.ioncube.com/sa_encoder.php');                       
define ('LOADER_VERSION_URL',                                           // Base URL to get Loader versioning and platform information.
            'http://www.ioncube.com/feeds/product_info/versions.php');    
define ('WIZARD_LATEST_VERSION_URL',                                    // URL to get latest version of the Loader Wizard.
            LOADER_VERSION_URL . '?item=loader-wizard'); 
define ('PHP_COMPILERS_URL',                                            // URL to get the supported compilers for PHP.
            LOADER_VERSION_URL . '?item=php-compilers');
define ('LOADER_PLATFORM_URL',                                          // URL to get the platforms on which the Loader is available.
            LOADER_VERSION_URL . '?item=loader-platforms');   
define ('LOADER_LATEST_VERSIONS_URL',                                   // URL to get latest versions of the Loader.
            LOADER_VERSION_URL . '?item=loader-versions'); 
define ('IONCUBE_DOWNLOADS_SERVER',                                     // Actual loader downloads server.
            'http://downloads2.ioncube.com/loader_downloads');          

define ('LOADER_NAME_CHECK',true);
define ('LOADER_EXTENSION_NAME','ionCube Loader');
define ('LOADER_SUBDIR','ioncube');
define ('WINDOWS_IIS_LOADER_DIR', 'system32');
define ('UNIX_SYSTEM_LOADER_DIR','/usr/local/ioncube');
define ('RECENT_LOADER_VERSION','3.1.24');
define ('LOADERS_PACKAGE_PREFIX','ioncube_loaders_');
define ('SESSION_LIFETIME_MINUTES',10);

    run();


function script_version()
{
    return "2.3";
}

function retrieve_latest_version()
{
    $v = false;

    $fp = @fopen(WIZARD_LATEST_VERSION_URL,'r');

    if ($fp) {
        $s = trim(fgets($fp));
        if (preg_match('/^\d+([.]\d+)*$/', $s)) {
          $v = $s;
        }
        fclose($fp);
    }

    return $v;
}

function latest_version()
{
    if (isset($_SESSION['latest_version'])) {
        return $_SESSION['latest_version'];
    } else {
        return false;
    }
}

function update_is_available($lv)
{
    if (is_numeric($lv)) {
        return ($lv > script_version());
    } else {
        return null;
    }
}

function check_for_wizard_update($echo_message = false)
{
    $latest_version = latest_version();
    $update_available = update_is_available($latest_version);

    if ($update_available) {
        if ($echo_message) {
            echo '<p class="alert">An updated version of this script is available <a href="' . LOADER_WIZARD_URL . '">here</a></p>';
        }
        return $latest_version;
    } else {
        return $update_available;
    }
}


function php_version()
{
    $v = explode('.',PHP_VERSION);

    return array(
           'major'      =>  $v[0],
           'minor'      =>  $v[1],
           'release'    =>  $v[2]);
}

function is_supported_php_version()
{
    $v = php_version(); 

    return ((($v['major'] == 4) && ($v['minor'] >= 1)) ||
      (($v['major'] == 5) && (($v['minor'] >= 1) || ($v['release'] >= 5))));
}

function is_php_version_or_greater($major,$minor,$release = 0)
{
    $version = php_version();
    return ($version['major'] > $major || 
            ($version['major'] == $major && $version['minor'] > $minor) ||
            ($version['major'] == $major && $version['minor'] == $minor && $version['release'] >= $release));
}

function ini_file_name()
{
    $sysinfo = get_sysinfo();
    return (isset($sysinfo['PHP_INI'])?$sysinfo['PHP_INI_BASENAME']:'php.ini');
}

function default_platform_list()
{
    $platforms = array();

    $platforms[] = array('os'=>'win', 'os_human'=>'Windows',            'arch'=>'x86',      'dirname'=>'win32', 'us1-dir'=>'windows/x86' );
    $platforms[] = array('os'=>'win', 'os_human'=>'Windows (Non-TS)',   'os_mod' => '_nonts',       'arch'=>'x86',      'dirname'=>'win32-nonts', 'us1-dir'=>'windows/x86-nonts' );
    $platforms[] = array('os'=>'lin', 'os_human'=>'Linux',              'arch'=>'x86',      'dirname'=>'linux_i686-glibc2.1.3', 'us1-dir'=>'linux/x86');
    $platforms[] = array('os'=>'lin', 'os_human'=>'Linux',              'arch'=>'x86-64',   'dirname'=>'linux_x86_64-glibc2.3.4', 'us1-dir'=>'linux/x86_64');
    $platforms[] = array('os'=>'lin','os_human'=>'Linux',               'arch'=>'ppc',      'dirname'=>'linux_ppc-glibc2.3.4','us1-dir'=>'linux/ppc');
    $platforms[] = array('os'=>'lin','os_human'=>'Linux',               'arch'=>'ppc64',    'dirname'=>'linux_ppc64-glibc2.5','us1-dir'=>'linux/ppc64');
    $platforms[] = array('os'=>'dra', 'os_human'=>'DragonFly', 
        'arch'=>'x86',      'dirname'=>'dragonfly_i386-1.7', 'us1-dir'=>'Dragonfly/x86');
    $platforms[] = array('os'=>'fre', 'os_human'=>'FreeBSD 4', 'os_mod'=>'_4',  'arch'=>'x86',      'dirname'=>'freebsd_i386-4.8', 'us1-dir'=>'FreeBSD/v4');
    $platforms[] = array('os'=>'fre', 'os_human'=>'FreeBSD 6', 'os_mod'=>'_6',  'arch'=>'x86',      'dirname'=>'freebsd_i386-6.2', 'us1-dir'=>'FreeBSD/v6/x86');
    $platforms[] = array('os'=>'fre', 'os_human'=>'FreeBSD 6', 'os_mod'=>'_6',  'arch'=>'x86-64',   'dirname'=>'freebsd_amd64-6.1', 'us1-dir'=>'FreeBSD/v6/AMD64');
    $platforms[] = array('os'=>'fre', 'os_human'=>'FreeBSD 7', 'os_mod'=>'_7',  'arch'=>'x86',      'dirname'=>'freebsd_i386-7.1', 'us1-dir'=>'FreeBSD/v7/x86');
    $platforms[] = array('os'=>'fre', 'os_human'=>'FreeBSD 7', 'os_mod'=>'_7',  'arch'=>'x86-64',   'dirname'=>'freebsd_amd64-7.0', 'us1-dir'=>'FreeBSD/v7/AMD64');
    $platforms[] = array('os'=>'fre', 'os_human'=>'FreeBSD 8', 'os_mod'=>'_8',  'arch'=>'x86',      'dirname'=>'freebsd_i386-8.0', 'us1-dir'=>'FreeBSD/v8/x86');
    $platforms[] = array('os'=>'bsd', 'os_human'=>'BSDi',               'arch'=>'x86',      'dirname'=>'bsdi_i386-4.3.1');
    $platforms[] = array('os'=>'bsd', 'os_human'=>'BSDi',               'arch'=>'x86',      'dirname'=>'bsdi_i386-4.3.1');
    $platforms[] = array('os'=>'net', 'os_human'=>'NetBSD',             'arch'=>'x86',      'dirname'=>'netbsd_i386-2.1','us1-dir'=>'NetBSD/x86');
    $platforms[] = array('os'=>'net', 'os_human'=>'NetBSD',             'arch'=>'x86-64',   'dirname'=>'netbsd_amd64-2.0','us1-dir'=>'NetBSD/x86_64');
    $platforms[] = array('os'=>'ope', 'os_human'=>'OpenBSD 3.7', 'os_mod'=>'_3.7',
        'arch'=>'x86-64', 'dirname'=>'openbsd_amd64-3.7');
    $platforms[] = array('os'=>'ope', 'os_human'=>'OpenBSD 3.9', 'os_mod'=>'_3.9',
        'arch'=>'x86-64', 'dirname'=>'openbsd_amd64-3.9');
    $platforms[] = array('os'=>'ope', 'os_human'=>'OpenBSD 3.8',            'arch'=>'x86',      'dirname'=>'openbsd_i386-3.8', 'os_mod'=>'_3.8', 'us1-dir'=>'OpenBSD');
    $platforms[] = array('os'=>'ope', 'os_human'=>'OpenBSD 4.2',            'arch'=>'x86',      'dirname'=>'openbsd_i386-4.2', 'os_mod'=>'_4.2', 'us1-dir'=>'OpenBSD');
    $platforms[] = array('os'=>'ope', 'os_human'=>'OpenBSD 4.5',            'arch'=>'x86',      'dirname'=>'openbsd_i386-4.5', 'os_mod'=>'_4.5', 'us1-dir'=>'OpenBSD');
    $platforms[] = array('os'=>'dar', 'os_human'=>'OS X',               'arch'=>'ppc',      'dirname'=>'osx_powerpc-8.5','us1-dir'=>'OSX/ppc');

    $platforms[] = array('os'=>'dar', 'os_human'=>'OS X',               'arch'=>'x86',      'dirname'=>'osx_i386-8.11','us1-dir'=>'OSX/x86');

    $platforms[] = array('os'=>'dar', 'os_human'=>'OS X',               'arch'=>'x86-64',       'dirname'=>'osx_x86-64-10.2','us1-dir'=>'OSX/x86_64');

    $platforms[] = array('os'=>'sun', 'os_human'=>'Solaris',            'arch'=>'sparc',    'dirname'=>'solaris_sparc-5.9', 'us1-dir'=>'Solaris/sparc');

    $platforms[] = array('os'=>'sun', 'os_human'=>'Solaris',            'arch'=>'x86',      'dirname'=>'solaris_i386-5.10','us1-dir'=>'Solaris/x86');

    return $platforms;
}

function get_loader_platforms()
{
    if (!isset($_SESSION['loader_platform_info'])) {
        $serialised_res = '';
        $fp = @fopen(LOADER_PLATFORM_URL,'r');
        if ($fp !== false) {
            $serialised_res = fgets($fp);
        } 
        if (empty($serialised_res)) {
            $serialised_res = serialize(default_platform_list());
        }
        $_SESSION['loader_platform_info'] = $serialised_res;
    }
    return unserialize($_SESSION['loader_platform_info']);
}

function get_platforminfo()
{
    static $platforminfo;

    if (empty($platforminfo)) {
        $platforminfo = get_loader_platforms();
    }
    return $platforminfo;
}

function supported_os_variants($os_code,$arch_code)
{
    if (empty($os_code)) {
        return ERROR_UNKNOWN_OS;
    }
    if (empty($arch_code)) {
        return ERROR_UNKNOWN_ARCH;
    }

    $os_found = false;
    $arch_found = false;
    $os_arch_matches = array();
    $pinfo = get_platforminfo();

    foreach ($pinfo as $p) {
        if ($p['os'] == $os_code && $p['arch'] == $arch_code) {
            $os_arch_matches[$p['os_human']] = (isset($p['os_mod']))?str_replace('_','',$p['os_mod']):'';
        } 
        if ($p['os'] == $os_code) {
            $os_found = true;
        } elseif ($p['arch'] == $arch_code) {
            $arch_found = true;
        }
    }
    if (!empty($os_arch_matches)) {
        asort($os_arch_matches);
        return $os_arch_matches;
    } elseif (!$os_found) {
        return ERROR_UNSUPPORTED_OS;
    } elseif (!$arch_found) {
        return ERROR_UNSUPPORTED_ARCH;
    } else {
        return ERROR_UNSUPPORTED_ARCH_OS;
    }
}

function supported_win_compilers()
{
    static $win_compilers;

    if (empty($win_compilers)) {
        $win_compilers = find_win_compilers();
    }
    return $win_compilers;
}

function find_win_compilers()
{
    if (!isset($_SESSION['php_compilers_info'])) {
        $serialised_res = '';
        $fp = @fopen(PHP_COMPILERS_URL,'r');
        if ($fp !== false) {
            $serialised_res = fgets($fp);
        } 
        if (empty($serialised_res)) {
            $serialised_res = serialize(array('VC6'));
        }
        $_SESSION['php_compilers_info'] = $serialised_res;
    }
    return unserialize($_SESSION['php_compilers_info']);
}

function server_software_info()
{
    $ss = array('full' => '','short' => '');
    $ss['full'] = $_SERVER['SERVER_SOFTWARE'];

    if (preg_match('/apache/i', $ss['full'])) {
        $ss['short'] = 'Apache';
    } else if (preg_match('/IIS/',$ss['full'])) {
        $ss['short'] = 'IIS';
    } else {
        $ss['short'] = '';
    }
    return $ss;
}

function required_loader()
{
    $un = php_uname();
    $php_major_version = substr(PHP_VERSION,0,3);

    $os_name = substr($un,0,strpos($un,' '));
    $os_code = empty($os_name)?'':strtolower(substr($os_name,0,3));

    $wordsize = ((-1^0xffffffff) ? 64 : 32);

    if ($os_code == 'win') {
        $arch = ($wordsize == 32)?'x86':'x86-64';
        if ($wordsize != 32) {
            return ERROR_WINDOWS_64_BIT;
        }
    } elseif (!empty($os_code)) {
        $arch = null;
        $arch_patterns = array(
                 'i.?86' => 'x86',
                 'x86[-_]64' => 'x86-64',
                 'x86' => 'x86',
                 'amd64' => 'x86-64',
                 'ppc64' => 'ppc64',
                 'ppc' => 'ppc',
                 'sparc' => 'sparc'
             );
    

        foreach ($arch_patterns as $token => $a) {
            if (preg_match("/$token/i", $un)) {
              $arch = $a;
              break;
            }
        }
    }
    $os_variants = supported_os_variants($os_code,$arch);
    if (!is_array($os_variants)) {
        return $os_variants;
    }

    $os_ver = '';
    if (preg_match('/([0-9.]+)/',php_uname('r'),$match)) {
        $os_ver = $match[1];
    }

    $loader_sfix = (($os_code == 'win') ? 'dll' : 'so');
    $file = "ioncube_loader_${os_code}_${php_major_version}.${loader_sfix}";

    if ($os_code == 'win') {
        $os_name = 'Windows';
        $file_ts = $file;
    } else {
        $os_names = array_keys($os_variants);
        $parts = explode(" ",$os_names[0]); 
        $os_name = $parts[0];
        $file_ts = "ioncube_loader_${os_code}_${php_major_version}_ts.${loader_sfix}";
    }

    return array(
           'arch'       =>  $arch,
           'oscode'     =>  $os_code,
           'osname'     =>  $os_name,
           'osvariants' =>  $os_variants,
           'osver'      =>  $os_ver,
           'osver2'     =>  preg_split('@\.@',$os_ver),
           'file'       =>  $file,
           'file_ts'    =>  $file_ts,
           'wordsize'   =>  $wordsize
       );
}

function ic_system_info()
{
    $thread_safe = null;
    $debug_build = null;
    $cgi_cli = false;
    $is_cgi = false;
    $is_cli = false;
    $php_ini_path = '';
    $is_supported_compiler = true;
    $php_compiler = '';

    ob_start();
    phpinfo(INFO_GENERAL);
    $php_info = ob_get_contents();
    ob_end_clean();

    $breaker = (php_sapi_name() == 'cli')?'\n':'</tr>';
    $lines = explode($breaker,$php_info);
    foreach ($lines as $line) {
        if (preg_match('/command/i',$line)) {
          continue;
        }

        if (preg_match('/thread safety/i', $line)) {
          $thread_safe = (preg_match('/(enabled|yes)/i', $line) != 0);
        }

        if (preg_match('/debug build/i', $line)) {
          $debug_build = (preg_match('/(enabled|yes)/i', $line) != 0);
        }

        if (preg_match('~configuration file.*(</B></td><TD ALIGN="left">| => |v">)([^ <]*)~i',$line,$match)) {
          $php_ini_path = $match[2];

          if (!@file_exists($php_ini_path)) {
                $php_ini_path = '';
          }
        }
        if (preg_match('/compiler/i',$line)) {
            $supported_match = join('|',supported_win_compilers());
            $is_supported_compiler = preg_match("/($supported_match)/i",$line);
            if (!$is_supported_compiler) {
                if (preg_match("/(VC[0-9]+)/i",$line,$match)) {
                    $php_compiler = $match[1];
                }

            }
        }
    }
    $is_cgi = strpos(php_sapi_name(),'cgi') !== false;
    $is_cli = strpos(php_sapi_name(),'cli') !== false;
    $cgi_cli = $is_cgi || $is_cli;

    $ss = server_software_info();

    if (!$php_ini_path && function_exists('php_ini_loaded_file')) {
        $php_ini_path = php_ini_loaded_file();
        if ($php_ini_path === false) {
            $php_ini_path = '';
        }
    }

    $php_ini_basename = basename($php_ini_path);

    return array(
           'THREAD_SAFE'        => $thread_safe,
           'DEBUG_BUILD'        => $debug_build,
           'PHP_INI'            => $php_ini_path,
           'PHP_INI_BASENAME'   => $php_ini_basename,
           'CGI_CLI'            => $cgi_cli,
           'IS_CGI'             => $is_cgi,
           'IS_CLI'             => $is_cli,
           'PHP_COMPILER'       => $php_compiler,
           'SUPPORTED_COMPILER' => $is_supported_compiler,
           'FULL_SS'            => $ss['full'],
           'SS'                 => $ss['short']);
}

function is_possibly_dedicated_or_local()
{
    $sys = get_sysinfo();

    return (empty($sys['PHP_INI']) || !file_exists($sys['PHP_INI']) || (is_readable($sys['PHP_INI']) && (0 !== strpos($sys['PHP_INI'],$_SERVER['DOCUMENT_ROOT']))));
}

function is_local()
{
    $ret = false;
    if ($_SERVER["SERVER_NAME"] == 'localhost') {
        $ret = true;
    } else {
        $ip_address = strtolower($_SERVER["REMOTE_ADDR"]);
        if (strpos(':',$ip_address) === false) {
            $ip_parts = explode('.',$ip_address);
            $ret = (($ip_parts[0] == 10) || 
                    ($ip_parts[0] == 172 && $ip_parts[1] >= 16 &&  $ip_parts[1] <= 31) ||
                    ($ip_parts[0] == 192 && $ip_parts[1] == 168));
        } else {
            $ret = ($ip_address == '::1') || (($ip_address[0] == 'f') && ($ip_address[1] >= 'c' && $ip_address[1] <= 'f'));
        }
    }
    return $ret;
}

function get_sysinfo()
{
    static $sysinfo;

    if (empty($sysinfo)) {
        $sysinfo = ic_system_info();
    }
    return $sysinfo;
}

function get_loaderinfo()
{
    static $loader;

    if (empty($loader)) {
        $loader = required_loader();
    }
    return $loader;
}

function is_ms_windows()
{
    $loader_info = get_loaderinfo();
    return ($loader_info['oscode'] == 'win');
}

function function_is_disabled($fn_name)
{
    $disabled_functions=explode(',',ini_get('disable_functions'));
    return in_array($fn_name, $disabled_functions);
}

function threaded_and_not_cgi()
{
    $sys = get_sysinfo();
    return($sys['THREAD_SAFE'] && !$sys['IS_CGI']);
}

function own_php_ini_possible()
{
    $sysinfo = get_sysinfo();
    return ($sysinfo['CGI_CLI'] && !is_ms_windows());
}

function extension_dir_path()
{
    $extdir = ini_get('extension_dir');
    if ($extdir == './') {
        $extdir = '.';
    }
    return realpath($extdir);
}

function get_loader_name()
{
    $u = php_uname();
    $os = substr($u,0,strpos($u,' '));
    $os_key = strtolower(substr($u,0,3));

    $php_version = phpversion();
    $php_family = substr($php_version,0,3);

    $loader_sfix = (($os_key == 'win') ? '.dll' : '.so');
    $loader_name="ioncube_loader_${os_key}_${php_family}${loader_sfix}";

    return $loader_name;
}

function get_reqd_version_fre()
{
    $max_version_built = 7;
    $min_version_built = 4;
    $req_version = 0;
    $exact_match = false;
    $loader_info = get_loaderinfo();
    $osv = $loader_info['osver2'];
    $arch = $loader_info['arch'];

    if ($osv[0] >= $min_version_built && !(6 > $osv[0] && $arch == 'x86-64')) {
        if ($osv[0] > $max_version_built) {
            $req_version = $max_version_built;
        } elseif (5 == $osv[0]) {
            $req_version = 4;
        } else {
            $exact_match = true;
            $req_version = $osv[0];
        }
    }
    return (array($req_version,$exact_match));
}


function get_reqd_version_ope()
{
    $max_version_built_32 = 4.5;
    $max_version_built_64 = 3.9;
    $min_version_built_32 = 3.8;
    $min_version_built_64 = 3.7;
    $req_version = 0;
    $exact_match = false;
    $loader_info = get_loaderinfo();
    $osv = $loader_info['osver2'][0] . "." . $loader_info['osver2'][1];

    $arch = $loader_info['arch'];

    $max_version_built = ($arch == 'x86-64')?$max_version_built_64:$max_version_built_32;
    $min_version_built = ($arch == 'x86-64')?$min_version_built_64:$min_version_built_32;
    
    if ($osv >=  $min_version_built) { 
        if ($osv > $max_version_built) {
            $req_version = $max_version_built;
        } elseif (3.8 == $osv && $arch == 'x86-64') {
            $req_version = 3.7;
        } elseif ($osv < 4.2 &&  $arch == 'x86') {
            $req_version = 3.8;
        } elseif ($osv > 4.0 && $osv < $max_version_built) {
            $req_version = 4.2;
        } else {
            $exact_match = true;
            $req_version = $osv[0];
        }
    }
    return (array($req_version,$exact_match));
}

function get_reqd_version($variants)
{
    $exact_match = false;
    $nearest_version = 0;
    $loader_info = get_loaderinfo();
    $os_version = $loader_info['osver2'][0] . '.' . $loader_info['osver2'][1];
    foreach ($variants as $v) {
        if ($v == $os_version) {
            $exact_match = true;
            $nearest_version = $v;
            break;
        } elseif ($v > $os_version) {
            break;
        } else {
            $nearest_version = $v;
        }
    }
    return (array($nearest_version,$exact_match));
}

function get_default_loader_dir($is_shared = false)
{
    return ($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . LOADER_SUBDIR);
}

function get_loader_location($loader_dir = '')
{
    if (empty($loader_dir)) {
        $loader_dir = get_default_loader_dir();
    }
    $loader_name = get_loader_name(); 
    return ($loader_dir . DIRECTORY_SEPARATOR . $loader_name);
}

function get_loader_location_from_ini($php_ini = '')
{
    if (empty($php_ini)) {
        $sysinfo = get_sysinfo();
        $php_ini = $sysinfo['PHP_INI'];
    }
    $lines = file($php_ini);
    $ext_start = zend_extension_line_start();
    $wrong_ext_start = ($ext_start == 'zend_extension')?'zend_extension_ts':'zend_extension';
    $first_ext = true;
    $loader_path = '';
    foreach ($lines as $l) {
        if (preg_match("/^\s*$ext_start\s*=\s*\"?([^\"]+)\"?/i",$l,$corr_matches)) {
            if (preg_match("/ioncube/i",$corr_matches[1])) {
                if ($first_ext !== true) {
                    $errors[] = "The ionCube Loader must be the first zend_extension listed in the configuration file, $php_ini.";
                }
                $loader_path = $corr_matches[1];
                break;
            } else {
                $first_ext = false;
            }
        }
        if (preg_match("/$wrong_ext_start\s*=\s*\"?([^\"]+)\"?/i",$l,$bad_start_matches)) {
            if (preg_match("/ioncube/i",$bad_start_matches[1])) {
                $bad_zend_ext_msg = "The line for the ionCube Loader in the configuration file, $php_ini, should start with $ext_start and <b>not</b> $wrong_ext_start.";
                $errors[] = $bad_zend_ext_msg;
            }
            $loader_path = $bad_start_matches[1];
        }
    }
    $loader_path = trim($loader_path);
    if ($loader_path == '') {
        $errors[] = "The necessary zend_extension line could not be found in the configuration file, $php_ini.";
    } elseif (!file_exists($loader_path)) {
        $errors[] = "The Loader file  $loader_path, listed in the configuration file, $php_ini, does not exist.";
    } elseif (basename($loader_path) == $loader_path) {
        $errors[] = "A full path must be specified for the loader file in the configuration file, $php_ini.";
    }
    if (empty($errors)) {
        return $loader_path;
    } else {
        return $errors;
    }
}

function find_loader()
{
    $sysinfo = get_sysinfo();
    $php_ini = $sysinfo['PHP_INI'];
    if (is_readable($php_ini)) {
        return get_loader_location_from_ini($php_ini);
    } else {
        $loader_name = get_loader_name();
        if (file_exists($loader_name)) {
            return $loader_name;
        } else {
            $ld_loc = get_loader_location();
            if (file_exists($ld_loc)) {
                return $ld_loc;
            } else {
                if (!file_exists($php_ini)) {
                    return array('Loader could not be found - php.ini file does not exist.');
                } else {
                    return array('Loader could not be found - unsuccessfully tried reading php.ini.');
                }
            }
        }
    }
}

function zend_extension_line_start()
{
    $sysinfo = get_sysinfo();
    $is_53_or_later = is_php_version_or_greater(5,3);
    return (is_bool($sysinfo['THREAD_SAFE']) && $sysinfo['THREAD_SAFE'] && !$is_53_or_later ? 'zend_extension_ts' : 'zend_extension');
}

function ioncube_loader_version_information()
{
    $old_version = true;
    $liv = "";
    if (function_exists('ioncube_loader_iversion')) {
        $liv = ioncube_loader_iversion();
        $lv = sprintf("%d.%d.%d", $liv / 10000, ($liv / 100) % 100, $liv % 100);

        if ($liv >= get_latestversion()) {
              $old_version = false;
        }
    }
    return array($lv,$old_version);
}

function get_loader_version_info()
{
    if (!isset($_SESSION['loader_version_info'])) {
        $serialised_res = '';
        $fp = @fopen(LOADER_LATEST_VERSIONS_URL,'r');
        if ($fp !== false) {
            $serialised_res = fgets($fp);
        } 
        if (empty($serialised_res)) {
            $serialised_res = serialize(array());
        }
        $_SESSION['loader_version_info'] = $serialised_res;
    }
    return unserialize($_SESSION['loader_version_info']);
}

function calc_loader_latest_version()
{
    $lv_info = get_loader_version_info();
    if (empty($lv_info)) {
        return RECENT_LOADER_VERSION;
    }  else {
        $platform_info = get_platforminfo();
        $loader = get_loaderinfo();
        if (count($loader['osvariants']) > 1) {
            list($osvar,$exact_match) = get_reqd_version($loader['osvariants']);
        } else {
            $osvar = null;
        }
        $dirname = '';
        foreach ($platform_info as $p) {
            if ($p['os'] == $loader['oscode'] && $p['arch'] == $loader['arch'] && (empty($osvar) || $p['os_mod'] == "_" . $osvar)) {
                $dirname = $p['dirname'];
                break;
            }
        }
        if (!empty($dirname) && array_key_exists($dirname,$lv_info)) {
            return $lv_info[$dirname];
        } else { 
            return RECENT_LOADER_VERSION;
        }
    }
}


function get_latestversion()
{
    static $latest_version;

    if (empty($latest_version)) {
        $latest_version = calc_loader_latest_version();
    }
    return $latest_version;
}


function runtime_loader_location()
{
    $loader_path = false;
    $ext_path = extension_dir_path();
    if ($ext_path !== false) {
        $id = $ext_path;
        $here = dirname(__FILE__);
        if (isset($id[1]) && $id[1] == ':') {
            $id = str_replace('\\','/',substr($id,2));
            $here = str_replace('\\','/',substr($here,2));
        }
        if (!is_php_version_or_greater(5,2,5)) {
            $rd=str_repeat('/..',substr_count($id,'/')).$here.'/';
            $i=strlen($rd);

            $loader_loc = DIRECTORY_SEPARATOR . LOADER_SUBDIR . DIRECTORY_SEPARATOR . get_loader_name();
            while($i--) {
                if($rd[$i]=='/') {
                    $loader_path = runtime_location_exists($ext_path,$rd,$i,$loader_loc);
                    if ($loader_path !== false) {
                        break;
                    }
                }
            }
        } else {
            $loader_loc = '';
        }

        if (!$loader_path && !empty($loader_loc) && @file_exists($loader_loc)) {
            $loader_path = basename($loader_loc);
        }
    }
    return $loader_path;
}

function runtime_location_exists($ext_dir,$path_str,$sep_pos,$loc_name)
{
    $sub_path = substr($path_str,0,$sep_pos);
    $lp = $sub_path . $loc_name;
    $fqlp = $ext_dir.$lp;

	if(@file_exists($fqlp)) {
	    return $lp;
    } else {
        return false;
    }
}

function runtime_loading_is_possible()
{
    return !(ini_get('safe_mode') || !ini_get('enable_dl') || !function_exists('dl') || function_is_disabled('dl') || threaded_and_not_cgi());
}

function get_valid_runtime_loading_path($ignore_loading_check = false)
{
    if ($ignore_loading_check || runtime_loading_is_possible()) {
        return runtime_loader_location();
    } else {
        return false;
    }
}

function runtime_loading()
{
    $rtl_path = get_valid_runtime_loading_path();
    if ($rtl_path !== false && @dl($rtl_path)) {
        return $rtl_path;
    } else {
        return false;
    }
}


function loader_download_instructions()
{
    $sysinfo = get_sysinfo();
    $loader = get_loaderinfo();
    $multiple_os_versions = false;

    if (is_ms_windows()) {
        if (is_bool($sysinfo['THREAD_SAFE'])) {
            if ($sysinfo['THREAD_SAFE']) {
                echo '<li>Download one of the following archives of Windows x86 Loaders:';
                $basename = LOADERS_PACKAGE_PREFIX . 'win_x86';
            } else {
                $basename = LOADERS_PACKAGE_PREFIX . 'win_nonts_x86';
                echo '<li>Download one of the following archives of Windows non-TS x86 Loaders:';
            }
            echo make_archive_list($basename,array('zip','ipf.zip'));
            echo "<p>Please note that the MS Windows installer version is suitable either for direct installation on a Windows machine or for 
uploading from a local PC to your server.<br>";
            echo 'A Loaders archive can also be downloaded from <a href="' . LOADERS_PAGE . '" target="loaders">' . LOADERS_PAGE . '</a>.';
        } else {
            echo '<li>Download a Windows Loaders archive from <a href="' . LOADERS_PAGE  . '" target=loaders>here</a>. If PHP is built with thread safety disabled, use the Windows non-TS Loaders.';
        }
    } else {
        $multiple_os_versions = count($loader['osvariants']) > 1;
        if ($multiple_os_versions) {

            list($reqd_version,$exact_match) = get_reqd_version($loader['osvariants']);
            if ($reqd_version) {
                $basename = LOADERS_PACKAGE_PREFIX . $loader['oscode'] . '_' . $reqd_version . '_' . $loader['arch'];
            } else {
                $basename = "";
            }
        } else {
            $basename = LOADERS_PACKAGE_PREFIX . $loader['oscode'] . '_' . $loader['arch'];
        }
        if ($basename == "") {
            echo '<li>Download a ' . $loader['osname'] . ' ' . $loader['arch'] . ' Loaders archive from <a href="' . LOADERS_PAGE . '" target="loaders">here</a>.';
            echo "<br>Your system appears to be ${loader['osname']} ${osv[0]} for ${loader['wordsize']} bit. If Loaders are not available for that exact release of ${loader['osname']}, Loaders built for an earlier release should work. Note that you may need to install back compatibility libraries for the operating system.";
            echo '<br>If you cannot find a suitable loader then please raise a ticket at <a href="'. SUPPORT_SITE . '">our support helpdesk</a>.';
        } else {
            echo '<li>Download one of the following archives of Loaders for ' . $loader['osname'] . ' ' . $loader['arch'] . ':'; 
            echo make_archive_list($basename,array('tar.gz','tar.bz2','ipf.zip'));
            echo "<p>Please note that the MS Windows installer version is suitable for uploading from a Windows PC to your ${loader['osname']} server.<br>";
            echo "</p>";
            if ($multiple_os_versions && !$exact_match) {
                echo "<p>Note that you may need to install back compatibility libraries for  ${loader['osname']}.</p>";
            }
        }
    }

    echo '</li>';
}

function loader_install_instructions($server_type)
{
    if (SERVER_SHARED_INI == $server_type) {
        $loader_dir = get_default_loader_dir();
    } else {
        if (is_ms_windows()) {
            $sysinfo = get_sysinfo();
            if ($sysinfo['SS'] == 'IIS') {
                $loader_dir = $_SERVER['windir'] . '\\' . WINDOWS_IIS_LOADER_DIR;
            } else {
                if (!empty($sysinfo['PHP_INI'])) {
                    $parent_dir = dirname($sysinfo['PHP_INI']);
                } else {
                    $parent_dir = $_SERVER["PHPRC"];
                }
                $loader_dir = $parent_dir . '\\' . 'ioncube';
            }
        } else {
            $loader_dir = UNIX_SYSTEM_LOADER_DIR;
        }
    }
    if (SERVER_LOCAL == $server_type) {
        echo "<li>Put the Loader files in <code>$loader_dir</code></li>";
    } else {
        echo "<li>Transfer the Loaders to your web server and install in <code>$loader_dir</code></li>";
    }
    return $loader_dir;
}

function zend_extension_instructions($server_type,$loader_dir)
{
    $sysinfo = get_sysinfo();
    $qt = (is_ms_windows()?'"':'');
    $loader = get_loaderinfo();

    $php_ini_name = ini_file_name();

    if (is_bool($sysinfo['THREAD_SAFE'])) {
        $kwd = zend_extension_line_start();
    } else {
        $kwd = 'zend_extension/zend_extension_ts';
    }

    if (isset($sysinfo['PHP_INI'])) {
        if (SERVER_SHARED_INI == $server_type) {
            $html_dir = $_SERVER['DOCUMENT_ROOT'];
            $ini_path = $html_dir . "/" . $php_ini_name;
            if (file_exists($ini_path)) {
               echo "<li>Edit the <code>$php_ini_name</code> in your  <code>$html_dir</code> directory and add the following line to the <b>top</b> of the file:<br>" . $zend_extension_line;
            } else {
               echo "<li><a href=\"$self?page=phpini\">Save this  <code>$php_ini_name</code> file</a> and upload it to your html directory, $html_dir";
            }
        } else {
            echo "<li>Edit the file <code>${sysinfo['PHP_INI']}</code>";
        } 
    } else {
        echo "<li>Edit the system <code>$php_ini_name</code> file";
    }
    echo " and <b>before</b> any other $kwd lines add:<br>";

    if (!is_bool($sysinfo['THREAD_SAFE']) || !$sysinfo['THREAD_SAFE']) {
        $path = $qt . $loader_dir . DIRECTORY_SEPARATOR . $loader['file'] . $qt;
        echo "<code>zend_extension = $path</code><br>";
    }
    if (!is_bool($sysinfo['THREAD_SAFE']) || $sysinfo['THREAD_SAFE']) {
        $line_start = zend_extension_line_start();
        $path = $qt . $loader_dir . DIRECTORY_SEPARATOR . $loader['file_ts'] . $qt;
        echo "<code>$line_start = $path</code><br>";
    }
    echo '</li>';
}

function server_restart_instructions()
{
    $sysinfo = get_sysinfo();
    $self = get_self();

    if ($sysinfo['SS']) {
        echo "<li>Restart the ${sysinfo['SS']} server software.</li>";
    } else {
        echo "<li>Restart the server software.</li>";
    }

    echo "<li>When the server software has restarted, <a href=\"$self?page=loader_check\">click here to test the Loader</a>.</li>";

    if ($sysinfo['SS'] == 'Apache' && !is_ms_windows()) {
        echo '<li>If the Loader installation failed, check the Apache error log file for errors and see our guide to <a target="unix_errors" href="'. UNIX_ERRORS_URL . '">Unix related errors</a>.</li>';
    }
}

function shared_test_instructions()
{
    echo "<li><a href=\"$self?page=loader_check\">Click here to test the Loader</a>.</li>";
}

function php_ini_instruction_list($server_type)
{
    echo '<h4>Installation Instructions</h4>';
    echo '<div class=panel>';
    echo '<ol>';

    loader_download_instructions(); 
    $loader_dir = loader_install_instructions($server_type);
    zend_extension_instructions($server_type,$loader_dir);
    if ($server_type != SERVER_SHARED_INI) {
        server_restart_instructions();
    } else {
        shared_test_instructions();
    } 
    echo '</ol>';
    echo '</div>';
}

function php_ini_install_shared()
{
    $php_ini_name = ini_file_name();
    $server_type = SERVER_SHARED;
    $self = get_self();
    echo "<p>On your <strong>shared</strong> server, the Loader must be installed using a <code>$php_ini_name</code> configuration file.</p>";
    echo "<p>(<a href=\"$self?manual=1\">Please click here if you are <strong>not</strong> on a shared server</a>.)</p>";

    if (own_php_ini_possible()) {
        $server_type = SERVER_SHARED_INI;
        echo '<p>With your hosting account, you may be able to use your own PHP configuration file.</p>';
    } else {
        echo "<p>It appears that you do not have access to the <code>$php_ini_name</code> file. Your server provider or system administrator should be able to perform the installation for you.<br>Please refer them to the following instructions.</p>";
    }

    php_ini_instruction_list($server_type);
}

function php_ini_install($server_type_desc = null, $server_type = SERVER_DEDICATED, $required = true)
{
    $php_ini_name = ini_file_name();
    $self = get_self();

    echo '<p>';
    if ($server_type_desc) {
        echo "For a <strong>$server_type_desc</strong> server ";
    } else {
        echo "For this server ";
    }

    if ($required) {
        echo "you should install the ionCube Loader using the <code>$php_ini_name</code> configuration file.";
    } else {
        echo "installing the ionCube Loader using the <code>$php_ini_name</code> file is recommended.";
    }
    echo '</p>';
    if ($server_type_desc) {
        echo "<p>(<a href=\"$self?manual=1\">Please click here if you are <strong>not</strong> on a $server_type_desc server</a>.)</p>";
    }
      
    php_ini_instruction_list($server_type);
}

function php_ini_contents($loader_location)
{
    $dbq = (is_ms_windows())?'"':'';
    $line = zend_extension_line_start() . ' = ' . $dbq . $loader_location . $dbq;
    return $line;
}

function help_resources($error_list = array())
{
    return (array(
        '<a target="_blank" href="' . LOADERS_FAQ_URL . '">ionCube Loaders FAQ</a>',
        '<a target="_blank" href="' . LOADER_FORUM_URL . '">ionCube Loader Forum</a>',
        '<a target="_blank" href="' . SUPPORT_SITE . 'index.php?department=3&subject=ionCube+Loader+installation+problem&message='. support_ticket_information($error_list) . '">Raise a support ticket through our helpdesk</a>'));
}

function support_ticket_information($error_list = array())
{
    $sys = get_sysinfo();
    $ld = get_loaderinfo();

    $ticket_strs = array();
    $ticket_strs[] = "PLEASE DO NOT REMOVE THE FOLLOWING INFORMATION\r\n";
    $ticket_strs[] = "==============\r\n";
    if (!empty($error_list)) {
        $ticket_strs[] = "[hr]";
        $ticket_strs[] = "ERRORS";
        $ticket_strs[] = "[table]";
        $ticket_strs[] = '[tr][td]' . join('[/td][/tr][tr][td]',$error_list) . '[/td][/tr]';
        $ticket_strs[] = "[/table]";
    }
    $ticket_strs[] = "[hr]";
    $ticket_strs[] = "SYSTEM INFORMATION";
    $ticket_strs[] = "[table]";
    $info_lines = array();
    $info_lines["Machine architecture"] = $ld['arch'];
    $info_lines["Operating system"] = $ld['osname'] . ' ' . $ld['osver'];
    $info_lines["PHP version"] = PHP_VERSION; 
    if (!$sys['SUPPORTED_COMPILER']) {
        $info_lines["SUPPORTED PHP COMPILER"] = "FALSE";
        $info_lines["PHP COMPILER"] = $sys['PHP_COMPILER'];
    }
    $info_lines["Is CLI?"] = ($sys['IS_CLI']?"Yes":"No");
    $info_lines["Is CGI?"] = ($sys['IS_CGI']?"Yes":"No");
    $info_lines["Is thread-safe?"] = ($sys['THREAD_SAFE']?"Yes":"No");
    $info_lines["Web server"] = $sys['FULL_SS'];
    $info_lines[ "PHP ini file"] = $sys['PHP_INI'];
    if (!file_exists($sys['PHP_INI'])) {
        $info_lines["Ini file found"] = "INI FILE NOT FOUND";
    } else {
        if (!is_readable($sys['PHP_INI'])) {
            $info_lines["Ini file found"] = "INI FILE READABLE";
        } else {
            $info_lines["Ini file found"] = "INI FILE FOUND BUT POSSIBLY NOT READABLE";
        }
    }
    $loader_path = find_loader();
    if (is_string($loader_path)) {
        $info_lines["Loader path"] =  $loader_path;
    } else {
        $info_lines["Loader path"] =  "LOADER PATH NOT FOUND";
    }
    $info_lines["Wizard script path"] = '[url]http://' . $_SERVER["HTTP_HOST"] . get_self() . '[/url]';
    foreach ($info_lines as $h => $i) {
        $ticket_strs[] = '[tr][td]' . $h . '[/td]' . '[td]' . $i . '[/td][/tr]';
    }
    $ticket_strs[] = '[/table]';
    $ticket_strs[] = '[hr]';
    $ticket_strs[] = "\r\n==============\r\n";
    $ticket_strs[] = "PLEASE ENTER ANY ADDITIONAL INFORMATION BELOW\r\n";

    $support_ticket_str = join('',$ticket_strs);
    return rawurlencode($support_ticket_str);
}

function loader_compatibility_test($loader_location)
{
    $errors = array();

    $sysinfo = get_sysinfo();
    if (LOADER_NAME_CHECK) {
        $installed_loader_name = basename($loader_location);
        $expected_loader_name = get_loader_name();
        if ($installed_loader_name != $expected_loader_name) {
                $errors[] = "The installed loader (<code>$installed_loader_name</code>) does not have the name expected (<code>$expected_loader_name</code>) for your system. Please check that you have the correct Loader for your system.";
        }
    }
    if (empty($errors) && !is_readable($loader_location)) {
        $execute_error = "The loader at $loader_location does not appear to be readable.";
        $execute_error .= "<br>Please check that it exists and is readable.";
        $execute_error .= "<br>Please also check the permissions of the containing ";
        $execute_error .= (is_ms_windows()?'folder':'directory') . '.';
        if (($sysinfo['SS'] == 'IIS') || !($sysinfo['IS_CGI'] || $sysinfo['IS_CLI'])) {
            $execute_error .= "<br>Please also check that the web server has been restarted.";
        }
        $execute_error .= ".";
        $errors[] = $execute_error;
    }
    if (function_exists('file_get_contents')) {
        $loader_strs = @file_get_contents($loader_location);
    } else {
        $lines = @file($loader_location);
        $loader_strs = join(' ',$lines);
    }
    $phpv = php_version(); 
    if (preg_match("/php version:\s*(.)\.(.)\.(..?)(-ts)?/i",$loader_strs,$version_matches)) {
        if ($version_matches[1] != $phpv['major'] || $version_matches[2]  != $phpv['minor']) {
            $loader_php = $version_matches[1] . "." . $version_matches[2];
            $server_php =  $phpv['major'] . "." .  $phpv['minor'];
            $errors[] = "The installed loader is for PHP $loader_php but your server is running $server_php";
        }
        if (is_bool($sysinfo['THREAD_SAFE']) &&  $sysinfo['THREAD_SAFE'] && !is_ms_windows() && !(isset($version_matches[4]) && $version_matches[4] == '-ts')) {
            $errors[] = "Your server is running a thread-safe version of PHP but the Loader is not a thread-safe version.";
        } elseif (isset($version_matches[4]) && $version_matches[4] == '-ts' && !(is_bool($sysinfo['THREAD_SAFE']) &&  $sysinfo['THREAD_SAFE'])) {
            $errors[] = "Your server is running a non-thread-safe version of PHP but the Loader is a thread-safe version.";
        }
    } elseif (preg_match("/ioncube_loader_.\.._(.)\.(.)\.(..?)(_nonts)?\.dll/i",$loader_strs,$version_matches)) {
        if (!is_ms_windows()) {
            $errors[] = "You have a Windows Loader but your server does not appear to be running Windows.";
        } else {
            if (isset($version_matches[4]) && $version_matches[4] == '_nonts' && is_bool($sysinfo['THREAD_SAFE']) &&  $sysinfo['THREAD_SAFE']) {
                $errors[] = "You have the non-thread-safe version of the Windows Loader but you need the thread-safe one.";
            } elseif (!(is_bool($sysinfo['THREAD_SAFE']) &&  $sysinfo['THREAD_SAFE']) && !(isset($version_matches[4]) && $version_matches[4] == '_nonts')) {
                $errors[] = "You have the thread-safe version of the Windows Loader but you need the non-thread-safe one."; 
            }
            if ($version_matches[1] != $phpv['major'] || $version_matches[2]  != $phpv['minor']) {
                $loader_php = $version_matches[1] . "." . $version_matches[2];
                $server_php =  $phpv['major'] . "." .  $phpv['minor'];
                $errors[] = "The installed Loader is for PHP $loader_php but your server is running PHP $server_php.";
            }
        }
    } else {
            $errors[] = "PHP version for the Loader cannot be determined - please check that you have a valid ionCube Loader.";
    } 
    if (preg_match("/target os:\s*(.*)_(.*)-/i",$loader_strs,$os_matches)) {
        $loader_info = get_loaderinfo();
        if (strtolower($loader_info['osname']) != $os_matches[1]) {
            $errors[] = "You have the wrong loader for your operating system, ". $loader_info['osname'] . ".";
        } elseif ($loader_info['arch'] == $os_matches[2]) {
            $errors[] = "You have the wrong loaders for your operating system and machine architecture - " . $loader_info['osname'] . " on " . $loader_info['arch'];
        }
    }

    return $errors;
}


function shared_server()
{
    if (!$rtl_path = runtime_loading()) {
        php_ini_install_shared();
    } else {
        if (is_string($rtl_path)) {
            echo "<p>The loader has been successfully installed by runtime loading using <code>dl($rtl_path)</code></p>";
        } else {
            echo "<p>The loader has been successfully installed.</p>";
        }
    }
}

function dedicated_server()
{
    php_ini_install('dedicated or VPS', SERVER_DEDICATED, true);
}

function local_install()
{
    php_ini_install('local',SERVER_LOCAL, true);
}


function unregister_globals()
{
    if (!ini_get('register_globals')) {
        return;
    }

    if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
        die('GLOBALS overwrite attempt detected');
    }

    $noUnset = array('GLOBALS',  '_GET',
                     '_POST',    '_COOKIE',
                     '_REQUEST', '_SERVER',
                     '_ENV',     '_FILES');

    $input = array_merge($_GET,    $_POST,
                         $_COOKIE, $_SERVER,
                         $_ENV,    $_FILES,
                         isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
    
    foreach ($input as $k => $v) {
        if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
            unset($GLOBALS[$k]);
        }
    }
}

function run()
{
    unregister_globals();
    ini_set('session.cache_expire',1);
    if (is_php_version_or_greater(4,3,0)) {
        ini_set('session.use_only_cookies',1);
    }
    ini_set('session.cookie_lifetime',1);
    @session_start();
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } elseif (time() - $_SESSION['CREATED'] > SESSION_LIFETIME_MINUTES * 60) {
        session_destroy();
        $_SESSION = array();
    }
    
    if (!isset($_SERVER)) $_SERVER =& $HTTP_SERVER_VARS;

    (php_sapi_name() == 'cli') && die("This script should only be run by a web server.\n");

    $page = get_request_parameter('page');
    $host = get_request_parameter('host');
    $clear = get_request_parameter('clear');

    if (!empty($host)) {
        if ($host == 'ngd') {
            $_SESSION['not_go_daddy'] = 1;
        }
    }

    if (!empty($clear)) {
        unset($_SESSION['not_go_daddy']);
        unset($_SESSION['latest_version']);
        unset($_SESSION['loader_platform_info']);
        unset($_SESSION['loader_version_info']);
        unset($_SESSION['php_compilers_info']);
    }

    if (!array_key_exists('latest_version',$_SESSION) || !isset($_SESSION['latest_version']) || $_SESSION['latest_version'] === false) {
        $_SESSION['latest_version'] = retrieve_latest_version();
    }

    if (!empty($page)) {
        $fn = "${page}_page";
        if (function_exists($fn)) {
            $fn();
        } else {
            default_page();
        }
    } else {
        $godaddy_root = GoDaddy_root();
        if (empty($godaddy_root)) {
            default_page();
        } else {
            GoDaddy_page($godaddy_root);
        }
    }
    @session_write_close();
    exit(0);
}

function default_page($loader_extension = LOADER_EXTENSION_NAME)
{
    $self = get_self();
    foreach (array('self') as $vn) {
        if (empty($$vn)) {
            error("Unable to initialise ($vn).");
        }
    }

    heading();

    $wizard_update = check_for_wizard_update(true);

    if (extension_loaded($loader_extension)) {
        loader_already_installed();
    } else {
        loader_not_installed();
    }

    footer($wizard_update);
}

function loader_already_installed()
{
    list($lv,$old_version) = ioncube_loader_version_information();
    echo "<p>";
    if ($old_version) {
        echo 'The ionCube Loader ' . $lv . ' is already installed but it is an old version. It is recommended that the Loader be updated to the latest version from the <a href="' . LOADERS_PAGE . '">ionCube Loaders page</a> if one is available for your platform.<p>';
    } else {
        echo 'The ionCube Loader ' . $lv . ' is already installed and encoded files should run without problems.'; 
    }
    echo "</p>";

    echo '<p>Please contact the script provider if you do experience any problems running encoded files.</p>';
}

function loader_not_installed()
{
    $loader = get_loaderinfo();
    $sysinfo = get_sysinfo();

    $stype = get_request_parameter('stype');
    $manual_select = get_request_parameter('manual');

    if (!isset($stype)) {
      echo '<p>To use files that have been protected by the <a href="' . ENCODER_URL . '" target=encoder>ionCube PHP Encoder</a>, a component called the ionCube Loader must be installed.</p>';
    }

    if (!is_array($loader)) {
        if ($loader == ERROR_WINDOWS_64_BIT) {
            echo '<p>Loaders for 64-bit PHP on Windows are not currently available. However, if you <b>install and run 32-bit PHP</b> the corresponding 32-bit Loader for Windows should work.</p>';
            if ($sysinfo['THREAD_SAFE']) {
                echo '<li>Download one of the following archives of 32-bit Windows x86 Loaders:';
                $basename = LOADERS_PACKAGE_PREFIX . 'win_x86';
            } else {
                $basename = LOADERS_PACKAGE_PREFIX . 'win_nonts_x86';
                echo '<li>Download one of the following archives of 32-bit Windows non-TS x86 Loaders:';
            }
            echo make_archive_list($basename);
        } else {
            echo '<p>There may not be a Loader available for your type of system at the moment, however if you create a <a href="'  . SUPPORT_SITE . '">support ticket</a> more advice and information may be available to assist. Please include the URL for this Wizard in your ticket.</p>';
        }
    } else {
        if (!is_supported_php_version()) {
            echo '<p>Your server is running PHP version ' . PHP_VERSION . ' and is
                    unsupported by ionCube Loaders.  Recommended PHP 4 versions are PHP 4.2 or higher, 
                    and PHP 5.1 or higher for PHP 5.</p>';
        } elseif (!$sysinfo['SUPPORTED_COMPILER']) {
            $supported_compilers = supported_win_compilers();
            $supported_compiler_string = join('/',$supported_compilers);
            echo '<p>At the current time the ionCube Loader requires PHP to be built with ' . $supported_compiler_string . '. Your PHP software has been built using ' . $sysinfo['PHP_COMPILER'] . '. Supported builds of PHP are available from <a href="http://windows.php.net/download/">PHP.net</a>.';
        } else {
            if (!in_array($stype,array('s','d','l'))) {
                if (!$manual_select && is_local()) {
                    local_install();
                } elseif (!$manual_select && !is_possibly_dedicated_or_local()) {
                    shared_server();
                } else {
                    echo server_selection_form();
                }
            } else {
                switch ($stype) {
                    case 's':
                        shared_server();
                    break;

                    case 'd':
                        dedicated_server();
                    break;

                    case 'l':
                        local_install();
                    break;
                }
            }
        }
    }
}

function server_selection_form()
{
    $self = get_self();
    $form = <<<EOT
    <p>This Wizard will give you information on how to install the Loader.</p>
    <p>Please select the type of web server that you have and then click Next.</p>
    <form method=GET action=$self>
        <input type=radio id=shared name=stype value=s><label for=shared>Shared <small>(for example, server with FTP access only and no access to php.ini)</small></label><br>
        <input type=radio id=dedi name=stype value=d><label for=dedi>Dedicated or VPS <small>(server with full root ssh access)</small></label><br>
        <input type=radio id=local name=stype value=l><label for=local>Local install</label>
        <p><input type=submit value=Next></p>
    </form>
EOT;
    return $form;
}

function phpinfo_page()
{
    phpinfo();
}

function loader_check_page($ext_name = LOADER_EXTENSION_NAME)
{
    heading();

    if (extension_loaded($ext_name)) {
        list($lv,$is_old) = ioncube_loader_version_information();
        echo 'The ionCube Loader ' . $lv . ' is installed and encoded files should run successfully.';
        echo '<p>Please contact the script provider if you do experience any problems running encoded files.</p>';
    } else {
        echo 'The ionCube Loader is <b>not</b> currently installed successfully.<br>';
        list_loader_errors();
    }

    footer(true);
}

function list_loader_errors()
{
    $errors = array();
    $loader_loc = find_loader(); 
    $self = get_self();

    if (is_string($loader_loc)) {
        $errors = loader_compatibility_test($loader_loc);
    } else {
        $errors = $loader_loc;
    } 
    if (!empty($errors)) {
        $try_again = '<a href="#" onClick="window.location.href=window.location.href">try again</a>';
        if (count($errors) > 1) {
            $retry_message = "Please correct those errors and $try_again.";
            echo 'The following problems have been found with the Loader installation:';
        } else {
            $retry_message = "Please correct that error and $try_again.";
            echo 'The following problem has been found with the Loader installation:';
        }
        echo make_list($errors,"ul");
        echo $retry_message;
    }
    echo " You may wish to view the following for further help:";
    echo make_list(help_resources($errors),"ul");
    echo '<a href="' . $self . '">Click here to go back to the start of the Loader Wizard</a>.<br>';
}

function phpini_page()
{
    if (empty($loader_dir)) {
        $loader_dir = get_default_loader_dir();
    }
    $loader_name = get_loader_name(); 
    $loader_loc = $loader_dir . "/" . $loader_name;
    $loader_loc = get_loader_location(get_request_parameter('ldpath'));
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename='. ini_file_name());
    echo php_ini_contents($loader_loc); 
}

function phpconfig_page()
{
    $sys = get_sysinfo();
    if (isset($sys['PHP_INI']) && file_exists($sys['PHP_INI'])) {
        header('Content-Type: text/plain');
        @readfile($sys['PHP_INI']);
    } else {
        echo "php.ini file could not be read.";
    }
}



function GoDaddy_root($html_root = '')
{
    $godaddy_root = '';
    if (empty($html_root)) {
        $html_root =  $_SERVER['DOCUMENT_ROOT'];
    }
    if (!(isset($_SESSION['not_go_daddy']) && $_SESSION['not_go_daddy'])) {
        $godaddy_pattern = "[\\/]home[\\/]content[\\/][0-9a-z][\\/][0-9a-z][\\/][0-9a-z][\\/][0-9a-z]+[\\/]html";

        if (preg_match("@$godaddy_pattern@i",$html_root,$matches)) {
            $godaddy_root = $matches[0];
        } 
    }
    return $godaddy_root;
}

function GoDaddy_windows_instructions()
{
    $instr = "It appears that you are hosted on a Windows server at GoDaddy.<br/>";
    $instr .= "Please change to a Linux hosting plan at GoDaddy.<br />";
    $instr .=  "If you contact their support team they should be able to switch you to a Linux server.";

    echo $instr;
}

function GoDaddy_linux_instructions($html_dir)
{
    $self = get_self();
    $loader_name = get_loader_name();
    $zend_extension_line="<code>zend_extension = $html_dir/ioncube/$loader_name</code>";
    $ini_path = $html_dir . "/php5.ini";

    $instr = array();
    $instr[] = 'In your html directory, ' . $html_dir . ', create a sub-directory called <b>ioncube</b>.';
    if (file_exists($ini_path)) {
       $instr[] = "Edit the php5.ini in your  $html_dir and add the following line to the <b>top</b> of the file:<br>" . $zend_extension_line ;
    } else {
        $instr[] = "<a href=\"$self?page=phpini\">Save this php5.ini file</a> and upload it to your html directory, $html_dir";
    }
    $instr[] = 'Download the <a target="_blank" href="http://downloads2.ioncube.com/loader_downloads/ioncube_loaders_lin_x86.zip">Linux Loaders</a>.';
    $instr[] = 'Unzip the loaders and upload them into the ioncube directory you created previously.';
    $instr[] = 'The encoded files should now be working.';

    echo '<div class=panel>';
    echo (make_list($instr));
    echo '</div>';
}

function GoDaddy_page($home_dir)
{
    $self = get_self();

    heading();

    $inst_str = '<h4>GoDaddy Installation Instructions</h4>';
    $inst_str .= '<p>It appears that you are hosted with GoDaddy (<a target="_blank" href="http://www.godaddy.com/">www.godaddy.com</a>). ';
    $inst_str .= "If that is <b>not</b> the case then please <a href=\"$self?host=ngd\">click here to go to the main page of this installation wizard</a>.</p>";
    $inst_str .= "<p>If you have already installed the loader then please <a href=\"$self?page=loader_check\">click here to test the Loader</a>.</p>";
    
    echo $inst_str;

    if (is_ms_windows()) {
        GoDaddy_windows_instructions();
    } else {
        GoDaddy_linux_instructions($home_dir);
    }

    footer(true);
}



function get_request_parameter($param_name)
{
    static $request_array;

    if (!isset($request_array)) {
        if (isset($_GET)) {
            $request_array = $_GET;
        } elseif (isset($HTTP_GET_VARS)) {
            $request_array = $HTTP_GET_VARS;
        }
    }

    if (isset($request_array[$param_name])) {
        return $request_array[$param_name];
    } else {
        return null;
    }
}

function make_list($list_items,$list_type='ol')
{
    $html = '';
    if (!empty($list_items)) {
        $html .= "<$list_type>";
        $html .= '<li>';
        $html .= join('</li><li>',$list_items);
        $html .= '</li>';
        $html .= "</$list_type>";
    }
    return $html;
} 

function make_archive_list($basename,$archives_list = array(),$download_server = IONCUBE_DOWNLOADS_SERVER)
{
    if (empty($archives_list)) {
		$archives_list = array('tar.gz','tar.bz2','zip','ipf.zip');
	}
	
	foreach ($archives_list as $a) {
		$link_text = ($a == 'ipf.zip')?'MS Windows installer':$a;
        $ext_sep = ($a == 'ipf.zip')?'_':'.';
		$archive_list[] = "<a href=\"$download_server/$basename$ext_sep$a\">$link_text</a>";
	}

    return make_list($archive_list,"ul");
}

function error($m)
{
    die("<b>ERROR:</b> <span class=\"error\">$m</span><p>Please help us improve this script by <a href=\"". SUPPORT_SITE . "\">reporting this error</a> and including the URL to the script so that we can test it.");
}

function get_self()
{ 
    return @$_SERVER['PHP_SELF'];
}

function heading()
{
    $self = get_self();

    echo <<<EOT
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN "http://www.w3.org/TR/html4/loose.dtd">
    <html>
    <head>
        <title>ionCube Loader Wizard</title>
        <link rel="stylesheet" type="text/css" href="$self?page=css">
    </head>
    <body>
    <div id=header>
        <img src="?page=logo" alt="ionCube logo">
    </div>
    <div id=main>
        <h2>ionCube Loader Wizard</h2>
EOT;
}

function footer($update_info = null)
{
    $self = get_self();
    $year = date("Y");

    echo "</div>";
    echo "<div id=\"footer\">" .
    "Copyright ionCube Ltd. 2002-$year | " .
    "Loader Wizard version " . script_version() . " ";

    if ($update_info === true) {
        $update_info = check_for_wizard_update(false);  
    }

    $wizard_version_string = '('; 
    if ($update_info === null) {
        $wizard_version_string .= '<a href="' . LOADERS_PAGE .'">check for new version</a>';
    } else if ($update_info !== false) {
        $wizard_version_string .= '<a href="' . LOADERS_PAGE .'">download version ' . $update_info . '</a>';
    } else {
        $wizard_version_string .=  "current";
    }
    $wizard_version_string .= ')'; 
    echo $wizard_version_string;

    echo " | <a href=\"$self?page=phpinfo\" target=\"phpinfo\">phpinfo</a>";
    echo " | <a href=\"$self?page=phpconfig\" target=\"phpconfig\">config</a>";
    echo " | <a href=\"$self\">wizard start</a>";
    echo " | <a href=\"$self?page=loader_check\">loader test</a>";
    echo ' | <a href="' . LOADERS_PAGE . '" target="loaders">Loaders</a>';

    echo "</div>\n";
    echo "\n</body></html>\n";
}

function css_page()
{
    header('Content-Type: text/css');
    echo <<<EOT
    BODY {
        font-family: verdana, helvetica, arial, sans-serif;
        font-size: 10pt;
        line-height: 150%;
        margin: 0px;
    }

    CODE {
        color: #c00080;
    }

    LI {
        margin-top: 10px;
    }

    p.alert {
        border: 1px solid #660000;
        padding: 1ex 1em;
        background-color: #ffeeee;
        color: #660000; 
    }

    .error {
        color: #FF0000;
    }

    .panel {
        border: 1px solid #c0c0c0;
        background-color: #f0f0f0;
        width: 75%;
        padding-right: 10px;
    }

    #header {
        background: #3f0f0f;
    }

    #footer {
        border-top: 1px solid #404040;
        margin-top: 20px;
        padding-top: 10px;
        font-size: 75%;
        text-align: center;
    }

    #main {
        margin: 20px;
    }
EOT;
}

function logo_page()
{
$img_encoded = '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';

header('Content-Type: image/png');
header('Cache-Control: public');
echo base64_decode($img_encoded);
}
