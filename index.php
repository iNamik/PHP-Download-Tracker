<?php
	#
	# iNamik PHP Download Tracker
	# Copyright (C) 2008-2011  David Farrell (SpamFreeDave-GitHub@yahoo.com)
	#
	# This program is free software; you can redistribute it and/or modify
	# it under the terms of the GNU General Public License as published by
	# the Free Software Foundation; either version 2 of the License, or
	# (at your option) any later version.
	#
	# This program is distributed in the hope that it will be useful,
	# but WITHOUT ANY WARRANTY; without even the implied warranty of
	# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	# GNU General Public License for more details.
	#
	# You should have received a copy of the GNU General Public License
	# along with this program; if not, write to the Free Software
	# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	#

	#
	# This project is a heavy modification (i.e. near-complete rewrite) of
	# DocTrax's Download Manager (http://freshmeat.net/projects/dlmanager/) - Link good as of 2008/03/02
	#

	#
	# Intalling
	#
	# - Copy this PHP script into a web-accessible folder
	# - Check the "Config Section" for customizable options
	# - If you want logging enabled, make sure that the configured 'logDir' is
	#   writable by the webserver.
	# - You should probably ensure that the configured directories are either
	#   Not web-accessible or protected against browser access.  To protect the
	#   directory using apache, create a file inside the directory called ".htaccess"
	#   and give it the following contents:
	#
	#      order deny, allow
	#      deny from all
	#
	# - Edit the HTML at the bottom of the file.  There is a bit of embedded PHP within
	#   the HTML, but you should be able to work around it quite easily.
	#

	#
	# Feedback
	#
	# If you use this project, please feel free to drop me an email letting me know what you
	# think about it.  Any feedback would be appreciated.
	#


	error_reporting(0);
# Enable For debugging
#error_reporting(E_ALL | E_STRICT);
#ini_set('display_startup_errors', 1);
#ini_set('display_errors', 1);
## Helpful debug print statement
##print __FILE__.':'.__LINE__.':'."<br/>\n";

#######################################################################
# Config Section
#######################################################################

	$downloadDir = './dl';
	$logDir      = './log';
	$logRequest  = true;
	$indentList  = false;
	$useForm     = true;
	$useCaptcha  = true; // Only usefull when $userForm = true

#######################################################################
# Code Section - Do Not Edit
#######################################################################

	if (!$useForm)
	{
		$useCaptcha = false;
	}

	if ($useCaptcha)
	{
		session_start();
	}

	$pathMap = readDownloadDir();
