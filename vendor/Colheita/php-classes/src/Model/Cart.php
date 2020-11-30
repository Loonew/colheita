<?php 

namespace Colheita\Model;

use \Colheita\Model;
use \Colheita\DB\Sql;
use \Colheita\Model\User;

class Cart extends Model {

	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";


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

		$this->getCalculatedTotal();//força update de frete e a soma dos produtos

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

 		$this->getCalculatedTotal();//força update de frete e a soma dos produtos

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


 	public function getProductsTotals(){

 		$sql = new Sql();
//pega a soma do preço, medidas de peso e tamanho, e quantidade de itens de tal carrinho
 		$results = $sql->select("
 			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, 
				SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
			FROM tb_products a
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL;
 			", [
 				':idcart'=>$this->getidcart()
 		]);
 		//se o results voltar maior que 0, ou seja, não nulo
 		if (count($results) > 0){
 			//retorna na posição 0, o início
 			return $results[0];

 		} else {//senão volta vazio para não dar erro

 			return [];

 		}

 	}


 	public function setFreight($nrzipcode){

 		$nrzipcode = str_replace('-', '', $nrzipcode);//no caso de alguém colocar um tracinho no CEP

 		$totals = $this->getProductsTotals();//já me trás os valores somados do carrinho, seja o preço, medida ou peso

 		if ($totals['nrqtd'] > 0) {

 			if($totals['vlheight'] < 2) $totals['vlheight'] = 2;
 			if($totals['vllength'] < 16) $totals['vllength'] = 16;
 			$qs = http_build_query([
 				'nCdEmpresa'=>'',
 				'sDsSenha'=>'',
 				'nCdServico'=>'40010',
 				'sCepOrigem'=>'72145424',//CEP do shopping JK
 				'sCepDestino'=>$nrzipcode,//CEP que o usuário escreveu no carrinho
 				'nVlPeso'=>$totals['vlweight'],
 				'nCdFormato'=>'1',
 				'nVlComprimento'=>$totals['vllength'],
 				'nVlAltura'=>$totals['vlheight'],
 				'nVlLargura'=>$totals['vlwidth'],
 				'nVlDiametro'=>'0',
 				'sCdMaoPropria'=>'S',
 				'nVlValorDeclarado'=>$totals['vlprice'],
 				'sCdAvisoRecebimento'=>'S'
 			]);

 			//esse coisinha é um calculador para preços de frete do correios, disponibilizado publiclamente no mesmo endereço (e me poupou algum imensurável trabalho)
 			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

 			$result = $xml->Servicos->cServico;

 			if ($result->MsgErro !=''){

 				Cart::setMsgError($result->MsgErro);//seta a mensagem de erro para ser mostrada

 			} else {

 				Cart::clearMsgError();


 			}

 			$this->setnrdays($result->PrazoEntrega);
 			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
 			$this->setdeszipcode($nrzipcode);

 			$this->save();//salva pelamor

 			return $result;

 		} else {




 		}
 		
 	}

 	//pra pegar o valor do campo Valor dentro do $value (que tem todos os campos) e substituir a virgula por ponto
	public static function formatValueToDecimal($value):float{

		$value = str_replace('.', '', $value);//some com o ponto
		return str_replace(',', '.', $value);//coloca a virgula onde seria o ponto

	}
	//seta a mensagem de error numa msg
	public static function setMsgError($msg){

		$_SESSION[Cart::SESSION_ERROR] = $msg;

	}
	//pega a mensagem de error
	public static function getMsgError(){//pega o erro, seta em msg, limpa a session, e retorna a msg
		//se a session_error for acionada, retorna o erro, ou nada
		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

		Cart::clearMsgError();//pro erro não ficar para sempre na session

		return $msg;

	}

	public static function clearMsgError(){

		$_SESSION[Cart::SESSION_ERROR] = NULL;

	}


	public function updateFreight(){//isso força o preço do frete a atualizar se eu adicionar ou remover um produto do carrinho

		if ($this->getdeszipcode () != '') {

			$this->setFreight($this->getdeszipcode());

		}

	}

	public function getValues(){//quando chama o getValues, ele não passa o subtotal, então preciso forçar isso

		$this->getCalculatedTotal();//função extra que chama o subtotal

		return parent::getValues();//faz tudo o que o getvalues originalmente já faz

	}

	public function getCalculatedTotal(){

		$this->updateFreight();

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + $this->getvlfreight());
		
	}


}

 ?>