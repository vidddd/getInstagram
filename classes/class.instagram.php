<?php
require_once "session.class.php";
use GuzzleHttp\Exception\ClientException;

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
                  'code'          => $code,
                  'scope'         => 'public_content'
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
    
    /**
     * Trans el login obteneos el scope necesario public_content y nos devuelve el access token correspondiente
     */
    public static function getOauthCodeScope($client_id, $client_secret, $redirect_url, $access_token){
        $client = new GuzzleHttp\Client();
        try {
                $res = $client->request("GET","https://api.instagram.com/oauth/authorize/?client_id=$client_id&redirect_uri=$redirect_url&response_type=code&scope=SCOPE"); 
                $con = $res->getBody()->getContents();
                $results =  json_decode($con, true);
        } catch (ClientException $e) {
                $res['is_error'] = true;
                $con = $e->getResponse()->getBody()->getContents();
                $results = array_merge($results =  json_decode($con, true), $res);
        }
        return $results; 
    }
    
    public static function getUserSelf($access_token){
        $client = new GuzzleHttp\Client();

        if($access_token != '') {
            $res = $client->get("https://api.instagram.com/v1/users/self/?access_token={$access_token}"); 
            $con = $res->getBody()->getContents();
            $results =  json_decode($con, true);
        }
                                /* {
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
        $tag = Instagram::sanitize($tag); $res = null;
        try {
            if($tag != '') {
                $res = $client->request("GET","https://api.instagram.com/v1/tags/{$tag}/media/recent?access_token={$access_token}"); 
                $con = $res->getBody()->getContents();
                $results =  json_decode($con, true);
            } 

        } catch (ClientException $e) {
            // To catch exactly error 400 use 
           // if ($e->getResponse()->getStatusCode() == '400') {
                $res['is_error'] = true;
                $con = $e->getResponse()->getBody()->getContents();
                $results = array_merge($results =  json_decode($con, true), $res);
            //}
        }
        return $results; 
    }
    
    public static function logout(){
           $client = new GuzzleHttp\Client(); $res = null;
           try {
                $res = $client->request("GET","https://instagram.com/accounts/logout/"); 
                $con = $res->getBody()->getContents();
                $results =  json_decode($con, true);

            } catch (ClientException $e) {

                $con = $e->getResponse()->getBody()->getContents();
                $results = array_merge($results =  json_decode($con, true), $res);
        }
        return $results;
    }
    
    /**
    * Function: sanitize
    * Returns a sanitized string, typically for URLs.
    *
    * Parameters:
    *     $string - The string to sanitize.
    *     $force_lowercase - Force the string to lowercase?
    *     $anal - If set to *true*, will remove all non-alphanumeric characters.
    */
   public static function sanitize($string, $force_lowercase = true, $anal = false) {
       $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                      "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                      "â€”", "â€“", ",", "<", ".", ">", "/", "?");
       $clean = trim(str_replace($strip, "", strip_tags($string)));
       $clean = preg_replace('/\s+/', "-", $clean);
       $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
       return ($force_lowercase) ?
           (function_exists('mb_strtolower')) ?
               mb_strtolower($clean, 'UTF-8') :
               strtolower($clean) :
           $clean;
   }
}