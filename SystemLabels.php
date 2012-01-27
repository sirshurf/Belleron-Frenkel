<?php
/**
 * User Model Class
 * 
 * @author shurf
 */
class Bf_SystemLabels
{
	
	public static function initLables ($boolForceReload = false)
	{
		
		$session_user = new Zend_Session_Namespace('labels');
		$objTable = new Bf_Db_Table_SystemLabels();
		
		if ($boolForceReload || empty($session_user->lables)) {
			
			$objRowSet = $objTable->fetchAll();
			
			$arrLables = array();
			
			foreach ($objRowSet as $objRow) {
				$arrLables[$objRow->{Bf_Db_Table_SystemLabels::COL_ID_SYSTEM}] = $objRow->{Bf_Db_Table_SystemLabels::COL_CONTENT};
			}
			
			$session_user->lables = $arrLables;
		
		}
		
		//		$objConfig = new Zend_Config_Ini ( APPLICATION_PATH . '/configs/messages.ini' );
		$objConfig = new Zend_Config($session_user->lables);
		
		//Zend_Registry::set ( 'lables', $objConfig );
		
		$translate = new Zend_Translate(array(
			'adapter' => 'array', 'content' => $objConfig->toArray(), 'locale' => 'en'
		));

		// Create a log instance
		//$writer = new Zend_Log_Writer_Stream('/path/to/file.log');
		//$writer = new Labadmin_Log_Writer_Db($objTable->getAdapter(), self::TBL_NAME);
		
		//$log = new Zend_Log($writer);
		
		// Attach it to the translation instance
		//$translate->setOptions(array(
//			'log' => $log, 'logMessage' => "%message%", 'logUntranslated' => true
	//	));
		
		Zend_Registry::set('Zend_Translate', $translate);
	
	}

}