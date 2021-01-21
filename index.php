<?php

require_once('src/twitch_login.php');
require_once('src/config.php');

use src\Twitch_Login;

isset($_SESSION) or session_start();

class LINE_Controller {

    protected $act;

    function __construct()
    {
        $this->main();
    }

    function main(){

        $this->act = (isset($_REQUEST['act']) && $_REQUEST['act'] != '') ? $_REQUEST['act'] : '';

        switch ($this->act){

            case "oauth":
                $this->oauth();
                break;
            case "login":
                $this->login();
                break;
            case "logout":
                $this->logout();
                break;
            default:
                $this->index();
        }

    }

    function index(){

        $access_token = (isset($_SESSION['access_token']) && $_SESSION['access_token'] != '') ? $_SESSION['access_token'] : '';
        $id_token = (isset($_SESSION['id_token']) && $_SESSION['id_token'] != '') ? $_SESSION['id_token'] : '';
        $user_id = (isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') ? $_SESSION['user_id'] : '';

        $TwitchProvider = new Twitch_Login(CLIENT_ID,CLIENT_SECRET);

        if(!$TwitchProvider->getUserInfoEndpoint($access_token)){

            $this->login();

        }else{

            $TwitchProvider->getUser($access_token,$user_id);


            include_once dirname(__FILE__) . '/view.php';
        }

    }

    function login(){

        include_once dirname(__FILE__) . '/login.php';

    }

    function oauth(){
        $TwitchProvider = new Twitch_Login(CLIENT_ID,CLIENT_SECRET);

        $_SESSION['state'] = md5(time());

        header('Location: '.$TwitchProvider->createOAuthUrl(CALLBACK_URL, $_SESSION['state']));
    }

    function logout(){

        $access_token = (isset($_SESSION['access_token']) && $_SESSION['access_token'] != '') ? $_SESSION['access_token'] : '';

        $TwitchProvider = new Twitch_Login(CLIENT_ID,CLIENT_SECRET);

        $TwitchProvider->revoke($access_token);

        unset($_SESSION['access_token']);
        unset($_SESSION['id_token']);

        header('Location: /');
    }

}

new LINE_Controller();

