<?php
define ('ARCHIVE_TAR_ATT_SEPARATOR', 90001);
class Archive_Tar
{
    var $_tarname='';
    var $_compress=false;
    var $_compress_type='none';
    var $_separator=' ';
    var $_file=0;
    var $_temp_tarname='';
    function Archive_Tar($p_tarname, $p_compress = null)
    {

		if (substr(PHP_OS, 0, 3) == 'WIN') {
			define('OS_WINDOWS', true);
			define('OS_UNIX',    false);
			define('PEAR_OS',    'Windows');
		} else {
			define('OS_WINDOWS', false);
			define('OS_UNIX',    true);
			define('PEAR_OS',    'Unix'); // blatant assumption
		}


        $this->_compress = false;
        $this->_compress_type = 'none';
        if ($p_compress === null) {
            if (@file_exists($p_tarname)) {
                if ($fp = @fopen($p_tarname, "rb")) {
                    $data = fread($fp, 2);
                    fclose($fp);
                    if ($data == "\37\213") {
                        $this->_compress = true;
                        $this->_compress_type = 'gz';
                    } elseif ($data == "BZ") {
                        $this->_compress = true;
                        $this->_compress_type = 'bz2';
                    }
                }
            } else {
                if (substr($p_tarname, -2) == 'gz') {
                    $this->_compress = true;
                    $this->_compress_type = 'gz';
                } elseif ((substr($p_tarname, -3) == 'bz2') ||
                          (substr($p_tarname, -2) == 'bz')) {
                    $this->_compress = true;
                    $this->_compress_type = 'bz2';
                }
            }
        } else {
            if (($p_compress === true) || ($p_compress == 'gz')) {
                $this->_compress = true;
                $this->_compress_type = 'gz';
            } else if ($p_compress == 'bz2') {
                $this->_compress = true;
                $this->_compress_type = 'bz2';
            }
        }
        $this->_tarname = $p_tarname;
        if ($this->_compress) 
		{
            if ($this->_compress_type == 'gz')
                $extname = 'zlib';
            else if ($this->_compress_type == 'bz2')
                $extname = 'bz2';

            if (!extension_loaded($extname)) {
                die("The extension '$extname' couldn't be found.\n".
                    "Please make sure your version of PHP was built ".
                    "with '$extname' support.\n");
                return false;
            }
        }
    }
	function _Archive_Tar()
    {
        $this->_close();
        if ($this->_temp_tarname != '')
            @unlink($this->_temp_tarname);
       
    }
 
    function extract($p_path='', $write_mode)
    {
        return $this->extractModify($p_path, '', $write_mode);
    }
 
    function listContent()
    {
        $v_list_detail = array();

        if ($this->_openRead()) {
            if (!$this->_extractList('', $v_list_detail, "list", '', '', 'wb')) {
                unset($v_list_detail);
                $v_list_detail = 0;
            }
            $this->_close();
        }
        return $v_list_detail;
    }
 
    function extractModify($p_path, $p_remove_path, $write_mode)
    {
        $v_result = true;
        $v_list_detail = array();

        if ($v_result = $this->_openRead()) {
            $v_result = $this->_extractList($p_path, $v_list_detail, "complete", 0, $p_remove_path, $write_mode );
            $this->_close();
        }

        return $v_result;
    }
  
	function raiseError($msg){
		echo("<b>TAR ERROR</b> $msg <br>");
	}

    function _error($p_message){   
        $this->raiseError($p_message);
    }

    function _warning($p_message){
        $this->raiseError($p_message);
    }
  
    function _openRead(){
    
        $v_filename = $this->_tarname;

        if ($this->_compress_type == 'gz')
            $this->_file = @gzopen($v_filename, "rb");
        else if ($this->_compress_type == 'bz2')
            $this->_file = @bzopen($v_filename, "rb");
        else if ($this->_compress_type == 'none')
            $this->_file = @fopen($v_filename, "rb");
        else
            $this->_error('Unknown or missing compression type ('.$this->_compress_type.')');

        if ($this->_file == 0) {
            $this->_error('Unable to open in read mode \''.$v_filename.'\'');
            return false;
        }

        return true;
    }
  
