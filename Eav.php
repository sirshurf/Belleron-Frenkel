<?php
class Bf_Eav
{
	
	protected $_options;

	/**
	 * @return the $_options
	 */
	public function getOptions ($strOption)
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

	/**
	 * 
	 * Enter description here ...
	 * @param array | Zend_Config | null $options
	 */
	public function __construct ($options = null)
	{
		$this->setOptions($options);
	}

	/**
	 * 
	 * Enter description here ...
	 * @param array | Zend_Config | null $options
	 */
	protected function setOptions ($options = null)
	{
		if ($options instanceof Zend_Config) {
			$this->_options = $options;
		} elseif (is_array($options)) {
			$this->_options = new Zend_Config($options);
		}
	}

	/**
	 * 
	 * Enter description here ...
	 * @param int $intEntityType
	 * @param int $inLanguageId
	 * @return Bf_Eav_Form
	 */
	public function getForm ($intEntityType, $intLanguageId = 0, $intEntityId = null)
	{
		$objForm = new Bf_Eav_Form();
		$objForm->setAttrib('id', $this->getOptions('formName'));
		$objGroups = $this->getGroupsByEntityType($intEntityType);
		
		if (! empty($intEntityId)) {
			//			Zend_Debug::dump($intEntityId,'ENT');
			$objEntityValues = new Bf_Eav_Db_EntitiesValues();
			$objValuesSelect = $objEntityValues->select();
			$objValuesSelect->from(Bf_Eav_Db_EntitiesValues::TBL_NAME, array(Bf_Eav_Db_EntitiesValues::COL_ID_ATTR, Bf_Eav_Db_EntitiesValues::COL_ID_VALUES));
			$objValuesSelect->where(Bf_Eav_Db_EntitiesValues::TBL_NAME . "." . Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES . "=?", $intEntityId);
			$objValuesSelect->where(Bf_Eav_Db_EntitiesValues::TBL_NAME . "." . Bf_Eav_Db_EntitiesValues::COL_ID_LANGUAGES . "=?", $intLanguageId);
			$objValuesSelect->where(Bf_Eav_Db_EntitiesValues::TBL_NAME . "." . Bf_Eav_Db_EntitiesValues::COL_IS_DELETED . "=?", FALSE);
			//TODO: there will be error for MultiSelect???
			//			Zend_Debug::dump($objValuesSelect->assemble());
			$arrValues = $objEntityValues->getAdapter()->fetchPairs($objValuesSelect);
		
		//			Zend_Debug::dump($arrValues);
		}
		
		foreach ($objGroups as $objGroup) {
			$arrElements = array();
			$objAttributes = $this->getGroupAttributes($objGroup->{Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP});
			foreach ($objAttributes as $objAttribute) {
				$objValue = Bf_Eav_Value::factory($objAttribute->{Bf_Eav_Db_Attributes::COL_VALUE_TYPE});
				$arrAttrValues = null;
				if (isset($arrValues[$objAttribute->{Bf_Eav_Db_Attributes::COL_ID_ATTR}])) {
					$arrAttrValues = $arrValues[$objAttribute->{Bf_Eav_Db_Attributes::COL_ID_ATTR}];
				}
				$objElement = $objValue->getFormElement($objAttribute->{Bf_Eav_Db_Attributes::COL_ID_ATTR}, $objAttribute->{Bf_Eav_Db_Attributes::COL_ATTR_CODE}, (bool) $objAttribute->{Bf_Eav_Db_GroupAttributes::COL_IS_REQUIERED}, $arrAttrValues);
				$arrElements[$objAttribute->{Bf_Eav_Db_Attributes::COL_ATTR_CODE}] = $objElement;
			}
			if (! empty($arrElements)) {
				$objForm->addDisplayGroup($arrElements, $objGroup->{Bf_Eav_Db_EntitiesTypesGroups::COL_GRP_LEGEND_CODE}, array('legend' => $objGroup->{Bf_Eav_Db_EntitiesTypesGroups::COL_GRP_LEGEND_CODE}));
			}
		}
		return $objForm;
	}

	/**
	 * 
	 * Enter description here ...
	 * @param Zend_Form $arrData
	 * @return integer
	 */
	public function saveForm ($intEntityId, Zend_Form $objForm, $intLangId)
	{
		
		$objEntitiesTable = new Bf_Eav_Db_Entities();
		
		if (empty($intEntityId)) {
			// Save Entity			
			$intEntityTypeVal = $objForm->getElement(Bf_Eav_Db_Entities::COL_ID_ENTITIES_TYPES)->getValue();
			$objEntityRow = $objEntitiesTable->createRow(array(Bf_Eav_Db_Entities::COL_ID_ENTITIES_TYPES => $intEntityTypeVal));
			$intEntityId = $objEntityRow->save();
			if (empty($intEntityId)) {
				throw new Bf_Exception();
			}
		} else {
			$objEntitiesRowSet = $objEntitiesTable->find($intEntityId);
			if ($objEntitiesRowSet->count() <= 0) {
				throw new Bf_Exception();
			}
			$objEntitiesRow = $objEntitiesRowSet->current();
			$intEntityTypeVal = $objEntitiesRow->{Bf_Eav_Db_Entities::COL_ID_ENTITIES_TYPES};
		}
		
		$objAttributes = $this->getAttributeByEntityType($intEntityTypeVal);
		
		foreach ($objAttributes as $objAttribute) {
			$objValue = Bf_Eav_Value::factory($objAttribute->{Bf_Eav_Db_Attributes::COL_VALUE_TYPE});
			if (! empty($objValue)) {
				$objElement = $objValue->saveElement($intEntityId, $objAttribute->{Bf_Eav_Db_Attributes::COL_ID_ATTR}, $objForm->getElement($objForm->filterName($objAttribute->{Bf_Eav_Db_Attributes::COL_ATTR_CODE}))
					->getValue(), $intLangId);
			}
		}
		
		return $intEntityId;
	
	}

