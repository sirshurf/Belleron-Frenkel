<?php

class Bf_Catalog_Models_Catalog
{

    const COL_PARENT_FLAG = 'isParent';

    const COL_ATTR_DATA = 'attr_data';
    
    protected $intModuleCode;
    
    /**
     * @var Bf_Catalog_Table
     */
    protected $objCatalogTable;
    
    /**
     * @var Bf_Catalog_Data
     */
    protected $objCatalogData;
    
    /**
     * @var Zend_Config
     */
    protected $_options;
    
    /**
     * 
     * @var Zend_Db_Table_Rowset
     */
    protected $arrAttriList;

    /**
     * @return Zend_Db_Table_Rowset $arrAttriList
     */
    public function getArrAttriList ()
    {
        return $this->arrAttriList;
    }

    /**
     * @param Zend_Db_Table_Rowset $arrAttriList
     */
    public function setArrAttriList (Zend_Db_Table_Rowset $arrAttriList)
    {
        $this->arrAttriList = $arrAttriList;
    }

    /**
     * @return Bf_Catalog_Models_Db_Catalog $objCatalogTable
     */
    public function getObjCatalogTable ()
    {
        return $this->objCatalogTable;
    }

    /**
     * @param Bf_Catalog_Table $objCatalogTable
     */
    public function setObjCatalogTable (Bf_Catalog_Models_Db_Catalog $objCatalogTable)
    {
        $this->objCatalogTable = $objCatalogTable;
        return $this;
    }

    /**
     * @return Bf_Catalog_Data
     */
    public function getObjCatalogData ()
    {
        return $this->objCatalogData;
    }

    /**
     * @param Bf_Catalog_Data $objCatalogData
     */
    public function setObjCatalogData ($objCatalogData)
    {
        $this->objCatalogData = $objCatalogData;
    }

    public function __construct (Zend_Config $options)
    {
        $this->_options = $options;
        
        $this->setModuleCode($options->moduleCode);
        
        $this->setObjCatalogTable( new Bf_Catalog_Models_Db_Catalog(array('moduleCode' => $this->getModuleCode())));
        
        $this->objCatalogData = new Bf_Catalog_Data($this->getOptions());
        
        if (isset($options->useAttrInList)) {
            
            $this->setArrAttriList(Bf_Eav_Db_Attributes::getAttribList());
        }
    }

    public function getModuleCode ()
    {
        return $this->intModuleCode;
    }

    public function setModuleCode ($intModuleCode)
    {
        $this->intModuleCode = (int) $intModuleCode;
    }

    public function setOptions (Zend_Config $options)
    {
        $this->_options = $options;
    }

    /**
     * @param string $strOption
     * @return mix
     * @throws Bf_Catalog_Exception
     */
    public function getOptions ($strOption = NULL)
    {
        if (! empty($strOption)) {
            if (isset($this->_options->{$strOption})) {
                return $this->_options->{$strOption};
            } else {
                throw new Bf_Catalog_Exception(Bf_Catalog_Exception::EX_OPTION_NOT_FOUND);
            }
        } else {
            return $this->_options;
        }
    }

    public function getCatalogByParent ($intParentId, $boolFoldersTreeOnly = false)
    {
        return $this->objCatalogTable->fetchAll($this->getCatalogSelectByParent($intParentId, $boolFoldersTreeOnly));
    }

