<?php 

namespace Colheita\Model;

use \Colheita\Model;
use \Colheita\DB\Sql;

class User extends Model {

	const SESSION = "User";

	protected $fields = [
		"iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister"
	];

	public static function login($login, $password):User
	{

		$db = new Sql();

		$results = $db->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));//procura o login no banco igual ao login vindo da tela

		if (count($results) === 0) {//se não encontrou
			throw new \Exception("Não foi possível fazer login.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"])) {//verifica se a senha vinda como parametro é igual à do banco dentro do $data, retorna true ou false

			$user = new User();
			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {

			throw new \Exception("Não foi possível fazer login.");

		}

	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

	public static function verifyLogin($inadmin = true)
	{

		if (//essas duas linhas significa "ou"
			!isset($_SESSION[User::SESSION]) //se não estiver definida
			|| 
			!$_SESSION[User::SESSION]//se estiver vazia
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0//se o id do user for menor que 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin//se ele pode acessar a administração
		) {
			
			header("Location: /admin/login");//faz o redirect
			exit;

		}

	}

}

 ?>