<?php 

namespace Colheita;

class Model {

	private $values = [];


	public function __call($name, $args)//o nome do método que for chamado (get ou set) e os argumentos de parametro
	{

		$method = substr($name, 0, 3);//o método é definido pelos três primeiros char
		$fieldName = substr($name, 3, strlen($name));//conta e grava o resto do metodo

		
			
			switch ($method)
			{

				case "get":
					return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
				break;

				case "set":
					$this->values[$fieldName] = $args[0];
				break;

			}

		

	}

	public function setData($data = array())//serve só para isolar a classe. "boa prática" e já transforma os dados em array
	{

		foreach ($data as $key => $value)
		{

			$this->{"set".$key}($value);//chama o método set da function__call com todos os valores recebidos (como array)

		}

	}

	public function getValues() //tbm serve só para isolar a classe. "boa prática". Será o get universal
	{

		return $this->values;

	}

}

 ?>
