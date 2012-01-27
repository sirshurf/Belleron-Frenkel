<?php

require_once ('Zend/Form/Decorator/Abstract.php');

class Bf_Decorator_Timestamp extends Zend_Form_Decorator_Abstract {

	public function render($content){
		
		return date("d/m/y",$content);
	}
}


