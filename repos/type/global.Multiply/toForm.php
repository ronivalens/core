<?php
$linkColumn = $field->getLinkColumn ();
$linkView = $field->getLinkView ();

$columns = implode (", ", $field->getColumnsView ());

$values = $field->getValue ();

array_unshift ($values, 0);

$sth = $db->prepare ("SELECT ". $columns .", ". $field->getLinkColumn () ." FROM ". $field->getLink () . ($field->getWhere () != '' ? ' WHERE '. $field->getWhere () : '') ." ORDER BY ". $columns);

$sth->execute ();

ob_start ();

if (!$field->useCheckBoxes () && !$field->useFastSearch ())
{
	?>
	<table id="<?= $fieldId ?>_table"></table>
	<select class="field" style="<?= $field->getStyle () ?>" name="<?= $fieldName ?>_select" id="<?= $fieldId ?>" onchange="JavaScript: global.Multiply.choose ('<?= $fieldName ?>', '<?= $fieldId ?>', this.options[this.selectedIndex].value, this.options[this.selectedIndex].text); this.selectedIndex = 0;">
		<option value="0">Selecione</option>
		<?php
		while ($item = $sth->fetch (PDO::FETCH_OBJ))
			echo '<option value="'. $item->$linkColumn .'">'. $field->makeView ($item) .'</option>';
		?>
	</select>
	<script language="javascript" type="text/javascript">
	<?php
	try
	{
		$sth = $db->prepare ("SELECT l.". implode (", l.", $field->getColumnsView ()) .", l.". $field->getLinkColumn () ." FROM ". $field->getRelation () ." r INNER JOIN ". $field->getLink () ." l ON r.". $field->getColumn () ." = l.". $field->getLinkColumn () ." WHERE r.". $field->getColumn () ." IN ('". implode ("', '", $values) ."')");
		
		$sth->execute ();
	}
	catch (PDOException $e)
	{
		ob_end_clean ();
		
		throw $e;
	}
	
	while ($obj = $sth->fetch (PDO::FETCH_OBJ))
	{
		?>
		global.Multiply.choose ('<?= $fieldName ?>', '<?= $fieldId ?>', <?= $obj->$linkColumn ?>, '<?= str_replace ("\n", '', $field->makeView ($obj)) ?>');
		<?php
	}
	?>
	</script>
	<?php
}
elseif ($field->useCheckBoxes ())
{
	?>
	<table>
		<?php
		while ($item = $sth->fetch (PDO::FETCH_OBJ))
			echo '<tr><td><input type="checkbox" name="'. $fieldName .'[]" value="'. $item->$linkColumn .'" id="check_'. $fieldId .'_'. $item->$linkColumn .'" '. (in_array ($item->$linkColumn, $values) ? 'checked="checked"' : '') .' /></td><td>'. $field->makeView ($item) .'</td></tr>';
		?>
	</table>
	<?php
}
else
{
	?>
	<select class="chosen field" style="<?= $field->getStyle () ?>" name="<?= $fieldName ?>[]" id="<?= $fieldId ?>" multiple="multiple">
		<?php
		while ($item = $sth->fetch (PDO::FETCH_OBJ))
			echo '<option value="'. $item->$linkColumn .'" '. (in_array ($item->$linkColumn, $values) ? 'selected="selected"' : '') .'>'. $field->makeView ($item) .'</option>';
		?>
	</select>
	<?php
}

return ob_get_clean ();
?>