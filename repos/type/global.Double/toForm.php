<?
return '<input type="text" class="field" style="'. $field->getStyle () .'" name="'. $fieldName .'" id="'. $fieldId .'" value="'. number_format ($field->getValue (), $field->getPrecision (), ',', '.') .'" onkeypress="JavaScript: return global.Float.format (this, event, '. $field->getPrecision () .');" onkeyup="JavaScript: global.Float.format (this, false, '. $field->getPrecision () .');" />';
?>