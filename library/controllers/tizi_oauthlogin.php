<?php
if(!defined('BASEPATH'))exit('Nodirectscriptaccessallowed');

class Tizi_Oauthlogin extends MY_Controller{

    function __construct()
    {
        parent::__construct();

        $this->load->model("login/login_model");
        $this->load->model("login/session_model");
    }

    public function index()
    {
        /*platform*/
        $platform = 'qq';
        isset($_GET['platform']) && $platform = $_GET['platform'];

        $this->load->library('Oauth');
        try{
            $this->oauth->init($platform);
            $this->oauth->login();

        }catch(OauthException$e){
            exit($e->getMessage());
        }

    }

    public function callback($platform = 1)
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
            //$user_auth_data = array('oauth_id'=>'','user_id'=>'');
            $user_auth_data = $this->oauth_model->save($db_data);
            $this->oauth_model->save($open_id, $platform, $db_data);

            if(empty($user_auth_data['user_id'])){//未绑定用户
                
            }else{//绑定用户
            
            }

        }catch(OauthException $e){
            exit($e->getMessage());
        }

    }

}
/*Endoffilelogin.php*/
/*Location:./application/controllers/login/login.php*/
