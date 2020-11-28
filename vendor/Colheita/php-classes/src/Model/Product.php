<?php 

namespace Colheita\Model;

use \Colheita\Model;
use \Colheita\DB\Sql;

class Product extends Model {


	public static function listALL()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	}

	public static function checkList($list){

		foreach ($list as &$row) { //o & faz o row da linha 22 ser alterado pelo foreach dentro do array list
			
			$p = new Product();
			$p->setData($row);
			$row = $p->getValues();
		}

		return $list;


	}



	public function save(){
		$sql = new Sql();

 		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
 			":idproduct"=>$this->getidproduct(),
 			":desproduct"=>$this->getdesproduct(),
 			":vlprice"=>$this->getvlprice(),
 			":vlwidth"=>$this->getvlwidth(),
 			":vlheight"=>$this->getvlheight(),
 			":vllength"=>$this->getvllength(),
 			":vlweight"=>$this->getvlweight(),
 			":desurl"=>$this->getdesurl()

 		));

 		$this->setData($results[0]);

 		

 	}

 	public function get($idproduct){

 		$sql = new Sql();

 		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct;", [
 			':idproduct'=>$idproduct
 		]);

 		$this->setData($results[0]);

 	}

 	public function delete(){

 		$sql = new Sql();

 		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct;", [
 			':idproduct'=>$this->getidproduct()
 		]);

 	}

 	public function checkPhoto(){

 		if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
 			"res" . DIRECTORY_SEPARATOR . 
 			"site" . DIRECTORY_SEPARATOR . 
 			"img" . DIRECTORY_SEPARATOR . 
 			"products" . DIRECTORY_SEPARATOR .
 			$this->getidproduct() . ".jpg"
 		)) {

 			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";

 		} else {

 			$url = "/res/site/img/product.jpg";

 		}

 		return $this->setdesphoto($url);

 	}

 	public function getValues(){

 		$this->checkPhoto();

 		$values = parent::getValues();

 		return $values;

 	}

 	public function setPhoto($file){

 		$extension = explode('.', $file['name']); //faz um array do nome do arquivo a partir do ponto
 		$extension = end($extension);//"a extensão é só a ultima posição do array"

 		switch ($extension) {
 			case "jpg":
 			case "jpeg":
 			$image = imagecreatefromjpeg($file["tmp_name"]);//cria uma imagem do jpeg do arquivo com o nome temporario que veio do servidor
 				break;

 			case "gif":
 			$image = imagecreatefromgif($file["tmp_name"]);
 				break;

 			case "png":
 			$image = imagecreatefrompng($file["tmp_name"]);
 				break;

 		}

 		$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
 			"res" . DIRECTORY_SEPARATOR . 
 			"site" . DIRECTORY_SEPARATOR . 
 			"img" . DIRECTORY_SEPARATOR . 
 			"products" . DIRECTORY_SEPARATOR .
 			$this->getidproduct() . ".jpg";


 		imagejpeg($image, $dist);

 		imagedestroy($image);

 		$this->checkPhoto();

 	}


 	public function getFromURL($desurl){

 		$sql = new Sql();

 		$rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [//o bind já põe as aspas simpels :D
 			':desurl'=>$desurl
 		]);

 		$this->setData($rows[0]);

 	}

 	public function getCategories(){

 		$sql = new Sql();

 		return $sql->select("
 			SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct", [//pegar o id da tabela b parece mais confiável
 			':idproduct'=>$this->getidproduct()
 		]);

 	}

}

 ?>