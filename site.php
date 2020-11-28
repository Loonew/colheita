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

		$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
		//se estiver setada a page, é a page em int, senão é a primeira

		$category = new Category();

		$category->get((int)$idcategory);

		$pagination = $category->getProductsPage($page);

		$pages = [];

		for ($i=1; $i <= $pagination['pages']; $i++) { 
			array_push($pages, [
				'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
				'page'=>$i
			]);
		}

		$page = new Page();

		$page->setTpl("category",[ 
			'category'=>$category->getValues(),
			'products'=>$pagination["data"], //data tem a checagem em lista do select calcFoundRows no getProductPage 
			'pages'=>$pages
		]);

});

$app->get('/products/:desurl', function($desurl){

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [

		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()

	]);

});







?>