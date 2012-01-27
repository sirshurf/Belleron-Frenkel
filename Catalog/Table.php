<?php
abstract class Bf_Catalog_Table extends Bf_Db_Table{
	
	protected $intModuleCode;
	
	protected $strModuleCodeColumn;
	
	CONST COL_MODULE_CODE = "module_code";
	
	public function __construct($config = array(), $definition = null) {
		parent::__construct($config,$definition);

		$strClassName = get_called_class();
		$this->strModuleCodeColumn = $strClassName::COL_MODULE_CODE;
		
		if (isset($config['moduleCode'])) {
			$this->setModuleCode($config['moduleCode']);
		} else {
			$this->setModuleCode();
		}
	}
	
	
    /**
     * Returns an instance of a Zend_Db_Table_Select object.
     *
     * @param bool $withFromPart Whether or not to include the from part of the select based on the table
     * @return Zend_Db_Table_Select
     */
    public function select($withFromPart = self::SELECT_WITHOUT_FROM_PART)
    {
		$select = parent::select($withFromPart);
        //Add Module Code to where conditions
		$select->where($this::TBL_NAME.".".$this->strModuleCodeColumn."=?",$this->intModuleCode);
        return $select;
    }
    
	public function setModuleCode($intModuleCode = 0) {
		$this->intModuleCode = (int)$intModuleCode;
		return $this;
	}
	
	public function getModuleCode() {
		return $this->intModuleCode;
	}
}