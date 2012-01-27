<?php
class Bf_Eav_Value_Select extends Bf_Eav_Value_Multi {
	CONST ELEMENT_CLASS = 'Zend_Form_Element_Select';
	CONST VALUES_DB_CLASS = 'Bf_Eav_Db_Values_Select';

	public static function addValidators(Zend_Form_Element $objElement) {}
	
	public static function addDecorators(Zend_Form_Element $objElement) {}
}