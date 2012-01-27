<?php
class Bf_Catalog_Exception extends Bf_Exception {
	const EX_ITEM_NOT_FOUND = 200100;
	const EX_WRONG_OPTIONS_TYPE = 200300;
	const EX_DATA_TABLE_CLASS_NOT_SET = 200330;  
	

	public function init() {
		parent::init();
		
		self::$arrExceptionMessages[self::EX_ITEM_NOT_FOUND] = "Item Not Found";
		self::$arrExceptionMessages[self::EX_WRONG_OPTIONS_TYPE] = "Wrong options type Zend_Config or Array required";
		self::$arrExceptionMessages[self::EX_DATA_TABLE_CLASS_NOT_SET] = "Data table class not set in config";
	}	
	

}