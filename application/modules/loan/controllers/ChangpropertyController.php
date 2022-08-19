<?php
class Loan_ChangpropertyController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	private $sex=array(1=>'M',2=>'F');
	public function indexAction(){
		try{
		    if($this->getRequest()->isPost()){
 				$search = $this->getRequest()->getPost();
 				
 			}else{
				$search = array(
						'txt_search'=>'',
						'client_name'=> -1,
						'branch_id' => -1,
						'status' => -1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Loan_Model_DbTable_Dbchangehouse();
			$rs_rows= $db->getAllChangeHouse($search,1);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_NAME","PROPERTY_CODE","SOLD_PRICE","PAID","BALANCE",
					"BRANCH_NAME","PROPERTY_CODE","SOLD_PRICE","DISCOUNT_PERCENT","Discount","SOLD_PRICE","BALANCE",
					"CHANGE_DATE","BY_USER","STATUS");
			$link_info=array('module'=>'loan','controller'=>'changproperty','action'=>'edit',);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('sale_number'=>$link_info,'client_number'=>$link_info,'name_kh'=>$link_info,'from_property'=>$link_info),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}	
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
  }
  function addAction()
  {
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				$_dbmodel = new Loan_Model_DbTable_Dbchangehouse();
				$_dbmodel->addChangeHouse($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/changproperty");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$frm = new Loan_Form_FrmTransferproject();
		$frm_loan=$frm->FrmTransferProject();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
        $db = new Application_Model_DbTable_DbGlobal();
        
        $frmpopup = new Application_Form_FrmPopupGlobal();
        $this->view->footer = $frmpopup->getFooterReceipt();
        
        $key = new Application_Model_DbTable_DbKeycode();
        $this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}	
	public function editAction(){
		$_dbmodel = new Loan_Model_DbTable_Dbchangehouse();
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				
				$_dbmodel->UpdateChangeHouse($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/loan/changproperty");
				}else{
					Application_Form_FrmMessage::message("INSERT_FAIL");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = $this->getRequest()->getParam('id');
		$rs = $_dbmodel->getTransferProject($id);
		if(empty($rs)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/changproperty",2);
			exit();
		}
		$this->view->rs = $rs;
		
		$frm = new Loan_Form_FrmTransferproject();
		$frm_loan=$frm->FrmTransferProject();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
        $db = new Application_Model_DbTable_DbGlobal();

        $frmpopup = new Application_Form_FrmPopupGlobal();
        $this->view->footer = $frmpopup->getFooterReceipt();
        
        $key = new Application_Model_DbTable_DbKeycode();
        $this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	function getalllandAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$action = (!empty($data['action'])?$data['action']:null);
			$row = $db->getAllLandInfo($data['branch_id'],1,$action);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
	function reprintAction(){
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$_dbmodel = new Loan_Model_DbTable_Dbchangehouse();
		$rs = $_dbmodel->getTransferProject($id);

		if(empty($rs)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/changproperty",2);
			exit();
		}
		$this->view->rs = $rs;
		
		$key = new Application_Model_DbTable_DbKeycode();
        $this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		 $frmpopup = new Application_Form_FrmPopupGlobal();
        $this->view->footer = $frmpopup->getFooterReceipt();
	}
	
}

