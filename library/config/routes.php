<?php
/*zujuan login*/
$route['logout']="login/login/logout";
$route['logout/(:any)']="login/login/logout/$1";
$route['login/submit']="login/login/submit";

$route['check_code']="login/login/check_code";
$route['check_captcha']="login/captcha_code/validate";
$route['captcha']="login/captcha_code/generate";