#print("<pre>");print_r($pathMap);print("</pre><br/>\n");
	if (!isset($pathMap) || !is_array($pathMap) || empty($pathMap))
	{
		unset($pathMap);
		$errorMessage = "No files found!";
	}
	else
	{
		if (isset($_GET['file']))
		{
			$file = $_GET['file'];

			if	(
					(
						!(preg_match('/^\./i', $file)) # Should not start with '.'
					&&	!(preg_match('/^\//i', $file)) # Should not start with '/'
					)
				&&	!(preg_match('/^#/i', basename($file))) # Should not start with '#' ?
				&&	 (file_exists($downloadDir . '/' . $file))
				)
			{
				if (!$useCaptcha || verifyCaptcha())
				{
					logRequest($file);
					header('Content-Description: File Transfer');
					header('Content-Type: application/forced-download');
					header('Content-Length: ' . filesize($downloadDir . '/' . $file));
					header('Content-Disposition: attachment; filename=' . basename($downloadDir . '/' . $file));
					readfileChunked($downloadDir . '/' . $file);
					exit;
				}
				else
				{
					$errorMessage = "Please enter the access code.";
				}
			}
			else
			{
				$errorMessage = "The specified file does not exist!";
			}
		}
		else
		{
			unset($errorMessage);
		}
	}
	$captcha       = $useCaptcha ? getCaptcha(3)              : '';
	$captchaString = $useCaptcha ? getCaptchaString($captcha) : '';


	##
	# getCaptcha
	#
	function getCaptcha($length)
	{
		$captcha = '';
		if (!is_null($length) && is_int($length) && $length > 0)
		{
			for ($i = 0; $i < $length; ++$i)
			{
				$int = rand(0, 9);
				$captcha .= "{$int}";
			}
			$_SESSION['captcha'] = $captcha;
		}
		return $captcha;
	}

	##
	# getCaptchaString
	#
	function getCaptchaString($captcha)
	{
		$captchaString = '';

		if (!is_null($captcha) && is_string($captcha) && strlen($captcha) > 0 && ctype_digit($captcha))
		{
			$numbersAsWords = array
			(
				'0' => 'Zero',
				'1' => 'One',
				'2' => 'Two',
				'3' => 'Three',
				'4' => 'Four',
				'5' => 'Five',
				'6' => 'Six',
				'7' => 'Seven',
				'8' => 'Eight',
				'9' => 'Nine'
			);

			for ($i = 0; $i < strlen($captcha); ++$i)
			{
				$char = $captcha[$i];
				if (array_key_exists($char, $numbersAsWords))
				{
					$word = $numbersAsWords[$char];
					if (!is_null($word) && is_string($word) && strlen($word) > 0)
					{
						if (strlen($captchaString) > 0)
						{
							$captchaString .= '&nbsp;';
						}
						$captchaString .= $word;
					}
				}
			}
		}
		return $captchaString;
	}

	##
	# verifyCaptcha
	#
	function verifyCaptcha()
	{
		$result = false;

		if (isset($_GET['code']))
		{
			$code = $_GET['code'];
			if (isset($_SESSION['captcha']))
			{
				$captcha = $_SESSION['captcha'];
				$result = !is_null($code) && !empty($code) && !is_null($captcha) && !empty($captcha) && $code == $captcha;
			}
		}
		return $result;
	}

	##
	# readfileChunked
	#
	function readfileChunked($filename, $retbytes=true)
	{
		$chunksize = 1*(1024*1024); // how many bytes per chunk

		$buffer = '';

		$cnt = 0;

		$handle = fopen($filename, 'rb');

		if ($handle === false)
		{
			return false;
		}

		while (!feof($handle))
		{
			$buffer = fread($handle, $chunksize);

			echo $buffer;

			ob_flush();

			flush();

			if ($retbytes)
			{
				$cnt += strlen($buffer);
			}
		}

		$status = fclose($handle);

		if ($retbytes && $status)
		{
			return $cnt; // return num. bytes delivered like readfile() does.
		}

		return $status;
	}

	##
	# logRequest
	#
	function logRequest($file)
	{
		global $logDir;
		global $logRequest;

		if ( $logRequest == true && is_dir($logDir) )
		{
			$today = date("Y-m-d");
			$time  = date("Y-m-d H:i:s");

			$logName = str_replace('/', '-', $file);

			$logFilename = $logDir . '/' . $logName . "-" . $today . '.log';

			if (!file_exists($logFilename))
			{
				$localFile = fopen($logFilename, "w") ;
				fclose($localFile) ;
				chmod($logFilename, 0744) ;
			}

			if ( (file_exists($logFilename)) )
			{
				$localFile = fopen($logFilename, "a") ;
				fwrite($localFile, $time . ' "' . $file . '" ' . $_SERVER['REMOTE_ADDR'] . ' "' . $_SERVER['HTTP_USER_AGENT'] . '" "' . $_SERVER['HTTP_REFERER'] . "\"\n" ) ;
				fclose($localFile) ;
			}
		}
	}

	##
	# readDownloadDir
	#
	function readDownloadDir($subDir='.', $count=0)
	{
		global $downloadDir;

		$pathMap = array();

		if($count >= 5)
		{
			return $pathMap;
		}

		$dir = $downloadDir . '/' . $subDir;

		if (!is_dir($dir))
		{
			return $pathMap;
		}

		$dirhandle = opendir($dir);

		$subDirList = array();
		$fileList   = array();

		while ($f = readdir($dirhandle))
		{
			if	(
					!(preg_match('/^\./', $f))
				&&  !(preg_match('/^#/',$f))
				)
			{
				if( (is_dir($dir . '/' . $f)) )
				{
					array_push($subDirList, $f);
				}
				elseif( file_exists($dir . '/' . $f) )
				{
					array_push($fileList, $f);
				}
			}
		}

		closedir($dirhandle);

		natcasesort($fileList);

		foreach ($fileList as $f)
		{
			$pathMap[$f] = $f;
		}

		natcasesort($subDirList);

		foreach($subDirList as $f)
		{
			$tmp = readDownloadDir($subDir . '/' . $f, $count++);

			if(is_array($tmp))
			{
				$pathMap[$f] = $tmp;
			}
		}
		return $pathMap;
	}

	##
	# printPath_indent
	#
	function printPath_indent($key, $value, $path='')
	{
		global $useForm;
		if (is_array($value))
		{
			echo "<li>${key}&nbsp;/</li>";
			echo "<ul>";
			$tmpPath = ( $path == '' ? '' : ($path . '/') ) . $key;
			foreach ($value as $tmpKey => $tmpValue)
			{
				printPath_indent($tmpKey, $tmpValue, $tmpPath);
			}
			echo "</ul>";
		}
		else
		if ($useForm)
		{
			echo '<li><input type="radio" name="file" value="' . ( $path == '' ? '' : ($path . '/') ) . $value . '">' . $value . '</li>';
		}
		else
		{
			echo '<li><a href="' . basename($_SERVER['SCRIPT_NAME']) . '?file=' . ( $path == '' ? '' : ($path . '/') ) . $value . '">' . $value . '</a></li>';
		}
	}

	##
	# printPath_flat
	#
	function printPath_flat($key, $value, $path='')
	{
		global $useForm;
		if (is_array($value))
		{
			$stringList = array();
			$arrayList  = array();
			foreach ($value as $tmpKey => $tmpValue)
			{
				if (is_array($tmpValue))
				{
					$arrayList[$tmpKey] = $tmpValue;
				}
				else
				{
					$stringList[$tmpKey] = $tmpValue;
				}
			}
			$tmpPath = ( $path == '' ? '' : ($path . '/') ) . $key;
			if (!empty($stringList))
			{
				$printPath = str_replace('/', '&nbsp;/&nbsp;', $tmpPath);
				echo "<li>${printPath}&nbsp;/</li>";
				echo "<ul>";
				foreach ($stringList as $tmpKey => $tmpValue)
				{
					printPath_flat($tmpKey, $tmpValue, $tmpPath);
				}
				echo "</ul>";
			}
			foreach ($arrayList as $tmpKey => $tmpValue)
			{
				printPath_flat($tmpKey, $tmpValue, $tmpPath);
			}
		}
		else
		if ($useForm)
		{
			echo '<li><input type="radio" name="file" value="' . ($path == '' ? '' : ($path . '/') ) . $value . '">' . $value . '</li>';
		}
		else
		{
			echo '<li><a href="' . basename($_SERVER['SCRIPT_NAME']) . '?file=' . ($path == '' ? '' : ($path . '/') ) . $value . '">' . $value . '</a></li>';
		}
	}
#######################################################################
# HTML Section
#######################################################################?>
<html>
	<head><title>Download Page</title></head>
	<body>
		<?php if (isset($errorMessage)) { ?>
			<p>
				<font color="red"><?php echo $errorMessage ?></font>
			</p>
		<?php } ?>
		<?php if (isset($pathMap)) { ?>
			<h1>Files Available For Download:</h1>
			<?php if ($useForm) { ?>
				<form action="<?php echo basename($_SERVER['SCRIPT_NAME'])?>" method="get">
			<?php } ?>
			<ul>
				<?php if ($indentList) { printPath_indent(null, $pathMap); } else { printPath_flat(null, $pathMap); } ?>
			</ul>
			<?php if ($useForm) { ?>
				<?php if ($useCaptcha) { ?>
					<br/>
					Access Code:&nbsp;<b><?php echo $captchaString ?></b>
					&nbsp;<input type="text" name="code" size="4" maxlength="3"/>
					<br/>
				<?php } ?>
				<br/>
				<input type="submit" name="submit" value="Download File" />
				</form>
			<?php } ?>
		<?php } ?>
	</body>
</html>