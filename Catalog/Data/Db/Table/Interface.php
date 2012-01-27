<?php
interface Bf_Catalog_Data_Db_Table_Interface {
	/**
	 * @param Zend_Db_Table_Select $objSelect
	 * @param string $strCatlogIdColumn
	 * @param string $strCatalogTableName
	 * @param int $intLanguageId
	 */
	public function addDataToCatalogSelect(Zend_Db_Table_Select &$objSelect,$strCatlogIdColumn,$strCatalogTableName,$intLanguageId = null);
}