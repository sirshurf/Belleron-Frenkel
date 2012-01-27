<?php
class Bf_Eav_Db_EntitiesTypesGroups extends Bf_Db_Table
{
	CONST TBL_NAME = "entities_types_groups";
	
	CONST COL_ID_ENTITIES_TYPES_GRP 	= 'id_entities_types_groups';
	CONST COL_ID_ENTITIES_TYPES 		= 'id_entities_types';
	CONST COL_GRP_LEGEND_CODE 			= 'group_legend_code';
	CONST COL_ORDER				 		= 'sort_order';

	public static function getGroupPair($intEntType){
		$obj = new self();
		$objSelect = $obj->select(TRUE);
		$objSelect->where(self::COL_ID_ENTITIES_TYPES." = ?",$intEntType);
		$objSelect->where(self::COL_IS_DELETED." = ?",FALSE);
		$objSelect->order(self::COL_ORDER." ".Zend_Db_Select::SQL_ASC);
		
		$objSelect->reset(Zend_Db_Select::COLUMNS);
		$objSelect->columns(array(self::COL_ID_ENTITIES_TYPES_GRP,self::COL_GRP_LEGEND_CODE));
		
		return $obj->getAdapter()->fetchPairs($objSelect);
	}
}