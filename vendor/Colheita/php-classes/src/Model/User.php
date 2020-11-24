<?php 

namespace Colheita\Model;

use \Colheita\Model;
use \Colheita\DB\Sql;

class User extends Model {

	const SESSION = "User";

	protected $fields = [
		"iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister", "desperson", "nrphone", "desemail"
	];

	public static function login($login, $password):User
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array( ":LOGIN"=>$login ));//procura o login no banco igual ao login vindo da tela

		if (count($results) === 0) {//se não encontrou
			throw new \Exception("Não foi possível fazer login.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"]) === true) {//verifica se a senha vinda como parametro é igual à do banco dentro do $data, retorna true ou false

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

	public static function listALL()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

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

	public function get($iduser)
{
 
 $sql = new Sql();
 
 $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array(
 ":iduser"=>$iduser
 ));
 

 $data = $results[0];
 
 $this->setData($data);
 
 }

 	public function save(){

 		$sql = new Sql();

 		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
 			":desperson"=>$this->getdesperson(),
 			":deslogin"=>$this->getdeslogin(),
 			":despassword"=>$this->getdespassword(),
 			":desemail"=>$this->getdesemail(),
 			":nrphone"=>$this->getnrphone(),
 			":inadmin"=>$this->getinadmin()
 		));

 		$this->setData($results[0]);

 	}


 	public function update(){

 		$sql = new Sql();

 		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
 			":iduser"=>$this->getiduser(),
 			":desperson"=>$this->getdesperson(),
 			":deslogin"=>$this->getdeslogin(),
 			":despassword"=>$this->getdespassword(),
 			":desemail"=>$this->getdesemail(),
 			":nrphone"=>$this->getnrphone(),
 			":inadmin"=>$this->getinadmin()
 		));

 		$this->setData($results[0]);

 	}

 	public function delete(){

 		$sql = new Sql();

 		$sql->query("CALL sp_users_delete(:iduser)", array(
 			":iduser"=>$this->getiduser()
 		));
 	}


}

 ?>