<?php
/**
 * Convert video files to play embbeded in browsers.
 *
 * @author Camilo Carromeu <camilo@carromeu.com>
 * @category class
 * @package core
 * @subpackage util
 * @copyright 2005-2017 Titan Framework
 * @license http://www.titanframework.com/license/ BSD License (3 Clause)
 * @see Archive
 */
class EncodeMedia
{
	private static $convertible = array (
		'audio/3gpp' => 'ogg',
		'audio/3gpp2' => 'ogg',
		'video/quicktime' => 'webm',
		'video/x-ms-wmv' => 'webm',
		'audio/x-ms-wma' => 'ogg'
	);

	public static function getEncodableTypes ()
	{
		return self::$convertible;
	}

	public static function getHtml5PlayableFile ($source, $mimetype, $playable)
	{
		if (!file_exists ($source) || !is_readable ($source) || !(int) filesize ($source))
			throw new Exception ('This file is not available!');

		$supportedHtml5Types = array ('video/mp4', 'video/webm', 'video/ogg', 'audio/mpeg', 'audio/ogg', 'audio/wav');

		if (in_array ($mimetype, $supportedHtml5Types))
			return $source;

		switch ($mimetype)
		{
			case 'audio/3gpp':
			case 'audio/3gpp2':

				$encoded = $playable .'.ogg';

				if (file_exists ($encoded) && is_readable ($encoded) && filesize ($encoded))
					return $encoded;

				if (!function_exists ('system'))
					throw new Exception ('Is needle enable OS call functions (verify if PHP is not in safe mode)!');

				$control = $playable .'.encoding';

				if (!file_put_contents ($control, strftime ('%c'), LOCK_EX))
					throw new Exception ('Impossible to create control file!');

				$log = $playable .'.3gp-ogg.log';

				// MP3 Stereo Best Quality: avconv -y -i file/cloud_0000016 -acodec libmp3lame -ab 192k -ac 2 -ar 44100 cache/ckeditor-file/encoded_0000016.mp3
				// MP3 Mono Poor Quality: avconv -y -i file/cloud_0000016 -acodec libmp3lame -ab 64k -ac 1 -ar 22050 cache/ckeditor-file/encoded_0000016.mp3
				// OGG: avconv -y -i "file/cloud_0000016" -acodec libvorbis -ac 2 "cache/ckeditor-file/encoded_0000016.ogg"

				system ('avconv -y -i "'. $source .'" -acodec libvorbis -ac 2 "'. $encoded .'" 2> "'. $log .'"', $return);

				unlink ($control);

				if ($return)
				{
					@unlink ($encoded);

					throw new Exception ('Has a problem with audio conversion! Verify if [avconv] exists in system and supports OGG codec (libvorbis). Read more in LOG file ['. $log .'].');
				}

				return $encoded;

			case 'video/quicktime':

				$encoded = $playable .'.webm';

				if (file_exists ($encoded) && is_readable ($encoded) && filesize ($encoded))
					return $encoded;

				if (!function_exists ('system'))
					throw new Exception ('Is needle enable OS call functions (verify if PHP is not in safe mode)!');

				$control = $playable .'.encoding';

				if (!file_put_contents ($control, strftime ('%c'), LOCK_EX))
					throw new Exception ('Impossible to create control file!');

				$log = $playable .'.mov-webm.log';

				system ('avconv -y -i "'. $source .'" "'. $encoded .'" 2> "'. $log .'"', $return);

				unlink ($control);

				if ($return)
				{
					@unlink ($encoded);

					throw new Exception ('Has a problem with video conversion! Verify if [avconv] exists in system and supports MP4 codec. Read more in LOG file ['. $log .'].');
				}

				return $encoded;

			case 'video/x-ms-wmv':

				$encoded = $playable .'.webm';

				if (file_exists ($encoded) && is_readable ($encoded) && filesize ($encoded))
					return $encoded;

				if (!function_exists ('system'))
					throw new Exception ('Is needle enable OS call functions (verify if PHP is not in safe mode)!');

				$control = $playable .'.encoding';

				if (!file_put_contents ($control, strftime ('%c'), LOCK_EX))
					throw new Exception ('Impossible to create control file!');

				$log = $playable .'.wmv-webm.log';

				system ('avconv -y -i "'. $source .'" "'. $encoded .'" 2> "'. $log .'"', $return);

				unlink ($control);

				if ($return)
				{
					@unlink ($encoded);

					throw new Exception ('Has a problem with video conversion! Verify if [avconv] exists in system and supports WebM codec. Read more in LOG file ['. $log .'].');
				}

				return $encoded;

			case 'audio/x-ms-wma':

				$encoded = $playable .'.ogg';

				if (file_exists ($encoded) && is_readable ($encoded) && filesize ($encoded))
					return $encoded;

				if (!function_exists ('system'))
					throw new Exception ('Is needle enable OS call functions (verify if PHP is not in safe mode)!');

				$control = $playable .'.encoding';

				if (!file_put_contents ($control, strftime ('%c'), LOCK_EX))
					throw new Exception ('Impossible to create control file!');

				$log = $playable .'.wma-ogg.log';

				system ('avconv -y -i "'. $source .'" "'. $encoded .'" 2> "'. $log .'"', $return);

				unlink ($control);

				if ($return)
				{
					@unlink ($encoded);

					throw new Exception ('Has a problem with video conversion! Verify if [avconv] exists in system and supports OGG codec (libvorbis). Read more in LOG file ['. $log .'].');
				}

				return $encoded;
		}

		return $source;
	}

