<?php
class Bf_Catalog_Form extends ZendX_JQuery_Form {

	/**
	 * 
	 * Enter description here ...
	 * @var Bf_Catalog_Data_Form_Interface
	 */
	protected $objDataForm;
	
	protected $_localOptions;
	
	/**
	 * @return Bf_Catalog_Data_Form_Abstract $objDataForm
	 */
	public function getObjDataForm() {
		return $this->objDataForm;
	}

	/**
	 * @param Bf_Catalog_Data_Form_Interface $objDataForm
	 */
	public function setObjDataForm($objDataForm) {
		$this->objDataForm = $objDataForm;
	}

	/**
	 * @param string $strOption
	 * @return mix
	 * @throws Bf_Catalog_Exception
	 */
	public function getLocalOptions($strOption = NULL) {
		if (!empty($strOption)) {
			if (isset($this->_localOptions->{$strOption})) {
				return $this->_localOptions->{$strOption};
			} else {
				throw new Bf_Catalog_Exception(Bf_Catalog_Exception::EX_OPTION_NOT_FOUND);
			}
		} else {
			return $this->_localOptions;
		}
	}

	/**
	 * @param field_type $_options
	 */
	public function setLocalOptions($_options) {
		$this->_localOptions = $_options;
	}

	public function __construct($options = null){
		$this->setLocalOptions($options);
		parent::__construct($options);
		$this->init();
	}
	
	public function init(){
		
		$this->addHiddenField(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG);
		$this->addHiddenField(Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT);
		$this->addHiddenField(Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES);
		$this->addHiddenField(Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER);

		$this->setObjDataForm(Bf_General::initObject($this->getLocalOptions('dataForm')->Class,array($this->getLocalOptions('dataForm')),'Bf_Catalog_Data_Form_Interface'));
		
	}
	
	protected function addHiddenField($strField) {
		$objElement = new Zend_Form_Element_Hidden($strField);
		$objElement->setAttrib('id', $strField);
 		$objElement->removeDecorator( 'HtmlTag' );
        $objElement->removeDecorator( 'Label' ); 		
		$this->addElement($objElement);
		
	}
	
	public function setFolderForm(){
		$this->setName($this->getLocalOptions('formName'));
//		$this->setAttrib('id', $this->getLocalOptions('formId'));
		return $this;
	}
	
	public function getDataForm($intEntTypeId){
		return $this->getObjDataForm()->getForm($this,$intEntTypeId);
	}
}