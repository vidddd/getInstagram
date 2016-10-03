<?php
require_once "vendor/autoload.php";
use Guzzle\Http\Client;
use Guzzle\Http\EntityBody;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
  //  'cache' => 'templates/cache',
));

echo $twig->render('index.twig', array('nombre' => ''));