<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<!--
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Preview page for the Image dialog window.
 *
 * Curiosity: http://en.wikipedia.org/wiki/Lorem_ipsum
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
	<meta name="robots" content="noindex, nofollow" />
	<link href="../common/fck_dialog_common.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">

// Sets the Skin CSS
document.write( '<link href="' + window.parent.FCKConfig.SkinPath + 'fck_dialog.css" type="text/css" rel="stylesheet">' ) ;

if ( window.parent.FCKConfig.BaseHref.length > 0 )
	document.write( '<base href="' + window.parent.FCKConfig.BaseHref + '">' ) ;

window.onload = function()
{
	window.parent.SetPreviewElements(
		document.getElementById( 'imgPreview' ),
		document.getElementById( 'lnkPreview' ) ) ;
}

	</script>
</head>
<body style="color: #000000; background-color: #ffffff">
	<a id="lnkPreview" onClick="return false;" style="cursor: default">
		<img id="imgPreview" onload="window.parent.UpdateOriginal();" style="display: none" /></a>Lorem
	ipsum dolor sit amet, consectetuer adipiscing elit. Maecenas feugiat consequat diam.
	Maecenas metus. Vivamus diam purus, cursus a, commodo non, facilisis vitae, nulla.
	Aenean dictum lacinia tortor. Nunc iaculis, nibh non iaculis aliquam, orci felis
	euismod neque, sed ornare massa mauris sed velit. Nulla pretium mi et risus. Fusce
	mi pede, tempor id, cursus ac, ullamcorper nec, enim. Sed tortor. Curabitur molestie.
	Duis velit augue, condimentum at, ultrices a, luctus ut, orci. Donec pellentesque
	egestas eros. Integer cursus, augue in cursus faucibus, eros pede bibendum sem,
	in tempus tellus justo quis ligula. Etiam eget tortor. Vestibulum rutrum, est ut
	placerat elementum, lectus nisl aliquam velit, tempor aliquam eros nunc nonummy
	metus. In eros metus, gravida a, gravida sed, lobortis id, turpis. Ut ultrices,
	ipsum at venenatis fringilla, sem nulla lacinia tellus, eget aliquet turpis mauris
	non enim. Nam turpis. Suspendisse lacinia. Curabitur ac tortor ut ipsum egestas
	elementum. Nunc imperdiet gravida mauris.
</body>
</html>