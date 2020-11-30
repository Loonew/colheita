<?php

use \Colheita\Page;
use \Colheita\Model\Product;
use \Colheita\Model\Category;
use \Colheita\Model\Cart;
use \Colheita\Model\Address;
use \Colheita\Model\User;



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

$app->get("/checkout", function(){

	User::verifyLogin(false);

	$cart = Cart::getFromSession();

	$address = new Address();

	$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);


});


$app->get("/login", function(){

	$page = new Page();

	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);


});


$app->post("/login", function(){

	try{

	User::login($_POST['login'], $_POST['password']); //tive que fazer o User::login para criar um novo "login" por que ele é static

	} catch(Exception $e){

		User::setError($e->getMessage());

	}

	header("Location: /checkout");
	exit;

});

$app->get("/logout", function(){

	User::logout();

	header("Location: /login");
	exit;

});

$app->post("/register", function(){

	//durante a sessão, salva o que estiver nos campos
	$_SESSION['registerValues'] = $_POST;

	//se não for definido, ou igual a vazio
	if (!isset($_POST['name']) || $_POST['name'] == ''){

		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;

	}

	if (!isset($_POST['email']) || $_POST['email'] == ''){

		User::setErrorRegister("Preencha o seu email.");
		header("Location: /login");
		exit;

	}

	if (!isset($_POST['password']) || $_POST['password'] == ''){

		User::setErrorRegister("Preencha a senha.");
		header("Location: /login");
		exit;

	}


	if (User::checkLoginExist($_POST['email']) === true){

		User::setErrorRegister("Este endereço de e-mail já está em uso.");
		header("Location: /login");
		exit;

	}




	$user = new User();

	$user->setData([
		'inadmin'=>0,//se tá cadastrando, o user não é o admin
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);

	$user->save();

	User::login($_POST['email'], $_POST['password']);//depois de cadastrar, já loga usando os campos do cadastro

	header('Location: /checkout');
	exit;

});


?>