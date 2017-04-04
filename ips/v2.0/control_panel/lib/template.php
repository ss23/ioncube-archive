<?php

/* Copyright (C) 2006 ionCube Ltd. 
 * This file is subject to the ionCube Performance System License. 
 * All rights reserved.
 */

$TF_stack = array();

class TF
{
  var $templates = array();
  var $template_dirs = array();
  var $template_sfix = '';
  var $error_checking = true;
  var $cache_templates = true;
  var $globals = null;

  function TF($dir = '.', $sfix = '.tpl') {
    $this->addTemplateDir($dir);
    $this->setTemplateSuffix($sfix);
  }
  
  function setErrorChecking($state) {
    $this->error_checking = $state;
  }

  function setGlobals($globals) {
    $this->globals = $globals;
  }

  function setTemplatePath($path) {
    $this->template_dirs = split(':', $path);
  }

  function addTemplateDir($dir, $first = false) {
    if ($first) {
      array_unshift($this->template_dirs, $dir);
    } else {
      $this->template_dirs[] = $dir;
    }
  }

  function templatePath() {
    return join(':',$this->template_dirs);
  }

  function setTemplateSuffix($sfix = '') {
    $this->template_sfix = $sfix;
  }

  function getTemplateContents($template_name, $sfix = null) {
    $template = null;

    if (isset($this->templates[$template_name])) {
      $template = $this->templates[$template_name];
    } else {
      if ($sfix === null) {
	$sfix = $this->template_sfix;
      }

      foreach ($this->template_dirs as $dir) {
	$path = "$dir/$template_name$sfix";
	if (file_exists($path)) {
	  $template = @file_get_contents($path);
	  if ($this->cache_templates && $template) {
	    $this->templates[$template_name] = $template;
	  }
	  if (substr($template,0,5) == '<?php') {
	    include_once($path);
	  }
	  break;
	}
      }
    }
    
    if ($template === null && $this->error_checking) {
      echo "Unable to locate template $template_name at $path\n";
    }

    return $template;
  }

  function setTemplate($name, $content) {
    $this->templates[$name] = $content;
  }

  function render($template_name, $data = array(), $sfix = null) {
    $template = $this->getTemplateContents($template_name, $sfix);

    if (substr($template, 0, 5) == '<?php') {
      $data['_TF'] = &$this;
      $fn = $template_name . '_tpl';
      return $fn($data);
    } else {
      return $this->renderS($template, $data);
    }
  }

  function renderArray($template_name, $data = array(), $sfix = null) {
    $template = $this->getTemplateContents($template_name, $sfix);

    if (substr($template, 0, 5) == '<?php') {
      $fn = $template_name . '_tpl';
      return $fn($data);
    } else {
      return $this->renderArrayS($template, $data);
    }
  }

  function renderS($template, $data = array(), $prepend = '', $append = '') {
    global $TF_stack;

    //    array_push($TF_stack, $data);
    if (is_array($this->globals)) {
      foreach ($this->globals as $k=>$v) {
	$data[$k] = $v;
      }
    }
    array_push($TF_stack, array('factory'=>&$this, 'data'=>$data));
     
	$res = preg_replace_callback('/<([^> :]*):([^" >]*)(([^>"]*|"[^"]*")*)>/', 'TF_replace', $template);
    $res = preg_replace_callback('/<=([^>]*)>/', 'TF_replace_val', $res);
    
    array_pop($TF_stack);

    return $prepend . $res . $append;
  }

  function renderArrayS($template, $data = array(), $prepend = '', $append = '', $sep = "\n") {
    $res = '';
    $n = 0;

    foreach ($data as $datum) {
      if ($n) {
	$res .= $sep;
      }
      $res .= $this->renderS($template, $datum);
      $n++;
    }
    return $prepend . $res . $append;
  }
}

function TF_replace($arg)
{
  global $TF_stack;

  $context = $TF_stack[count($TF_stack) - 1];
  $factory =& $context['factory'];
  $data = $context['data'];

  $class = $arg[1];
  $method = $arg[2];
  $method_args = $arg[3];

  if ($method == "#")
	  return "";
  $args = array();
  if ($method_args) {

    preg_match_all('/(([a-z0-9_:]+)=)?(([^ \t"]+)|"([^"]*)")/', $method_args, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
      $arg_name = $match[2];
      if ($match[4] !== '') {	// If not a quoted arg value
			$key = $match[4];
			if (ctype_digit($key[0]) || $key[0] == '-') { // If a numeric arg
			$val = $key + 0;	// convert to number
	  } else {		// Treat as an attribute assignment
			$val = @$data[$key];	// Do a simple lookup for attribute value first
			if ($val === null) 
			{	// If didn't exist then maybe it's a dotted ref
				$data_source = $data;
				while ($dot = strpos($key,'.')) {
				  $adata = substr($key, 0, $dot); // Get the name of the source array
				  $key = substr($key, $dot + 1); // then the rest
				  $data_source = $data_source[$adata]; 
				}
				$val = @$data_source[$key];
	  }
	}
      } else {
	$val = $match[5];
	$val = preg_replace_callback('/{=([^}]*)}/', 'TF_replace_val', $val);
      }
      
      if ($arg_name) {
	$args[$arg_name] = $val;
      } else {
	$args[] = $val;
      }
    }
  }
  $s = @$args['_suffix'];

  $res = '';

  if ($dot = strpos($method,'.')) {
    $sfield = substr($method, $dot + 1);
    $method = substr($method, 0, $dot);

    $array_data = @$data[$sfield];

    if (is_array($array_data)) {
      $n = 0;

      foreach ($array_data as $key => $datum) {
	if (is_array($datum)) {
	  $merged_data = $data;
	  foreach ($datum as $k=>$v) {
	    $merged_data[$k] = $v;
	  }
	} else {
	
	  $merged_data = $data;
	  $merged_data[$key] = $datum;
	}

	if ($n && @$args['_separator']) {
	  $res .= $args['_separator'];
	}

	foreach ($args as $k=>$v) {
	  if ($sep = strpos($k,':')) {
	    $class = substr($k, $sep + 1);
	    $k = substr($k, 0, $sep);

	    switch ($class) {
	    case 'odd':
	      if (($n % 2) == 1) {
		$merged_data[$k] = $v;
	      }
	      break;

	    case 'even':
	      if (($n % 2) == 0) {
		$merged_data[$k] = $v;
	      }
	      break;
	    }
	  } else {
	    $merged_data[$k] = $v;
	  }
	}
	$res .= $factory->render($method, $merged_data, $s);

	$n++;
      }
    } else {
      if ($array_data !== null) {
	
		echo "Data not an array<br>";
        echo "Do $method with field $sfield<br>";

		//dump_var($data);
      }
    }
  } else {
    $merged_data = array_merge($data, $args);
    $res = $factory->render($method, $merged_data, $s);
  }

  return $res;
}

function TF_replace_val($arg)
{
  global $TF_stack;

  $context = $TF_stack[count($TF_stack) - 1];
  $data = $context['data'];

  $key = $arg[1];

  if ($key[0] == '*') {
    $key = $data[substr($key,1)];
  }

  $val = @$data[$key];
  if ($val === null) {
    if ($dot = strpos($key,'.')) {
      $skey = substr($key, 0, $dot);
      $sfield = substr($key, $dot + 1);

      $val = @$data[$skey];
      if (is_array($val)) {
	$val = @$val[$sfield];
      } else {
	$val = '';
      }
    }
  }

  return $val;
}

?>
