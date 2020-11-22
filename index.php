<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Colheita\Page;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();

	$page->setTPL("index");



});

$app->run(); //essa linha diz "tudo carregado? Sim? Roda"

 ?>