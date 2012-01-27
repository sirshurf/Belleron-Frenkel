<?php
class Bf_Catalog
{
	
	/**
	 * @var Bf_Catalog_Models_Catalog
	 */
	protected $objCatalogModel;
	
	/**
	 * 
	 * @var Bf_Eav
	 */
	protected $objEav;
	
	/**
	 * @var unknown
	 * @todo update class reference here
	 */
	protected $objItemsModel;
	
	/**
	 * @var bool
	 */
	protected $boolIsI18n;
	
	/**
	 * @var integer
	 */
	protected $intModuleCode = 0;
	
	/**
	 * @var Zend_Config
	 */
	protected $_options;
	
	/**
	 * 
	 * @var string
	 */
	protected $strEavIndex = 'eav';

	/**
	 * @return string
	 */
	public function getStrEavIndex ()
	{
		return $this->strEavIndex;
	}

	/**
	 * @param string $strEavIndex
	 */
	public function setStrEavIndex ($strEavIndex)
	{
		$this->strEavIndex = $strEavIndex;
	}

	/**
	 * @return Bf_Eav
	 */
	public function getObjEav ()
	{
		return $this->objEav;
	}

	/**
	 * @param Bf_Eav $objEav
	 */
	public function setObjEav ($objEav)
	{
		$this->objEav = $objEav;
	}

	/**
	 * @param array|Zend_Config $options
	 */
	public function __construct ($options = array())
	{
		//Store Zend_Config options
		if ($options instanceof Zend_Config) {
			$this->_options = $options;
		} elseif (is_array($options)) {
			$this->_options = new Zend_Config($options);
		} else {
			throw new Bf_Catalog_Exception(Bf_Catalog_Exception::EX_WRONG_OPTIONS_TYPE);
		}
		
		//Set Module Code
		if (isset($this->_options->moduleCode)) {
			$this->setModuleCode($this->_options->moduleCode);
		}
		
		//Set Model Object
		if (isset($this->_options->objCatalogModel) && ($this->_options->objCatalogModel instanceof Bf_Catalog_Models_Catalog)) {
			$this->objCatalogModel = $this->_options->objCatalogModel;
		} else {
			//TODO: pool default model class from options with fallback
			$this->objCatalogModel = new Bf_Catalog_Models_Catalog($this->getOptions());
		}
		$this->setObjEav(new Bf_Eav($this->getOptions('form')->eav));
	
	}

	public function save ($arrForms, $arrData)
	{
		
		if ($this->isValidForms($arrForms, $arrData)) {
			
			$objMainForm = $arrForms[$this->getOptions('form')->formName];
			
			$arrCleanCatalogData = $objMainForm->getValues();
			$arrCleanCatalogData[Bf_Catalog_Models_Db_Catalog::COL_MODULE_CODE] = $this->getModuleCode();
			
			// Save EAV
			if (! empty($arrForms[$this->getObjEav()->getOptions('formName')])) {
				$objEavForm = $arrForms[$this->getObjEav()->getOptions('formName')];
				
				$intEntityId = $this->getObjEav()->saveForm($arrCleanCatalogData[Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES], $objEavForm, $this->getCatalogModel()
					->getObjCatalogData()
					->getLanguage());
				
				// Set New ID if needed...
				$arrCleanCatalogData[Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES] = $intEntityId;
			}
			
			// Save Main Catalog 
			$intCatId = $this->getCatalogModel()->saveCatalogEntry($arrCleanCatalogData);
			
			// Save Data
			if (! empty($intCatId)) {
				$objDataForm = $arrForms[$this->getOptions('form')->dataForm->formName];
				
				if ($this->getCatalogModel()
					->getObjCatalogData()
					->save($intCatId, $objDataForm)) {
					return $intCatId;
				}
			}
		} else {
			return FALSE;
		}
		return FALSE;
	}
	
