<?php
interface Bf_Eav_Value_Interface {
	/**
	 * 
	 * @param unknown_type $strAttributeCode
	 * @return Zend_Form_Element | Zend_Form_Element_Multi
	 */
	public static function getFormElement($intAttributeId, $strAttributeCode,$boolIsRequired = false, $arrValues = null);	
	
	public static function addValidators(Zend_Form_Element $objElement);
	public static function addDecorators(Zend_Form_Element $objElement);
	public static function setValue(Zend_Form_Element $objElement,$strValueTableClass, $arrValues = null);
}