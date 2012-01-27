<?php

abstract class Bf_Eav_Value_Simple extends Bf_Eav_Value_Abstract
{

    public static function getFormElementHelper ($strElementClass, $strValueTableClass, $intAttributeId, $strAttributeCode, $boolIsRequired = FALSE, $arrValues = null)
    {
        /**
         * @var Zend_Form_Element
         */
        
        $objElement = Bf_General::initObject($strElementClass, array($strAttributeCode), null, 'Zend_Form_Element');
        $objElement->setName($strAttributeCode);
        $objElement->setLabel('LBL_' . $strAttributeCode);
        $objElement->setAttrib('id', $objElement->filterName($strAttributeCode));
        
        $objElement->setRequired($boolIsRequired);
        
        return $objElement;
    }

    public static function setValue (Zend_Form_Element $objElement, $strValueTableClass, $arrValues = null)
    {
        if (! is_null($arrValues)) {
            $objValueTable = Bf_General::initObject($strValueTableClass, array(), null, 'Bf_Eav_Db_Values_Abstract');
            
            $value = $objValueTable->find($arrValues);
            if ($value->count() > 0) {
                $objElement->setValue($value->current()->{Bf_Eav_Db_Values_Abstract::COL_VALUE});
            }
        }
    }

    public static function saveElementHelper ($strValueTableClass, $intEntityId, $intAttribId, $mixValue, $intLangId)
    {
        $objValueTable = Bf_General::initObject($strValueTableClass, array(), null, 'Bf_Eav_Db_Values_Abstract');
        
        $strCalledClass = get_called_class();
        $intValueId = $strCalledClass::getValueId($objValueTable, $mixValue);
        
        $objEavEntityValueTable = new Bf_Eav_Db_EntitiesValues();
        //Remove all other values for this Entity,Attribute,Language
        $arrWhere[] = $objEavEntityValueTable->getAdapter()->quoteInto(Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES . "= ?", $intEntityId);
        $arrWhere[] = $objEavEntityValueTable->getAdapter()->quoteInto(Bf_Eav_Db_EntitiesValues::COL_ID_ATTR . "= ?", $intAttribId);
        $arrWhere[] = $objEavEntityValueTable->getAdapter()->quoteInto(Bf_Eav_Db_EntitiesValues::COL_ID_VALUES . "<>?", $intValueId);
        $arrWhere[] = $objEavEntityValueTable->getAdapter()->quoteInto(Bf_Eav_Db_EntitiesValues::COL_ID_LANGUAGES . "=?", $intLangId);
        $objEavEntityValueTable->delete($arrWhere);
        
        $objEavEntityValueTableRowSet = $objEavEntityValueTable->find($intEntityId, $intAttribId, $intValueId, $intLangId);
        
        if ($objEavEntityValueTableRowSet->count() <= 0) {
            // Save New			
            $arrEntityValueData[Bf_Eav_Db_EntitiesValues::COL_ID_ENTITIES] = $intEntityId;
            $arrEntityValueData[Bf_Eav_Db_EntitiesValues::COL_ID_ATTR] = $intAttribId;
            $arrEntityValueData[Bf_Eav_Db_EntitiesValues::COL_ID_VALUES] = $intValueId;
            $arrEntityValueData[Bf_Eav_Db_EntitiesValues::COL_ID_LANGUAGES] = $intLangId;
            $arrEntityValueData[Bf_Eav_Db_EntitiesValues::COL_IS_DELETED] = FALSE;
            
            if (! $objEavEntityValueTable->createRow($arrEntityValueData)->save()) {
                throw new Bf_Exception();
            }
        } else {
            $objValueRow = $objEavEntityValueTableRowSet->current();
            if ($objValueRow->{Bf_Eav_Db_EntitiesValues::COL_IS_DELETED}) {
                $objValueRow->{Bf_Eav_Db_EntitiesValues::COL_IS_DELETED} = FALSE;
                $objValueRow->save();
            }
        }
        return TRUE;
    }

    protected static function getValueId ($objValueTable, $mixValue)
    {
        if (strlen(trim($mixValue)) > 0) {
            
            $objValueTableSelect = $objValueTable->select(TRUE);
            $objValueTableSelect->where(Bf_Eav_Db_Values_Abstract::COL_VALUE . " = ?", $mixValue);
            
            $objValueTableRow = $objValueTable->fetchRow($objValueTableSelect);
            
            if (empty($objValueTableRow)) {
                $objValueTableRow = $objValueTable->createRow(array(Bf_Eav_Db_Values_Abstract::COL_VALUE => $mixValue));
                if (! $objValueTableRow->save()) {
                    throw new Bf_Exception();
                }
            }
            $intValueId = (int) $objValueTableRow->{Bf_Eav_Db_Values_Abstract::COL_ID_VALUES};
        } else {
            $intValueId = 0; //Empty string
        }
        return $intValueId;
    }

