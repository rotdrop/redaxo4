<?
$cmd     = (isset($_POST['cmd'])) ? stripslashes(trim($_POST['cmd'])) : false;
$filesToCopy = (isset($_POST['file']) && is_array($_POST['file'])) ? $_POST['file'] : false;

//define the path as relative
$path = $_SERVER['DOCUMENT_ROOT'].'/upload/';
// define allowed File Suffixes
$fileTypes = array('mp3','wmv','gif','jpg','png','wav','swf','mp4','flv','m4v','rm','mp4','mpg','m4a');
// Array with Files to upload
$fileList = array();
//using the opendir function
$dir_handle = @opendir($path) or die("Unable to open $path");
//running the while loop
while ($file = readdir($dir_handle)) 
{
	if ($file != '.' && $file != '..') {
   		$path_info = pathinfo($file);
   		$suffix = $path_info['extension'];
		if (!in_array($suffix,$fileTypes)) continue;
		$fileList[] = $file; 
		//echo "<a href='$file'>$file</a><br/>";
   }
}
//closing the directory
closedir($dir_handle);
?> 
<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
	<fieldset>
		<legend>Dateiliste</legend>
		<?php
			foreach ($fileList as $key => $value) {
				$checked = ($filesToCopy && $filesToCopy[$key]) ? 'checked="checked"' : '';
				echo '<label><input type="checkbox" name="file['.$key.']" value="1" '.$checked.'/>&nbsp;'.$value.'</label><br />'."\n";
			}
			if (count($fileList)) {
				echo '<button type="submit" name="cmd" value="copy">Dateien in den Files-Ordner kopieren</button>'."\n";
			}
		?>
	</fieldset>
</form>
<?php

if ($cmd == 'copy' && is_array($filesToCopy) && count($filesToCopy)) {
	foreach($filesToCopy as $key => $value) {
		echo '<hr >Copy File: '.$fileList[$key];
		copy($fileList[$key],"../files/".$fileList[$key]);
	}
}
?>
