<?php
/**
 *
 * @author sirshurf
 * @version 
 */

class Bf_View_Helper_PageDesc extends Zend_View_Helper_Abstract {
	
	public function PageDesc() {
		
		$htmlF = "";
		
		$controllerName = Zend_Controller_Front::getInstance ()->getRequest ()->getControllerName ();
		$actionName = Zend_Controller_Front::getInstance ()->getRequest ()->getActionName ();
		
		$strCode = "LBL_TEXT_" . strtoupper ( $controllerName ) . "_" . strtoupper ( $actionName );
		// Zend_debug::dump($strCode);
		if (!Zend_Registry::isRegistered("textFlag") && $this->view->translate ( $strCode ) !== $strCode) {

			$htmlF = $this->view->translate ( $strCode );
			
			Zend_Registry::set("textFlag", true);
		} 
		
		return $htmlF;
	}

}

