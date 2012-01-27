<?php

require_once 'Zend/Application.php';

/**
 * @category   Zend
 * @package    Zend_Application
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Bf_Application extends Zend_Application {
	
	/**
	 * Load configuration file of options
	 *
	 * @param  string $file
	 * @throws Zend_Application_Exception When invalid configuration file is provided
	 * @return array
	 */
	protected function _loadConfig($file) {
		
		$environment = $this->getEnvironment ();
		
		$filename = strtolower ( pathinfo ( $file, PATHINFO_FILENAME ) );
		
		if ("*" === $filename) {
			// Read all configuration from directory!
//			throw new Exception($file);
			$dirRead = strtolower ( pathinfo ( $file, PATHINFO_DIRNAME ) );
			$resDir = opendir ( $dirRead );
			
			$config = array ();
			while ( false !== ($strFile = readdir ( $resDir )) ) {
				if (is_file( $dirRead . '/' . $strFile)){
					$config = $this->mergeOptions ( $config, $this->_loadConfig ( $dirRead . '/' . $strFile ) );
				}
			}
		
		} else {
			
			$suffix = strtolower ( pathinfo ( $file, PATHINFO_EXTENSION ) );
			
			switch ($suffix) {
				case 'ini' :
					$config = new Zend_Config_Ini ( $file, $environment );
					break;
				
				case 'xml' :
					$config = new Zend_Config_Xml ( $file, $environment );
					break;
				
				case 'json' :
					$config = new Zend_Config_Json ( $file, $environment );
					break;
				
				case 'yaml' :
					$config = new Zend_Config_Yaml ( $file, $environment );
					break;
				
				case 'php' :
				case 'inc' :
					$config = include $file;
					if (! is_array ( $config )) {
						throw new Zend_Application_Exception ( 'Invalid configuration file provided; PHP file does not return array value' );
					}
					return $config;
					break;
				
				default :
					throw new Zend_Application_Exception ( 'Invalid configuration file provided; unknown config type' );
			}
			
			$config = $config->toArray ();
			
			// check that there is no second layer
			if (! empty ( $config ['config'] )) {
				if (is_array ( $config ['config'] )) {
					$_options = array ();
					foreach ( $config ['config'] as $tmp ) {
						$_options = $this->mergeOptions ( $_options, $this->_loadConfig ( $tmp ) );
					}
					$config = $this->mergeOptions ( $_options, $config );
				} else {
					$config = $this->mergeOptions ( $this->_loadConfig ( $config ['config'] ), $config );
				}
			}
		}
		
		return $config;
	}

}