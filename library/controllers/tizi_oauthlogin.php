<?php
if(!defined('BASEPATH'))exit('No direct script access allowed');
require_once "tizi_login.php";

class Tizi_Oauthlogin extends Tizi_Login {

    function __construct()
    {
        parent::__construct();

        $this->load->model("login/login_model");
        $this->load->model("login/session_model");
    }

    public function oauth()
    {
        /*platform*/
        $platform = 'qq';
        isset($_GET['type']) && $platform = $_GET['type'];

        $oauth_redirect=$this->input->get('redirect',true);
        if($oauth_redirect) $this->session->set_userdata('oauth_redirect',$oauth_redirect);

        $this->load->library('Oauth');
        try{
            $this->oauth->init($platform);
            $this->oauth->login();
        }catch(OauthException $e){
            //exit($e->getMessage());
            show_error($e->getMessage());
        }

    }

    public function callback($platform)
    {
        $this->load->library('Oauth');
        try{
            $this->oauth->init($platform);
            $data = $this->oauth->callback();//data = array('open_id'=>'','access_token'=>'');

            $this->load->model('oauth/oauth_model');
            if($platform == 'qq'){
                $platform = 1;
            }elseif($platform == 'weibo'){
                $platform = 2;
            }
            $db_data=array(
                'open_id'=>$data['open_id'],
                'platform'=>$platform,
                'access_token'=>$data['access_token'],
            );

            $oauth_redirect='';
            if($db_data['open_id']){
                $user_auth_data = $this->oauth_model->save($db_data);
                $this->oauth_model->save($db_data['open_id'], $platform, $db_data);

                $oauth_redirect=$this->session->userdata('oauth_redirect');
                if(empty($user_auth_data['user_id'])){//未绑定用户
                    $this->session->set_userdata("oauth_id", $user_auth_data["oauth_id"]);
    				//redirect(login_url("login/perfect/role"));

                    if(stripos($oauth_redirect,'http://')!==false)
                    {
                        $this->session->set_userdata('perfect_redirect',$oauth_redirect);
                    }

                    $oauth_redirect=login_url("login/perfect/role?platform={$platform}");
                }else{//绑定用户
    				$session=$this->session_model->generate_session($user_auth_data["user_id"]);
                    $this->session_model->generate_cookie($db_data['open_id'],$user_auth_data["user_id"]);
    				$this->session_model->clear_mscookie();
                    //redirect(redirect_url($session['user_data']['user_type'],'login'));
                    //if(!$oauth_redirect) $oauth_redirect=redirect_url($session['user_data']['user_type'],'login');
                    $oauth_redirect=$this->get_redirect($session['user_data']['user_type'],$session['user_data'],'login',$oauth_redirect);
                }
            }
            $this->smarty->assign('oauth_redirect',$oauth_redirect);
            $this->smarty->display('file:[lib]header/tizi_oauth.html');

        }catch(OauthException $e){
            //exit($e->getMessage());
            show_error($e->getMessage());
        }

    }

}
/*Endoffilelogin.php*/
/*Location:./application/controllers/login/login.php*/