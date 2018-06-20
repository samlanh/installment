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
						'branch_id' => -1,
						'land_id'=> -1,
						'client_name'=> -1,
						'from_date_search'=> date('Y-m-d'),
						'to_date_search'=>date('Y-m-d'));
			}
			$this->view->rssearch = $search;
			$rs_rows= $db->getCustomerReceivedPlong($search);//call frome model
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME","LOAN_NO","CLIENT_NAME","PROPERTY_TYPE","PROPERTY_CODE","STREET","RECEIVED_DATE","DATE","NOTE","STATUS","ប័ណ្ណប្រគល់ប្លង់កម្មសិទ្ធ");
			$link=array(
					'module'=>'loan','controller'=>'receivplong','action'=>'edit',
			);
			$link1=array(
					'module'=>'loan','controller'=>'receivplong','action'=>'titledeed',
			);
			$this->view->list=$list->getCheckList(0,$collumns,$rs_rows,array('branch_name'=>$link,'client_name'=>$link,'ប័ណ្ណប្រគល់ប្លង់កម្មសិទ្ធ'=>$link1));
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
		$_dbmodel = new Loan_Model_DbTable_DdReceived();
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			try {		
				
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
		$layoutType = $_dbmodel->getLayoutType();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($layoutType,array(
				'id' => -1,
				'name' =>$tr->translate("ADD_NEW"),
		) );
		$this->view->layouttype=$layoutType;
		
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
	      $layoutType = $_dbmodel->getLayoutType();
	      $tr = Application_Form_FrmLanguages::getCurrentlanguage();
	      array_unshift($layoutType,array(
	      		'id' => -1,
	      		'name' =>$tr->translate("ADD_NEW"),
	      ) );
	      $this->view->layouttype=$layoutType;
	      
	    $row  = $_dbmodel->getPlongById($id);
	    $this->view->row = $row;
		$fm = new Loan_Form_Frmreceiveplong();
		$frm = $fm->FrmAddFrmCancel($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
	}
	function titledeedAction(){
		$id = $this->getRequest()->getParam('id');
		$_dbmodel = new Loan_Model_DbTable_DdReceived();
		$row  = $_dbmodel->getRecivePlongInfo($id);
		$this->view->row = $row;
	}
	function getsaleinfoAction(){
		if($this->getRequest()->isPost()){
			$db = new Loan_Model_DbTable_DdReceived();
			$data = $this->getRequest()->getPost();
			$id = $db->getSaleInfo($data['sale_client']);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}