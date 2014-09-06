<html>
<head>
	<title>CAFEV Test Newsletter</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<table width="500" align="center" style="border:1px solid #000000;">
<tr>
	<td valign="top">
		Newsletter:<br><br>
		Hier können Sie sich für den Newsletter an/ und abmelden!<br>
<form name="letteritform" action="test.php" method="post">
<input type="hidden" name="letteritbid[]" value="1">
<table cellpadding="0" cellspacing="0">
<tr>
	<td>Email</td>
	<td>&nbsp;<input type="text" size="30" name="letteritemail"></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>&nbsp;<input type="submit" value="An/Abmelden"></td>
</tr>
</table>
</form>
	</td>
</tr>
<tr>
	<td valign="top">
		<br>Hier erfolgt die Ausgabe der An/Abmeldung!<br>
<?php
$letteritpath="../letterit2/";
$letteritlanguage="german";
include($letteritpath."submit_inc.php");
?>
	</td>
</tr>
</table>
</html>
