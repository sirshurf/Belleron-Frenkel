<?php
class Bf_Exception extends Exception {
	const EX_OPTION_NOT_FOUND = 100100;
	const EX_CLASS_NOT_INPLEMENTS_CORRECT_INTERFACE = 100110;
	const EX_CLASS_NOT_FOUND = 100120;
	
	public static $arrExceptionMessages;
	
	public function __construct ($code) {
		$this->init();
		if(array_key_exists($code, self::$arrExceptionMessages)) {
			parent::__construct(self::$arrExceptionMessages[$code], $code);
		} else {
			parent::__construct('Unknown Catalog Exception');
		}
	}
	
	public function init() {
		self::$arrExceptionMessages[self::EX_CLASS_NOT_INPLEMENTS_CORRECT_INTERFACE] = "Data table class does not implement correct interface";
		self::$arrExceptionMessages[self::EX_CLASS_NOT_FOUND] = "Data table class not found";
		self::$arrExceptionMessages[self::EX_OPTION_NOT_FOUND] = "Invalid option or option not set";
	}
}
