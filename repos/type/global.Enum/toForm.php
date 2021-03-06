<?php ob_start () ?>
<select class="field" style="<?= $field->getStyle () ?>" name="<?= $fieldName ?>" id="<?= $fieldId ?>">
	<?php
	if (!$field->isRequired ())
		echo '<option value="">'. __ ('Select...') .'</option>';
	
	foreach ($field->getMapping () as $column => $value)
		echo '<option value="'. $column .'"'. ($column == $field->getValue () ? ' selected' : '') .'>'. $value .'</option>';
	?>
</select>
<?php
$aux = ob_get_contents ();

ob_end_clean();

return $aux;
?>