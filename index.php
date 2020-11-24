<?php 
session_start();
require_once("vendor/autoload.php");
require_once("functions.php");

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

$app->get('/admin/users', function() {

	User::verifyLogin();

	$users = User::listALL();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users
	));

});

$app->get("/admin/users/create", function() {

	User::verifyLogin();
	$page = new PageAdmin ();
	$page->setTpl ( "users-create" );


});

$app->get("/admin/users/:iduser/delete", function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

});

$app->get('/admin/users/:iduser', function($iduser){
		User::verifyLogin();

		$user = new User();
	    $user->get((int)$iduser);
		$page = new PageAdmin();
		$page ->setTpl("users-update", array(
	        "user"=>$user->getValues()
	    ));
	});



$app->post("/admin/users/create", function() {

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0; //se o inadmin estiver marcado, seu valor é 1, senão 0

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;
});

$app->post('/admin/users/:iduser', function($iduser){
		User::verifyLogin();

		$user = new User();

		$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	    $user->get((int)$iduser);

		$user->setData($_POST);

		$user->update();

		header("Location: /admin/users");
		exit;
});


$app->run(); //essa linha diz "tudo carregado? Sim? Roda"

 ?>