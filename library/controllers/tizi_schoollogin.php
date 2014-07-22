<?php  if(!defined("BASEPATH"))exit("No direct script access allowed");
require_once("tizi_controller.php");

class Tizi_Schoollogin extends Tizi_Controller {

    function __construct(){
        parent::__construct();
    }

    protected function callback(){
		echo 123;
    }
    
}
/*Endoffile schoollogin.php*/
/*Location:./library/controllers/tizi_schoollogin.php*/