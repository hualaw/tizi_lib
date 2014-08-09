<?php
/*zujuan login*/
$route['logout']="login/login/logout";
$route['logout/(:any)']="login/login/logout/$1";
$route['logout/check']="login/login/check_logout";

$route['login/submit']="login/login/submit";
$route['login/check']="login/login/check_login";

$route['school/login']="login/schoollogin/index";
$route['oauth/login']="login/oauthlogin/oauth";
$route['oauth/callback/(:any)']="login/oauthlogin/callback/$1";
$route['sso/callback']="login/ssologin/index";

$route['verify']="login/verify/verify_email";
$route['verify/code/(:any)']="login/verify/verify_email/$1";

$route['send_email_code']="login/verify/send_email_code";
$route['send_phone_code']="login/verify/send_phone_code";

$route['check_code']="login/login/check_code";
$route['check_captcha']="login/captcha_code/validate";
$route['captcha']="login/captcha_code/generate";
$route['captcha_img']="login/captcha_code/generate_img";
$route['qrcode']="login/qrcode_code/generate";
$route['qrcode_img']="login/qrcode_code/generate_img";
