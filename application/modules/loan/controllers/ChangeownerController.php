<?php
class Loan_ChangeownerController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
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
			$db = new Loan_Model_DbTable_Dbtransferowner();
			$rs_rows= $db->getAllTranferOwner($search,1);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_NAME","PROPERTY_NAME","PRICE","PAID_BEFORE","BALANCE","TO_CUSTOMER","NOTE","CHANGE_DATE","STATUS");
			
			$link_info=array('module'=>'loan','controller'=>'changeowner','action'=>'index',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array(),0);
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
				$_dbmodel = new Loan_Model_DbTable_Dbtransferowner();
				$_dbmodel->addTransferOwner($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/changeowner");
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
        
//         $db_keycode = new Application_Model_DbTable_DbKeycode();
//         $this->view->keycode = $db_keycode->getKeyCodeMiniInv();
        
//         $key = new Application_Model_DbTable_DbKeycode();
//         $this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}	
	
// 	public function editAction(){
// 		$_dbmodel = new Loan_Model_DbTable_DbTransferProject();
		
// 		if($this->getRequest()->isPost()){
// 			$_data = $this->getRequest()->getPost();
// 			try {
// 				$_dbmodel->addChangeProject($_data);
// 				if(!empty($_data['saveclose'])){
// 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/transferproject");
// 				}else{
// 					Application_Form_FrmMessage::message("INSERT_SUCCESS");
// 				}
// 			}catch (Exception $e) {
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				$err =$e->getMessage();
// 				Application_Model_DbTable_DbUserLog::writeMessageError($err);
// 			}
// 		}
// 		$id = $this->getRequest()->getParam('id');
// 		$rs = $_dbmodel->getTransferProject($id);
// 		$this->view->rs = $rs;
		
		
// 		$frm = new Loan_Form_FrmTransferproject();
// 		$frm_loan=$frm->FrmTransferProject($rs);
// 		Application_Model_Decorator::removeAllDecorator($frm_loan);
// 		$this->view->frm_loan = $frm_loan;
//         $db = new Application_Model_DbTable_DbGlobal();
        
//         $db_keycode = new Application_Model_DbTable_DbKeycode();
//         $this->view->keycode = $db_keycode->getKeyCodeMiniInv();
// 	}
}