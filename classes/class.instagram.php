<?php
require_once "session.class.php";

class Instagram
{
    public $username; // Instagram username
    public $password; // Instagram password
    public $session;
    public $access_token; 
    
    public function __construct()
    { 
        $this->session = new Session();
        $this->session->create_session('getinst',true);
    }

    public function setUser($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    
    public static function getInstagramAccessToken($client_id, $client_secret, $redirect_url, $code) {
          
            $fields = array(
                  'client_id'     => $client_id,
                  'client_secret' => $client_secret,
                  'grant_type'    => 'authorization_code',
                  'redirect_uri'  => $redirect_url,
                  'code'          => $code
               );

           $url = 'https://api.instagram.com/oauth/access_token';
           $ch = curl_init();
           curl_setopt($ch, CURLOPT_URL, $url);
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
           curl_setopt($ch, CURLOPT_TIMEOUT, 20);
           curl_setopt($ch,CURLOPT_POST,true); 
           curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
           $result = curl_exec($ch);
           curl_close($ch); 
           $data = json_decode($result);
          // print_r($data); die;
           return $data; 
    }
    
    public static function getUserSelf($access_token){
         $client = new GuzzleHttp\Client();

        if($access_token != '') {
            $res = $client->get("https://api.instagram.com/v1/users/self/?access_token={$access_token}"); 
            $results =  json_decode($res->getBody()->getContents(), true);
        }
        /*
         * {
                "data": {
                    "id": "1574083",
                    "username": "snoopdogg",
                    "full_name": "Snoop Dogg",
                    "profile_picture": "http://distillery.s3.amazonaws.com/profiles/profile_1574083_75sq_1295469061.jpg",
                    "bio": "This is my bio",
                    "website": "http://snoopdogg.com",
                    "counts": {
                        "media": 1320,
                        "follows": 420,
                        "followed_by": 3410
                    }
            }
         */
        return $results;
    }
    
    public static function getMediaTag($tag, $access_token){
        $client = new GuzzleHttp\Client();
        $tag = filter_var ( $tag, FILTER_SANITIZE_URL);
        if($tag != '') {
            $res = $client->get("https://api.instagram.com/v1/tags/{$tag}/media/recent?access_token={$access_token}"); 
            $results =  json_decode($res->getBody()->getContents(), true);
        } 
        return $results;
    }
}