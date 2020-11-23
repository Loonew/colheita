<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Colheita\Page;
use \Colheita\PageAdmin;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {//se a url for nada alem do .com, chama o page.php
//nisso, dentro do tpl, já tem o header, o index e o fotter prontos. Juntou os 3
    
	$page = new Page();

	$page->setTpl("index");



});

$app->get('/admin', function() {//se a url além do .com tiver um '/admin', redireciona para o pageAdmin.php
    
	$page = new PageAdmin();

	$page->setTpl("index");



});

$app->run(); //essa linha diz "tudo carregado? Sim? Roda"

 ?>