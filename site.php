<?php

use \Colheita\Page;
use \Colheita\Model\Product;



$app->get('/', function() {//se a url for nada alem do .com, chama o page.php
//nisso, dentro do tpl, já tem o header, o index e o fotter prontos. Juntou os 3

	$products = Product::listAll();
    
	$page = new Page();

	$page->setTpl("index", [
		'products'=>Product::checkList($products)


	]);



});





?>