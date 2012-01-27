<?php
class Bf_Eav_Value_Date extends Bf_Eav_Value_Simple {
	CONST ELEMENT_CLASS = 'ZendX_JQuery_Form_Element_DatePicker';
	CONST VALUES_DB_CLASS = 'Bf_Eav_Db_Values_Datetime';

	public static function addValidators(Zend_Form_Element $objElement) {
		$objElement->addValidator('Date');
	}
	
	public static function addDecorators(Zend_Form_Element $objElement) {
	    
//		$this->addElementPrefixPath ( 'Openiview_Decorator', 'Openiview/Decorator/', 'decorator' );
	    
//	    $objElement->addDecorator("Bf_Decorator_MysqlDateTime");
//	    $objElement->addFilter( new Bf_Filter_MysqlDate () );
	}	
}