    /**
     * 
     * Enter description here ...
     * @param int $intAttrId
     * @param string $mixAttrType
     * @return ZendX_JQuery_Form
     */
    public function getAttrEditForm ($intAttrId, $mixAttrType)
    {
        
        $objForm = new ZendX_JQuery_Form();
        
        // :TODO 
        $objForm->setName('attrForm');
        $objForm->setAttrib('id', 'attrForm');
        
        // ID
        $objAttrId = new Zend_Form_Element_Hidden(Bf_Eav_Db_Attributes::COL_ID_ATTR);
        
        // Code
        $objAttrCode = new Zend_Form_Element_Text(Bf_Eav_Db_Attributes::COL_ATTR_CODE);
        $objAttrCode->setLabel('LBL_EAV_ATTR_CODE');
        if (! empty($intAttrId)) {
            $objAttrCode->setAttrib('readonly', true);
        } else {
            $objAttrCode->addValidator('Db_NoRecordExists', TRUE, array('table' => Bf_Eav_Db_Attributes::TBL_NAME, 'field' => Bf_Eav_Db_Attributes::COL_ATTR_CODE));
        }
        $objAttrCode->isRequired(TRUE);
        
        // Description
        $objAttrDesc = new Zend_Form_Element_Text(Bf_Eav_Db_Attributes::COL_DESCRIPTION);
        $objAttrDesc->setLabel('LBL_EAV_ATTR_DESC');
        
        $objAttrShowList = new Zend_Form_Element_Checkbox(Bf_Eav_Db_Attributes::COL_IS_SHOW_LIST);
        $objAttrShowList->setLabel('LBL_EAV_ATTR_SHOW_LIST')
            ->setAttrib('id', Bf_Eav_Db_Attributes::COL_IS_SHOW_LIST)
            ->setRequired(FALSE);
        
        // Unit
        $objAttrUnits = new Zend_Form_Element_Text(Bf_Eav_Db_Attributes::COL_UNITS);
        $objAttrUnits->setLabel('LBL_EAV_ATTR_UNITS');
        
        // Type
        $objAttrType = new Zend_Form_Element_Hidden(Bf_Eav_Db_Attributes::COL_VALUE_TYPE);
        $objAttrType->setValue($mixAttrType);
        
        // Group Them
        $objForm->addDisplayGroup(array($objAttrId, $objAttrCode, $objAttrDesc, $objAttrUnits, $objAttrShowList, $objAttrType), 'attr_spec', array('legend' => "LBL_ATTR_SPEC"));
        
        // Get Data To Populate		
        $objAttr = new Bf_Eav_Db_Attributes();
        if (! empty($intAttrId)) {
            $objAttrRowSet = $objAttr->find($intAttrId);
            if ($objAttrRowSet->count() > 0) {
                $objAttrRow = $objAttrRowSet->current();
                $objForm->populate($objAttrRow->toArray());
            }
        }
        
        return $objForm;
    }

    public function getAttrEditExtra ($intAttrId)
    {}

    public function saveAttr (Array $arrData)
    {
        $intAttrId = 0;
        $objAttr = new Bf_Eav_Db_Attributes();
        if (! empty($arrData[Bf_Eav_Db_Attributes::COL_ID_ATTR])) {
            // Update
            $objAttrRowSet = $objAttr->find($arrData[Bf_Eav_Db_Attributes::COL_ID_ATTR]);
            if ($objAttrRowSet->count() > 0) {
                $objAttrRow = $objAttrRowSet->current();
                // Remove unneede elements (not allowed to change...)
                unset($arrData[Bf_Eav_Db_Attributes::COL_ID_ATTR]);
                unset($arrData[Bf_Eav_Db_Attributes::COL_ATTR_CODE]);
                unset($arrData[Bf_Eav_Db_Attributes::COL_VALUE_TYPE]);
                
                $objAttrRow->setFromArray($arrData);
                $intAttrId = $objAttrRow->save();
            }
        
        } else {
            // Add
            unset($arrData[Bf_Eav_Db_Attributes::COL_ID_ATTR]);
            $objAttrRow = $objAttr->createRow();
            $objAttrRow->setFromArray($arrData);
            $intAttrId = $objAttrRow->save();
        }
        
        if (! empty($intAttrId)) {
            $this->saveAttrEditFormExtra($intAttrId, $arrData);
        }
        
        return $intAttrId;
    }

    protected function saveAttrEditFormExtra ($intAttrId, Array $arrData)
    {}

    public function setGridCol ($objAttrListRow, $objGrid)
    {
        $objColumn = new Ingot_JQuery_JqGrid_Column(str_replace(' ', '_', $objAttrListRow->{Bf_Eav_Db_Attributes::COL_ATTR_CODE}), 
        array('sortable' => FALSE, 'useHaving' => true, 'customField' => Bf_Catalog_Models_Catalog::COL_ATTR_DATA, 'unionPart' => 1));
        $objDecorator = new Ingot_JQuery_JqGrid_Column_Decorator_Eav($objColumn, 
        array('intAttrId' => $objAttrListRow->{Bf_Eav_Db_Attributes::COL_ID_ATTR}, 'eavValueType' => $objAttrListRow->{Bf_Eav_Db_Attributes::COL_VALUE_TYPE}));
        $objGrid->addColumn($objDecorator);
    }

    public function getValuePairs ($arrSearchCriteria = array())
    {
        $strClassName = $this->getValuesDbClassName();
        
        $objDbClass = new $strClassName();
        
        $objSelect = $objDbClass->select(TRUE);
        foreach ($arrSearchCriteria as $mixSearchCriteria) {
            if (is_array($mixSearchCriteria)) {
                $objSelect->where($mixSearchCriteria['criteria'], $mixSearchCriteria['value']);
            } else {
                $objSelect->where(Bf_Eav_Db_Values_Abstract::COL_VALUE . " like ?", $mixSearchCriteria);
            }
        }
        $objSelect->where(Bf_Eav_Db_Values_Abstract::COL_IS_DELETED." = ?",FALSE);
        $objSelect->reset(Zend_Db_Select::COLUMNS);
        $objSelect->columns(array(Bf_Eav_Db_Values_Abstract::COL_ID_VALUES, Bf_Eav_Db_Values_Abstract::COL_VALUE));
        return $objDbClass->getAdapter()->fetchPairs($objSelect);
    }
}