	/*
	 * This function resize image generating other of identical MIME type.
	 * If webp parameter is TRUE, the generated image will be image/webp MIME type with optimal quality for compress.
	 * If webp is FALSE, but jp2 is TRUE, the generated image will be converted to JPEG2000 format.
	 */
	public static function resizeImage ($source, $type, $resized, $width = 0, $height = 0, $force = FALSE, $bw = FALSE, $crop = FALSE, $webp = FALSE, $jp2 = FALSE)
	{
		if (!file_exists ($source) || !is_readable ($source) || !(int) filesize ($source))
			throw new Exception ('Trying to resize inexistent or unreadable image!');
		
		if (!(int) $width && !(int) $height && !(bool) $bw && !(bool) $webp && !(bool) $jp2)
			return $source;
	
		$vetor = getimagesize ($source);
	
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

		$w = $width = round ($width);
		$h = $height = round ($height);
	
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

		if ($crop)
		{
			$wAux = round (($atualWidth * $height) / $atualHeight);

			$hAux = round (($atualHeight * $width) / $atualWidth);

			if ($hAux > $height)
				$height = $hAux;
			elseif ($wAux > $width)
				$width = $wAux;
		}

		/*
		 * To WebP and JPEG 2000 compress works with every type (File, Fck, CKEditor and CloudFile) and local derivaded types, has been necessary
		 * to parse 'resized' path and fix it.
		 */

		$pieces = explode ('_', $source);

		$padded = array_pop ($pieces);

		$folder = realpath (substr ($resized, 0, strrpos ($resized, DIRECTORY_SEPARATOR)));

		if ($webp)
			$qualifier = 'webp';
		elseif ($jp2)
			$qualifier = 'jp2';
		else
			$qualifier = 'resized';

		$resized = $folder . DIRECTORY_SEPARATOR . $qualifier .'_'. $padded .'_'. $width .'x'. $height . ($bw ? '_bw' : '') . ($crop ? '_crop' : '');

		if (file_exists ($resized) && is_readable ($resized) && (int) filesize ($resized))
			return $resized;

		$buffer = FALSE;

		switch ($type)
		{
			case 'image/jpeg':
			case 'image/pjpeg':
				$buffer = imagecreatefromjpeg ($source);
				break;

			case 'image/gif':
				$buffer = imagecreatefromgif ($source);
				break;

			case 'image/png':
				$buffer = imagecreatefrompng ($source);
				imagealphablending ($buffer, TRUE);
				imagesavealpha ($buffer, TRUE);
				break;
			
			case 'image/webp':
				$buffer = imagecreatefromwebp ($source);
				break;
		}

		if (!$buffer)
			throw new Exception ('File mimetype is invalid or the image does not exists!');

		if ($bw)
			@imagefilter ($buffer, IMG_FILTER_GRAYSCALE);

		if ($type != 'image/gif')
		{
			$thumb = imagecreatetruecolor ($w, $h);
			$color = imagecolorallocatealpha ($thumb, 255, 255, 255, 0);
			imagefill ($thumb, 0, 0, $color);
		}
		else
			$thumb = imagecreate ($w, $h);

		if ($crop)
			$ok = imagecopyresampled ($thumb, $buffer, -floor (($width - $w) / 2), -floor (($height - $h) / 2), 0, 0, $width, $height, $atualWidth, $atualHeight);
		else
			$ok = imagecopyresampled ($thumb, $buffer, 0, 0, 0, 0, $w, $h, $atualWidth, $atualHeight);

		imagedestroy ($buffer);
		
		if (!$ok)
			throw new Exception ('Impossible to resize image!');

		if ($webp)
		{
			/*
			 * Forcing to WebP means user needs optimize size of images. So, the quality used
			 * is not 100, but the default of PHP (infering that is the optimal). 
			 */

			$resized = $folder . DIRECTORY_SEPARATOR .'webp_'. $padded .'_'. $width .'x'. $height . ($bw ? '_bw' : '') . ($crop ? '_crop' : '');
	
			header ('Content-Type: image/webp');
	
			imagewebp ($thumb, $resized);

			imagedestroy ($thumb);

			return $resized;
		}

		if ($jp2)
		{
			/*
			 * Forcing to JPEG2000 means user needs optimize size of images. So, the quality used
			 * is not 100, but 70% (resulting in file size similar to WebP). 
			 */

			$resized = $folder . DIRECTORY_SEPARATOR .'jp2_'. $padded .'_'. $width .'x'. $height . ($bw ? '_bw' : '') . ($crop ? '_crop' : '');

			$jp2conversion = $resized .'-toJPEG2000';

			imagejpeg ($thumb, $jp2conversion, 100);

			// Try use ImageMagick in command line first...

			if (function_exists ('exec') && (`which convert`))
				@exec ('convert '. $jp2conversion .' -quality 70 '. $resized);
			
			if (file_exists ($resized) && is_readable ($resized) && (int) filesize ($resized))
			{
				imagedestroy ($thumb);

				@unlink ($jp2conversion);
		
				return $resized;
			}

			// If not success, try use imagick module from PHP...

			if (extension_loaded ('imagick'))
			{
				$image = new Imagick ($jp2conversion);

				$image->setImageFormat ('jp2');

				$image->setImageCompression (Imagick::COMPRESSION_JPEG2000);

				$image->setImageCompressionQuality (70);

				$image->stripImage ();

				$image->writeImage ($resized);

				$image->clear ();

				imagedestroy ($thumb);

				@unlink ($jp2conversion);

				if (file_exists ($resized) && is_readable ($resized) && (int) filesize ($resized))
					return $resized;
			}

			@unlink ($jp2conversion);
		}

		$resized = $folder . DIRECTORY_SEPARATOR .'resized_'. $padded .'_'. $width .'x'. $height . ($bw ? '_bw' : '') . ($crop ? '_crop' : '');

		switch ($type)
		{
			case 'image/jpeg':
			case 'image/pjpeg':
				imagejpeg ($thumb, $resized);
				break;

			case 'image/gif':
				imagegif ($thumb, $resized);
				break;

			case 'image/png':
				imagepng ($thumb, $resized);
				break;
			
			case 'image/webp':
				imagewebp ($thumb, $resized);
				break;
		}

		imagedestroy ($thumb);

		return $resized;
	}
}
