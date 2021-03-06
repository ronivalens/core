<?php

function autoload ($class)
{
	$file = Instance::singleton ()->getCorePath () .'class'. DIRECTORY_SEPARATOR . $class . '.php';

	if (!file_exists ($file))
		return FALSE;

	require_once $file;
}

spl_autoload_register ('autoload');

function __ ()
{
	return Localization::singleton ()->translate (func_get_args ());
}

function month ($mes = NULL)
{
	if ($mes == NULL || $mes > 12 || $mes < 1)
		$mes = date ('m');

	$meses = array ( 1 => 'Janeiro',
					 2 => 'Fevereiro',
					 3 => 'Mar&ccedil;o',
					 4 => 'Abril',
					 5 => 'Maio',
					 6 => 'Junho',
					 7 => 'Julho',
					 8 => 'Agosto',
					 9 => 'Setembro',
					10 => 'Outubro',
					11 => 'Novembro',
					12 => 'Dezembro');

	return $meses [(int) $mes];
}

function fileName ($str)
{
	$str = substr ($str, 0, 255);

	$str = strtolower ($str);

	$trade = array ('á'=>'a','à'=>'a','ã'=>'a',
					'ä'=>'a','â'=>'a',
					'Á'=>'A','À'=>'A','Ã'=>'A',
					'Ä'=>'A','Â'=>'A',
					'é'=>'e','è'=>'e',
					'ë'=>'e','ê'=>'e',
					'É'=>'E','È'=>'E',
					'Ë'=>'E','Ê'=>'E',
					'í'=>'i','ì'=>'i',
					'ï'=>'i','î'=>'i',
					'Í'=>'I','Ì'=>'I',
					'Ï'=>'I','Î'=>'I',
					'ó'=>'o','ò'=>'o','õ'=>'o',
					'ö'=>'o','ô'=>'o',
					'Ó'=>'O','Ò'=>'O','Õ'=>'O',
					'Ö'=>'O','Ô'=>'O',
					'ú'=>'u','ù'=>'u',
					'ü'=>'u','û'=>'u',
					'Ú'=>'U','Ù'=>'U',
					'Ü'=>'U','Û'=>'U',
					'$'=>'_','@'=>'_','!'=>'_',
					'#'=>'_','%'=>'_','"'=>'',
					'^'=>'_','&'=>'_','*'=>'_',
					'('=>'_',')'=>'_',"'"=>'',
					'-'=>'_','+'=>'_','='=>'_',
					'\\'=>'_','|'=>'_',
					'`'=>'_','~'=>'_','/'=>'_',
					'\"'=>'_','\''=>'_',
					'<'=>'_','>'=>'_','?'=>'_',
					','=>'_','ç'=>'c','Ç'=>'C',' '=>'_');

	$str = strtr ($str, $trade);

	return $str;
}

