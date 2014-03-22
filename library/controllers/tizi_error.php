<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tizi_Error extends MY_Controller {

    private $_smarty_dir="file:[lib]header/";

    function __construct()
    {
        parent::__construct();
    }

    function index($redirect='',$settimeout=true,$status_code=404)
    {
        set_status_header($status_code);

        if($redirect&&urldecode($redirect)) $redirect=urldecode($redirect);
        if(strpos($redirect,'http://') === false) $redirect='';
        if(!$redirect) $redirect=redirect_url($this->tizi_utype);

        $this->smarty->assign('settimeout',$settimeout);
        $this->smarty->assign('redirect',$redirect);
        $this->smarty->display($this->_smarty_dir.'404.html');
    }
}