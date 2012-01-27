<?php

require_once ('Zend/Filter/Interface.php');

class Bf_Filter_MysqlDate implements Zend_Filter_Interface {
	/**
	 * Filter element converts date in d/m/y to time stampe
	 * @param unknown_type $value
	 */
	public function filter($value) {
	    
	    $objDateTime = DateTime::createFromFormat('d/m/Y', $value);    
	    		
		return $objDateTime->format(Bf_Db_Table::MYSQL_DATETIME);
	
	}

}