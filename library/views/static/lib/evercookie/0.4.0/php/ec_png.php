<?php
// width of 200 means 600 bytes (3 RGB bytes per pixel)
$x = 200;
$y = 1;

$gd = imagecreatetruecolor($x, $y);

$data_arr = str_split($_COOKIE[$_GET['name']]);

$x = 0;
$y = 0;
for ($i = 0; $i < count($data_arr); $i += 3)
{
	$color = imagecolorallocate($gd, ord($data_arr[$i]), ord($data_arr[$i+1]), ord($data_arr[$i+2]));
	imagesetpixel($gd, $x++, $y, $color);
}
 
header('Content-Type: image/png');
header('Last-Modified: Wed, 30 Jun 2010 21:36:48 GMT');
header('Expires: Tue, 31 Dec 2030 23:30:45 GMT');
header('Cache-Control: private, max-age=630720000');

// boom. headshot.
imagepng($gd);

