<?php
class Bf_Catalog_Models_Db_Catalog_Row extends Bf_Db_Table_Row {
	
	public function delete() {
		$res = parent::delete();
		$this->_getTable()->setHasChildrenFlags($this->_getPrimaryKey());
		return $res;
	}
}