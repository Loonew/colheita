<?php

use \Colheita\Page;
use \Colheita\Model\Product;
use \Colheita\Model\Category;
use \Colheita\Model\Cart;



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


$app->get("/cart", function(){

	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);

});

$app->get("/cart/:idproduct/add", function($idproduct){

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();//se tem carrinho pronto, ou não, ele vai retornar um carrinho

	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	for ($i=0; $i < $qtd; $i++) { 
		
		$cart->addProduct($product);

	}

	header("Location: /cart");
	exit;

});


$app->get("/cart/:idproduct/minus", function($idproduct){//só tira um

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();//se tem carrinho pronto, ou não, ele vai retornar um carrinho

	$cart->removeProduct($product);//o all por padrão é false

	header("Location: /cart");
	exit;

});


$app->get("/cart/:idproduct/remove", function($idproduct){//tira todos

	$product = new Product();

	$product->get((int)$idproduct);

	$cart = Cart::getFromSession();//se tem carrinho pronto, ou não, ele vai retornar um carrinho

	$cart->removeProduct($product, true);//o all agora é true

	header("Location: /cart");
	exit;

});

$app->post("/cart/freight", function(){

	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']); //zipcode é o name do imput lá no cart.html

	header("Location: /cart");
	exit;

});


?>