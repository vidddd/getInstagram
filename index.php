<?php
ini_set('display_erros', 'On');error_reporting(E_ALL ^ E_NOTICE);
require_once "vendor/autoload.php";
require_once "secret.php";

use GuzzleHttp\Client;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer("templates/");

// PAGINA DE INICIO
$app->get('/', function (Request $request, Response $response) {
    
    return $this->view->render($response, "index.phtml", [ 'urllogin' => URL_LOGIN ]);

});

$app->get('/instalogin', function (Request $request, Response $response) {

   $url = "https://api.instagram.com/oauth/authorize/?client_id=".CLIENT_ID."&redirect_uri=".REDIRECT_URL."&response_type=code";
   // envia cabeceras al login de instagram    
   return $response->withStatus(302)->withHeader('Location', $url);
});


// Despues de loguearme en Instagram en devuelve aqui con el codigo  http://your-redirect-uri?code=CODE
$app->get('/dos', function (Request $request, Response $response) {
   $data = array();
   // comprobamos que no ha sido error http://your-redirect-uri?error=access_denied&error_reason=user_denied&error_description=The+user+denied+your+request
   if($request->getAttribute('error')){
        $response = $this->view->render($response, "index.phtml", ["error" => $request->getAttribute('error_reason') ]);
   }
   $code = $request->getParam('code');
        $fields = array(
              'client_id'     => CLIENT_ID,
              'client_secret' => CLIENT_SECRET,
              'grant_type'    => 'authorization_code',
              'redirect_uri'  => REDIRECT_URL,
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
       $access_token = $data->access_token; 
       $_SESSION['instagram_access_token'] = $access_token;
   $response = $this->view->render($response, "index.phtml",array('access_token' => $access_token, 'urllogin' => URL_LOGIN));
   return $response;
});

// PULSAMOS GET POSTS !!!
$app->post('/busca', function (Request $request, Response $response) {
    $access_token = $request->getParam('access_token');
    //    $app->get('/tags/search', function() use($app, $client, $access_token) {
    $client = new GuzzleHttp\Client();
    $tag = 'tbwa'; 
    $mediaid='tsxp1hhQTG'; $uid = '12693727';
    $res = $client->get("https://api.instagram.com/v1/tags/{$tag}/media/recent?access_token={$access_token}");
    //$res = $client->get("https://api.instagram.com/v1/media/shortcode/$mediaid?access_token=$access_token");
    
   // $res = $client->get("https://api.instagram.com/v1/users/$uid/?access_token=$access_token");
    $results =  json_decode($res->getBody()->getContents(), true);

    $response = $this->view->render($response, "index.phtml", array('access_token' => $access_token, 'urllogin' => URL_LOGIN, "results" => $results));
    return $response;
});

$app->run();
