<?php
class Bf_Db_Table_Row extends Zend_Db_Table_Row {
	
	/**DateTime();
		$this->created_on = $objDateTime->format(DATE_ISO8601);
		
	 * Allows pre-insert logic to be applied to row.
	 * Subclasses may override this method.
	 *
	 * @return void
	 */
	protected function _insert() {
	    $objDateTime = new DateTime();

	    $this->created_on = $objDateTime->format(Bf_Db_Table::MYSQL_DATETIME);
	    
		$objUserSessionData = new Zend_Session_Namespace ( 'user' );
		$objUserDetails = $objUserSessionData->userDetails;
		
		if (! empty ( $objUserDetails->{User_Model_Db_Users::COL_ID_USERS} )) {
			$this->{Bf_Db_Table::COL_CREATED_BY} = (int)$objUserDetails->{User_Model_Db_Users::COL_ID_USERS};
		} else {
			$this->{Bf_Db_Table::COL_CREATED_BY} = 0;
		}
	}
	
	/**
	 * Allows pre-update logic to be applied to row.
	 * Subclasses may override this method.
	 *
	 * @return void
	 */
	protected function _update() {
		
		$objUserSessionData = new Zend_Session_Namespace ( 'user' );
		$objUserDetails = $objUserSessionData->userDetails;
		
	    $objDateTime = new DateTime();
		$this->{Bf_Db_Table::COL_UPDATED_BY} = $objDateTime->format(Bf_Db_Table::MYSQL_DATETIME);
		
		if (! empty ( $objUserDetails->{User_Model_Db_Users::COL_ID_USERS} )) {
			$this->{Bf_Db_Table::COL_UPDATED_BY} = (int)$objUserDetails->{User_Model_Db_Users::COL_ID_USERS};
		} else {
			$this->{Bf_Db_Table::COL_UPDATED_BY} = 0;
		}
	
	}
	
	
	public function delete(){
		
		$this->is_deleted = 1;
		return $this->save (  );
	}
	
	public function isModified($columnName) {
		
		$columnName = $this->_transformColumn ( $columnName );
		if (! array_key_exists ( $columnName, $this->_data )) {
			require_once 'Zend/Db/Table/Row/Exception.php';
			throw new Zend_Db_Table_Row_Exception ( "Specified column \"$columnName\" is not in the row" );
		}
		return isset( $this->_modifiedFields[$columnName] );
	
	}
	
	/**
	 * Set row field value
	 *
	 * @param  string $columnName The column key.
	 * @param  mixed  $value      The value for the property.
	 * @return void
	 * @throws Zend_Db_Table_Row_Exception
	 */
	public function __set($columnName, $value) {
		$columnName = $this->_transformColumn ( $columnName );
		if (! array_key_exists ( $columnName, $this->_data )) {
			require_once 'Zend/Db/Table/Row/Exception.php';
			throw new Zend_Db_Table_Row_Exception ( "Specified column \"$columnName\" is not in the row" );
		}
		if ($this->_data [$columnName] !== $value) {
			$this->_data [$columnName] = $value;
			$this->_modifiedFields [$columnName] = true;
		}
	}
	
	public function getCleanColumn($columnName){
		$columnName = $this->_transformColumn ( $columnName );
		if (! array_key_exists ( $columnName, $this->_data )) {
			require_once 'Zend/Db/Table/Row/Exception.php';
			throw new Zend_Db_Table_Row_Exception ( "Specified column \"$columnName\" is not in the row" );
		}
	    
        return $this->_cleanData[$columnName];
	}
	
}