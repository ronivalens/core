<?php

if (!isset ($_GET ['id']) || !is_numeric ($_GET['id']) || !(int) $_GET['id'] ||
	!isset ($_GET ['hash']) || strlen (preg_replace ('/[^0-9a-zA-Z]/i', '', $_GET ['hash'])) != 32)
	die ('There was lost of variables!');

if (isset ($_GET['width']) && is_numeric ($_GET['width']))
	$width = (int) $_GET['width'];
else
	$width = 0;

if (isset ($_GET['height']) && is_numeric ($_GET['height']))
	$height = (int) $_GET['height'];
else
	$height = 0;

$id = (int) $_GET ['id'];
$hash = $_GET ['hash'];

$webp = (isset ($_GET['webp']) && $_GET['webp'] == '1') || strpos ($_SERVER ['HTTP_ACCEPT'], 'image/webp') !== FALSE ? TRUE : FALSE;

$jp2 = FALSE;

if ((isset ($_GET['jp2']) && $_GET['jp2'] == '1') || (isset ($_SERVER ['HTTP_USER_AGENT']) && strpos ($_SERVER ['HTTP_USER_AGENT'], 'Safari') !== FALSE))
{
	preg_match ('/Version\/(?P<version>[0-9]+)/', $_SERVER ['HTTP_USER_AGENT'], $parameters);

	if ((float) $parameters ['version'] >= 6)
		$jp2 = TRUE;
}

try
{	
	$sth = Database::singleton ()->prepare ("SELECT * FROM _ckeditor WHERE _id = :id AND _hash = :hash");
	
	$sth->bindParam (':id', $id, PDO::PARAM_INT);
	$sth->bindParam (':hash', $hash, PDO::PARAM_INT, 32);
	
	$sth->execute ();
	
	$obj = $sth->fetch (PDO::FETCH_OBJ);
	
	if (!$obj)
		throw new Exception ('This file is not available!');
	
	$archive = Archive::singleton ();
	
	if (!$archive->isAcceptable ($obj->_mimetype))
		throw new Exception ('This file type ('. $obj->mimetype .') is not supported!');
}
catch (PDOException $e)
{
	toLog ($e->getMessage ());
	
	die ('Critical error!');
}
catch (Exception $e)
{
	die ($e->getMessage ());
}

if (isset ($_GET['assume']))
	$assume = (int) $_GET['assume'];
else
	$assume = $archive->getAssume ($obj->_mimetype);

$file = CKEditor::getFilePath ($id);

if (!file_exists ($file) || !is_readable ($file) || !(int) filesize ($file))
	die ('This file is not available!');

ob_clean ();

if (function_exists ('apache_setenv'))
	@apache_setenv ('no-gzip', 1);

@ini_set ('zlib.output_compression', 'Off');

switch ($assume)
{
	case Archive::IMAGE:
		$file = CKEditor::resize ($id, $obj->_mimetype, $width, $height, ($width && $height), FALSE, FALSE, $webp, $jp2);

		header ('Content-Type: '. mime_content_type ($file));
		header ('Content-Disposition: inline; filename=' . fileName ($obj->_name));
		break;
		
	case Archive::OPEN:
		header ('Content-Type: '. $obj->_mimetype);
		header ('Content-Disposition: inline; filename=' . fileName ($obj->_name));
		break;
	
	case Archive::DOWNLOAD:
	case Archive::VIDEO:
	case Archive::AUDIO:
	default:
		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename=' . fileName ($obj->_name));
		break;
}

$binary = fopen ($file, 'rb');

$buffer = fread ($binary, filesize ($file));

fclose ($binary);

echo $buffer;