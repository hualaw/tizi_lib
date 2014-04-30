<?php
/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */
require_once(dirname(__FILE__)."/comm/config.php");
require_once(CLASS_PATH."QC.class.php");
require_once(dirname(__DIR__).DIRECTORY_SEPARATOR."Connect.php");

class qqConnect extends Connect{

    private $qc;

    public function __construct(){
        parent::__construct();
        self::$module = 'qq';
        $this->qc = new QC("", "", $this->get_config());
    }

    public function login(){

        $this->qc->qq_login();

    }

    public function callback(){

        $params = $this->qc->qq_callback();
        $access_token = $params['access_token'];
        $openid = $this->qc->get_openid();

        $this->qc->keysArr = array(
            'access_token' => $access_token,
            'openid' => $openid
        );
        $user_info = $this->qc->get_user_info();
        $user_info['access_token'] = $access_token;
        $user_info['open_id'] = $openid;
        
        return $user_info;

    }

}
