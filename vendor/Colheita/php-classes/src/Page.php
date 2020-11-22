<?php 

namespace Colheita;

use Rain\Tpl;

class Page {

//deixar as variaveis como private impede que outras classes tenham acesso
	private $tpl; //variavel do template
	private $options = []; //coisas a mais que podem aparecer graças ao usuario
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data"=>[]
	];//variaveis que serão passadas pelo template como padrão

	public function __construct($opts = array())
	{

		$this->options = array_merge($this->defaults, $opts);
		//se a informação dentro do parâmetro opts der conflito com o default, fica o default

		$config = array(
		    "base_url"      => null,
		    "tpl_dir"       => $_SERVER['DOCUMENT_ROOT']."/views/", //esse $_server tráz o diretorio root do servidor
		    "cache_dir"     => $_SERVER['DOCUMENT_ROOT']."/views-cache/",
		    "debug"         => false
		);

		Tpl::configure( $config );

		$this->tpl = new Tpl();

		if ($this->options['data']) $this->setData($this->options['data']);

		if ($this->options['header'] === true) $this->tpl->draw("header", false);

	}

	public function __destruct()
	{

		if ($this->options['footer'] === true) $this->tpl->draw("footer", false);

	}

	private function setData($data = array())//só foi criado para não repetir codigo, e grava o que o usuário irá alterar no padrão
	{

		foreach($data as $key => $val)
		{

			$this->tpl->assign($key, $val);

		}

	}

	public function setTpl($tplname, $data = array(), $returnHTML = false)
	{

		$this->setData($data);

		return $this->tpl->draw($tplname, $returnHTML);

	}

}

 ?>