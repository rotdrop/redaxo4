<?php
	session_start();
	if (!isset($_GET['stamp']) || !isset($_SESSION['TIMESTAMP']) || $_GET['stamp'] != $_SESSION['TIMESTAMP']) exit; 
	$email = (isset($_GET['email'])) ? stripslashes(trim($_GET['email'])) : false;
	
	if (!$email) exit();

	header ("Content-type: image/png");

	$font = 2;
	$color = '000000';
	$image = @ImageCreate(imagefontwidth($font)*strlen($email), imagefontheight(10)) or die ("Cannot Initialize new GD image stream");
	$background_color = ImageColorAllocate ($image, 255, 255, 255);
	//imagecolortransparent($image , '000000');
	$text_color = ImageColorAllocate ($image, hexdec(substr($color, 0, 2)), hexdec(substr($color, 2, 2)), hexdec(substr($color, 4, 2)));
	ImageString ($image, $font, 0, 0,  $email, $text_color);
	ImagePng ($image);
?>