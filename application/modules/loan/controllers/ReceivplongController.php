<?php
class Loan_ReceivplongController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
		try{
			$db = new Loan_Model_DbTable_DdReceived();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					    'adv_search'=>'',
						'branch_id_search' => -1,
						'land_id'=> -1,
						'client_name'=> -1,
						'from_date_search'=> date('Y-m-d'),
						'to_date_search'=>date('Y-m-d'));
			}
			$rs_rows= $db->getCustomerReceivedPlong($search);//call frome model
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME","CLIENT_NAME","PROPERTY_TYPE","PROPERTY_CODE","STREET","RECEIVED_DATE","DATE","NOTE","STATUS");
			$link=array(
					'module'=>'loan','controller'=>'receivplong','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0,$collumns,$rs_rows,array('branch_name'=>$link,'client_name'=>$link,));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
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
				$_dbmodel = new Loan_Model_DbTable_DdReceived();
				$_dbmodel->addReceivedplong($_data);
				if(isset($_data['save'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/receivplong/add");
				}elseif(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/receivplong");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/receivplong");
				}				
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Loan_Form_Frmreceiveplong();
		$frm = $fm->FrmAddFrmCancel();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
	}
	public function editAction(){
		$id = $this->getRequest()->getParam('id');
		$_dbmodel = new Loan_Model_DbTable_DdReceived();
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try {
					$_dbmodel->editReceivedplong($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/receivplong");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
	      }
	    $row  = $_dbmodel->getPlongById($id);
	    $this->view->row = $row;
		$fm = new Loan_Form_Frmreceiveplong();
		$frm = $fm->FrmAddFrmCancel($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
	}
}