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
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					    'adv_search'=>'',
						'branch_id_search' => -1,
						'from_date_search'=> date('Y-m-d'),
						'to_date_search'=>date('Y-m-d'));
			}
			$rs_rows= $db->getCancelSale($search);//call frome model
			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME","SALE_NO","CLIENT_NO","CLIENT_NAME","PROPERTY_TYPE","PROPERTY_CODE","PROPERTY_NAME","STREET","PRICE_SOLD","INSTALLMENT_PAID","PAID_AMOUNT","DATE");
			$link=array(
					'module'=>'loan','controller'=>'cancel','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0,$collumns,$rs_rows,array('sale_number'=>$link,'client_number'=>$link,));
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
	$id = $this->getRequest()->getParam('id');
	$_dbmodel = new Loan_Model_DbTable_DbCancel();
	if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try {
				//print_r($_data);exit();
				if(isset($_data['save'])){
						$_dbmodel->editCancelSale($_data);
						
				}elseif(isset($_data['save_close'])){
						$_dbmodel->editCancelSale($_data);
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/cancel");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
	      }
	    $row  = $_dbmodel->getCancelById($id);
	    $this->view->row = $row;
		$fm = new Loan_Form_FrmCancel();
		$frm = $fm->FrmAddFrmCancel($row);
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
	function getSaleclieAction(){// by vandy get property code
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbCancel();
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

