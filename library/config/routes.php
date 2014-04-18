<?php
/*zujuan login*/
$route['logout']="login/login/logout";
$route['logout/(:any)']="login/login/logout/$1";
$route['logout/check']="login/login/check_logout";

$route['login/submit']="login/login/submit";
$route['login/check']="login/login/check_login";

$route['oauth/login']="login/oauthlogin";
$route['oauth/callback']="login/oauthlogin/callback";

$route['check_code']="login/login/check_code";
$route['check_captcha']="login/captcha_code/validate";
$route['captcha']="login/captcha_code/generate";
