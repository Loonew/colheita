<?php 

namespace Colheita\Model;

use \Colheita\Model;
use \Colheita\DB\Sql;
use \Colheita\Model\User;

class Cart extends Model {

	const SESSION = "Cart";

	public static function getFromSession(){

		$cart = new Cart();
		//se a sessão selecionada for a mesma que a sessão da constante "Cart" e (dá cast em int) o id da sessão for maior que 0 (ou seja, não for null). Isso é pensando que o carrinho já exista no banco
		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']); //cart recebe como inteiro o idcart da sessão que foi encontrata no banco de dados. Essa busca é pelo idcart


		} else {

			$cart->getFromSessionId();//essa busca é pelo id da sessão, e o cart recebe ele

			//e se mesmo buscando pelo id do carrinho e pelo id da sessão ele não achar nada, cria um novo carrinho

			if(!(int)$cart->getidcart() > 0){//se o id inteiro do cart não for maior que zero, ou seja, nullo

				$data = [
					'dessessionid'=>session_id()

				];

				if (User::checkLogin(false))  {//na função checlLogin, isso faz com que o usuário entre, mesmo não sendo admin. Ele não é admin, mas pode entrar
				//a função getFromSession traz o user, já que existe
				$user = User::getFromSession();
				//isso tudo para alocar o id do user que veio no carrinho no banco
				$data['iduser'] = $user->getiduser();

				}

				$cart->setData($data);

				$cart->save();
				//no caso de passar do if, ou seja, do carrinho ainda não existir, seta ele com a sessão
				$cart->setToSession();

			}

		}

		return $cart;

		}
		//setta o carrinho com a session
		public function setToSession(){

			$_SESSION[Cart::SESSION] = $this->getValues();

		}


		public function getFromSessionId(){

			$sql = new Sql();

			$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
				'dessessionid'=>session_id()
			]);//pega pelo id uma sessão da tabela do banco igual à sessão atual

			if (count($results) > 0){

			$this->setData($results[0]);

			}
		}



		public function get(int $idcart){

			$sql = new Sql();

			$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
				'idcart'=>$idcart
			]);//pega o id cart do banco, vê se é igual ao $idcart passado, e traz

			if (count($results) > 0){//pode ser que o index volte vazio, e se vier vazio, o $results[0] nem existe. Então já seta que se o index for maior que 0 retorne para a posição 0. Isso basicamente fala "se não for nulo"

			$this->setData($results[0]);

			}
		}

	

	public function save(){

		$sql = new Sql();

 		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
 			":idcart"=>$this->getidcart(),
 			":dessessionid"=>$this->getdessessionid(),
 			":iduser"=>$this->getiduser(),
 			":deszipcode"=>$this->getdeszipcode(),
 			":vlfreight"=>$this->getvlfreight(),
 			":nrdays"=>$this->getnrdays()
 		]);

 		$this->setData($results[0]);

 	} 

 	public function addProduct(Product $product){

 		$sql = new Sql();

 		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
 			':idcart'=>$this->getidcart(),
 			':idproduct'=>$product->getidproduct()
		]);

 	}

 	public function removeProduct(Product $product, $all = false){

 		$sql = new Sql();

 		if ($all) {

 			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
 				':idcart'=>$this->getidcart(),
 				':idproduct'=>$product->getidproduct()
 			]);

 		} else {

 			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
 				':idcart'=>$this->getidcart(),
 				':idproduct'=>$product->getidproduct()
 			]);
 		}

 	}

 	public function getProducts(){

 		$sql = new Sql();
 		//pega todos esses campos do tb_cartsproducts, conta o numero de colunas deles e joga em mrqtd e soma todas as colunas de valores e joga em total
 		//isso tudo pensando em um só carrinho
 		


 		$rows = $sql->select("
 			SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
 			FROM tb_cartsproducts a 
 			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
 			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
 			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
 			ORDER BY b.desproduct
 			", [
 				':idcart'=>$this->getidcart()
 			]);
 		//depois de selecionar tudo, faz o tratamento
 		return Product::checkList($rows);

 	}


}

 ?>