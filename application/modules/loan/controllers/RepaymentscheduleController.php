<?php
class Loan_RepaymentScheduleController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
		    if($this->getRequest()->isPost()){
 				$search = $this->getRequest()->getPost();
 			}else{
				$search = array(
				    'txt_search'=>'',
					'client_name'=> -1,
					'schedule_opt' => -1,
					'branch_id' => -1,
					'land_id' => -1,
					'status' => -1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$db = new Loan_Model_DbTable_DbRepaymentSchedule();
			$rs_rows= $db->getAllReschedule($search,1);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","SALE_NO","CUSTOMER_NAME","PROPERTY_CODE","PAMENT_METHOD","BALANCE","PAMENT_METHOD","BALANCE","DATE_BUY",
				"STATUS");
			$link=array(
					'module'=>'loan','controller'=>'repaymentschedule','action'=>'view',
			);
			$link_info=array('module'=>'loan','controller'=>'repaymentschedule','action'=>'index',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array(),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
  }
  function addAction()
  {
	  	$rightclick = $this->getRequest()->getParam('rightclick');
	  	$rightclick = empty($rightclick)?"":$rightclick;
	  	$this->view->rightclick = $rightclick;
	  	$id = $this->getRequest()->getParam('id');
	  	if(empty($id)){
	  		$this->_redirect('/loan/index');
	  	}
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				$_dbmodel = new Loan_Model_DbTable_DbRepaymentSchedule();
				$reschedule = $_dbmodel->addRepayMentSchedule($_data);
				$_dbmodel->recordhistory($_data);
				if($rightclick=="true"){
					Application_Form_FrmMessage::message('INSERT_SUCCESS');
					echo "<script>window.close();</script>";exit();
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/loan/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		
        $db = new Application_Model_DbTable_DbGlobal();
        $this->view->stepoption = $db->getOptionStepPayment();
        
        $frmpopup = new Application_Form_FrmPopupGlobal();
        $this->view->officailreceipt = $frmpopup->getOfficailReceipt();
        
        $tr = Application_Form_FrmLanguages::getCurrentlanguage();
        $interest = $db->getAllInterestratestore();
        array_unshift($interest,array(
	        'id' => -1,
	        'name' =>$tr->translate("ADD_NEW"),
        ) );
        $this->view->rs_interest = $interest;
        
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		if(!empty($id)){
			$db = new Loan_Model_DbTable_DbLandpayment();
			$rs = $db->getTranLoanByIdWithBranch($id,null);
			$this->view->rsresult =  $rs;
			if(empty($rs)){
				Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index",2);
				exit();
			}
			if($rs['is_cancel']==1){
				Application_Form_FrmMessage::Sucessfull("This Sale already cancel","/loan",2);
				//Application_Form_FrmMessage::message('This Sale already cancel');
				//echo "<script>window.close();</script>";
			}
			if($rs['payment_id']!=1){
				Application_Form_FrmMessage::Sucessfull("RESCHEDULE_EXIST","/loan",2);
			}
		}
		$this->view->id = $id;//use
	}	
	public function editAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_dbmodel = new Loan_Model_DbTable_DbRepaymentSchedule();
				$_dbmodel->updateRepaymentSchedule($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/repaymentschedule/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$_db = new Loan_Model_DbTable_DbRepaymentSchedule();
		$data_row = $_db->getRescheduleById($id); 
		$this->view->rsresult = $data_row;
		print_r($data_row);
		$frm = new Loan_Form_FrmRepaymentSchedule();
		$frm_loan=$frm->FrmAddLoan($data_row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
        $db = new Application_Model_DbTable_DbGlobal();
        $db_keycode = new Application_Model_DbTable_DbKeycode();
        $this->view->keycode = $db_keycode->getKeyCodeMiniInv();
		$db = new Setting_Model_DbTable_DbLabel();
		$this->view->setting=$db->getAllSystemSetting();
	}
	function getloanRescheduleAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db=new Loan_Model_DbTable_DbRepaymentSchedule();
			$row=$db->getLaoForRepaymentSchedule($data['member_id']);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getTimesAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db=new Loan_Model_DbTable_DbRepaymentSchedule();
			$row=$db->countDepositTimes($data['loan_number']);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
}