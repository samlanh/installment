<?php
class Loan_CancelController extends Zend_Controller_Action {
	
	public function init()
	{
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction()
	{
		try{
			$db = new Loan_Model_DbTable_DbCancel();
// 			if($this->getRequest()->isPost()){
// 				$search=$this->getRequest()->getPost();
// 			}
// 			else{
// 				$search = array(
// 					    'adv_search'=>'',
// 						'branch' => '',
// 						'client_name' =>'',
// 						'client_code'=>'',
// 						'Term'=>'',
// 						'status' =>'',
// 						'cash_type'=>'',
// 						'start_date'=> date('Y-m-01'),
// 						'end_date'=>date('Y-m-d'));
// 			}
			$rs_rows= $db->getCancelSale();//call frome model
// 			$glClass = new Application_Model_GlobalClass();
// 			$rs_row = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("CANCEL_CODE","SALE_NO","CLIENT_NO","CLIENT_NAME","PROJECT_NAME","PROPERY_CODE","DATE");
			$link=array(
					'module'=>'loan','controller'=>'badloan','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0,$collumns,$rs_rows,array('cancel_code'=>$link,'sale_number'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Loan_Form_FrmCancel();
		$frm = $fm->FrmAddFrmCancel();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_cancel = $frm;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			try {		
				$_dbmodel = new Loan_Model_DbTable_DbCancel();
				if(isset($_data['save'])){
					$_dbmodel->addCancelSale($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/cancel/add");
				}elseif(isset($_data['save_close'])){
					$_dbmodel->addCancelSale($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/cancel");
				}				
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Loan_Form_FrmCancel();
		$frm = $fm->FrmAddFrmCancel();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
	}
	public function editAction()
	{
		// action body
	if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			try {
				//print_r($_data);exit();
				$_dbmodel = new Loan_Model_DbTable_DbBadloan();
				if(isset($_data['save'])){
					if($this->getRequest()->getParam('id')==$_data['client_name']){
						$_dbmodel->updatebadloan($_data);
					}else{
						$_dbmodel->updatebadloan_bad($_data);
						
					}
				}elseif(isset($_data['save_close'])){
					if($this->getRequest()->getParam('id')==$_data['client_name']){
						$_dbmodel->updatebadloan_bad($_data);
					}else{
						$_dbmodel->updatebadloan_bad($_data);
					}
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/badloan");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
	      }
		$id = $this->getRequest()->getParam('id');
		// 		if(empty($id)){
		// 			Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
		// 		}
		$db = new Loan_Model_DbTable_DbBadloan();
		$row  = $db->getbadloanbyid($id);
		$fm = new Loan_Form_Frmbadloan();
		$frm = $fm->FrmBadLoan($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
	
		 
	}
    function getCancelNoAction(){// by vandy get property code
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$dataclient=$db->getNewCacelCodeByBranch($data['branch_id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function getSaleAction(){// by vandy get property code
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$dataclient=$db->getSaleNoByProject($data['branch_id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function getInfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbCancel();
			$dataclient=$db->getCientAndPropertyInfo($data['sale_id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
}

