<?php
abstract class Bf_Eav_Value_Abstract implements Bf_Eav_Value_Interface
{

	public static function getFormElement ($intAttributeId, $strAttributeCode, $boolIsRequired = FALSE, $arrValues = null) {
		$strCalledClass = get_called_class();		
		$objElement = $strCalledClass::getFormElementHelper(self::getElementClassName(), self::getValuesDbClassName(), $intAttributeId, $strAttributeCode, $boolIsRequired, $arrValues);
		$strCalledClass::setValue($objElement, self::getValuesDbClassName(), $arrValues);
		$strCalledClass::addValidators($objElement);
		$strCalledClass::addDecorators($objElement);
		return $objElement;
	
	}

	public static function saveElement ($intEntityId, $intAttribId, $mixValue, $intLangId) {
		$strCalledClass = get_called_class();
		return $strCalledClass::saveElementHelper(self::getValuesDbClassName(), $intEntityId, $intAttribId, $mixValue, $intLangId);
	}

	public static function getValuesDbClassName () {
		$strCalledClass = get_called_class();
		return $strCalledClass::VALUES_DB_CLASS;
	
	}

	public static function getElementClassName () {
		$strCalledClass = get_called_class();
		return $strCalledClass::ELEMENT_CLASS;
	}

	abstract public static function getFormElementHelper ($strElementClass, $strValueTableClass, $intAttributeId, $strAttributeCode, $boolIsRequired = FALSE, $arrValues = null);
}