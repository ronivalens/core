<?php
set_time_limit (0);

if (!User::singleton ()->isLogged ())
	throw new Exception (__ ('Attention! Probably attack detected. Access Denied!'));

if (!isset ($_GET['field']) || trim ($_GET['field']) == '' || !isset ($_GET['media']) || !in_array ($_GET['media'], array ('image', 'video', 'audio', 'media', 'all')) || !isset ($_GET['public']) || !isset ($_GET['owner']))
	throw new Exception (__ ('There was lost of variables!'));

$field = $_GET['field'];

$media = $_GET['media'];

$public = (bool) $_GET['public'];

$owner = (bool) $_GET['owner'];
?>
<html>
	<head>
		<?php
		if (isset ($_FILES['file']) && (int) $_FILES['file']['size'])
		{
			$file = $_FILES['file'];
			
			$fileTemp = $file ['tmp_name'];
			$fileSize = $file ['size'];
			$fileType = $file ['type'];
			$fileName = fileName ($file ['name']);
			
			try
			{
				$db = Database::singleton ();

				$db->beginTransaction ();
				
				$archive = Archive::singleton ();

				if ($fileType == 'application/save' && !($fileType = $archive->getMimeByExtension (array_pop (explode ('.', $file ['name'])))))
					throw new Exception (__ ('This file type ([1]) is not supported!', $obj->_mimetype));
				
				if ($fileType == 'video/3gpp' && !Archive::is3GPPVideo ($fileTemp))
					$fileType = 'audio/3gpp';
				
				if (!$archive->isAcceptable ($fileType))
					throw new Exception (__ ('This type of file is not accepted by the system ([1])!', $fileType));
				
				if ($media != 'all')
				{
					switch ($media)
					{
						case 'image':
							$filter = $archive->getMimesByType (Archive::IMAGE);
							break;
						
						case 'video':
							$filter = $archive->getMimesByType (Archive::VIDEO);
							break;
						
						case 'audio':
							$filter = $archive->getMimesByType (Archive::AUDIO);
							break;
						
						case 'media':
							$filter = array_merge ($archive->getMimesByType (Archive::VIDEO), $archive->getMimesByType (Archive::AUDIO));
							break;
						
						default:
							throw new Exception (__ ('There was lost of variables!'));
					}
					
					if (!in_array ($fileType, $filter))
					{
						$types = array ();
						
						foreach ($filter as $trash => $mime)
						{
							$aux = strtoupper (trim ($archive->getExtensionByMime ($mime)));
							
							if (empty ($aux) || in_array ($aux, $types))
								continue;
							
							$types [] = $aux;
						}
						
						throw new Exception (__ ('This type of file ([1]) is not accept at this field! Files accepts are: [2].', strtoupper ($archive->getExtensionByMime ($fileType)), implode (', ', $types)));
					}
				}

				$id = Database::nextId ('_file', '_id');
				
				$hash = '';
				
				$array = array (
					array ('_id', $id, PDO::PARAM_INT),
					array ('_name', $fileName, PDO::PARAM_STR),
					array ('_mimetype', $fileType, PDO::PARAM_STR),
					array ('_size', $fileSize, PDO::PARAM_INT),
					array ('_user', User::singleton ()->getId (), PDO::PARAM_INT)
				);
				
				if (!$public)
				{
					$hash = File::getRandomHash ();
					
					$array [] = array ('_public', 0, PDO::PARAM_INT);
					$array [] = array ('_hash', $hash, PDO::PARAM_STR);
				}
				
				$columns = array ();
				$values  = array ();
				
				foreach ($array as $trash => $item)
				{
					$columns [] = $item [0];
					$values []  = ':'. $item [0];
				}
				
				$sth = $db->prepare ("INSERT INTO _file (". implode (", ", $columns) .") VALUES (". implode (", ", $values) .")");
				
				foreach ($array as $trash => $item)
					$sth->bindParam (':'. $item [0], $item [1], $item [2]);
				
				$sth->execute ();
				
				$path = File::getFilePath ($id);
				
				if (move_uploaded_file ($fileTemp, $path))
				{
					try
					{
						File::getPlayableFile ($id, $fileType);
					}
					catch (Exception $e)
					{
						toLog ($e->getMessage ());
					}
					
					?>
					<script language="javascript" type="text/javascript">
						parent.global.Fck.load ('<?= $field ?>', '<?= $media ?>', <?= $id ?>, '<?= $hash ?>');
					</script>
					<?php
				}
				else
					throw new Exception (__ ('Unable copy file to directory [[1]]!',  $archive->getDataPath ()));

				$db->commit ();
			}
			catch (PDOException $e)
			{
				$db->rollBack ();

				?>
				<script language="javascript" type="text/javascript">
					parent.global.Fck.imageUploadError ('<?= $field ?>', '<?= $media ?>', '<?= $e->getMessage () ?>');
				</script>
				<?php
			}
			catch (Exception $e)
			{
				$db->rollBack ();

				?>
				<script language="javascript" type="text/javascript">
					parent.global.Fck.imageUploadError ('<?= $field ?>', '<?= $media ?>', '<?= $e->getMessage () ?>');
				</script>
				<?php
			}
		}
		?>
		<link rel="stylesheet" href="titan.php?target=packerCss&amp;contexts=main" type="text/css" />
		<style type="text/css">
		.globalFckArchive
		{
			float: right;
			width: 40px;
			height: 47px;
			border-width: 0px;
			margin: 0px 0px 0px 5px;
			background: #EEE url(titan.php?target=tResource&type=File&file=archive.png) no-repeat top right;
			cursor: pointer;
			border: 0px;
			padding: 0px;
		}
		.globalFckArchive:hover
		{
			background-position: bottom;
		}
		</style>
		<!--[if IE]><link rel="stylesheet" type="text/css" href="titan.php?target=packerCss&amp;contexts=ie" /><![endif]-->
		<script language="javascript" type="text/javascript" src="titan.php?target=loadFile&file=js/prototype.js"></script>
		<script language="javascript">
		function clear ()
		{
			parent.global.Fck.imageUploadClear ('<?= $field ?>', '<?= $media ?>');
		}
		
		function upload ()
		{
			$('_CLOUD_FORM_').style.display = 'none';
			
			$('_CLOUD_PROGRESS_').style.display = '';
			
			$('_CLOUD_FORM_').submit ();
		}
		</script>
	</head>
	<body onUnload="JavaScript: clear ();" style="background: none #EEE; padding: 0px; height: 63px; overflow: hidden; vertical-align: middle;">
		<div id="_CLOUD_PROGRESS_" style="display: none; margin: 16px auto 0px; text-align: center; color: #656565;">
			<b><?= __ ('Wait! Sending...') ?></b> <br />
			<img src="titan.php?target=loadFile&file=interface/image/loader.gif" border="0" />
		</div>
		<form action="<?= $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'] ?>" id="_CLOUD_FORM_" method="POST" enctype="multipart/form-data" style="display: block;">
			<div class="globalFckArchive" style="display: block;" onClick="JavaScript: parent.global.Fck.archive ('<?= $field ?>', <?= $owner ? 'true' : 'false' ?>, '<?= $media ?>');" title="<?= __ ('Recovery from archive...') ?>"></div>
			<input type="button" class="button" value="<?= __ ('Send File') ?>" onClick="JavaScript: upload ();" style="float: right; margin-top: 8px;" />
			<input type="file" name="file" id="_CLOUD_FILE_" style="margin-top: 8px;" /><br />
			<div style="font-size: 10px; font-family: Verdana, Geneva, sans-serif; margin-top: 2px;"><?= __ ('Max Size:') .' <b style="color: #900">'. Archive::getServerUploadLimit () .' MB</b>' ?></div>
		</form>
	</body>
</html>