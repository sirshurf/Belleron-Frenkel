<?php
abstract class Bf_Catalog_Data_Form_Abstract implements Bf_Catalog_Data_Form_Interface{
	protected $_strSubFormName;
	protected $_strSubFormGroupName;
	protected $_strSubFormGroupLegend;
	
	/**
	 * 
	 * @var Zend_Form
	 */
	protected $_objForm;
	
	protected $_options;
	
	/**
	 * @return Zend_Form $_objForm
	 */
	public function getObjForm() {
		return $this->_objForm;
	}

	/**
	 * @param Zend_Form $_objForm
	 */
	public function setObjForm(Zend_Form $_objForm) {
		$this->_objForm = $_objForm;
	}

	/**
	 * @return $_options
	 */
	public function getOptions($strOption = NULL) {
		if (!empty($strOption)) {
			if (isset($this->_options->{$strOption})) {
				return $this->_options->{$strOption};
			} else {
				throw new Bf_Catalog_Exception(Bf_Catalog_Exception::EX_OPTION_NOT_FOUND);
			}
		} else {
			return $this->_options;
		}
	}

	/**
	 * @param array | Zend_Config $_options
	 */
	public function setOptions($_options) {
		$this->_options = $_options;
	}

	public function __construct($options = null) {
		//TODO
		$this->setOptions($options);
		
		$this->setStrSubFormName($this->getOptions('formName'));
		$this->setStrSubFormGroupName($this->getOptions('formGroupName'));
		$this->setStrSubFormGroupLegend($this->getOptions('formGroupLegend'));
		
		
		$objSubForm = new Zend_Form();
	
		$objSubForm->setName($this->getStrSubFormName());
		$objSubForm->setAttrib('id', $this->getOptions('formId'));
		
		$this->setObjForm($objSubForm);
		
	}
	
	public function getStrSubFormName() {
		return $this->_strSubFormName;
	}
	
	public function setStrSubFormName($strName) {
		$this->_strSubFormName = $strName;
		return $this;
	}
	/**
	 * @return the $_strSubFormGroupName
	 */
	public function getStrSubFormGroupName() {
		return $this->_strSubFormGroupName;
	}

	/**
	 * @return the $_strSubFormGroupLegend
	 */
	public function getStrSubFormGroupLegend() {
		return $this->_strSubFormGroupLegend;
	}

	/**
	 * @param field_type $_strSubFormGroupName
	 */
	public function setStrSubFormGroupName($_strSubFormGroupName) {
		$this->_strSubFormGroupName = $_strSubFormGroupName;
		return $this;
	}

	/**
	 * @param field_type $_strSubFormGroupLegend
	 */
	public function setStrSubFormGroupLegend($_strSubFormGroupLegend) {
		$this->_strSubFormGroupLegend = $_strSubFormGroupLegend;
		return $this;
	}

	public function getForm(Zend_Form $objParentForm, $intEntTypeId) {
		
		$this->getObjForm()->addDisplayGroup($this->getElements($intEntTypeId), $this->getStrSubFormGroupName(), array('legend' => $this->getStrSubFormGroupLegend()));
		
		return $this->getObjForm();
		
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return Array
	 */
	abstract public function getElements($intEntTypeId);
	
}