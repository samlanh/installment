<?php
class Issue_indexController extends Zend_Controller_Action {
	
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
      try{
    	$db = new Issue_Model_DbTable_Dbgivehouse();
    	 if($this->getRequest()->isPost()){
    	 	$search = $this->getRequest()->getPost();
   		}else{
	   		 $search = array(
				'txt_search'=>'',
				'client_name'=> '',
				'branch_id' => -1,
				'streetlist'=>'',
				'status' => -1,
				'land_id'=>-1,
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d'),
				);
  		 }
        $rs_rows= $db->getAllIssueHouse($search);
		$list = new Application_Form_Frmtable();
		$collumns = array("PROJECT_NAME","CUSTOMER_NAME","TEL","PROPERTY_CODE","STREET","PAYMENT_TYPE","ELECTRIC_START","WATER_START","RECEIVED_DATE","BY_USER","STATUS");
		$link=array(
	      'module'=>'project','controller'=>'project','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('project_name'=>$link,'project_type'=>$link));
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm_search = new Loan_Form_FrmSearchLoan();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->rssearch = $search;
	}
	public function addAction(){
		$_dbmodel = new Issue_Model_DbTable_Dbgivehouse();
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			try {
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				$_dbmodel->addIssueHouse($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Issue_Form_FrmGivehouse();
		$frm = $fm->FrmGivehouse();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
	}
	
	public function editAction(){
		$id = $this->getRequest()->getParam('id');
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/index");
		}
		$_dbmodel = new Issue_Model_DbTable_Dbgivehouse();
		if($this->getRequest()->isPost()){//check condition return true click submit button
				$_data = $this->getRequest()->getPost();
			try {
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				$_data['id']=$id;
				$_dbmodel->addIssueHouse($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/issue/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$row = $_dbmodel->getIssueHousebyId($id);
		$this->view->rs = $row;
		$fm = new Issue_Form_FrmGivehouse();
		$frm = $fm->FrmGivehouse($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
	}
	
	function getSaleclieAction(){// by vandy get property code
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_Dbgivehouse();
			$dataclient=$db->getSaleNoByProjectNotYetReceiveHouse($data['branch_id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
}