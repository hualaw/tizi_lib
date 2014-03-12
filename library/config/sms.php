<?php
$config['smsversion'] = 2; // 1:old one.  2:new 

switch($config['smsversion']){
  case 1:
        //$config['api_uri'] = 'http://192.168.11.12:8080/sms/SendSms';
        $config['api_uri'] = 'http://10.160.29.246:8080/sms/SendSms';
        $config['server_ip'] = '192.168.11.113';
        $config['secret'] = 'd41d8cd98f00b204e9800998ecf8427e';
        break;
  case 2:
        $config['api_uri'] = '';
        $config['server_ip'] = '';
        $config['sn'] = 'SDK-BBX-010-18603';
        $config['secret'] = "MD45&(17";
        break;
}
	// $config['api_uri'] = 'http://192.168.11.12:8080/sms/SendSms';
	// $config['server_ip'] = '192.168.11.113';
	// $config['secret'] = 'd41d8cd98f00b204e9800998ecf8427e';

/* End of file sms.php */
/* Location: ./application/config/sms.php */  
