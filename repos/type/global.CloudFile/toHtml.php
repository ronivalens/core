<?
ob_start ();

$aux = new xCloudFile ();

if ($field->showDetails ())
{
	?>
	<div style="position: relative; width: 300px; height: 106px; top: 0px; left: 0px; border-width: 1px; border-color: #CCCCCC; border-style: solid; background-color: #FFFFFF;">
		<div id="preload_file_<?= $fieldId ?>" style="display: <?= $field->getValue () ? '' : 'none' ?>; position: absolute; width: 300px; height: 106; top: 0px; left: 0px; background-color: #FFFFFF;">
			<?
			echo $aux->getFileResume ($field->getValue ());
			?>
		</div>
		<div id="not_file_<?= $fieldId ?>" style="display: <?= $field->getValue () ? 'none' : '' ?>; position: absolute; width: 300px; height: 106px; top: 0px; left: 0px; background-color: #FFFFFF;">
			<div style="position: absolute; width: 100px; height: 100px; top: 3px; left: 3px;">
				<img src="titan.php?target=loadFile&file=interface/file/file.gif" border="0">
			</div>				
			<div style="position: absolute; width: 190px; top: 10px; left: 105px; color: #990000; font-weight: bold; overflow: hidden;"><?= __ ('There is no associated file!') ?></div>
		</div>
	</div>	
	<?
}
else
{
	?>
	<span style="display: inline-block; border-width: 1px; border-color: #CCCCCC; border-style: solid; background-color: #FFFFFF; padding: 2px;">
		<?
		if ($field->getValue ())
			echo $aux->getFileResume ($field->getValue (), $field->showDetails (), $field->getResolution ());
		else
			echo '<img src="titan.php?target=loadFile&file=interface/file/file.gif" border="0">';
		?>
	</span>
	<?
}

return ob_get_clean ();
?>