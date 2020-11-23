<?php 

namespace Colheita;

class Model {

	private $values = [];

	public function setData($data)
	{

		foreach ($data as $key => $value)
		{

			$this->{"set".$key}($value);

		}

	}

	public function __call($name, $args)//o nome do método que for chamado (get ou set) e os argumentos de parametro
	{

		$method = substr($name, 0, 3);//o método é definido pelos três primeiros char
		$fieldName = substr($name, 3, strlen($name));//conta e grava o resto do metodo

		if (in_array($fieldName, $this->fields))
		{
			
			switch ($method)
			{

				case "get":
					return $this->values[$fieldName];
				break;

				case "set":
					$this->values[$fieldName] = $args[0];
				break;

			}

		}

	}

	public function getValues() //serve só para isolar a classe. "boa prática"
	{

		return $this->values;

	}

}

 ?>
