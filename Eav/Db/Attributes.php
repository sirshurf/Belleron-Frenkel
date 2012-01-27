<?php
class Bf_Eav_Db_Attributes extends Bf_Db_Table
{
	CONST TBL_NAME = "attributes";
	
	CONST COL_ID_ATTR = 'id_attributes';
	CONST COL_ATTR_CODE = 'attribute_code';
	CONST COL_DESCRIPTION = 'description';
	CONST COL_UNITS = 'units';
	CONST COL_VALUE_TYPE = 'value_type';
	CONST COL_IS_MULTI_VALUE = 'is_multi_value';
	CONST COL_IS_SHOW_LIST = 'is_show_list';
	
	CONST ATTR_VAL_TYPE_TEXT = 'text';
	CONST ATTR_VAL_TYPE_LONGTEXT = 'longtext';
	CONST ATTR_VAL_TYPE_INT = 'integer';
	CONST ATTR_VAL_TYPE_FLOAT = 'float';
	CONST ATTR_VAL_TYPE_DATETIME = 'datetime';
	CONST ATTR_VAL_TYPE_SELECT = 'select';
	CONST ATTR_VAL_TYPE_FILE = 'file';
	CONST ATTR_VAL_TYPE_BLOB = 'blob';
	CONST ATTR_VAL_TYPE_DATE = 'date';
	CONST ATTR_VAL_TYPE_BOOLEAN = 'boolean';
	CONST ATTR_VAL_TYPE_MULTISELECT = 'multiselect';
	CONST ATTR_VAL_TYPE_RADIOSELECT = 'radioselect';
	CONST ATTR_VAL_TYPE_CHECKLIST = 'checklist';
	
	public static $arrAttrValType = array(self::ATTR_VAL_TYPE_TEXT => 'LBL_ATTR_VAL_TYPE_TEXT', self::ATTR_VAL_TYPE_LONGTEXT => 'LBL_ATTR_VAL_TYPE_LONGTEXT', self::ATTR_VAL_TYPE_INT => 'LBL_ATTR_VAL_TYPE_INT', self::ATTR_VAL_TYPE_FLOAT => 'LBL_ATTR_VAL_TYPE_FLOAT', //		self::ATTR_VAL_TYPE_DATETIME => 'LBL_ATTR_VAL_TYPE_DATETIME',
	self::ATTR_VAL_TYPE_SELECT => 'LBL_ATTR_VAL_TYPE_SELECT')//		self::ATTR_VAL_TYPE_FILE => 'LBL_ATTR_VAL_TYPE_FILE',
	//		self::ATTR_VAL_TYPE_BLOB => 'LBL_ATTR_VAL_TYPE_BLOB',
	//		self::ATTR_VAL_TYPE_DATE => 'LBL_ATTR_VAL_TYPE_DATE',
	;

	
	public static function getAttribList($boolReload = FALSE){
	    $objAttr = new self();
	    $objSelect = $objAttr->select(TRUE);
	    $objSelect->where(self::COL_IS_DELETED." = ?",FALSE);
	    $objSelect->where(self::COL_IS_SHOW_LIST." = ?",TRUE);
	    
	    return $objAttr->fetchAll($objSelect);
	    
	}
}