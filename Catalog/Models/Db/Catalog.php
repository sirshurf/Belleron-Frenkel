<?php
class Bf_Catalog_Models_Db_Catalog extends Bf_Catalog_Table {
	
	protected $_rowClass = 'Bf_Catalog_Models_Db_Catalog_Row';
	
	const TBL_NAME = "catalog";
	
	const COL_ID_CATALOG = "id_catalog";
	const COL_MODULE_CODE = "module_code";
	
	const COL_ID_PARENT	= "id_parent";
	const COL_CAT_PATH	= "cat_path";
	const COL_ID_ENTITIES = "id_entities";

	const COL_IS_FOLDER = "is_folder";
	const COL_IS_LOCKED = "is_locked";
	const COL_HAS_CHILDREN = 'has_children';
	
	/**
	 * 
	 * Enter description here ...
	 * @var string
	 */
	protected $strCatPathDelimiter = ";";
	
	/**
	 * 
	 * Enter description here ...
	 * @param integer $intCatalogId
	 * @return void
	 */
	public function setPathById($intCatalogId) {
		$objItem = $this->fetchRow(array(self::getColumnName(self::COL_ID_CATALOG)."=?"=>(int)$intCatalogId));
		if (null !== $objItem) {
			//Fetched element
			if (0 == $objItem->{self::COL_ID_PARENT}) {
				//Top level element
				$strCatPath = $this->getCatPathDelmiter() . $objItem->{self::COL_ID_CATALOG} . $this->getCatPathDelmiter();
			} else {
				//Internal elememnts
				//Get Parent's cat path, and add the current ID...
				$objParent = $this->fetchRow(array(self::getColumnName(self::COL_ID_CATALOG)."=?"=>$objItem->{self::COL_ID_PARENT}));
				$strCatPath = $objParent->{self::COL_CAT_PATH}.$objItem->{self::COL_ID_CATALOG}.$this->getCatPathDelmiter();
			}
			$objItem->{self::COL_CAT_PATH} = $strCatPath;
			$objItem->save();
		} //no else
	}

	/**
	 * 
	 * Resets cat_path for entire tree starting with parentId
	 * @param integer $intParentId
	 * @return void
	 */
	public function resetCatPath($intParentId = 0) {
		$objCatalogSelect = $this->select(TRUE)->setIntegrityCheck(FALSE);
		$objCatalogSelect->where(self::getColumnName(self::COL_ID_PARENT)." = ? ",$intParentId);
		$arrItems = $this->getAdapter()->fetchAll($objCatalogSelect);
		
		foreach($arrItems as $arrItem) {
			$this->setPathById($arrItem[self::COL_ID_CATALOG]);
			if ($intParentId != $arrItem[self::COL_ID_CATALOG] ) {
				$this->resetCatPath($arrItem[self::COL_ID_CATALOG]);
			}
		}
	}
	/**
	 * Enter description here ...
	 * @return string
	 */
	public function getCatPathDelmiter() {
		return $this->strCatPathDelimiter;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $strCatPathDelimiter
	 * @return void
	 */
	public function setCatPathDelimiter($strCatPathDelimiter) {
		$this->strCatPathDelimiter = $strCatPathDelimiter;
	}
	
	public function insert($data) {
		$mixReturnId = parent::insert($data);
		if (!empty($mixReturnId)) {
			$this->setPathById($mixReturnId);
			$this->setHasChildrenFlags($mixReturnId);
		}
		return $mixReturnId;
	}
	
	public function delete($where) {
		//TODO: does it work??
		$arrRowsToBeDeleted = $this->fetchAll($where);
		$mixResult = parent::delete($where);
		if ($mixResult >0) {
			foreach ($arrRowsToBeDeleted as $arrRow) {
				$this->setHasChildrenFlags($arrRow[self::COL_ID_CATALOG]);
			}
		}
		return $mixResult;
	}
	
	public function setHasChildrenFlags($mixId) {
		if (is_array($mixId)) {
			$intId = $mixId[self::COL_ID_CATALOG];
		} else {
			$intId = (int)$mixId;
		}
		$objRowSet = $this->find($intId);
		if ($objRowSet->count() > 0) {
			$objRow = $objRowSet->current();
			$arrPath = array_filter(explode($this->getCatPathDelmiter(),$objRow->{self::COL_CAT_PATH}));
			foreach($arrPath as $strId) {
				$objItemRow = $this->find($strId)->current();
				$intCount = (int)$this->getAdapter()->fetchOne('SELECT count(1) FROM '.self::TBL_NAME .' WHERE '.self::COL_IS_DELETED.' = 0 AND '.$this->getAdapter()->quoteInto(self::COL_ID_PARENT .'=?', $strId));
				$objItemRow->{self::COL_HAS_CHILDREN} = ($intCount > 0)?1:0;
				$objItemRow->save();
			}
		}
	}
}