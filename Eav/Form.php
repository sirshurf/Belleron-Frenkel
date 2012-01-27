<?php
class Bf_Eav_Form extends ZendX_JQuery_Form {
	public function __construct($options = null) {
		parent::__construct($options);
	}
	
	public function setEntityType($intEntType){
		
        $objElement = new Zend_Form_Element_Hidden(Bf_Eav_Db_Entities::COL_ID_ENTITIES_TYPES);
        $objElement->setAttrib('id', Bf_Eav_Db_Entities::COL_ID_ENTITIES_TYPES);
		$objElement->setValue($intEntType);
 		$objElement->removeDecorator( 'HtmlTag' );
        $objElement->removeDecorator( 'Label' );         
        $this->addElement($objElement);
	}
}