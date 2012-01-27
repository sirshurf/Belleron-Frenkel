<?php
class Bf_General {
	/**
	 * @param string $strClassName
	 * @param array $arrParams 
	 * @param string $strInterface 
	 * @param string $strParent
	 * @return stdClass
	 * @throws Bf_Exception
	 */
	
	public static function initObject($strClassName,Array $arrParams = array() ,$strInterface = null, $strParent = null) {
		if (class_exists($strClassName) || Zend_Loader_Autoloader::autoload($strClassName)) {
			$objReflectionClass = new ReflectionClass($strClassName);			
			if ( (is_null($strInterface) && is_null($strParent)) || 
				(!is_null($strInterface) && $objReflectionClass->implementsInterface($strInterface)) ||
				(!is_null($strParent) && $objReflectionClass->isSubclassOf($strParent)) 
			) {
				return $objReflectionClass->newInstanceArgs($arrParams);
			} else {
				throw new Bf_Catalog_Exception(Bf_Exception::EX_CLASS_NOT_INPLEMENTS_CORRECT_INTERFACE);					
			}
		} else {
			throw new Bf_Catalog_Exception(Bf_Exception::EX_CLASS_NOT_FOUND);
		}
	}
}
