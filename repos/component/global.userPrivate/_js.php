<script language="javascript" type="text/javascript" src="titan.php?target=packer&files=sha1"></script>
<script language="javascript" type="text/javascript">
function showActivate (id)
{
	var row = document.getElementById ('_USER_ROW_' + id);
	
	var label = document.getElementById ('_USER_CONTENT_' + id);
	
	var str;
	
	if (row.style.display == '')
		row.style.display = 'none';
	else
	{
		label.innerHTML = '<img src="titan.php?target=loadFile&file=interface/icon/upload.gif" border="0" /> <label>Aguarde! Carregando...</label>';
		
		row.style.display = '';
		
		if (ajax.isActive (id))
			str = '	<table width="100%" style="border: #990000 1px solid;">\
						<tr>\
							<td>\
								<label style="color: #990000; font-weight: bold;">Atenção!</label> Tem certeza que deseja <label style="color: #990000;">DESATIVAR</label> este usuário? Com isto ele <b>não poderá</b> mais efetuar <i>logon</i> no sistema.\
							</td>\
							<td style="text-align: right;">\
								<input type="button" class="button buttonRegisterCancel" value="Desativar Usuário" onclick="JavaScript: activateUser (' + id + ', 0);" />\
							</td>\
						</tr>\
					</table>';
		else
			str = '	<table width="100%" style="border: #009900 1px solid;">\
						<tr>\
							<td>\
								<label style="color: #990000; font-weight: bold;">Atenção!</label> Tem certeza que deseja <label style="color: #009900;">ATIVAR</label> este usuário? Com isto ele <b>poderá</b> efetuar <i>logon</i> no sistema.\
							</td>\
							<td style="text-align: right;">\
								<input type="button" class="button buttonRegisterOk" value="Ativar Usuário" onclick="JavaScript: activateUser (' + id + ', 1);" />\
							</td>\
						</tr>\
					</table>';
		
		label.innerHTML = str;
	}
}

function activateUser (id, value)
{
	showWait ();
	
	showActivate (id);
	
	ajax.activate (id, value, function () {
		hideWait ();
	});
	
	return false;
}

function showChangePasswd (id)
{
	var row = document.getElementById ('_USER_ROW_' + id);
	
	var label = document.getElementById ('_USER_CONTENT_' + id);
	
	var str;
	
	if (row.style.display == '')
		row.style.display = 'none';
	else
	{
		label.innerHTML = '<img src="titan.php?target=loadFile&file=interface/icon/upload.gif" border="0" /> <label>Aguarde! Carregando...</label>';
		
		row.style.display = '';
		
		str = '	<table width="100%" style="border: #090 2px solid;">\
					<tr>\
						<td style="padding: 10px 5px;">\
							<label style="color: #900; font-weight: bold;">Atenção!</label> Preencha ambos os campos a seguir com a nova senha:\
						</td>\
						<td style="text-align: right;">\
							<input type="password" class="field" id="_PASSWD_FIELD_' + id + '" style="width: 150px; float: none;" /> \
							<input type="password" class="field" id="_RETYPE_FIELD_' + id + '" style="width: 150px; float: none;" /> \
							<input type="button" class="button" value="Alterar Senha" onclick="JavaScript: changePasswd (' + id + ');" />\
						</td>\
					</tr>\
				</table>';
		
		label.innerHTML = str;
	}
}

function changePasswd (id)
{
	passwd = $('_PASSWD_FIELD_' + id);
	retype = $('_RETYPE_FIELD_' + id);
	
	if (passwd.value.replace(/ /g,'') == '')
	{
		alert ('<?= __ ('The password cannot be empty and neither contain empty spaces!') ?>');

		return false;
	}

	if (passwd.value != retype.value)
	{
		alert ('<?= __ ('The both field values ("New Password" and "Confirm Password") must be equal') ?>');

		return false;
	}
	
	showWait ();
	
	p = passwd.value;
	
	<?php if (Security::singleton ()->encryptOnClient ()) { echo 'p = hex_sha1 (passwd.value);'; } ?>
	
	if (ajax.changePasswd (id, p))
		showChangePasswd (id);

	ajax.delay (function () {
		passwd.value = '';
		retype.value = '';
		
		ajax.showMessages ();

		hideWait ();
	});
}

function selectAll ()
{
	var check = false, i;
	
	if ($('_SELECT_ALL_').checked)
		check = true;
	
	counter = 0;
	while (obj = $('check_' + counter++))
		obj.checked = check;
}

function exportCsv ()
{
	var assigns = '', useSearch = '0';
	
	counter = 0;
	while (obj = $('check_' + counter++))
		if (obj.checked)
			assigns = assigns + obj.name + ',';
	
	if (assigns == '')
	{
		message ('Selecione pelo menos um campo para ser exportado!', 300, 120, true);
		
		return false;
	}
	
	if ($('_SEARCH_').checked)
		useSearch = '1'
	
	openPrintPopup ('titan.php?target=script&toSection=<?= Business::singleton ()->getSection (Section::TCURRENT)->getName () ?>&file=exportCsv&auth=1&search=' + useSearch + '&assigns=' + assigns);
}
</script>