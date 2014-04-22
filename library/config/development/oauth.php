<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$config['qq'] = array(
    
    'appid' => '101063587',
    'appkey' => 'fc5808c5b51035363ab579949fab8154',
    'callback' => login_url('oauth/callback/qq'),
    'scope' => 'get_user_info',
);


$config['weibo'] = array(
    
    'appid' => '4212439649',
    'appkey' => '78650e6e0e1e1a0b2c916421bb73a0e8',
    'callback' => login_url('oauth/callback/weibo')

);

