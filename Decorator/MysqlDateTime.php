<?php

require_once ('Zend/Form/Decorator/Abstract.php');

class Bf_Decorator_MysqlDateTime extends Zend_Form_Decorator_Abstract {

	public function render($content){
			    
	    $objDateTime = DateTime::createFromFormat(self::MYSQL_DATETIME, $content);    
	    
		return $objDateTime->format(DateTime::W3C);
//		  date("d/m/y",$content);
	}
}