function removeAccents ($str)
{
	$str = htmlentities (html_entity_decode ($str, ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8');

	$str = preg_replace ('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil);/', '$1', $str);

	return html_entity_decode ($str, ENT_NOQUOTES, 'UTF-8');
}

function resize ($file, $type, $width = 0, $height = 0, $force = FALSE, $bw = FALSE, $webp = FALSE, $jp2 = FALSE)
{
	if (!file_exists ($file) || !is_readable ($file) || !(int) filesize ($file))
		throw new Exception ('Trying to resize inexistent or unreadable image!');
	
	if (!(int) $width && !(int) $height && !(bool) $bw && !(bool) $webp && !(bool) $jp2)
	{
		header ('Content-Type: '. $type);
		
		echo file_get_contents ($file);

		exit ();
	}

	$vetor = getimagesize ($file);

	$atualWidth  = $vetor [0];
	$atualHeight = $vetor [1];

	if (!$width || !$height)
	{
		if (!$width && !$height)
		{
			$width = $atualWidth;
			$height = $atualHeight;
		}
		elseif ($width && !$height)
			$height = ($atualHeight * $width) / $atualWidth;
		else
			$width = ($atualWidth * $height) / $atualHeight;
	}

	if(!$force)
	{
		if ($atualWidth < $atualHeight && $width > $height)
		{
			$aux = $width;
			$width = $height;
			$height = $aux;
		}

		if ((int) $atualWidth < (int) $width)
		{
			$width = $atualWidth;

			$height = ($atualHeight * $width) / $atualWidth;
		}
	}

	$width = round ($width);
	$height = round ($height);

	$padded = array_pop (explode ('_', $file));

	$cache = Instance::singleton ()->getCachePath ();

	if (!file_exists ($cache .'file') && !@mkdir ($cache .'file', 0777))
		throw new Exception ('Unable create cache directory ['. $cache .'file] for cache WebP images!');

	if (!file_exists ($cache .'file'. DIRECTORY_SEPARATOR .'.htaccess') && !file_put_contents ($cache .'file'. DIRECTORY_SEPARATOR .'.htaccess', 'deny from all'))
		throw new Exception ('Impossible to enhance security for folder ['. $cache .'file].');
	
	if ($webp)
		$qualifier = 'webp';
	elseif ($jp2)
		$qualifier = 'jp2';
	else
		$qualifier = 'resized';

	$cached = $cache .'file'. DIRECTORY_SEPARATOR . $qualifier .'_'. $padded .'_'. $width .'x'. $height . ($bw ? '_bw' : '');
	
	if (file_exists ($cached) && is_readable ($cached) && (int) filesize ($cached))
	{
		header ('Content-Type: '. ($webp || $jp2 ? 'image/'. $qualifier : $type));
		
		echo file_get_contents ($cached);

		exit ();
	}
	
	$buffer = FALSE;

	switch ($type)
	{
		case 'image/jpeg':
		case 'image/pjpeg':
			$buffer = imagecreatefromjpeg ($file);
			break;

		case 'image/gif':
			$buffer = imagecreatefromgif ($file);
			break;

		case 'image/png':
			$buffer = imagecreatefrompng ($file);
			imagealphablending ($buffer, TRUE);
			imagesavealpha ($buffer, TRUE);
			break;
		
		case 'image/webp':
			$buffer = imagecreatefromwebp ($file);
			break;
	}

	if (!$buffer)
		throw new Exception ('Invalid image MIME type!');

	if ($bw)
		@imagefilter ($buffer, IMG_FILTER_GRAYSCALE);

	if ($type != 'image/gif')
	{
		$thumb = imagecreatetruecolor ($width, $height);
		$color = imagecolorallocatealpha ($thumb, 255, 255, 255, 0);
		imagefill ($thumb, 0, 0, $color);
	}
	else
		$thumb = imagecreate ($width, $height);

	$ok = imagecopyresized ($thumb, $buffer, 0, 0, 0, 0, $width, $height, $atualWidth, $atualHeight);

	imagedestroy ($buffer);

	if (!$ok)
		throw new Exception ('Impossible to resize image!');
	
	if ($webp)
	{
		/*
		 * Forcing to WebP means user needs optimize size of images. So, the quality used
		 * is not 100, but the default of PHP (infering that is the optimal). 
		 */

		$cached = $cache .'file'. DIRECTORY_SEPARATOR . 'webp_'. $padded .'_'. $width .'x'. $height . ($bw ? '_bw' : '');

		imagewebp ($thumb, $cached);

		header ('Content-Type: image/webp');

		imagewebp ($thumb);

		imagedestroy ($thumb);

		exit ();
	}
	
	if ($jp2)
	{
		/*
		 * Forcing to JPEG2000 means user needs optimize size of images. So, the quality used
		 * is not 100, but 70% (resulting in file size similar to WebP). 
		 */

		$cached = $cache .'file'. DIRECTORY_SEPARATOR . 'jp2_'. $padded .'_'. $width .'x'. $height . ($bw ? '_bw' : '');

		$jp2conversion = $cached .'-toJPEG2000';

		imagejpeg ($thumb, $jp2conversion, 100);

		// Try use ImageMagick in command line first...
		
		if (function_exists ('exec') && (`which convert`))
			@exec ('convert '. $jp2conversion .' -quality 70 '. $cached);
		
		if (file_exists ($cached) && is_readable ($cached) && (int) filesize ($cached))
		{
			header ('Content-Type: image/jp2');
			
			echo file_get_contents ($cached);

			imagedestroy ($thumb);

			@unlink ($jp2conversion);
	
			exit ();
		}

		// If not success, try use imagick module from PHP...

		if (extension_loaded ('imagick'))
		{
			$image = new Imagick ($jp2conversion);

			$image->setImageFormat ('jp2');

			$image->setImageCompression (Imagick::COMPRESSION_JPEG2000);

			$image->setImageCompressionQuality (70);

			$image->stripImage ();

			$image->writeImage ($cached);

			header ('Content-Type: image/jp2');

			echo $image->getImageBlob();

			$image->clear ();

			imagedestroy ($thumb);

			@unlink ($jp2conversion);

			exit ();
		}

		@unlink ($jp2conversion);
	}

	$cached = $cache .'file'. DIRECTORY_SEPARATOR . 'resized_'. $padded .'_'. $width .'x'. $height . ($bw ? '_bw' : '');

	header ('Content-Type: '. $type);

	switch ($type)
	{
		case 'image/jpeg':
		case 'image/pjpeg':
			imagejpeg ($thumb, $cached);

			imagejpeg ($thumb);
			break;

		case 'image/gif':
			imagegif ($thumb, $cached);

			imagegif ($thumb);
			break;

		case 'image/png':
			imagepng ($thumb, $cached);

			imagepng ($thumb);
			break;
		
		case 'image/webp':
			imagewebp ($thumb, $cached);

			imagewebp ($thumb);
			break;
	}

	imagedestroy ($thumb);

	exit ();
}

function getBreadPath ($section, $withLink = TRUE, $setMenu = TRUE)
{
	if (!$section)
		return '';

	$business = Business::singleton ();

	$father = $business->getSection ($section->getFather ());

	if ($setMenu)
	{
		global $menuPosition;

		$menuPosition [] = $section->getName ();
	}

	return getBreadPath ($father, $withLink) . ($withLink && !$section->isFake () ? '<a href="titan.php?target=body&amp;toSection='. $section->getName () .'">'. $section->getLabel () .'</a>' : $section->getLabel ()) .' &raquo; ';
}

function keyboard ($link = NULL, $color = '999999')
{
	static $counter = 0;

    if (!$link || empty ($link) || trim ($link) == '')
		$link = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
    ?>
    <table align="center" width="1px" border="0" cellpadding="0" cellspacing="3">
		<form name="keyboard<?= $counter ?>" action="<?= $link ?>" method="POST">
		<input type="hidden" id="keyboard_id_<?= $counter ?>" name="letter" value="">
		<tr>
			<?php
			for ($i = 0 ; $i < 13 ; $i++)
			{
				?>
				<td width="1px">
					<input type="submit" class="button" style="width: 20px; color: #<?= $color ?>; border-color: #<?= $color ?>;" value="<?= chr($i + 65) ?>" onclick="JavaScript: document.getElementById ('keyboard_id_<?= $counter ?>').value = '<?= chr ($i + 65) ?>'; return true;">
				</td>
				<?php
			}
			?>
			<td width="1px" rowspan=2>
				<input type="submit" class="button" style="width: 50px; height: 43px; color: #<?= $color ?>; border-color: #<?= $color ?>;" value="Todas">
			</td>
		</tr>
		<tr>
			<?php
			for ($i = 13 ; $i < 26 ; $i++)
			{
				?>
				<td width="1px">
					<input type="submit" class="button" style="width: 20px; color: #<?= $color ?>; border-color: #<?= $color ?>;" value="<?= chr ($i + 65) ?>" onclick="JavaScript: document.getElementById ('keyboard_id_<?= $counter ?>').value = '<?= chr ($i + 65) ?>'; return true;">
				</td>
				<?php
			}
			?>
		</tr>
		</form>
	</table>
    <?php
	$counter++;
}

function makeMenu ($menuHeight, $previous = FALSE, $father = '', $sectionName = '')
{
	$business = Business::singleton ();

	$indentation = '<div class="indentation"></div>';

	$children = $business->getChildren ($father);

	if (!sizeof ($children))
		return FALSE;

	$user = User::singleton ();

	$array = array ();

	$output = array ();

	ob_start ();

	if ($previous === FALSE)
		echo '<li style="background-color: #333; background-image: none;"></li>';
	else
	{
		?>
		<li style="background: #333 url(titan.php?target=loadFile&amp;file=interface/image/arrow.left.gif) left no-repeat; font-weight: bold;" onclick="JavaScript: backMenu ('<?= $father ?>', '<?= $previous ?>');">
			<div class="indentation"></div>
			<label><?= $business->getSection ($father)->getLabel () ?></label>
		</li>
		<?php
	}

	$header = ob_get_clean ();

	if ($father != '' && !$business->getSection ($father)->isFake () && !$business->getSection ($father)->isHidden ())
	{
		$icon = $indentation;

		if ($business->getSection ($father)->getIcon () != '')
			$icon = '<div class="indentation"><i class="fa fa-'. $business->getSection ($father)->getIcon () .' fa-2x"></i></div>';

		if ($user->accessSection ($business->getSection ($father)->getName ()))
			$output [] = '<li style="background-image: none;" onclick="JavaScript: showWait (); document.location = \'titan.php?target=body&amp;toSection='. $father .'\';" title="'. $business->getSection ($father)->getDescription () .'">'. $icon .'<label>'. $business->getSection ($father)->getLabel () .'</label></li>';
		elseif (Instance::singleton ()->showAllSections ())
			$output [] = '<li style="background-image: none; color: #AAA;">'. $icon .'<label style="color: #AAA;" title="'. $business->getSection ($father)->getDescription () .'">'. $business->getSection ($father)->getLabel () .'</label></li>';
	}

	foreach ($children as $trash => $section)
	{
		if (array_key_exists ($father, $menuHeight))
			$menuHeight [$father]++;
		else
			$menuHeight [$father] = 1;

		if ($section->isHidden ())
			continue;

		$icon = $indentation;

		if ($section->getIcon () != '')
			$icon = '<div class="indentation"><i class="fa fa-'. $section->getIcon () .' fa-2x"></i></div>';

		$next = makeMenu ($menuHeight, $father, $section->getName (), $section->getLabel ());

		if (is_array ($next))
		{
			$array = array_merge ($array, $next);
			$output [] = '<li onclick="JavaScript: slideMenu (\''. $father .'\', \''. $section->getName () .'\');">'. $icon .'<label>'. $section->getLabel () .'</label></li>';
		}
		elseif ($user->accessSection ($section->getName ()))
			$output [] = '<li style="background-image: none;" onclick="JavaScript: showWait (); document.location = \'titan.php?target=body&amp;toSection='. $section->getName () .'\';" title="'. $section->getDescription () .'">'. $icon .'<label>'. $section->getLabel () .'</label></li>';
		elseif (Instance::singleton ()->showAllSections ())
			$output [] = '<li style="background-image: none; color: #AAA;">'. $icon .'<label style="color: #AAA;" title="'. $section->getDescription () .'">'. $section->getLabel () .'</label></li>';
	}

	if (!sizeof ($output))
		return FALSE;

	ob_start ();
	?>
	<div class="menuMain" id="menuMain_<?= $father ?>" style="<?= $father == '' ? 'left: 0px;' : 'left: 260px;' ?>">
		<ul>
			<?php
			echo $header;

			foreach ($output as $trash => $item)
				echo $item;
			?>
		</ul>
	</div>
	<?php
	$array [] = ob_get_clean ();

	return $array;
}

function copyDir ($srcdir, $dstdir, $verbose = FALSE)
{
	$num = 0;

	$noCopy = array ('.', '..', 'Thumbs.db', '.svn');

	if(!is_dir($dstdir)) mkdir($dstdir);

	if($curdir = opendir($srcdir))
	{
		while($file = readdir($curdir))
		{
			if(!in_array ($file, $noCopy))
			{
				$srcfile = $srcdir . DIRECTORY_SEPARATOR . $file;
				$dstfile = $dstdir . DIRECTORY_SEPARATOR . $file;

				if(is_file ($srcfile))
				{
					if(is_file ($dstfile)) $ow = filemtime ($srcfile) - filemtime ($dstfile); else $ow = 1;

					if($ow > 0)
					{
						if ($verbose) echo "Copiando...<br />'$srcfile' &raquo; '$dstfile' ";

						if (copy($srcfile, $dstfile))
						{
							touch($dstfile, filemtime($srcfile)); $num++;
							if ($verbose) echo '<br /><label style="color: #009900;">OK</label><br />';
						}
						else
						{
							if ($verbose) echo '<br /><label style="color: #009900;">Error: File \'$srcfile\' could not be copied!</label><br />';
							exit ();
						}
					}
				}
				elseif(is_dir($srcfile))
					$num += copyDir ($srcfile, $dstfile, $verbose);
			}
		}

		closedir ($curdir);
	}

	return $num;
}

function removeDir ($path)
{
	if (is_dir ($path))
	{
		foreach (glob ($path .'/*') as $file)
			if (is_file ($file))
				unlink ($file);
			elseif (is_dir ($file))
				removeDir ($file);

		rmdir ($path);
	}
}

function dirSize ($dir)
{
	if (!is_dir($dir))
		return FALSE;

	$dh = opendir($dir);
	$size = 0;

	while (($file = readdir($dh)) !== false)
		if ($file != '.' && $file != '..')
		{
			$path = $dir .'/'. $file;

			if (is_dir ($path))
				$size += dirSize ($path);
			elseif (is_file($path))
				$size += filesize ($path);
		}

	closedir($dh);

	return $size;
}

function randomHash ($size = 32)
{
	$hash = '';

	while (strlen ($hash) < $size)
		$hash .= substr ('0123456789abcdef', mt_rand (0,15), 1);

	return $hash;
}

function logPhpError ($errno, $errstr, $errfile, $errline)
{
	$errorType = array (E_ERROR				=> 'ERROR',
						E_WARNING			=> 'WARNING',
						E_PARSE				=> 'PARSING ERROR',
						E_NOTICE			=> 'NOTICE',
						E_CORE_ERROR		=> 'CORE ERROR',
						E_CORE_WARNING		=> 'CORE WARNING',
						E_COMPILE_ERROR		=> 'COMPILE ERROR',
						E_COMPILE_WARNING	=> 'COMPILE WARNING',
						E_USER_ERROR		=> 'USER ERROR',
						E_USER_WARNING		=> 'USER WARNING',
						E_USER_NOTICE		=> 'USER NOTICE',
						E_STRICT			=> 'STRICT NOTICE',
						E_RECOVERABLE_ERROR	=> 'RECOVERABLE ERROR');

	$err = array_key_exists ($errno, $errorType) ? $errorType [$errno] : 'CAUGHT EXCEPTION';

	toLog ('['. $err .'] '. $errstr .' [File: '. $errfile .'] [Line: '. $errline .']');

	return TRUE;
}

function toLog ($message)
{
	if (file_exists ('FirePHPCore/FirePHP.class.php'))
		@include_once ('FirePHPCore/FirePHP.class.php');

	if (class_exists ('FirePHP', FALSE) && !headers_sent ())
	{
		$firePhp = FirePHP::getInstance (TRUE);

		$firePhp->log ($message);
	}

	$path = Instance::singleton ()->getCachePath () .'log/';

	if (!file_exists ($path) && !@mkdir ($path, 0777))
		throw new Exception ('Impossible to create folder ['. $path .'].');

	if (!file_exists ($path .'.htaccess') && !file_put_contents ($path .'.htaccess', 'deny from all'))
		throw new Exception ('Impossible to enhance security for folder ['. $path .'].');

	$fd = fopen ($path .'log.'. date ('Ym'), 'a');

	if (!$fd)
		throw new Exception ('Impossible to open/create LOG file. Verify permissions on folder ['. $path .']!');

	if (!fwrite ($fd, date ('d-m-Y H:i:s') ." [". $_SERVER['REMOTE_ADDR'] ."]\n". $message ."\n\n"))
		throw new Exception ('Impossible to write in LOG file. Verify permissions on folder and file ['. $path .']!');

	fclose ($fd);
}

function apiPhpError ($errno, $errstr, $errfile, $errline)
{
	$errorType = array (E_ERROR				=> 'ERROR',
						E_WARNING			=> 'WARNING',
						E_PARSE				=> 'PARSING ERROR',
						E_NOTICE			=> 'NOTICE',
						E_CORE_ERROR		=> 'CORE ERROR',
						E_CORE_WARNING		=> 'CORE WARNING',
						E_COMPILE_ERROR		=> 'COMPILE ERROR',
						E_COMPILE_WARNING	=> 'COMPILE WARNING',
						E_USER_ERROR		=> 'USER ERROR',
						E_USER_WARNING		=> 'USER WARNING',
						E_USER_NOTICE		=> 'USER NOTICE',
						E_STRICT			=> 'STRICT NOTICE',
						E_RECOVERABLE_ERROR	=> 'RECOVERABLE ERROR');

	$err = array_key_exists ($errno, $errorType) ? $errorType [$errno] : 'CAUGHT EXCEPTION';

	toLog ('['. $err .'] '. $errstr .' [File: '. $errfile .'] [Line: '. $errline .']');

	header ('HTTP/1.1 500 Internal Server Error');
	header ('Content-Type: application/json');

	$array = array ('ERROR' => 'SYSTEM_ERROR',
					'MESSAGE' => 'System error! Please, contact administrator.',
					'TECHNICAL' => 'PHP Error: '. $err);

	echo json_encode ($array);

	exit ();
}

function xmlCache ($file, $array, $path = FALSE)
{
	if ($path === FALSE)
		$path = Instance::singleton ()->getCachePath () .'parsed/';

	if (!file_exists ($path) && !@mkdir ($path, 0777))
		throw new Exception ('Impossible to create folder ['. $path .'].');

	$content  = "<?php \n";
	$content .= "/* ". date ('d-m-Y H:i:s') ." */ \n\n";
	$content .= "return ". var_export ($array, TRUE) ."; \n";

	@file_put_contents ($file, $content);
}

function isFirefox ($force = FALSE)
{
	return TRUE;
}

function getBrowser ()
{
	return '';
}

function swf ($path, $width, $height)
{
	$name = randomHash ();
	?>
	<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="<?= $width ?>" height="<?= $height ?>" id="<?= $name ?>" align="middle">
	<param name="movie" value="<?= $path ?>" />
	<param name="wmode" value="transparent" />
	<param name="quality" value="best" />
	<param name="allowScriptAccess" value="sameDomain" />
	<embed wmode="transparent" src="<?= $path ?>" quality="best" width="<?= $width ?>" height="<?= $height ?>" align="middle" allowscriptaccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
	</object>
	<?php
}

function encrypt ($input)
{
	return Blowfish::encrypt (
		$input,
		'84826372',
		Blowfish::BLOWFISH_MODE_EBC,
		Blowfish::BLOWFISH_PADDING_NONE,
		NULL
	);
}

function decrypt ($encrypted)
{
	return Blowfish::decrypt (
		base64_decode (trim ($input)),
		'84826372',
		Blowfish::BLOWFISH_MODE_EBC,
		Blowfish::BLOWFISH_PADDING_NONE,
		NULL
	);
}

function tableExists ($name)
{
	return Database::tableExists ($name);
}

function cleanArray (&$item, $key)
{
	$item = trim ($item);
}

function toUtf8 (&$item, $key)
{
	$item = utf8_encode ($item);
}

function relevant ($str, $terms)
{
	$str = strtolower ($str);

	$positions = array ();
	foreach ($terms as $trash => $term)
	{
		$pos = strpos ($str, $term);

		if ($pos === FALSE)
			continue;

		$positions [$term] = $pos;
	}

	asort ($positions);

	$pieces = array ();
	foreach ($positions as $trash => $start)
	{
		$next = next ($positions);

		$end = $start + 200;

		while ($next !== FALSE && $next < $end)
		{
			$end = $next + 200;

			$next = next ($positions);
		}

		prev ($positions);

		$start = $start - 50 < 0 ? 0 : $start - 50;

		$end = $end - 50;

		$start = !$start ? 0 : strrpos (substr ($str, 0, $start), ' ');

		if (!is_integer ($start))
			$start = 0;

		$sub = substr ($str, $start, $end - $start);

		$length = strrpos ($sub, ' ');

		$length = !$length ? strlen ($sub) - 1 : $length;

		$pieces [] = trim (substr ($str, $start, $length));
	}

	$output = '...'. implode ('...', $pieces) .'...';

	foreach ($positions as $term => $trash)
		$output = preg_replace ("|($term)|Ui", "<span style=\"background: #009; color: #FFF;\"><b>$1</b></span>", $output);

	return $output;
}

function translate ($str)
{
	$array = explode ('|', $str);

	if (sizeof ($array) < 2)
		return $str;

	$language = Localization::singleton ()->getLanguage ();

	foreach ($array as $key => $value)
	{
		$aux = explode (':', $value);

		if (!$key)
			$str = sizeof ($aux) > 1 ? $aux [1] : $aux [0];

		if ($language != trim ($aux [0]))
			continue;

		return trim ($aux [1]);
	}

	return $str;
}

function shortlyHash ($hash)
{
	$valid = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxwz_-';

	$i = 0;

	$short = '';
	while ($i < strlen ($hash))
		$short .= $valid [hexdec ($hash [$i++] . $hash [$i++] . $hash [$i++] . $hash [$i++]) % 64];

	return $short;
}

function retrievePut ()
{
	$_POST = array ();
	$_FILES = array ();

	$raw = file_get_contents ('php://input');

	$boundary = substr ($raw, 0, strpos($raw, "\r\n"));

	if (empty ($boundary))
	{
		parse_str ($raw, $_POST);

		return;
	}

	$parts = array_slice (explode ($boundary, $raw), 1);

	$data = array ();

	foreach ($parts as $part)
	{
		// If this is the last part, break
		if ($part == "--\r\n")
			break;

		// Separate content from headers
		$part = ltrim ($part, "\r\n");

		list ($rawHeaders, $body) = explode ("\r\n\r\n", $part, 2);

		// Parse the headers list
		$rawHeaders = explode ("\r\n", $rawHeaders);

		$headers = array ();

		foreach ($rawHeaders as $header)
		{
			list ($name, $value) = explode (':', $header);

			$headers [strtolower ($name)] = ltrim ($value, ' ');
		}

		// Parse the Content-Disposition to get the field name, etc.
		if (isset ($headers ['content-disposition']))
		{
			$filename = NULL;

			$tmp_name = NULL;

			preg_match ('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $headers ['content-disposition'], $matches);

			list(, $type, $name) = $matches;

			//Parse File
			if (isset ($matches[4]))
			{
				//if labeled the same as previous, skip
				if (isset ($_FILES [$matches [2]]))
					continue;

				//get filename
				$filename = $matches [4];

				//get tmp name
				$filename_parts = pathinfo ($filename);

				$tmp_name = tempnam (ini_get ('upload_tmp_dir'), $filename_parts ['filename']);

				//populate $_FILES with information, size may be off in multibyte situation
				$_FILES [$matches [2]] = array (
					'error' => 0,
					'name' => trim ($filename),
					'tmp_name' => $tmp_name,
					'size' => strlen ($body),
					'type' => trim ($value)
				);

				//place in temporary directory
				file_put_contents ($tmp_name, $body);
			}
			else
				$data [$name] = substr ($body, 0, strlen ($body) - 2);
		}
	}

	$_POST = $data;
}

function convertApiParametersToUtf8 ()
{
	require Instance::singleton ()->getCorePath () .'extra/Encoding.php';

	array_walk_recursive($_POST, function (&$item, $key)
	{
		$item = Encoding::toUTF8 ($item);
	});
}

if (!function_exists ('apache_request_headers'))
{
	function apache_request_headers()
	{
		$arh = array ();

		$rx_http = '/\AHTTP_/';

		foreach ($_SERVER as $key => $val)
			if (preg_match ($rx_http, $key))
			{
				$arh_key = preg_replace ($rx_http, '', $key);

				$rx_matches = array ();

				$rx_matches = explode ('_', $arh_key);

				if (count ($rx_matches) > 0 && strlen ($arh_key) > 2)
				{
					foreach ($rx_matches as $ak_key => $ak_val)
						$rx_matches[$ak_key] = ucfirst($ak_val);

					$arh_key = implode ('-', $rx_matches);
				}

				$arh [strtolower ($arh_key)] = $val;
	    	}

	  	return ($arh);
	}
}
