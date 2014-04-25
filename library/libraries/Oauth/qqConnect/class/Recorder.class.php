<?php
/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */

require_once(CLASS_PATH."ErrorCase.class.php");
class Recorder{
    private static $data;
    private $inc;
    private $error;
    private $_CI;

    public function __construct($config){
        $this->_CI = &get_instance();
        $QC_userData = $this->_CI->session->userdata('QC_userData');
        $this->error = new ErrorCase();

        //-------读取配置文件
        $this->inc = $config;
        if(empty($this->inc)){
            $this->error->showError("20001");
        }

        if(!$QC_userData){
            self::$data = array();
        }else{
            self::$data = $QC_userData;
        }
    }

    public function write($name,$value){
        self::$data[$name] = $value;
    }

    public function read($name){
        if(empty(self::$data[$name])){
            return null;
        }else{
            return self::$data[$name];
        }
    }

    public function readInc($name){
        if(!isset($this->inc[$name]) || empty($this->inc[$name])){
            return null;
        }else{
            return $this->inc[$name];
        }
    }

    public function delete($name){
        unset(self::$data[$name]);
    }

    function __destruct(){
        $this->_CI->session->set_userdata('QC_userData',self::$data);
        //$_SESSION['QC_userData'] = self::$data;
    }
}
