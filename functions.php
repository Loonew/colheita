<?php 

use \Colheita\Model\User;

function formatPrice(float $vlprice){ //força o vlprice a ser um float

	return number_format($vlprice, 2, ",", ".");//primeiro separador vai ser virgula, o de casa milhar vai ser ponto

	
}

function checkLogin($inadmin = true){

	return User::checkLogin($inadmin);

}

function getUserName(){

	$user = User::getFromSession();

	return $user->getdesperson();

}


 ?>