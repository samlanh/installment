<?php
class Issue_IssuecompletedController extends Zend_Controller_Action {
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
			$rs_rows= $db->getAllissueCompleted($search,1);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","PROCESS","CUSTOMER_NAME","PHONE","PROPERTY_CODE","STREET","HEAD_TITLE_NO","SOLD_PRICE","BALANCE","NOTE");
			$this->view->list=$list->getCheckList(12, $collumns, $rs_rows,array(),0);
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
		$this->_redirect("/issue/issuecompleted");
		exit();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
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
	
	
	function updatenotepaymentAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Issue_Model_DbTable_Dbissueplong();
			$row = $db->updateNotePaymentforPlongStep($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
}

