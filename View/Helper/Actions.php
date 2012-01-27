<?php
/**
 *
 * @author sirshurf
 * @version 
 */

class Bf_View_Helper_Actions extends Zend_View_Helper_Abstract {
	
	public function Actions($arrOptions = array()) {
		$arrOptions['hr'] = isset($arrOptions['hr'])?((bool)$arrOptions['hr']):true; 
		$html = '';
		$htmlF = "";
		if (! empty ( $this->view->arrActions )) {
			ob_start ();
			foreach ( ( array ) $this->view->arrActions as $arrAction ) {
				// Check Permissions
				$objAcl = User_Model_Acl::$objIntance;
				$strClass = '';
				$strOnClick = '';
// 				Zend_Debug::dump($arrAction);
// 				exit();
				if (! $objAcl->checkPermissions ( $arrAction ['module'],$arrAction ['controller'], $arrAction ['action'] )) {
					continue;
				}
			
				if (! empty ( $arrAction ['class'] )) {
					$strClass = $arrAction ['class'];
				}
				
				if (! empty ( $arrAction ['onClick'] )) {
					$strOnClick = $arrAction ['onClick'];
				} elseif (! empty ( $arrAction ['uri'] )) {
					$strOnClick = 'document.location.href="' . $arrAction ['uri'] . '"';
				} else {
					
					$arrParams = array ();
					if (!empty($arrAction ['module'])){
						$arrParams ['module'] = $arrAction ['module'];
					} else {
						$arrParams ['module'] = 'default';
					}
					$arrParams ['controller'] = $arrAction ['controller'];
					$arrParams ['action'] = $arrAction ['action'];
					if (! empty ( $arrAction ['params'] )) {
						foreach ( $arrAction ['params'] as $strParamKey => $strParamValue ) {
							$arrParams [$strParamKey] = $strParamValue;
						}
					}
					$strOnClick = 'document.location.href="' . $this->view->url ( $arrParams, null, true ) . '"';
				}
				?>
			<button type="button" onClick='<?php
				echo $strOnClick;
				?>' class='<?php
				echo $strClass;
				?>'><?php
				echo ($arrAction ['name'])?$this->view->translate($arrAction ['name']):"";?></button>&nbsp;	
			<?php
			}

			$html = ob_get_clean ();
			$html = trim($html);
			if (!empty($html)){
					ob_start ();
						echo $this->view->PageDesc();
						echo $arrOptions['hr']?"<hr />":"";
						echo isset($arrOptions['caption'])?$arrOptions['caption']:$this->view->translate('LBL_ACTIONS');
						echo "<div>{$html}</div>";
						echo $arrOptions['hr']?"<hr />":"";
					$htmlF = ob_get_clean ();
			} else {
				$htmlF = $this->view->PageDesc();	
			}
		} else {
				$htmlF = $this->view->PageDesc();		
		}
		return $htmlF;
	}

}