    public function getCatalogSelectByParent ($intParentId, $boolFoldersTreeOnly = false)
    {
        
        $objAttrList = $this->getArrAttriList();
        
        if (! $boolFoldersTreeOnly) {
            //Get Parent row select
            $objCatalogParentSelect = $this->objCatalogTable->select(TRUE)->setIntegrityCheck(FALSE);
            $objCatalogParentSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME . "." . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG . "=?", (int) $intParentId);
            $objCatalogParentSelect->limit(1);
            $objCatalogParentSelect->columns(array(self::COL_PARENT_FLAG => new Zend_Db_Expr('1')));
            if ($objAttrList->count() > 0) {
                $objCatalogParentSelect->columns(array(self::COL_ATTR_DATA => new Zend_Db_Expr('1')));
            }
            $this->objCatalogData->addDataToCatalogSelect($objCatalogParentSelect);
        
        }
        //Get catolog items
        $objCatalogSelect = $this->objCatalogTable->select(TRUE)->setIntegrityCheck(FALSE);
        $objCatalogSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME . "." . Bf_Catalog_Models_Db_Catalog::COL_ID_PARENT . "=?", (int) $intParentId);
        $objCatalogSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME . "." . Bf_Catalog_Models_Db_Catalog::COL_IS_DELETED . " = ?", FALSE);
        if ($boolFoldersTreeOnly) {
            $objCatalogSelect->where(Bf_Catalog_Models_Db_Catalog::getColumnName(Bf_Catalog_Models_Db_Catalog::COL_IS_FOLDER) . "=?", TRUE);
        }
        
        $objCatalogSelect->columns(array(self::COL_PARENT_FLAG => new Zend_Db_Expr('0')));
        if ($objAttrList->count() > 0) {
            $arrAttrList= array();
            foreach ($objAttrList as $objAttrRow){
                $arrAttrList[] = $objAttrRow->{Bf_Eav_Db_Attributes::COL_ID_ATTR};
            }
            
            $objEntVal = new Bf_Eav_Db_EntitiesValues();
            $objEntValSelect = $objEntVal->select(TRUE)->setIntegrityCheck(FALSE);
            
            $objEntValSelect->where(Bf_Eav_Db_EntitiesValues::TBL_NAME.'.'.Bf_Eav_Db_EntitiesValues::COL_ID_ATTR." in (?)",$arrAttrList);
            
            $objEntValSelect->where(
            Bf_Eav_Db_EntitiesValues::getColumnName(Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES) . " = " . Bf_Catalog_Models_Db_Catalog::getColumnName(Bf_Catalog_Models_Db_Catalog::COL_ID_ENTITIES));
            $objEntValSelect->group(Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES);
            
            $objEntValSelect->reset(Zend_Db_Select::COLUMNS);
            $objEntValSelect->columns(array(new Zend_Db_Expr("GROUP_CONCAT(concat(".Bf_Eav_Db_EntitiesValues::getColumnName(Bf_Eav_Db_EntitiesValues::COL_ID_ATTR).",':',".Bf_Eav_Db_EntitiesValues::getColumnName(Bf_Eav_Db_EntitiesValues::COL_ID_VALUES).'))')));
            
            $objCatalogSelect->columns(array(self::COL_ATTR_DATA => new Zend_Db_Expr("CONCAT(',',(".$objEntValSelect."),',')")));
            
        }
        $this->objCatalogData->addDataToCatalogSelect($objCatalogSelect);
        
        //We MUST use Zend_Db_Table_Select to comply with Zend_Db_Table structure in the module
        $objSelect = new Zend_Db_Table_Select($this->objCatalogTable);
        if ($boolFoldersTreeOnly) {
            $objSelect->union(array($objCatalogSelect));
        } else {
            //Union
            $objSelect->union(array($objCatalogParentSelect, $objCatalogSelect));
        }
        return $objSelect;
    }

    /**
     * 
     * Enter description here ...
     * @param int $intCatalogId
     * @return Zend_Db_Table_Row
     */
    public function getItem ($intCatalogId)
    {
        $objSelect = $this->getItemSelect($intCatalogId);
        
        return $this->getCatalogTable()->fetchRow($objSelect);
    }

    /**
     * @param int $intCatalogId
     * @return Zend_Db_Select
     */
    public function getItemSelect ($intCatalogId)
    {
        $objSelect = $this->getCatalogTable()
            ->select(TRUE)
            ->setIntegrityCheck(FALSE);
        $objSelect->where(Bf_Catalog_Models_Db_Catalog::TBL_NAME . "." . Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG . "=?", $intCatalogId);
        $this->objCatalogData->addDataToCatalogSelect($objSelect);
        return $objSelect;
    }

    /**
     * 
     * @param array $data
     * @return int
     */
    public function saveCatalogEntry ($data)
    {
        
        if (empty($data[Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG])) {
            unset($data[Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG]);
            $intCatId = $this->getCatalogTable()
                ->createRow($data)
                ->save();
        } else {
            $objRowSet = $this->getCatalogTable()->find($data[Bf_Catalog_Models_Db_Catalog::COL_ID_CATALOG]);
            if (empty($objRowSet)) {
                // :TODO 
                throw new Bf_Catalog_Exception();
            }
            $objRow = $objRowSet->current();
            $intCatId = $objRow->setFromArray($data)->save();
        }
        
        return $intCatId;
    }

    /**
     * 
     * @return Bf_Catalog_Models_Db_Catalog
     */
    protected function getCatalogTable ()
    {
        return $this->objCatalogTable;
    }
}