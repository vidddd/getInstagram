<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once "vendor/autoload.php";
require_once "secret.php";
require_once "classes/class.instagram.php";

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$config['displayErrorDetails'] = true; $config['addContentLengthHeader'] = false;
$instagram = new Instagram();

$app = new \Slim\App(["settings" => $config]);
$app->add(new \RKA\SessionMiddleware(['name' => 'SessiongetInst']));
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer("templates/");
$session = new \RKA\Session();
$container['access_token'] = ''; $container['user'] = '';
$container['session'] = $session;

// PAGINA DE INICIO
$app->get('/', function (Request $request, Response $response) {
    $login = false;
    if($this->session->__isset('access_token')){
        $login = true;
    }
    return $this->view->render($response, "index.phtml", [ 'urllogin' => URL_LOGIN, 'login' => $login, 'username' => $this->session->get('username'), 'profile_picture' => $this->session->get('profile_picture') ]);
});

// LOGIN 
$app->get('/instalogin', function (Request $request, Response $response) {
   $url = "https://api.instagram.com/oauth/authorize/?client_id=".CLIENT_ID."&redirect_uri=".REDIRECT_URL."&response_type=code&scope=public_content";
   // envia cabeceras al login de instagram    
   return $response->withStatus(302)->withHeader('Location', $url);
});

$app->get('/logout', function (Request $request, Response $response) {
    \RKA\Session::destroy();
    $login = false;
    $results = Instagram::logout();
    return $this->view->render($response, "index.phtml", [ 'login' => $login, 'results' => $results ]);
});

// Despues de loguearme en Instagram en devuelve aqui con el codigo  http://your-redirect-uri?code=CODE
$app->get('/dos', function (Request $request, Response $response, $instagram) {
   // comprobamos que no ha sido error http://your-redirect-uri?error=access_denied&error_reason=user_denied&error_description=The+user+denied+your+request
   if($request->getAttribute('error')){
        $response = $this->view->render($response, "index.phtml", ["error" => $request->getAttribute('error_reason'), 'username' => $this->session->username, 'profile_picture' => $this->session->get('profile_picture') ]);
   }
   $code = $request->getParam('code');
   $data = Instagram::getInstagramAccessToken(CLIENT_ID,CLIENT_SECRET,REDIRECT_URL,$code);
   if($data->code == '400'){ // Error 
       $response = $this->view->render($response, "index.phtml", ["error" => true, 'error_type' => $data->error_type, 'error_reason' => $data->error_reason,  'username' => $this->session->get('username'), 'profile_picture' => $this->session->get('profile_picture') ]);
        $login = false;  $access_token = '';
   } else {
       $this->session->set('access_token', $data->access_token); $login = true; $access_token = $this->access_token;
       $user = Instagram::getUserSelf($this->session->get('access_token'));
       $this->session->set('username', $user['data']['username']);
       $this->session->set('profile_picture', $user['data']['profile_picture']);
   }
   $response = $this->view->render($response, "index.phtml",array('access_token' => $access_token, 'login' => $login, 'username' => $this->session->get('username'), 'profile_picture' => $this->session->get('profile_picture')));
   return $response;
});

// PULSAMOS GET POSTS !!!
$app->post('/busca', function (Request $request, Response $response) {
    $results = array(); 
    $tag = $request->getParam('tag'); 
    
    if($this->session->__isset('access_token')){     $login = true; 
        $access_token = $this->session->get('access_token');
    } else {
          return $response->withStatus(302)->withHeader('Location', '/');  
    } 

    $results = Instagram::getMediaTag($tag,$access_token);

    $response = $this->view->render($response, "index.phtml", array('access_token' => $access_token, 
                                                                        'login' => $login, 
                                                                        'results' => $results,
                                                                        'tag' => $tag, 'username' => $this->session->get('username'), 'profile_picture' => $this->session->get('profile_picture')));
    return $response;
});

$app->run();