    function _close()    {
        if (is_resource($this->_file)) {
            if ($this->_compress_type == 'gz')
                @gzclose($this->_file);
            else if ($this->_compress_type == 'bz2')
                @bzclose($this->_file);
            else if ($this->_compress_type == 'none')
                @fclose($this->_file);
            else
                $this->_error('Unknown or missing compression type ('.$this->_compress_type.')');

            $this->_file = 0;
        }
		
        if ($this->_temp_tarname != '') {
            @unlink($this->_temp_tarname);
            $this->_temp_tarname = '';
        }

        return true;
    }

    function _readBlock($p_len=null)
    {
      $v_block = null;
      if (is_resource($this->_file)) {
          if ($p_len === null)
              $p_len = 512;
              
          if ($this->_compress_type == 'gz')
              $v_block = @gzread($this->_file, 512);
          else if ($this->_compress_type == 'bz2')
              $v_block = @bzread($this->_file, 512);
          else if ($this->_compress_type == 'none')
              $v_block = @fread($this->_file, 512);
          else
              $this->_error('Unknown or missing compression type ('.$this->_compress_type.')');

      }
      return $v_block;
    }

    function _jumpBlock($p_len=null)
    {
      if (is_resource($this->_file)) {
          if ($p_len === null)
              $p_len = 1;

          if ($this->_compress_type == 'gz')
              @gzseek($this->_file, @gztell($this->_file)+($p_len*512));
          else if ($this->_compress_type == 'bz2') {
              // ----- Replace missing bztell() and bzseek()
              for ($i=0; $i<$p_len; $i++)
                  $this->_readBlock();
          } else if ($this->_compress_type == 'none')
              @fseek($this->_file, @ftell($this->_file)+($p_len*512));
          else
              $this->_error('Unknown or missing compression type ('.$this->_compress_type.')');

      }
      return true;
    }
 
    function _readHeader($v_binary_data, &$v_header)
    {
        if (strlen($v_binary_data)==0) {
            $v_header['filename'] = '';
            return true;
        }

        if (strlen($v_binary_data) != 512) {
            $v_header['filename'] = '';
            $this->_error('Invalid block size : '.strlen($v_binary_data));
            return false;
        }

      
        $v_checksum = 0;
        
        for ($i=0; $i<148; $i++)
            $v_checksum+=ord(substr($v_binary_data,$i,1));
       
        for ($i=148; $i<156; $i++)
            $v_checksum += ord(' ');
      
        for ($i=156; $i<512; $i++)
           $v_checksum+=ord(substr($v_binary_data,$i,1));

        $v_data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor", $v_binary_data);

       
        $v_header['checksum'] = OctDec(trim($v_data['checksum']));
        if ($v_header['checksum'] != $v_checksum) {
            $v_header['filename'] = '';

            
            if (($v_checksum == 256) && ($v_header['checksum'] == 0))
                return true;

            $this->_error('Invalid checksum for file "'.$v_data['filename'].'" : '.$v_checksum.' calculated, '.$v_header['checksum'].' expected');
            return false;
        }

       
        $v_header['filename'] = trim($v_data['filename']);
        $v_header['mode'] = OctDec(trim($v_data['mode']));
        $v_header['uid'] = OctDec(trim($v_data['uid']));
        $v_header['gid'] = OctDec(trim($v_data['gid']));
        $v_header['size'] = OctDec(trim($v_data['size']));
        $v_header['mtime'] = OctDec(trim($v_data['mtime']));
        if (($v_header['typeflag'] = $v_data['typeflag']) == "5") {
          $v_header['size'] = 0;
        }
      
        return true;
    }
   
