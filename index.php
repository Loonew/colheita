<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Colheita\Page;
use \Colheita\PageAdmin;
use \Colheita\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {//se a url for nada alem do .com, chama o page.php
//nisso, dentro do tpl, já tem o header, o index e o fotter prontos. Juntou os 3
    
	$page = new Page();

	$page->setTpl("index");



});

$app->get('/admin', function() {//se a url além do .com tiver um '/admin', redireciona para o pageAdmin.php
    User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");



});

$app->get('/admin/login', function() {
    
	$page = new PageAdmin([
	"header"=>false,
	"header"=>false
	//desabilitando o cabeçalho e o rodapé da pagina de login
	]);

	$page->setTpl("login");

});

$app->post('/admin/login', function() {
    
	User::login($_POST["deslogin"], $_POST["despassword"]);

	header("Location: /admin");
	exit;

});

$app->get('/admin/logout', function() {
    
	User::logout();

	header("Location: /admin/login");
	exit;

});


$app->run(); //essa linha diz "tudo carregado? Sim? Roda"

 ?>