<?php

class Bf_Eav_Db_AttributesValues extends Bf_Db_Table
{

    const TBL_NAME = 'attributes_values_select';

    const COL_ID_ATTRIBUTES = 'id_attributes';

    const COL_ID_VALUES = 'id_values';

    const COL_SORT_ORDER = 'sort_order';

    /**
     * 
     * Enter description here ...
     * @param unknown_type $objValues
     * @param unknown_type $intAttributeId
     * @return Zend_Db_Table_Select
     */
    public static function getAttrValueSelect ($objValues, $intAttributeId)
    {
        //Get options
        $objValuesSelect = $objValues->select(TRUE)->setIntegrityCheck(FALSE);
        $objValuesSelect->joinInner(Bf_Eav_Db_AttributesValues::TBL_NAME, 
        Bf_Eav_Db_AttributesValues::getColumnName(Bf_Eav_Db_AttributesValues::COL_ID_VALUES) . "=" . Bf_Eav_Db_Values_Select::getColumnName(Bf_Eav_Db_Values_Select::COL_ID_VALUES));
        $objValuesSelect->where(Bf_Eav_Db_AttributesValues::getColumnName(Bf_Eav_Db_AttributesValues::COL_ID_ATTRIBUTES) . "=?", (int) $intAttributeId);
        $objValuesSelect->where(Bf_Eav_Db_AttributesValues::getColumnName(Bf_Eav_Db_AttributesValues::COL_IS_DELETED) . "=?", FALSE);
        
        $objValuesSelect->order(Bf_Eav_Db_AttributesValues::getColumnName(Bf_Eav_Db_AttributesValues::COL_SORT_ORDER));
        
        return $objValuesSelect;
    }

    public static function fetchAttrValuePair ($strValueTableClass, $intAttributeId)
    {
        $objValues = Bf_General::initObject($strValueTableClass, array(), null, 'Bf_Eav_Db_Values_Abstract');
        $objAttrValSelect = self::getAttrValueSelect($objValues, $intAttributeId);
        $objAttrValSelect->reset(Zend_Db_Select::COLUMNS);
        $objAttrValSelect->columns(array(Bf_Eav_Db_Values_Abstract::COL_ID_VALUES, Bf_Eav_Db_Values_Abstract::COL_VALUE), $strValueTableClass::TBL_NAME);
        return $objValues->getAdapter()->fetchPairs($objAttrValSelect);
    }

    public static function saveValForAttr ($intAttrId, $strAttrVal = null)
    {
        if (! is_null($strAttrVal)) {
            $boolNewVal = false;
            // First check that value exist...        
            $objEavSelectVal = new Bf_Eav_Db_Values_Select();
            $objEavSelectValSelect = $objEavSelectVal->select(TRUE);
            $objEavSelectValSelect->where(Bf_Eav_Db_Values_Select::TBL_NAME . '.' . Bf_Eav_Db_Values_Select::COL_VALUE . " = ?", $strAttrVal);
            
            $objEavSelectValRow = $objEavSelectVal->fetchRow($objEavSelectValSelect);
            
            if (empty($objEavSelectValRow)) {
                // VAl not found, create new one
                $boolNewVal = TRUE;
                $objEavSelectValRow = $objEavSelectVal->createRow();
                $objEavSelectValRow->{Bf_Eav_Db_Values_Select::COL_VALUE} = $strAttrVal;
                
                if (! $objEavSelectValRow->save()) {
                    throw new Bf_Catalog_Exception();
                }
            }
            $objEavAttrValRow = NULL;
            $objEavAttrVal = new self();
            if (! $boolNewVal) {
                // This is not a new value...
                $objEavAttrValSelect = $objEavAttrVal->select(TRUE)->setIntegrityCheck(FALSE);
                $objEavAttrValSelect->where(self::TBL_NAME . '.' . self::COL_ID_ATTRIBUTES . ' = ?', $intAttrId);
                $objEavAttrValSelect->where(self::TBL_NAME . '.' . self::COL_ID_VALUES . ' = ?', $objEavSelectValRow->{Bf_Eav_Db_Values_Select::COL_ID_VALUES});
                $objEavAttrValRow = $objEavAttrVal->fetchRow($objEavAttrValSelect);
            }
            
            if (empty($objEavAttrValRow)) {
                // Eather it's a new value or it was not found for this attrib
                $objEavAttrValRow = $objEavAttrVal->createRow();
                $objEavAttrValRow->{self::COL_ID_ATTRIBUTES} = $intAttrId;
                $objEavAttrValRow->{self::COL_ID_VALUES} = $objEavSelectValRow->{Bf_Eav_Db_Values_Select::COL_ID_VALUES};
            }
            
            $objEavAttrValRow->{self::COL_IS_DELETED} = FALSE;
            
            if (! $objEavAttrValRow->save()) {
                throw new Bf_Catalog_Exception();
            }
            return $objEavSelectValRow->{Bf_Eav_Db_Values_Select::COL_ID_VALUES};
        } else {
            return 0;
        }
    }

}