<?php
class Bf_Catalog_Data  {

	/**
	 * @var int
	 */
	protected $intLanguage;

	/**
	 * @var bool
	 */
	protected $boolUseLanguage;

	/**
	 * @var Zend_Config
	 */
	protected $_options;

	/**
	 * @var Bf_Catalog_Models_Db_Catalogdata
	 */
	protected $objDbDataTable;
	
	/**
	 * @return Bf_Catalog_Models_Db_Catalogdata $objDbDataTable
	 */
	public function getObjDbDataTable ()
	{
		return $this->objDbDataTable;
	}

	/**
	 * @param Bf_Catalog_Models_Db_Catalogdata $objDbDataTable
	 */
	public function setObjDbDataTable ($objDbDataTable)
	{
		$this->objDbDataTable = $objDbDataTable;
	}

	/**
	 * 
	 * Enter description here ...
	 * @param Zend_Config $options
	 */
	public function __construct(Zend_Config $options) {
		$this->setOptions($options);

		$strTableClassName = $this->getOptions('dataTableClass');
		if (empty($strTableClassName)) {
			throw new Bf_Catalog_Exception(Bf_Catalog_Exception::EX_DATA_TABLE_CLASS_NOT_SET);
		}
		$this->objDbDataTable = Bf_General::initObject($strTableClassName,array($this->getOptions()->toArray(),'Bf_Catalog_Data_Db_Table_Interface'));

		$this->setUseLanguage($this->getOptions('useLanguage'));

		
		if ($this->getUseLanguage()) {
			//TODO: add handling of current language...
//			$this->setLanguage();
		} else {
			$this->setLanguage(0);			
		}
		
	}
	
	public function addDataToCatalogSelect(Zend_Db_Select &$objSelect,$strCatlogIdColumn = null,$strCatalogTableName = null) {
		
		//TODO: review handling of table and column names...
		
		if (is_null($strCatalogTableName)) {
			$strCatalogTableName = Bf_Catalog_Models_Db_Catalog::TBL_NAME;
		}
		if (is_null($strCatlogIdColumn)) {
			$strCatlogIdColumn = Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG;
		}
		
		$this->objDbDataTable->addDataToCatalogSelect($objSelect, $strCatlogIdColumn, $strCatalogTableName,$this->getLanguage());
		
	}
	
	public function setOptions(Zend_Config $options) {
		$this->_options = $options;
		return $this;
	}
	
	public function getOptions($strOption = NULL) {
		if (!empty($strOption)) {
			if (isset($this->_options->{$strOption})) {
				return $this->_options->{$strOption};
			} else {
				throw new Bf_Exception(Bf_Exception::EX_OPTION_NOT_FOUND);
			}
		} else {
			return $this->_options;
		}
	}
		
	public function setLanguage($intLanguage) {
		$this->intLanguage = (int) $intLanguage;
		return $this;
	}
	
	public function getLanguage() {
		return $this->intLanguage;
	}
	
	public function setUseLanguage($boolUseLanguage) {
		$this->boolUseLanguage = (bool)$boolUseLanguage;
		return $this;
	} 
	
	public function getUseLanguage() {
		return $this->boolUseLanguage;
	}	

	public function save($intCatId, Zend_Form $objForm){
		// Check Data if exist row...
		
		$objRowSet = $this->getObjDbDataTable()->find($intCatId, $this->getLanguage());
		
		if ($objRowSet->count() > 0){
			$objRow = $objRowSet->current();
		} else {
			$objRow = $this->getObjDbDataTable()->createRow(array(Bf_Catalog_Data_Db_Table_Abstract::COL_ID_CATALOG=>$intCatId, Bf_Catalog_Data_Db_Table_Abstract::COL_ID_LANGUAGES=>$this->getLanguage()));			
		}
		$objRow->setFromArray($objForm->getValues());
		return $objRow->save();
	}
	
}