	protected function getGroupAttributes ($intGroupId)
	{
		$objGroupAttributes = new Bf_Eav_Db_GroupAttributes();
		$objSelect = $objGroupAttributes->select(TRUE)->setIntegrityCheck(FALSE);
		$objSelect->join(Bf_Eav_Db_Attributes::TBL_NAME, Bf_Eav_Db_Attributes::TBL_NAME . "." . Bf_Eav_Db_Attributes::COL_ID_ATTR . "=" . Bf_Eav_Db_GroupAttributes::TBL_NAME . "." . Bf_Eav_Db_GroupAttributes::COL_ID_ATTR, '*');
		$objSelect->where(Bf_Eav_Db_GroupAttributes::TBL_NAME . "." . Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP . "=?", $intGroupId);
		$objSelect->where(Bf_Eav_Db_GroupAttributes::TBL_NAME . '.' . Bf_Eav_Db_GroupAttributes::COL_IS_DELETED . " = ?", FALSE);
		$objSelect->where(Bf_Eav_Db_Attributes::TBL_NAME . '.' . Bf_Eav_Db_Attributes::COL_IS_DELETED . " = ?", FALSE);
		$objSelect->order(Bf_Eav_Db_GroupAttributes::TBL_NAME . "." . Bf_Eav_Db_GroupAttributes::COL_ORDER);
		
		return $objGroupAttributes->fetchAll($objSelect);
	}

	protected function getGroupsByEntityType ($intEntityTypeId)
	{
		//TODO:
		$objGroups = new Bf_Eav_Db_EntitiesTypesGroups();
		$objSelect = $objGroups->select(TRUE)->setIntegrityCheck(FALSE);
		$objSelect->where(Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME . "." . Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES . "=?", $intEntityTypeId);
		$objSelect->where(Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME . '.' . Bf_Eav_Db_EntitiesTypesGroups::COL_IS_DELETED . " = ?", FALSE);
		$objSelect->order(Bf_Eav_Db_EntitiesTypesGroups::COL_ORDER);
		
		return $objGroups->fetchAll($objSelect);
	}

	protected function getAttributeByEntityType ($intEntityTypeId)
	{
		$objAttributes = new Bf_Eav_Db_GroupAttributes();
		$objSelect = $objAttributes->select(TRUE)->setIntegrityCheck(FALSE);
		$objSelect->join(Bf_Eav_Db_Attributes::TBL_NAME, Bf_Eav_Db_Attributes::TBL_NAME . "." . Bf_Eav_Db_Attributes::COL_ID_ATTR . "=" . Bf_Eav_Db_GroupAttributes::TBL_NAME . "." . Bf_Eav_Db_GroupAttributes::COL_ID_ATTR, '*');
		$objSelect->join(Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME, Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME . '.' . Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES_GRP . ' = ' . Bf_Eav_Db_GroupAttributes::TBL_NAME . '.' . Bf_Eav_Db_GroupAttributes::COL_ID_ATTR_GRP);
		$objSelect->where(Bf_Eav_Db_EntitiesTypesGroups::TBL_NAME . "." . Bf_Eav_Db_EntitiesTypesGroups::COL_ID_ENTITIES_TYPES . "=?", $intEntityTypeId);
		$objSelect->where(Bf_Eav_Db_GroupAttributes::TBL_NAME . '.' . Bf_Eav_Db_GroupAttributes::COL_IS_DELETED . " = ?", FALSE);
		$objSelect->where(Bf_Eav_Db_Attributes::TBL_NAME . '.' . Bf_Eav_Db_Attributes::COL_IS_DELETED . " = ?", FALSE);
		$objSelect->order(Bf_Eav_Db_GroupAttributes::TBL_NAME . "." . Bf_Eav_Db_GroupAttributes::COL_ORDER);
		
		return $objAttributes->fetchAll($objSelect);
	
	}

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $intEntityId
	 * @param unknown_type $intLanguageId
	 * @param array $arrAttributes
	 */
	protected function getEntityValues ($intEntityId, $intLanguageId = 0, Array $arrAttributes = array())
	{

	}

	/**
	 * 
	 * Enter description here ...
	 * @param int $intAttrId
	 * @param String $mixAttrType
	 * @return ZendX_JQuery_Form
	 */
	public function getAttrForm ($intAttrId, $mixAttrType)
	{
		$objValue = Bf_Eav_Value::factory($mixAttrType);
		return $objValue->getAttrEditForm($intAttrId, $mixAttrType);
	}
	
	public function getAttrExtraData($intAttrId, $mixAttrType)
	{
		if (!empty($intAttrId)){
		$objValue = Bf_Eav_Value::factory($mixAttrType);
		return $objValue->getAttrEditExtra($intAttrId, $mixAttrType);
		} else {
			return Zend_Registry::get("Zend_Translate")->translate('LBL_EAV_ATTR_MAST_SAVE_FIRST');
		}
	}

	/**
	 * 
	 * Saves Attribute Data
	 * @param array $arrData
	 * @return Int
	 */
	public function saveAttr (Array $arrData)
	{
		$intAttrId = 0;
		if (! empty($arrData[Bf_Eav_Db_Attributes::COL_VALUE_TYPE])) {
			$objValue = Bf_Eav_Value::factory($arrData[Bf_Eav_Db_Attributes::COL_VALUE_TYPE]);
			$intAttrId = $objValue->saveAttr($arrData);
		} 
		
		return $intAttrId;
	}
}