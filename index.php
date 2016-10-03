<?php
require_once "vendor/autoload.php";require 'vendor/autoload.php';
use GuzzleHttp\Client;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

define("CLIENT_ID", "4df767750d944ea4a0025a2523f249c4");
define("CLIENT_SECRET", "ed187ab8b6504093bd2bb4049fec8aad");
define("REDIRECT_URL", "https://getinst.promofb.es/dos");
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
                    'response_type' => 'code'
                    ]]);
       
       //print_r($res->getHeader('content-type')); // text/html
       echo $res->getBody();
    
    $response = $this->view->render($response, "index.phtml", ["tickets" => ""]);
    return $response;
});

// Despues de loguearme en Instagram en devuelve aqui con el codigo  http://your-redirect-uri?code=CODE
$app->get('/dos', function (Request $request, Response $response) {
    echo "CODE".$app->request->get('code'); die;
    
    /* 

    if($app->request->get('code')){

        $code = $app->request->get('code');

        $response = $client->post('https://api.instagram.com/oauth/authorize', array('body' => array(
            'client_id' => CLIENT_ID,
            'redirect_uri' => REDIRECT_URL,
            'response_type' => 'code'
        )));

        $data = $response->json();

    }else{

        $login_url = "https://api.instagram.com/oauth/authorize?client_id={$client_id}&redirect_uri={$redirect_url}&scope=basic&response_type=code";

    }


    $app->render('home.php', array('data' => $data, 'login_url' => $login_url));*/
    
    $response = $this->view->render($response, "index.phtml", ["tickets" => ""]);
    return $response;
});


$app->post('/busca', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $dataa = [];
    $dataa['tags'] = filter_var($data['tags'], FILTER_SANITIZE_STRING);
    $dataa['username'] = filter_var($data['username'], FILTER_SANITIZE_STRING);
    
   $response = $this->view->render($response, "index.phtml", ["ok" => "222222"]);

    return $response;
});
$app->run();
/*
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
  //  'cache' => 'templates/cache',
));

echo $twig->render('index.twig', array('nombre' => ''));*/