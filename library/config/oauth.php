<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['qq'] = array(
    'appid' => '101069729',
    'appkey' => '3e5f2540e6621d3d62f3d8d93458a910',
    'callback' => login_url('oauth/callback/qq'),
    'scope' => 'get_user_info'
);

$config['weibo'] = array(
    'appid' => '3082683861',
    'appkey' => '3e5f2540e6621d3d62f3d8d93458a910',
    'callback' => login_url('oauth/callback/weibo')
);