    function _readLongHeader(&$v_header)
    {
      $v_filename = '';
      $n = floor($v_header['size']/512);
      for ($i=0; $i<$n; $i++) {
        $v_content = $this->_readBlock();
        $v_filename .= $v_content;
      }
      if (($v_header['size'] % 512) != 0) {
        $v_content = $this->_readBlock();
        $v_filename .= $v_content;
      }

     
      $v_binary_data = $this->_readBlock();

      if (!$this->_readHeader($v_binary_data, $v_header))
        return false;

      $v_header['filename'] = $v_filename;

      return true;
    }
  
    function _extractList($p_path, &$p_list_detail, $p_mode, $p_file_list, $p_remove_path, $write_mode)
    {
    $v_result=true;
    $v_nb = 0;
    $v_extract_all = true;
    $v_listing = false;

    $p_path = $this->_translateWinPath($p_path, false);
    if ($p_path == '' || (substr($p_path, 0, 1) != '/' && substr($p_path, 0, 3) != "../" && !strpos($p_path, ':'))) {
      $p_path = "./".$p_path;
    }
    $p_remove_path = $this->_translateWinPath($p_remove_path);

    // ----- Look for path to remove format (should end by /)
    if (($p_remove_path != '') && (substr($p_remove_path, -1) != '/'))
      $p_remove_path .= '/';
    $p_remove_path_size = strlen($p_remove_path);

    switch ($p_mode) {
      case "complete" :
        $v_extract_all = TRUE;
        $v_listing = FALSE;
      break;
      case "partial" :
          $v_extract_all = FALSE;
          $v_listing = FALSE;
      break;
      case "list" :
          $v_extract_all = FALSE;
          $v_listing = TRUE;
      break;
      default :
        $this->_error('Invalid extract mode ('.$p_mode.')');
        return false;
    }

    clearstatcache();

    While (strlen($v_binary_data = $this->_readBlock()) != 0)
    {
      $v_extract_file = FALSE;
      $v_extraction_stopped = 0;

      if (!$this->_readHeader($v_binary_data, $v_header))
        return false;

      if ($v_header['filename'] == '')
        continue;

      // ----- Look for long filename
      if ($v_header['typeflag'] == 'L') {
        if (!$this->_readLongHeader($v_header))
          return false;
      }
      if ((!$v_extract_all) && (is_array($p_file_list))) {
        $v_extract_file = false;

        for ($i=0; $i<sizeof($p_file_list); $i++) {
          if (substr($p_file_list[$i], -1) == '/') {
            if ((strlen($v_header['filename']) > strlen($p_file_list[$i])) && (substr($v_header['filename'], 0, strlen($p_file_list[$i])) == $p_file_list[$i])) {
              $v_extract_file = TRUE;
              break;
            }
          }
          elseif ($p_file_list[$i] == $v_header['filename']) {
            $v_extract_file = TRUE;
            break;
          }
        }
      } else {
        $v_extract_file = TRUE;
      }
      if (($v_extract_file) && (!$v_listing))
      {
        if (($p_remove_path != '')
            && (substr($v_header['filename'], 0, $p_remove_path_size) == $p_remove_path))
          $v_header['filename'] = substr($v_header['filename'], $p_remove_path_size);
        if (($p_path != './') && ($p_path != '/')) {
          while (substr($p_path, -1) == '/')
            $p_path = substr($p_path, 0, strlen($p_path)-1);

          if (substr($v_header['filename'], 0, 1) == '/')
              $v_header['filename'] = $p_path.$v_header['filename'];
          else
            $v_header['filename'] = $p_path.'/'.$v_header['filename'];
        }
        if (file_exists($v_header['filename'])) {
          if ((@is_dir($v_header['filename'])) && ($v_header['typeflag'] == '')) {
            $this->_error('File '.$v_header['filename'].' already exists as a directory');
            return false;
          }
          if ((is_file($v_header['filename'])) && ($v_header['typeflag'] == "5")) {
            $this->_error('Directory '.$v_header['filename'].' already exists as a file');
            return false;
          }
          if (!is_writeable($v_header['filename'])) {
            $this->_error('File '.$v_header['filename'].' already exists and is write protected');
            return false;
          }
          if (filemtime($v_header['filename']) > $v_header['mtime']) {
          }
        }
        elseif (($v_result = $this->_dirCheck(($v_header['typeflag'] == "5"?$v_header['filename']:dirname($v_header['filename'])))) != 1) {
            $this->_error('Unable to create path for '.$v_header['filename']);
            return false;
        }
        if ($v_extract_file) {
          if ($v_header['typeflag'] == "5") {
            if (!@file_exists($v_header['filename'])) {
                if (!@mkdir($v_header['filename'], 0777)) {
                    $this->_error('Unable to create directory {'.$v_header['filename'].'}');
                    return false;
                }
            }
          } else {
              if (($v_dest_file = @fopen($v_header['filename'], $write_mode)) == 0) {			//ab
                  $this->_error('Error while opening {'.$v_header['filename'].'} in write binary mode');
                  return false;
              } else {
                  $n = floor($v_header['size']/512);
                  for ($i=0; $i<$n; $i++) {
                      $v_content = $this->_readBlock();
                      fwrite($v_dest_file, $v_content, 512);
                  }
            if (($v_header['size'] % 512) != 0) {
              $v_content = $this->_readBlock();
              fwrite($v_dest_file, $v_content, ($v_header['size'] % 512));
            }
            @fclose($v_dest_file);
            @touch($v_header['filename'], $v_header['mtime']);
          }

          clearstatcache();
          if (filesize($v_header['filename']) != $v_header['size']) {
              $this->_error('Extracted file '.$v_header['filename'].' does not have the correct file size \''.filesize($v_header['filename']).'\' ('.$v_header['size'].' expected). Archive may be corrupted.');
              return false;
          }
          }
        } else {
          $this->_jumpBlock(ceil(($v_header['size']/512)));
        }
      } else {
          $this->_jumpBlock(ceil(($v_header['size']/512)));
      }

  
      if ($v_listing || $v_extract_file || $v_extraction_stopped) {
        if (($v_file_dir = dirname($v_header['filename'])) == $v_header['filename'])
          $v_file_dir = '';
        if ((substr($v_header['filename'], 0, 1) == '/') && ($v_file_dir == ''))
          $v_file_dir = '/';

        $p_list_detail[$v_nb++] = $v_header;
      }
    }
        return true;
    }
   
