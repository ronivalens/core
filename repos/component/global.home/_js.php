<script language="javascript" type="text/javascript" src="titan.php?target=packer&files=dragable-ajax,dragable-boxes,sha1"></script>
<script language="javascript" type="text/javascript">
function createARSSBox(url,columnIndex,heightOfBox,maxRssItems,minutesBeforeReload,uniqueIdentifier,state)
{
	if(!heightOfBox)heightOfBox = '0';
	if(!minutesBeforeReload)minutesBeforeReload = '0';

	var tmpIndex = createABox(columnIndex,heightOfBox,true);

	if(useCookiesToRememberRSSSources && !cookieRSSSources[url])
	{
		cookieRSSSources[url] = cookieCounter;
		Set_Cookie(nameOfCookie + cookieCounter,url + '#;#' + columnIndex + '#;#' + maxRssItems + '#;#' + heightOfBox + '#;#' + minutesBeforeReload + '#;#' + uniqueIdentifier + '#;#' + state  ,60000);
		cookieCounter++;
	}

	dragableBoxesArray[tmpIndex]['rssUrl'] = url;
	dragableBoxesArray[tmpIndex]['maxRssItems'] = maxRssItems?maxRssItems:100;
	dragableBoxesArray[tmpIndex]['minutesBeforeReload'] = minutesBeforeReload;
	dragableBoxesArray[tmpIndex]['heightOfBox'] = heightOfBox;
	dragableBoxesArray[tmpIndex]['uniqueIdentifier'] = uniqueIdentifier;
	dragableBoxesArray[tmpIndex]['state'] = state;

	if(state==0){
		showHideBoxContent(false,document.getElementById('dragableBoxExpand' + tmpIndex));
	}

	staticObjectArray[uniqueIdentifier] = tmpIndex;

	var tmpInterval = false;
	if(minutesBeforeReload && minutesBeforeReload>0){
		var tmpInterval = setInterval("reloadRSSData(" + tmpIndex + ")",(minutesBeforeReload*1000*60));
	}

	dragableBoxesArray[tmpIndex]['intervalObj'] = tmpInterval;

	addRSSEditContent(document.getElementById('dragableBoxHeader' + tmpIndex))

	if(!document.getElementById('dragableBoxContent' + tmpIndex).innerHTML)document.getElementById('dragableBoxContent' + tmpIndex).innerHTML = 'loading RSS data';

	if(url.length>0 && url!='undefined')
	{
		var ajaxIndex = ajaxObjects.length;
		ajaxObjects[ajaxIndex] = new sack();
		if(!maxRssItems)maxRssItems = 100;
		ajaxObjects[ajaxIndex].requestFile = 'titan.php?target=readRss&rssURL=' + escape(url) + '&maxRssItems=' + maxRssItems;	// Specifying which file to get
		ajaxObjects[ajaxIndex].onCompletion = function(){ showRSSData(ajaxIndex,tmpIndex); };	// Specify function that will be executed after file has been found
		ajaxObjects[ajaxIndex].runAJAX();		// Execute AJAX function
	}
	else
		hideHeaderOptionsForStaticBoxes(tmpIndex);
}

function reloadRSSData(numericId)
{
	var ajaxIndex = ajaxObjects.length;
	ajaxObjects[ajaxIndex] = new sack();
	showStatusBarMessage(numericId,'Loading data...');
	ajaxObjects[ajaxIndex].requestFile = 'titan.php?target=readRss&rssURL=' + escape(dragableBoxesArray[numericId]['rssUrl']) + '&maxRssItems=' + dragableBoxesArray[numericId]['maxRssItems'];	// Specifying which file to get
	ajaxObjects[ajaxIndex].onCompletion = function(){ showRSSData(ajaxIndex,numericId); };	// Specify function that will be executed after file has been found
	ajaxObjects[ajaxIndex].runAJAX();		// Execute AJAX function
}

function createDefaultBoxes()
{
	eval (ajax.getBoxes ());

	<? ob_start () ?>
	<b><?= __ ('Name') ?>:</b> <?= $user->getName () ?><br />\
	<b>Login:</b> <?= $user->getLogin () ?><br />\
	<b>E-mail:</b> <?= $user->getEmail () ?><br />\
	<b><?= __ ('User since') ?>:</b> <?= $user->getCreateDate () ?><br />\
	<b><?= __ ('Last Logon') ?>:</b> <?= $user->getCreateDate () == $user->getLastLogon () ? __ ('Never had logged.') : $user->getLastLogon () ?><br />\
	<? $str = ob_get_clean () ?>

	var htmlContentOfNewBox = '<div style="line-height: 15px;"><?= $str ?></div>';

	var titleOfNewBox = '<?= __ ('Informations') ?>';
	var newIndex = createABox (1, 80, false, 'staticObject1');
	document.getElementById ('dragableBoxContent' + newIndex).innerHTML = htmlContentOfNewBox;
	document.getElementById ('dragableBoxHeader_txt' + newIndex).innerHTML = titleOfNewBox;
	hideHeaderOptionsForStaticBoxes(staticObjectArray['staticObject1']);
	disableBoxDrag (staticObjectArray['staticObject1']);
}

function showChangePasswd ()
{
	var div = document.getElementById ('idSearch');

	if (div.style.display == '')
		div.style.display = 'none';
	else
		div.style.display = '';
}
function changePassword (form)
{
	var objForm = document.getElementById (form);

	if (objForm.newPassword.value.replace(/ /g,'') == '')
	{
		alert ('<?= __ ('The password cannot be empty and neither contain empty spaces!') ?>');

		return false;
	}

	if (objForm.newPassword.value == '<?= $user->getLogin () ?>')
	{
		alert ('<?= __ ('The password cannot be equal with your login!') ?>');

		return false;
	}

	if (objForm.newPassword.value != objForm.repeat.value)
	{
		objForm.password.value = '';
		objForm.newPassword.value = '';
		objForm.repeat.value = '';

		alert ('<?= __ ('The both field values ("New Password" and "Confirm Password") must be equal') ?>');

		return false;
	}

	showWait ();
	
	var passwd = objForm.password.value;
	var newPas = objForm.newPassword.value;
	
	<? if (Security::singleton ()->encryptOnClient ()) { echo 'passwd = hex_sha1 (passwd); newPas = hex_sha1 (newPas);'; } ?>
	
	if (ajax.changePasswd (passwd, newPas))
		showChangePasswd ();

	ajax.delay (function () {
		objForm.password.value = '';
		objForm.newPassword.value = '';
		objForm.repeat.value = '';

		ajax.showMessages ();

		hideWait ();
	});
}
</script>