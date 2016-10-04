<?php
//ini_set('display_erros', 'On');error_reporting(E_ALL);phpinfo();
require_once "vendor/autoload.php";require 'vendor/autoload.php';
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
    $data = array();
    $client = new Client();
    // Devuelve el formulario de login de Instagram
    $res = $client->request('GET', 'https://api.instagram.com/oauth/authorize', ['query' => [
                    'client_id' => CLIENT_ID,
                    'redirect_uri' => REDIRECT_URL,
                    'response_type' => 'code',
                    'scope' => 'public_content'
                    ]]);
    if($res->getStatusCode()=='200') {
        echo $res->getBody();
    } else {
        return $this->view->render($response, "index.phtml", ["tickets" => ""]);
    }
});


// Despues de loguearme en Instagram en devuelve aqui con el codigo  http://your-redirect-uri?code=CODE
$app->get('/dos', function (Request $request, Response $response) {

   if($request->getAttribute('erro')){
        $response = $this->view->render($response, "index.phtml", ["error" => $request->getAttribute('error_reason') ]);
   }
   $access_token = $request->getAttribute('access_token');
   echo "333".$code = $request->getAttribute('code'); die;
    
   $response = $this->view->render($response, "index.phtml");
   return $response;
});

/*
$app->post('/busca', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $dataa = [];
    $dataa['tags'] = filter_var($data['tags'], FILTER_SANITIZE_STRING);
    $dataa['username'] = filter_var($data['username'], FILTER_SANITIZE_STRING);
    
   $response = $this->view->render($response, "index.phtml", ["ok" => "222222"]);

    return $response;
});
*/

$app->run();