	public function delete($intCatId){
	    if (!empty($intCatId)){
	        
	        $objCatTableSubSelect = $this->getCatalogModel()->getObjCatalogTable()->select(TRUE);
	        $objCatTableSubSelect->where(Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED." = ?",FALSE);
	        $objCatTableSubSelect->where(Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT." = ?",$intCatId);
	        $objCatTableSubSelect->reset(Zend_Db_Select::COLUMNS);
	        $objCatTableSubSelect->columns(array('childrens' => new Zend_Db_Expr('count(*)')));
	        
	        
	        $objCatTableSelect = $this->getCatalogModel()->getObjCatalogTable()->select(TRUE)->setIntegrityCheck(FALSE);
	        $objCatTableSelect->where(Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED." = ?",FALSE);
	        $objCatTableSelect->where(Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG." = ?",$intCatId);
	        $objCatTableSelect->columns(array('childrens' => new Zend_Db_Expr("(".$objCatTableSubSelect.")"))); 
	        
	        $objCatTableRowSet = $this->getCatalogModel()->getObjCatalogTable()->fetchAll($objCatTableSelect);
	        
	        if ($objCatTableRowSet->count()>0){
	            $objCatTableRow = $objCatTableRowSet->current();
	            $objCatTableRow->setReadOnly(FALSE);
	            
	            if (!empty($objCatTableRow->childrens)){
                    $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = Zend_Registry::get('Zend_Translate')->translate('LBL_ERROR_CATALOG_FOLDER_NOT_EMPTY');
                    $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;	                
	            } elseif ($objCatTableRow->{Bf_Catalog_Models_Db_Catalog::COL_IS_LOCKED}) {	                
                    $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = Zend_Registry::get('Zend_Translate')->translate('LBL_ERROR_CATALOG_LOCKED_FROM_DEL');
                    $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
	            } else {
	                if ($objCatTableRow->delete()){	                    
                        $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = "";
                        $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_OK;
	                } else {
                        $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = Zend_Registry::get('Zend_Translate')->translate('LBL_ERROR_CATALOG_DEL_FAILED');
                        $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;	                    
	                }
	            }
	            
	        } else {	            
                $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = Zend_Registry::get('Zend_Translate')->translate('LBL_ERROR_CATALOG_ROW_NOT_FOUND');
                $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
	        }
	        
	    } else {	        
            $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_MSG] = Zend_Registry::get('Zend_Translate')->translate('LBL_ERROR_CATALOG_DEL');
            $arrResponse[Ingot_JQuery_JqGrid::RETURN_INDEX_CODE] = Ingot_JQuery_JqGrid::RETURN_CODE_ERROR;
	    }
	    return $arrResponse;
	}

	/**
	 * 
	 * Check all forms validation
	 * 
	 * @param array $arrForms
	 * @param Array $arrData
	 * @return bool
	 */
	protected function isValidForms ($arrForms, $arrData)
	{
		
		$boolIsValid = TRUE;
		
		foreach ($arrForms as $objForm) {
			if (! $objForm->isValid($arrData)) {
				$boolIsValid = FALSE;
			}
		}
		
		return $boolIsValid;
	
	}

	/**
	 * 
	 * Enter description here ...
	 * @param integer $intParent
	 * @param bool $boolGetSelect default TRUE
	 */
	public function getItems ($intParent = 0, $boolGetSelect = TRUE,$boolFoldersTreeOnly = FALSE)
	{
		if ($boolGetSelect) {
			return $this->objCatalogModel->getCatalogSelectByParent($intParent,$boolFoldersTreeOnly);
		} else {
			return $this->objCatalogModel->getCatalogByParent($intParent,$boolFoldersTreeOnly);
		}
	}

	/**
	 * @deprecated by buildPath
	 * @param unknown_type $intCatalogId
	 */
	public function setPath ($intCatalogId)
	{
		return $this->buildPath($intCatalogId);
	}

	public function buildPath ($intCatalogId)
	{
		return $this->objCatalogModel->getObjCatalogTable()->setPathById($intCatalogId);
	}

	/**
	 * 
	 * Enter description here ...
	 * @return int
	 */
	public function addCategory ($intParentId, Array $arrData)
	{
		$arrData[Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT] = (int) $intParentId;
		$arrData[Bf_Catalog_Models_Db_Catalog::COL_MODULE_CODE] = $this->getModuleCode();
		$arrData[Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER] = TRUE;
		return $this->getCatalogModel()->addCatalogEntry($arrData);
	}

	protected function getModuleCode ()
	{
		return $this->intModuleCode;
	}

	protected function setModuleCode ($intModuleCode = 0)
	{
		$this->intModuleCode = (int) $intModuleCode;
		return $this;
	}

	/**
	 * 
	 * Enter description here ...
	 * @param int $intCatalogId
	 * @return Zend_Db_Table_Row
	 */
	public function getItem ($intCatalogId)
	{
		$objItem = $this->getCatalogModel()->getItem($intCatalogId);
		return $objItem;
	
	}

	/**
	 * 
	 * @param Zend_Db_Table_Row $objItemRow
	 */
	public function getItemArray ($intCatalogId)
	{
		$objItem = $this->getItem($intCatalogId);
		$arrItem = $objItem->toArray();
		
		if (! empty($objItem->{Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES})) {
			$objEav = new Bf_Eav();
		
		// 			$arrItem[$this->getStrEavIndex()] = $objEav->getEntityData($objItem->{Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES},$this->getCatalogModel()->getObjCatalogData()->getLanguage()); 	
		}
		
		return $arrItem;
	}

	public function getOptions ($strOption = null)
	{
		if (! empty($strOption)) {
			if (isset($this->_options->{$strOption})) {
				return $this->_options->{$strOption};
			} else {
				throw new Bf_Exception(Bf_Exception::EX_OPTION_NOT_FOUND);
			}
		} else {
			return $this->_options;
		}
	}

	public function setOption ($strOption, $mixValue = null)
	{
		$this->_options->{$strOption} = $mixValue;
		return $this;
	}

	/**
	 * 
	 * Enter description here ...
	 * @return Bf_Catalog_Models_Catalog
	 */
	public function getCatalogModel ()
	{
		return $this->objCatalogModel;
	}

	/**
	 * 
	 * Enter description here ...
	 * @return Bf_Catalog_Form
	 */
	public function getFolderForm ($intEntTypeId = 0, $arrValues = array())
	{
		return $this->getForms($intEntTypeId, $arrValues, TRUE);
	
	}

	public function getItemForm ($intEntTypeId = 0, $arrValues = array())
	{
		return $this->getForms($intEntTypeId, $arrValues, FALSE);
	}

	/**
	 * 
	 * @param integer $intEntTypeId
	 * @param Array $arrValues
	 * @param bool $boolIsFolder 
	 */
	private function getForms ($intEntTypeId, Array $arrValues, $boolIsFolder)
	{
		$arrForms = array();
		
		$arrOptions = $this->getOptions('form')->toArray();
//		Zend_Debug::dump($boolIsFolder);
		$arrOptions['dataForm']['isFolder'] = (bool)$boolIsFolder;		
		$objOptions = new Zend_Config($arrOptions);
		
		$objForm = new Bf_Catalog_Form($objOptions);
		$objForm->setAttrib('id', $this->getOptions('form')->formName);
		$objForm->populate($arrValues);
		if ($boolIsFolder) {
			$objForm->setFolderForm();
		}
		$arrForms[$this->getOptions('form')->formName] = $objForm;
		$objDataForm = $objForm->getDataForm($intEntTypeId);
		
		$objDataForm->populate($arrValues);
		$arrForms[$this->getOptions('form')->dataForm->formName] = $objDataForm;
		
		if (($boolIsFolder && $this->getOptions('useFolderEav')) || (! $boolIsFolder && $this->getOptions('useItemEav'))) {
			if (isset($arrValues[Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES])) {
				$intEntityId = (int) $arrValues[Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES];
			} else {
				$intEntityId = 0;
			}
			$objEavForm = $this->getObjEav()->getForm($intEntTypeId, $this->getCatalogModel()
				->getObjCatalogData()
				->getLanguage(), $intEntityId);
			$objEavForm->setEntityType($intEntTypeId);
			$arrForms[$this->getObjEav()->getOptions('formName')] = $objEavForm;
		}
		
		return $arrForms;
	}
}