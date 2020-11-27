<?php

use \Colheita\Page;
use \Colheita\Model\Product;
use \Colheita\Model\Category;



$app->get('/', function() {//se a url for nada alem do .com, chama o page.php
//nisso, dentro do tpl, já tem o header, o index e o fotter prontos. Juntou os 3

	$products = Product::listAll();
    
	$page = new Page();

	$page->setTpl("index", [
		'products'=>Product::checkList($products)


	]);

});

$app->get('/categories/:idcategory', function($idcategory){

		$category = new Category();

		$category->get((int)$idcategory);

		$page = new Page();

		$page->setTpl("category",[ 
			'category'=>$category->getValues(),
			'products'=>Product::checkList($category->getProducts())
		]);

});





?>