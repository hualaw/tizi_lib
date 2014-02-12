<?php

/*
$start_second = microtime(true);
$ret = Mail::send('lh1584@163.com', '梯子网注册验证', 'Got it!', 1);
var_dump($ret);
echo "time cost ". (microtime(true) - $start_second)."\n";
*/

Class Mail
{
	public static function send($to_mail_addr, $subject, $mail_content, $debug=false)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_URL, 'https://sendcloud.sohu.com/webapi/mail.send.json');
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); //allow maximum 5 seconds to execute
		//不同于登录SendCloud站点的帐号，您需要登录后台创建发信子帐号，使用子帐号和密码才可以进行邮件的发送。
		curl_setopt($ch, CURLOPT_POSTFIELDS,
				array('api_user' => 'postmaster@tizi-com.sendcloud.org',
					'api_key' => 'v33EreaE',
					'from' => 'noreply@daily.tizi.com',
					'fromname' => '梯子网',
					'to' => $to_mail_addr,
					'subject' => $subject,
					'html' => $mail_content,
					));        

		$result = curl_exec($ch);

		//for test
		if($debug) var_dump($result);

		$http_errno = curl_errno($ch);
		$http_error = 'Success!';
		$http_code = 200;
		$ret = 0; //2 stand for error

		if($http_errno === 0 )
		{
			$result_arr = json_decode($result, TRUE);
			if(isset($result_arr['message']) && $result_arr['message'] == 'success')
			{
				$ret = 1; //success!
			}
			else
			{
				$ret = 2; //http success, but logic error
				$http_error .= "\t".print_r($result_arr, 1);
			}
		}
		else {
			$ret = 3; //http error
			$http_error = curl_error($ch);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		}

		curl_close($ch);

		return array(
			'ret' => $ret,
			'http_errno' => $http_errno,
			'http_error' => $http_error,	
			'http_code' => $http_code,
		);
	}
}

#end of Mail.php
