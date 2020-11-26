<?php 
session_start();
require_once("vendor/autoload.php");


use \Slim\Slim;
use \Colheita\Page;
use \Colheita\PageAdmin;
use \Colheita\Model\User;
use \Colheita\Model\Category;

$app = new Slim();

$app->config('debug', true);

require_once("functions.php");
require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");


$app->run(); //essa linha diz "tudo carregado? Sim? Roda"

 ?>