    function _dirCheck($p_dir)
    {
        if ((@is_dir($p_dir)) || ($p_dir == ''))
            return true;

        $p_parent_dir = dirname($p_dir);

        if (($p_parent_dir != $p_dir) &&
            ($p_parent_dir != '') &&
            (!$this->_dirCheck($p_parent_dir)))
             return false;

        if (!@mkdir($p_dir, 0777)) {
            $this->_error("Unable to create directory '$p_dir'");
            return false;
        }
        return true;
    }
	
    function _pathReduction($p_dir)
    {
        $v_result = '';

		if ($p_dir != '') {
            $v_list = explode('/', $p_dir);

            for ($i=sizeof($v_list)-1; $i>=0; $i--) {
                if ($v_list[$i] == ".") {
                }
                else if ($v_list[$i] == "..") {
                    $i--;
                }
                else if (($v_list[$i] == '') && ($i!=(sizeof($v_list)-1)) && ($i!=0)) {
                } else {
                    $v_result = $v_list[$i].($i!=(sizeof($v_list)-1)?'/'.$v_result:'');
                }
            }
        }
        $v_result = strtr($v_result, '\\', '/');
        return $v_result;
    }

	function _translateWinPath($p_path, $p_remove_disk_letter=true)
    {
      if (OS_WINDOWS) 
	  {
          if (($p_remove_disk_letter) && (($v_position = strpos($p_path, ':')) != false)) {
              $p_path = substr($p_path, $v_position+1);
          }
          if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0,1) == '\\')) {
              $p_path = strtr($p_path, '\\', '/');
          }
      }
      return $p_path;
    }
}
?>