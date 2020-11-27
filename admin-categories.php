<?php

use \Colheita\Page;
use \Colheita\PageAdmin;
use \Colheita\Model\User;
use \Colheita\Model\Category;
use \Colheita\Model\Product;

$app->get('/admin/categories', function(){

		User::verifyLogin();

		$categories = Category::listALL();

		$page = new PageAdmin();

		$page->setTpl ("categories", [
		'categories'=>$categories

		]);

});
$app->get('/admin/categories/create', function(){

		User::verifyLogin();

		$page = new PageAdmin();

		$page->setTpl ("categories-create");

});


$app->post('/admin/categories/create', function(){

		User::verifyLogin();

		$category = new Category();

		$category->setData($_POST); //seta o que vier pelo post

		$category->save();

		header('Location: /admin/categories');
		exit;

});


$app->get('/admin/categories/:idcategory/delete', function($idcategory){

		User::verifyLogin();

		$category = new Category();

		$category->get((int)$idcategory);

		$category->delete();

		header('Location: /admin/categories');
		exit;

});

$app->get('/admin/categories/:idcategory', function($idcategory){

		User::verifyLogin();

		$category = new Category();

		$category->get((int)$idcategory);

		$page = new PageAdmin();

		$page->setTpl ("categories-update", [
			'category'=>$category->getvalues()
		]);

});

$app->post('/admin/categories/:idcategory', function($idcategory){

		User::verifyLogin();

		$category = new Category();

		$category->get((int)$idcategory);

		$category->setData($_POST);

		$category->save();

		header('Location: /admin/categories');
		exit;

});


$app->get("/admin/categories/:idcategory/products", function($idcategory){

		User::verifyLogin();

		$category = new Category();

		$category->get((int)$idcategory);


		$page = new PageAdmin();

		$page->setTpl("categories-products",[ 
			'category'=>$category->getValues(),
			'productsRelated'=>$category->getProducts(),
			'productsNotRelated'=>$category->getProducts(false)
		]);
});

$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){

		User::verifyLogin();

		$category = new Category();

		$category->get((int)$idcategory);

		$product = new Product();

		$product->get((int)$idproduct);

		$category->addProduct($product);

		header("Location: /admin/categories/".$idcategory."/products");
		exit;

});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){

		User::verifyLogin();

		$category = new Category();

		$category->get((int)$idcategory);

		$product = new Product();

		$product->get((int)$idproduct);

		$category->removeProduct($product);

		header("Location: /admin/categories/".$idcategory."/products");
		exit;

});


?>