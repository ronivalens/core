<?
ldapUpdate ($itemId);

$form =& Form::singleton ('delete.xml', 'view.xml', 'all.xml');

if (!$form->load ($itemId))
	throw new Exception (__('Unable to load the user data!'));

$menu =& Menu::singleton ();
$menu->addJavaScript (__('Clean'), 'titan.php?target=loadFile&file=interface/menu/save.png', "deleteForm ('". $form->getFile () ."', 'form_". $form->getAssign () ."', '". $itemId ."');");
$menu->add ($form->goToAction ('cancel')->getName (), __('Cancel'), 0, $section->getName (), 'titan.php?target=loadFile&file=interface/menu/close.png');
?>