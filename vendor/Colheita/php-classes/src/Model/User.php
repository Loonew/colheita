<?php 

namespace Colheita\Model;

use \Colheita\Model;
use \Colheita\DB\Sql;

class User extends Model {

	const SESSION = "User";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSuccess";


	public static function getFromSession(){

		$user = new User();
//se a sessão do usuário for a mesma da constante User, e ela for maior que 0, ou seja, não nulo, seta a session do user como a atual
		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {

			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;

	}


	public static function checkLogin($inadmin = true){

		if (//em qualquer uma dessas situações, o user não tá logado
			!isset($_SESSION[User::SESSION]) //se a sessão do usuario nao for definida
			|| 
			!$_SESSION[User::SESSION]//se for definida, mas está vazia
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0//se for definida, não estiver vazia, mas for menor que 0
		) {
			//não está logado
			return false;

		} else {
			//se a sessão existe e é um admin
			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {//cast em bool faz ele retornar true ou false

				return true;

			} else if ($inadmin === false){//se a rota não for da administração

				return true;

			} else {

				return false;

			}
		}

	}


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

			$data['desperson'] = utf8_encode($data['desperson']);
			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {

			throw new \Exception("Não foi possível fazer login.");

		}

	}


	public static function verifyLogin($inadmin = true)
	{

		if (!User::checkLogin($inadmin))
			{
				if ($inadmin){
				header("Location: /admin/login");
				
		} else{
				header("Location: /login");

		}

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



	public function get($iduser)
{
 
 $sql = new Sql();
 
 $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array(
 ":iduser"=>$iduser
 ));
 

 $data = $results[0];

 $data['desperson'] = utf8_encode($data['desperson']);
 
 $this->setData($data);
 
 }


 	public function save(){

 		$sql = new Sql();

 		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
 			":desperson"=>utf8_decode($this->getdesperson()),
 			":deslogin"=>$this->getdeslogin(),
 			":despassword"=>User::getPasswordHash($this->getdespassword()),
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
 			":desperson"=>utf8_decode($this->getdesperson()),
 			":deslogin"=>$this->getdeslogin(),
 			":despassword"=>User::getPasswordHash($this->getdespassword()),
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

 	public static function setError($msg){

		$_SESSION[User::ERROR] = $msg;

	}

	//pega a mensagem de error
	public static function getError(){//pega o erro, seta em msg, limpa a session, e retorna a msg
		//se a session_error for acionada, e se não estiver vazio, retorna o erro, ou nada
		$msg = (isset($_SESSION[User::ERROR])) && $_SESSION[User::ERROR] ? $_SESSION[User::ERROR] : "";

		User::clearError();//pro erro não ficar para sempre na session

		return $msg;

	}

	public static function clearError(){

		$_SESSION[User::ERROR] = NULL;

	}


	public static function setErrorRegister($msg){

		$_SESSION[User::ERROR_REGISTER] = $msg;

	}
	//pega a mensagem de error
	public static function getErrorRegister(){//pega o erro, seta em msg, limpa a session, e retorna a msg
		//se a session_error for acionada, e se não estiver vazio, retorna o erro, ou nada
		$msg = (isset($_SESSION[User::ERROR_REGISTER])) && $_SESSION[User::ERROR_REGISTER] ? $_SESSION[User::ERROR_REGISTER] : "";

		User::clearErrorRegister();//pro erro não ficar para sempre na session

		return $msg;

	}


	public static function clearErrorRegister(){

		$_SESSION[User::ERROR_REGISTER] = NULL;

	}


	public static function checkLoginExists($login){//verifica se o login já existe

		$sql = new Sql();

		//puxa todos os logins que possui o deslogin igual ao login que vem no registrar do usuário
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);

		//se vier maior que 0, ou seja, mais de um, retorna true. Senão, faz nada (false)
		return (count($results) > 0);


	}


	public static function getPasswordHash($password){

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);

	}


	public static function setSuccess($msg){

		$_SESSION[User::SUCCESS] = $msg;

	}

	
	public static function getSuccess(){

		$msg = (isset($_SESSION[User::SUCCESS])) && $_SESSION[User::SUCCESS] ? $_SESSION[User::SUCCESS] : "";

		User::clearSuccess();

		return $msg;

	}

	public static function clearSuccess(){

		$_SESSION[User::SUCCESS] = NULL;

	}


}





 ?>