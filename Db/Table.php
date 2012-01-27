<?php
class Bf_Db_Table extends Zend_Db_Table {
	/**
	 * 
	 * @var string DateTime format for mysql
	 */
	const MYSQL_DATETIME = "Y-m-d H:i:s";
	
	const COL_CREATED_BY = 'created_by';
	const COL_CREATED_ON = 'created_on';
	const COL_UPDATED_BY = 'updated_by';
	const COL_UPDATED_ON = 'updated_on';
	const COL_IS_DELETED = 'is_deleted';
	
	/**
	 * Default Table name, takes it from the Const, (if exists)
	 * @var unknown_type
	 */
	protected $_name = self::TBL_NAME;

	protected $_rowClass = 'Bf_Db_Table_Row';
	
	protected $strInitSql = "";
	
	public static function getColumnName($strColumn, $strDelimiter = ".") {
		$strClass = get_called_class();
		return $strClass::TBL_NAME.$strDelimiter.$strColumn;
	} 
	
	
	public function init(){
				
		$this->_getCols();
		if (!in_array(self::COL_CREATED_BY,$this->_cols)) {
			//Add updated_on column
			$db = $this->getAdapter()->query("ALTER TABLE `{$this->_name}` ADD COLUMN `".self::COL_CREATED_BY."` INT NOT NULL DEFAULT 0");
		}
		if (!in_array(self::COL_CREATED_ON,$this->_cols)) {
			//Add updated_on column
			$db = $this->getAdapter()->query("ALTER TABLE `{$this->_name}` ADD COLUMN `".self::COL_CREATED_ON."` DATETIME");
		}
		
		if (!in_array(self::COL_UPDATED_BY,$this->_cols)) {
			//Add updated_on column
			$db = $this->getAdapter()->query("ALTER TABLE `{$this->_name}` ADD COLUMN `".self::COL_UPDATED_BY."` INT NOT NULL DEFAULT 0");
		}	
		
		if (!in_array(self::COL_UPDATED_ON,$this->_cols)) {
			//Add updated_on column
			$db = $this->getAdapter()->query("ALTER TABLE `{$this->_name}` ADD COLUMN `".self::COL_UPDATED_ON."` DATETIME");
		}

		if (!in_array(self::COL_IS_DELETED,$this->_cols)) {
			//Add updated_on column
			$db = $this->getAdapter()->query("ALTER TABLE `{$this->_name}` ADD COLUMN `".self::COL_IS_DELETED."` INT NOT NULL DEFAULT 0");
		}	
		
		$this->_setup();
	}
	
	public function insert($data) {
		$objDateTime = new DateTime();
		
		$data [self::COL_CREATED_ON] = $objDateTime->format(self::MYSQL_DATETIME); 
		$objUserSessionData = new Zend_Session_Namespace ( 'user' );
		$objUserDetails = $objUserSessionData->userDetails;
		
		if (! empty ( $objUserDetails->{User_Model_Db_Users::COL_ID_USERS} )) {
			$data [self::COL_CREATED_BY] = (int)$objUserDetails->{User_Model_Db_Users::COL_ID_USERS};
		} else {
			$data [self::COL_CREATED_BY] = 0;
		}
		return parent::insert ( $data );
	}
	
	public function update($data, $where) {
		$objUserSessionData = new Zend_Session_Namespace ( 'user' );
		$objUserDetails = $objUserSessionData->userDetails;
		
		$objDateTime = new DateTime();
		$data [self::COL_UPDATED_ON] = $objDateTime->format(self::MYSQL_DATETIME);
	
		if (! empty ( $objUserDetails->{User_Model_Db_Users::COL_ID_USERS} )) {
			$data [self::COL_UPDATED_BY] = (int)$objUserDetails->{User_Model_Db_Users::COL_ID_USERS};
		} else {
			$data [self::COL_UPDATED_BY] = 0;
		}
						
		return parent::update ( $data, $where );
	}
	
	public function delete($where) {
		$data [self::COL_IS_DELETED] = 1;
		$this->update ( $data, $where );
	}
	
	
	public static function getName() {
		$objClass = new self ();
		return $objClass->_name;
	}
	
	/**
	 * 
	 * @param $objTable
	 * @param $objReflection
	 */
	public static function initTable(&$objTable, &$objReflection) {
		$objReflection = new ReflectionClass(get_called_class());
			$strClassName = $objReflection->getName();
			/**
			 * @var  Bf_Db_Table
			 */
			$objTable = new $strClassName ();
			if (!($objTable  instanceof Bf_Db_Table)) {
				unset($objTable);
			}
	}
	
	public function getReferenceByName($ruleKey){
	
        $thisClass = get_class($this);
        if ($thisClass === 'Zend_Db_Table') {
            $thisClass = $this->_definitionConfigName;
        }
        $refMap = $this->_getReferenceMapNormalized();
	
        if ($ruleKey !== null) {
            if (!isset($refMap[$ruleKey])) {
                require_once "Zend/Db/Table/Exception.php";
                throw new Zend_Db_Table_Exception("No reference rule \"$ruleKey\" from table $thisClass");
            }
            return $refMap[$ruleKey];
        }
	}
		
	/**
	 * @todo :: change static indexes 'code', 'msg' etc...  
	 * Enter description here ...
	 * @param Zend_Controller_Action_Interface $objController
	 * @param Bf_Db_Table $objDbTable
	 */
	public static function gridSave(Zend_Controller_Action_Interface $objController,Bf_Db_Table $objDbTable) {
		
		
		
		$intId = ( int ) $objController->getRequest()->getParam ( 'id' );
		
		if (! empty ( $intId )) {
			$objRows = $objDbTable->find ( $intId );
			if (! empty ( $objRows )) {
				$objRow = $objDbTable->find ( $intId )->current ();
			} else {
				$objRow = array ();
			}
		} else {
			if ("add" == $objController->getRequest()->getParam ( "oper" )) {
				$objRow = $objDbTable->createRow ();
			} else {
				$objController->view->data = array ("code" => "error", "msg" => $this->view->translate ( "LBL_ERROR_UNAUTHORIZED" ) );
				return;
			}
		}
		
		if (empty ( $objRow )) {
			$objController->view->data = array ("code" => "error", "msg" => $this->view->translate ( "LBL_ERROR_UNAUTHORIZED" ) );
			return;
		}
		
		if ("del" == $objController->getRequest()->getParam ( "oper" )) {
			if ($objRow->delete ()) {
				// Deleted 
				$objController->view->data = array ("code" => "ok", "msg" => "" );
			} else {
				// Delete failed
				$objController->view->data = array ("code" => "error", "msg" => $this->view->translate ( "LBL_DEL_FAIL" ) );
			}
		} else {
			if ($objController->getRequest()->isPost ()) {
				$arrData = $objController->getRequest()->getPost ();
				$objRow->setFromArray ( $arrData );
				
				$intId = $objRow->save ();
				
				if (! empty ( $intId )) {
					$objController->view->data = array ("code" => "ok", "msg" => "" );
				} else {
					$objController->view->data = array ("code" => "error", "msg" => $this->view->translate ( "LBL_UPDATE_FAIL" ) );
				}
			} else {
				$objController->view->data = array ("code" => "error", "msg" => $this->view->translate ( "LBL_UPDATE_FAIL" ) );
			
			}
		}
		
		Zend_Controller_Action_HelperBroker::getStaticHelper ( 'viewRenderer' )->setNoRender ();
		Zend_Controller_Action_HelperBroker::getStaticHelper ( 'layout' )->disableLayout ();
		
		echo $objController->view->json($objController->view->data);
		exit();
		
	}

}