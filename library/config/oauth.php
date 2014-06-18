<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['qq'] = array(
    'appid' => '101069729',
    'appkey' => '26dfe68a08e9ff97a74058788c83acce',
    'callback' => 'http://user.tizi.com/oauth/callback/qq',
    'scope' => 'get_user_info'
);

$config['weibo'] = array(
    'appid' => '3082683861',
    'appkey' => '3e5f2540e6621d3d62f3d8d93458a910',
    'callback' => login_url('oauth/callback/weibo')
);

$config['wx'] = array(
    'appid' => 'wx4ee2b10de62b8b74',
    'token' => 'tizijiaoshi',
    'appsecret'=>'24dfcf4464af0e7f2219276fcd3a3bf4',
    'callback' => login_url('oauth/wx_callback'),
    'scope'=>'snsapi_base',//snsapi_userinfo

);

$config['tizi'] = array(
    'appid' => 'I6Lh72kTItE6y29Ig607N74M7i21oyTo',
    'appkey' => 'dswREHV2YJjF7iL5Zr5ETEFBwGwDQYjQ',
    'callback' => 'http://192.168.14.132:8090/oauth/callback/tizi',
    'scope' => 'get_user_info'
);

