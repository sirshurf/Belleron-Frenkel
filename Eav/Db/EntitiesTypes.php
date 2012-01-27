<?php
class Bf_Eav_Db_EntitiesTypes extends Bf_Db_Table
{
	CONST TBL_NAME = "entities_types";
	
	CONST COL_ID_ENTITIES_TYPES = 'id_entities_types';
	CONST COL_ENTITY_TYPE_TITLE	= 'entity_type_title';
	CONST COL_SORT_ORDER		= 'sort_order';
	CONST COL_IS_FOLDER			= 'is_folder';
	CONST COL_IS_LOCKED			= 'is_locked';
	
	public static function getPairSelect($boolIsFolder){
	    $objModel = new self();
	    $objSelect = $objModel->select(TRUE);
	    $objSelect->where(self::COL_IS_FOLDER." = ?",$boolIsFolder);
	    $objSelect->where(self::COL_IS_DELETED." = ?",FALSE);
	    
	    return $objSelect;
	}
	
}