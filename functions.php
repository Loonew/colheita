<?php 

function formatPrice(float $vlprice){ //força o vlprice a ser um float

	return number_format($vlprice, 2, ",", ".");//primeiro separador vai ser virgula, o de casa milhar vai ser ponto

	
}


 ?>