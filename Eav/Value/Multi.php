<?php
abstract class Bf_Eav_Value_Multi extends Bf_Eav_Value_Simple
{

	public static function getFormElementHelper ($strElementClass, $strValueTableClass, $intAttributeId, $strAttributeCode, $boolIsRequired = FALSE, $arrValues = null) {
		
		$objElement = parent::getFormElementHelper($strElementClass, $strValueTableClass, $intAttributeId, $strAttributeCode, $boolIsRequired, $arrValues);
		
		$arrSelectValues = array();
		$arrSelectValues[] = "LBL_EAV_PLEASE_SELECT";
		
		$arrSelectValues += Bf_Eav_Db_AttributesValues::fetchAttrValuePair($strValueTableClass, $intAttributeId);
		$objElement->addMultiOptions($arrSelectValues);
		
		return $objElement;
	}

	protected static function getValueId ($objValueTable, $mixValue) {
		return (int) $mixValue;
	}

	public static function setValue (Zend_Form_Element $objElement, $strValueTableClass, $arrValues = null) {
		if (! is_null($arrValues)) {
			$objValueTable = Bf_General::initObject($strValueTableClass, array(), null, 'Bf_Eav_Db_Values_Abstract');
			
			$value = $objValueTable->find($arrValues);
			if ($value->count() > 0) {
				$objElement->setValue($value->current()->{Bf_Eav_Db_Values_Abstract::COL_ID_VALUES});
			}
		}
	}

	public function getAttrEditExtra ($intAttrId, $mixAttrType) {
		
		$strValueDbClassName = self::getValuesDbClassName();
		
		// Get Values		
		$objValues = Bf_General::initObject($strValueDbClassName, array(), null, 'Bf_Eav_Db_Values_Abstract');
		$objAttrValuesSelect = Bf_Eav_Db_AttributesValues::getAttrValueSelect($objValues, $intAttrId);
		
		$arrOptions = array('caption' => '');
		$arrOptions['sortname'] = Bf_Eav_Db_AttributesValues::COL_SORT_ORDER;
		$arrOptions['sortorder'] = Ingot_JQuery_JqGrid::SORT_ASC;
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		if ($viewRenderer->view === null) {
			$viewRenderer->initView();
		}
		$viewRenderer->view;
		
		$strUrl = $viewRenderer->view->url(array('module' => 'eav', 'controller' => 'index', 'action' => 'save-attrib-multi-value', Bf_Eav_Db_Attributes::COL_ID_ATTR=>$intAttrId,Bf_Eav_Db_Attributes::COL_VALUE_TYPE=>$mixAttrType ), null, TRUE);
		$arrOptions['editurl'] = $strUrl;
		$arrOptions['postData'] = array(Bf_Eav_Db_Attributes::COL_ID_ATTR=>$intAttrId,Bf_Eav_Db_Attributes::COL_VALUE_TYPE=>$mixAttrType);
		
		$objGrid = new Ingot_JQuery_JqGrid('Groups', $objAttrValuesSelect, $arrOptions);
		$objGrid->setIdCol($strValueDbClassName::COL_ID_VALUES);
		$objGrid->setDblClkEdit(TRUE);
		
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column($strValueDbClassName::COL_VALUE, array('editable' => true)));
		$objGrid->addColumn(new Ingot_JQuery_JqGrid_Column(Bf_Eav_Db_AttributesValues::COL_SORT_ORDER, array('editable' => true, 'defaultValue' => 99)));
		
		$objGridPager = $objGrid->getPager();
		$objGridPager->setDefaultAdd();
		$objGridPager->setDefaultEdit();
		$objGridPager->setDefaultDel();
		
		$objGrid->registerPlugin(new Ingot_JQuery_JqGrid_Plugin_ToolbarFilter());
		return $objGrid->render();
	}
	
    public function setGridCol ($objAttrListRow, $objGrid)
    {
        $objForm = new Zend_Form();
        
        $objColumn = new Ingot_JQuery_JqGrid_Column($objForm->filterName($objAttrListRow->{Bf_Eav_Db_Attributes::COL_ATTR_CODE}), 
        array('sortable' => FALSE, 'useHaving' => true, 'customField' => Bf_Catalog_Models_Catalog::COL_ATTR_DATA, 'unionPart' => 1));

        $objParentDecorator = new Ingot_JQuery_JqGrid_Column_Decorator_Search_Select($objColumn, array('value' => self::getValuePairs(array(), $objAttrListRow->{Bf_Eav_Db_Attributes::COL_ID_ATTR}), 'sopt' => array('bw')));
        
        $objDecorator = new Ingot_JQuery_JqGrid_Column_Decorator_EavSelect($objParentDecorator, 
        array('intAttrId' => $objAttrListRow->{Bf_Eav_Db_Attributes::COL_ID_ATTR}, 'eavValueType' => $objAttrListRow->{Bf_Eav_Db_Attributes::COL_VALUE_TYPE}));
              
       
        $objGrid->addColumn($objDecorator);
    }
    
    public function getValuePairs ($arrSearchCriteria = array(), $intAttrId)
    {
        $strClassName = $this->getValuesDbClassName();
        
        $objDbClass = new $strClassName();
        
        $objSelect = $objDbClass->select(TRUE);
        $objSelect->join(Bf_Eav_Db_AttributesValues::TBL_NAME,Bf_Eav_Db_AttributesValues::TBL_NAME.'.'.Bf_Eav_Db_AttributesValues::COL_ID_VALUES." = ".$strClassName::TBL_NAME.".".Bf_Eav_Db_Values_Abstract::COL_ID_VALUES);
        
        foreach ($arrSearchCriteria as $mixSearchCriteria) {
            if (is_array($mixSearchCriteria)) {
                $objSelect->where($mixSearchCriteria['criteria'], $mixSearchCriteria['value']);
            } else {
                $objSelect->where(Bf_Eav_Db_Values_Abstract::COL_VALUE . " like ?", $mixSearchCriteria);
            }
        }
        $objSelect->where(Bf_Eav_Db_AttributesValues::TBL_NAME.'.'.Bf_Eav_Db_Values_Abstract::COL_IS_DELETED." = ?",FALSE);
        $objSelect->where(Bf_Eav_Db_AttributesValues::COL_ID_ATTRIBUTES." = ?",$intAttrId);
        $objSelect->reset(Zend_Db_Select::COLUMNS);
        $objSelect->columns(array(Bf_Eav_Db_Values_Abstract::COL_ID_VALUES, Bf_Eav_Db_Values_Abstract::COL_VALUE));
        return $objDbClass->getAdapter()->fetchPairs($objSelect);
    }
    
	
}