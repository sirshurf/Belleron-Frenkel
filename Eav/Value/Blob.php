<?php 
class Bf_Eav_Value_Blob extends Bf_Eav_Value_Select {
	public static function getFormElement($value = NULL) {
		//TODO: WTF??
		return new Zend_Form_Element_Text();
	}
	
}