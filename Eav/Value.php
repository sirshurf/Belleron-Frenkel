<?php
class Bf_Eav_Value {

	/**
	 * Enter description here ...
	 * @param string $strValueType
	 * @param unknown_type $config
	 * @return Bf_Eav_Value_Abstract
	 */
	public static function factory($strValueType,$config = array()) {

		switch ($strValueType) {
			case Bf_Eav_Db_Attributes::ATTR_VAL_TYPE_BLOB:
				return new Bf_Eav_Value_Blob($config);
				break;
			case Bf_Eav_Db_Attributes::ATTR_VAL_TYPE_DATETIME:
				return new Bf_Eav_Value_Datetime($config);
				break;
			case Bf_Eav_Db_Attributes::ATTR_VAL_TYPE_DATE:
				return new Bf_Eav_Value_Date($config);
				break;
			case Bf_Eav_Db_Attributes::ATTR_VAL_TYPE_FILE:
				return new Bf_Eav_Value_File($config);
				break;
			case Bf_Eav_Db_Attributes::ATTR_VAL_TYPE_FLOAT:
				return new Bf_Eav_Value_Float($config);
				break;
			case Bf_Eav_Db_Attributes::ATTR_VAL_TYPE_INT:
				return new Bf_Eav_Value_Integer($config);
				break;
			case Bf_Eav_Db_Attributes::ATTR_VAL_TYPE_SELECT:
				return new Bf_Eav_Value_Select($config);
				break;
			case Bf_Eav_Db_Attributes::ATTR_VAL_TYPE_LONGTEXT:
				return new Bf_Eav_Value_Longtext($config);
				break;
			case Bf_Eav_Db_Attributes::ATTR_VAL_TYPE_TEXT:
				return new Bf_Eav_Value_Text($config);
				break;
			default:
				throw new Bf_Exception();
		}
	}
}