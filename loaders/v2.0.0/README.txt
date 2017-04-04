                      The ionCube Loader - Version 2
                      ------------------------------

This package contains the latest available Loaders for the PHP versions and
operating systems that you selected.

In the package you should have:

* Loader(s)

* Run-time loading support test script (ioncube-rtl-tester.php)

* php.ini install assistant script (ioncube-install-assistant.php)

* License for use of the Loader and encoded files (LICENSE)


Installation
------------

* INSTALLING FOR RUN-TIME LOADING

The run-time loading feature is the easiest way to run encoded files, and
lets encoded files locate and install the correct Loader when needed. 

Provided that run-time loading is supported on the target system, encoded
scripts and Loaders can be bundled together without the user having to install
any Loader or update their system configuration, and without having to
know about the PHP version or operating system used.

For run-time loading to work, a directory called 'ioncube' containing the
Loaders (e.g. this directory) should be placed in or above the top
directory of encoded files. For example, if you have encoded files in
or below '/var/www/htdocs/', you might place the 'ioncube' directory
in '/var/www/htdocs' or '/var/www'.  If you have an application or library
to distribute, you could place the ioncube directory within the top directory
of your project or library.

It's not working - why?
-----------------------

If encoded files fail to run with run-time loading, you can test this by
using the PHP script 'ioncube-rtl-tester.php' that's included in this package.

1. Copy the 'ioncube-rtl-tester.php' PHP script to a directory where you
   expect encoded files to be working.

2. Access the script from a web server or with a PHP cli or cgi executable.

3. The script will try to locate and install the required Loader, and will
   produce output as it runs.

4. If the script output does not make any problem obvious then please send
   the output to support@ioncube.com to receive support.


* INSTALLING IN PHP.INI

Installing in the php.ini file is also simple, and offers the best
performance for encoded scripts. It is also required for systems that use
safe mode, or if PHP has been built with thread support, e.g. on Windows.

The 'ioncube-install-assistant.php' PHP script is provided to assist with 
this. Access the script from a web server or a PHP cli or cgi executable
and it should tell you which Loader to install, which file to edit and
what you need to add  (it's just a one line change).

If you wish to install without using the assistant script then please read
the following section.


* INSTALLING IN PHP.INI EXPLAINED

Before installing, you need to know:

1) Which operating system you are using.

2) Which PHP version you are using.

3) Is your PHP build threaded or not? 

4) Where your php.ini file is.

Calling phpinfo(1) from a script will give you the required information.

e.g.

  PHP Version => 4.3.0
  System => Linux pod 2.2.16 #1 Sat Sep 30 22:47:40 BST 2000 i686
  Build Date => May 28 2003 13:41:42
  Configure Command =>  './configure' 
  Server API => Command Line Interface
  Virtual Directory Support => disabled
  Configuration File (php.ini) Path => /usr/local/lib/php.ini
  PHP API => 20020918
  PHP Extension => 20020429
  Zend Extension => 20021010
  Debug Build => no
  Thread Safety => disabled

This shows that:

1) The system is Linux

2) PHP is PHP 4.3.0

3) PHP is not threaded (thread safety disabled)

4) The php.ini file is in /usr/local/lib 

Then...

* If using UNIX
  -------------

If your PHP is not threaded you need a Loader called:

  ioncube_loader_<os type>_<php flavour>.so

If your PHP is threaded you need a Loader called:

  ioncube_loader_<os type>_<php flavour>_ts.so

<os type> will be 'lin' for Intel Linux, 'fre' for FreeBSD, 'sun' for Sparc
Solaris, 'ope' for OpenBSD, 'dar' for OSX and 'net' for NetBSD.

<php flavour> will be 4.0, 4.1, 4.2 or 4.3 - i.e the first 2 digits of your 
PHP version.

Edit your php.ini file and for non-threaded PHP add:

  zend_extension = /<path>/ioncube_loader_<os type>_<php flavour>.so

and for threaded PHP add:

  zend_extension_ts = /<path>/ioncube_loader_<os type>_<php flavour>_ts.so

Replace <os type> and <php flavour> with whatever is right for your system,
and <path> with the path to where the Loader is installed, 
e.g. /usr/local/ioncube

If there are other zend_extension entries in the php.ini file place this new
entry before the existing entries.

Examples

For Linux running PHP 4.1.2 and Apache 1, you might add:

  zend_extension = /usr/local/ioncube/ioncube_loader_lin_4.1.so

For FreeBSD running threaded PHP 4.3.1 with Apache 2, you might add:

  zend_extension_ts = /usr/local/ioncube/ioncube_loader_fre_4.3_ts.so


* If using Windows
  ----------------

You need a Loader called

  ioncube_loader_win_<php flavour>.dll

<php flavour> will be 4.1, 4.2, 4.3 or 5.0 - i.e the first 2 digits of your
PHP version.

Edit your php.ini file and add:

  zend_extension_ts = <drive>:\<path>\ioncube_loader_win_<php flavour>.dll

where <drive> and <path> locate the Loader, and <php flavour> is whatever the
correct value is for your system. If there are other zend_extension entries 
in the php.ini file place this new entry before the existing entries.


e.g.

  zend_extension_ts = c:\WINNT\ioncube_loader_win_4.3.dll



Copyright (c) 2002-2005 ionCube Ltd.                  Last revised 21-Feb-2005
