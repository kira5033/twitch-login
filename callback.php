<?php

ini_set('display_errors','1');
error_reporting(E_ALL);

require_once('src/twitch_login.php');
require_once('src/config.php');

use src\Twitch_Login;

isset($_SESSION) or session_start();

class Callback_Controller {

    function __construct()
    {
        $this->main();
    }

    function main(){

        $TwitchProvider = new Twitch_Login(CLIENT_ID,CLIENT_SECRET);

        $state = (isset($_SESSION['state']) && $_SESSION['state'] != '') ? $_SESSION['state'] : '';

        $result = $TwitchProvider->catchResponse()->Authorization(CALLBACK_URL, $state);

        if($result){

            $_SESSION['id_token'] = $TwitchProvider->getUserIdToken();
            $_SESSION['access_token'] = $TwitchProvider->getUserAccessToken();
            $_SESSION['user_id'] = $TwitchProvider->getUserId();

        }

        header('Location: /');

    }

}

new Callback_Controller();

