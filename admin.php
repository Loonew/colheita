<?php

use \Colheita\PageAdmin;
use \Colheita\Model\User;



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




?>