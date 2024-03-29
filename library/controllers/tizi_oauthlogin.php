<?php  if(!defined('BASEPATH'))exit('No direct script access allowed');
require_once(__DIR__.DIRECTORY_SEPARATOR."tizi_controller.php");

class Tizi_Oauthlogin extends Tizi_Controller {

    function __construct()
    {

        parent::__construct();
        $this->load->model("login/login_model");
        $this->load->model("login/session_model");
        $this->load->model('oauth/oauth_model');

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
        try{

            $platform_code = Constant::oauth_platform($platform);
            $this->load->library('Oauth');
            $this->oauth->init($platform);

            if($platform_code != 3){

                $data = $this->oauth->callback();//data = array('open_id'=>'','access_token'=>'');

            }else{

                $data = $this->oauth->wx_user_detail();//微信

            }

            $db_data=array(
                'open_id'=>$data['open_id'],
                'platform'=>$platform_code,
                'access_token'=>$data['access_token']
            );

            $oauth_redirect='';
            
            if(!empty($db_data['open_id'])){

                $user_auth_data = $this->oauth_model->save($db_data);
                $oauth_redirect=$this->session->userdata('oauth_redirect');
                if(empty($user_auth_data['user_id'])){//未绑定用户
                    
                    $this->session->set_userdata("sso_t", Constant::LOGIN_SSO_TYPE_OAUTH);
                    $this->session->set_userdata("sso_id", $user_auth_data["oauth_id"]);

                    if(stripos($oauth_redirect,'http://')!==false)
                    {
                        $this->session->set_userdata('perfect_redirect',$oauth_redirect);
                    }

                    $oauth_redirect=login_url("sso/role?platform=".$platform_code);
                }else{//绑定用户
                    
                    $session=$this->session_model->generate_session($user_auth_data["user_id"]);
                    $this->session_model->generate_cookie($db_data['open_id'],$user_auth_data["user_id"]);
    				$this->session_model->clear_mscookie();
                    $oauth_redirect=$this->get_redirect($session['user_data']['user_type'],$session['user_data'],'login',$oauth_redirect);
                }

            }

            if($this->tizi_mobile)
            {
                if(!empty($oauth_redirect)) redirect($oauth_redirect);
                exit('Auth access is not allowed');
            }
            else
            {
                $this->smarty->assign('oauth_redirect',$oauth_redirect);
                $this->smarty->display('file:[lib]common/tizi_oauth.html');
            }
        }catch(OauthException $e){
            //exit($e->getMessage());
            show_error($e->getMessage());
        }

    }





}
/*Endoffilelogin.php*/
/*Location:./application/controllers/login/login.php*/
