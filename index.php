<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Colheita\Page;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {//se a url for nada alem do .com, chama o page.php
//nisso, dentro do tpl, já tem o header, o index e o fotter prontos. Juntou os 3
    
	$page = new Page();

	$page->setTPL("index");



});

$app->run(); //essa linha diz "tudo carregado? Sim? Roda"

 ?>