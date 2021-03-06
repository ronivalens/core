<?php

if (!isset ($_GET ['fileId']) || !$_GET['fileId'] || !is_numeric ($_GET['fileId']))
	exit ();

if (isset ($_GET['width']) && is_numeric ($_GET['width']))
	$width = (int) $_GET['width'];
else
	$width = 0;

if (isset ($_GET['height']) && is_numeric ($_GET['height']))
	$height = (int) $_GET['height'];
else
	$height = 0;
	
$force = isset ($_GET['force']) && $_GET['force'] == '1' ? TRUE : FALSE;

$bw = isset ($_GET['bw']) && $_GET['bw'] == '1' ? TRUE : FALSE;

$webp = (isset ($_GET['webp']) && $_GET['webp'] == '1') || (isset ($_SERVER ['HTTP_ACCEPT']) && strpos ($_SERVER ['HTTP_ACCEPT'], 'image/webp') !== FALSE) ? TRUE : FALSE;

$jp2 = FALSE;

if ((isset ($_GET['jp2']) && $_GET['jp2'] == '1') || (isset ($_SERVER ['HTTP_USER_AGENT']) && strpos ($_SERVER ['HTTP_USER_AGENT'], 'Safari') !== FALSE))
{
	preg_match ('/Version\/(?P<version>[0-9]+)/', $_SERVER ['HTTP_USER_AGENT'], $parameters);

	if ((float) $parameters ['version'] >= 6)
		$jp2 = TRUE;
}

$fileId = (int) $_GET ['fileId'];

try
{
	if (!$fileId)
		throw new Exception ('Invalid file!');
	
	$db = Database::singleton ();
	
	$sth = $db->prepare ("SELECT * FROM _file WHERE _id = :id");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
	
	$obj = $sth->fetch (PDO::FETCH_OBJ);
	
	if (!$obj)
		throw new Exception ('This file is not available!');
	
	if (!is_null (@$obj->_public) && !(int) $obj->_public &&
	   (!isset ($_GET['hash']) || is_null (@$obj->_hash) || strlen (trim ($obj->_hash)) != 32 || $_GET['hash'] != $obj->_hash))
		throw new Exception (__ ('You do not have permission to access this file!'));
	
	$archive = Archive::singleton ();
	
	if (!$archive->isAcceptable ($obj->_mimetype))
		throw new Exception ('This file type is not supported!');
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

$filePath = $archive->getDataPath () . 'file_' . str_pad ($fileId, 19, '0', STR_PAD_LEFT);

if (!file_exists ($filePath))
	$filePath = $archive->getDataPath () . 'file_' . str_pad ($fileId, 7, '0', STR_PAD_LEFT);

if (!file_exists ($filePath))
	die ('This file is not available!');
		
$contentType = $obj->_mimetype;

switch ($assume)
{
	case Archive::IMAGE:
		if ($width || $height || $bw || $webp || $jp2)
			resize ($filePath, $contentType, $width, $height, $force, $bw, $webp, $jp2);
		
		header ('Content-Type: '. $contentType);
		header ('Content-Disposition: inline; filename=' . fileName ($obj->_name));
		break;
	
	case Archive::OPEN:
		header ('Content-Type: '. $contentType);
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

$binary = fopen ($filePath, 'rb');

$buffer = fread ($binary, filesize ($filePath));

fclose ($binary);

echo $buffer;

try
{
	$sth = $db->prepare ("UPDATE _file SET _counter = _counter + 1 WHERE _id = :id");
	
	$sth->bindParam (':id', $fileId, PDO::PARAM_INT);
	
	$sth->execute ();
}
catch (PDOException $e)
{}
