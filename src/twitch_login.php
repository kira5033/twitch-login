<?php

namespace src;

class Twitch_Login {

    private $APP_ID;

    private $APP_SECRET;

    private $userId;

    private $userEmail;

    private $userLoginName;

    private $userDisplayName;

    private $userPicture;

    private $userAccessToken;

    private $userIdToken;

    private $jwtData;

    private $response;

    public function __construct($ID, $SECRET){
        $this->APP_ID = $ID;
        $this->APP_SECRET = $SECRET;
    }

    /**
     * Create OAuth Link
     * @param $callback
     * @param $state
     * @return string
     */
    public function createOAuthUrl($callback, $state){

        $parameter = [
            'response_type' => 'code',
            'client_id'     => $this->APP_ID,
            'state'         => $state,
            'redirect_uri'  => $callback
        ];

        $scope = 'openid%20user:read:email%20channel:manage:redemptions%20channel:read:redemptions%20chat:edit%20chat:read';

        $claims = '{"id_token":{"email":null,"email_verified":null},"userinfo":{"picture":null,"preferred_username":null,"updated_at":null,"email":null,"email_verified":null}}';

        return 'https://id.twitch.tv/oauth2/authorize?'.http_build_query($parameter).'&scope='.$scope.'&claims='.$claims;
    }

    /**
     * Handle OAuth Callback Data
     * @return $this
     */
    public function catchResponse(){

        $this->response = [];

        if(http_response_code() == 200){

            if(isset($_GET['code'])){
                $this->response = [
                    'success'           => TRUE,
                    'code'              => $_GET['code'],
                    'state'             => (isset($_GET['state'])) ? $_GET['state'] : ''
                ];
            }else if(isset($_GET['error'])){
                $this->response = [
                    'success'   => FALSE,
                    'code'      => $_GET['error'],
                    'state'     => (isset($_GET['state'])) ? $_GET['state'] : '',
                    'error_msg' => (isset($_GET['error_description'])) ? $_GET['error_description'] : '',
                ];
            }
        }

        return $this;
    }

    /**
     * Get access token and ID token , then get user profile to check
     * @param $redirect
     * @param $state
     * @return bool
     */
    public function Authorization($redirect, $state = ''){

        if( empty($redirect) ||
            empty($this->response) ||
            !$this->response['success'] ||
            $this->response['state'] != $state
        )
        {
            return false;
        }

        $header = [
            'Content-Type: application/x-www-form-urlencoded'
        ];
        $content = [
            'grant_type'    => 'authorization_code',
            'code'          => $this->response['code'],
            'redirect_uri'  => $redirect,
            'client_id'     => $this->APP_ID,
            'client_secret' => $this->APP_SECRET,
        ];

        $result = $this->sendRequest('post', $header, "https://id.twitch.tv/oauth2/token", $content);

        if(empty($result->access_token)){
            return FALSE;
        }

        $this->setUserIdToken($result->id_token);

        $this->setUserAccessToken($result->access_token);

        return $this->getUserInfoEndpoint($result->access_token);
    }

    /**
     * Refer From Authorization function
     * It will set profile data to params when callback's data is not empty.
     * @param $access_token
     * @return bool
     */
    public function getUserInfoEndpoint($access_token){

        $header = [
            "content-type: application/x-www-form-urlencoded",
            "charset=UTF-8",
            'Authorization: Bearer ' . $access_token,
        ];

        $result = $this->sendRequest('get', $header, "https://id.twitch.tv/oauth2/userinfo");


        if(!isset($result->sub)){
            $this->jwtData = null;
            return false;
        }else{
            $this->setJwtData($result);
            $this->setUserId($result->sub);
            $this->setUserEmail($result->email);
            $this->setUserPicture($result->picture);
            $this->setUserDisplayName($result->preferred_username);
            return true;
        }

    }

    /**
     * @param $token
     * @param $user_id
     * @return bool
     */
    public function getUser($token, $user_id){

        $header = [
            "content-type: application/x-www-form-urlencoded",
            "charset=UTF-8",
            'Authorization: Bearer ' . $token,
            'Client-Id: ' . $this->APP_ID,
        ];

        $result = $this->sendRequest('get', $header, "https://api.twitch.tv/helix/users?id=" . $user_id);

        if(isset($result->data[0]->id)){

            $this->setUserLoginName($result->data[0]->login);

            return true;
        }else{
            return false;
        }

    }

    /**
     * Logout LINE
     * @param $access_token
     * @return bool
     */
    public function revoke($access_token){

        $header = [
            "Content-Type: application/x-www-form-urlencoded",
        ];

        $content = [
            'access_token'  => $access_token,
            'client_id'     => $this->APP_ID,
        ];

        $result = $this->sendRequest('post',$header, 'https://id.twitch.tv/oauth2/revoke', $content);

        return http_response_code() == 200;
    }

    private function sendRequest($method, $header, $url, $content = array()){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(strtolower($method) == 'post'){
            curl_setopt($ch, CURLOPT_POST, true);
        }
        if(!empty($content)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content));
        }

        $result = curl_exec($ch);
        $curl_response = curl_getinfo($ch);
        $result = json_decode($result);
        curl_close($ch);

        if ( isset($curl_response['http_code']) && $curl_response['http_code'] != 200 )
        {
            return FALSE;
        }

        return $result;

    }

    /**
     * @param mixed $userId
     */
    private function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param mixed $userLoginName
     */
    private function setUserLoginName($userLoginName)
    {
        $this->userLoginName = $userLoginName;
    }

    /**
     * @param mixed $userDisplayName
     */
    private function setUserDisplayName($userDisplayName)
    {
        $this->userDisplayName = $userDisplayName;
    }

    /**
     * @param mixed $userPicture
     */
    private function setUserPicture($userPicture)
    {
        $this->userPicture = $userPicture;
    }

    /**
     * @param mixed $userAccessToken
     */
    private function setUserAccessToken($userAccessToken)
    {
        $this->userAccessToken = $userAccessToken;
    }

    /**
     * @param mixed $userIdToken
     */
    private function setUserIdToken($userIdToken)
    {
        $this->userIdToken = $userIdToken;
    }

    /**
     * @param mixed $jwtData
     */
    private function setJwtData($jwtData)
    {
        $this->jwtData = $jwtData;
    }

    /**
     * @param mixed $userEmail
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getUserLoginName()
    {
        return $this->userLoginName;
    }

    /**
     * @return mixed
     */
    public function getUserDisplayName()
    {
        return $this->userDisplayName;
    }

    /**
     * @return mixed
     */
    public function getUserPicture()
    {
        return $this->userPicture;
    }

    /**
     * @return mixed
     */
    public function getUserAccessToken()
    {
        return $this->userAccessToken;
    }

    /**
     * @return mixed
     */
    public function getUserIdToken()
    {
        return $this->userIdToken;
    }

    /**
     * @return mixed
     */
    public function getJwtData()
    {
        return $this->jwtData;
    }

    /**
     * @return mixed
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

}
