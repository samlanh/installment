<?php
class Issue_IssueplongController extends Zend_Controller_Action {
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
						'land_id'	=>-1,
						'status' => -1,
						'status_plong'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Issue_Model_DbTable_Dbissueplong();
			$rs_rows= $db->getAllissueplong($search,1);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("PROCESS","BRANCH_NAME","CUSTOMER_NAME","PHONE","PROPERTY_CODE","SOLD_PRICE","BALANCE","DATE","NOTE","STATUS");
			$link_info=array('module'=>'issue','controller'=>'issueplong','action'=>'edit',);
			$this->view->list=$list->getCheckList(12, $collumns, $rs_rows,array('ask_for'=>$link_info,'branch_name'=>$link_info,'name_kh'=>$link_info,
					'land_address'=>$link_info,'street'=>$link_info,'phone'=>$link_info),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->rssearch = $search;
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
				
				$_dbmodel = new Issue_Model_DbTable_Dbissueplong();
				$_dbmodel->addIssuePlong($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/issueplong");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$frm = new Issue_Form_FrmIssuePlong();
		$frm_loan=$frm->FrmIssuePlong();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
	}	
	
	public function editAction(){
		$_dbmodel = new Issue_Model_DbTable_Dbissueplong();
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
				
				$_dbmodel->EditIssuePlong($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/issueplong");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$rs = $_dbmodel->getPlongbyId($id);
		$this->view->rs = $rs;
		if(empty($rs)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/issue/issueplong");
			exit();
		}
		
		$frm = new Issue_Form_FrmIssuePlong();
		$frm_loan=$frm->FrmIssuePlong($rs);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
	}
	
	function updatenoteAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_Dbissueplong();
			$row = $db->updateNote($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
}

