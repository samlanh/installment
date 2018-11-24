<?php
class Loan_NewscheduleController extends Zend_Controller_Action {
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
						'schedule_opt' => -1,
						'branch_id' => -1,
						'land_id' => -1,
						'status' => -1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Loan_Model_DbTable_DbNewSchedule();
			$rs_rows= $db->getAllReschedule($search,1);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","SALE_NO","CUSTOMER_NAME","PROPERTY_CODE","PAMENT_METHOD","BALANCE","PAMENT_METHOD","BALANCE","DATE_BUY",
				"STATUS");
			$link=array(
					'module'=>'loan','controller'=>'newschedule','action'=>'view',
			);
			$link_info=array('module'=>'loan','controller'=>'newschedule','action'=>'index',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link_info,'sale_number'=>$link_info,'name_kh'=>$link_info,'land_address'=>$link_info,'total_capital'=>$link_info),0);
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
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Loan_Model_DbTable_DbNewSchedule();
				$_dbmodel->addNewSchedule($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/newschedule");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
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
        $this->view->allclient = $db->getAllClient();
        $this->view->allclient_number = $db->getAllClientNumber();
        $frmpopup = new Application_Form_FrmPopupGlobal();
        $db_keycode = new Application_Model_DbTable_DbKeycode();
        $this->view->keycode = $db_keycode->getKeyCodeMiniInv();        
		$db = new Setting_Model_DbTable_DbLabel();
		$this->view->setting=$db->getAllSystemSetting();
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		$id = $this->getRequest()->getParam('id');
		if(!empty($id)){
			$db = new Loan_Model_DbTable_DbLandpayment();
			$rs = $db->getTranLoanByIdWithBranch($id,null);
			$this->view->rsresult =  $rs;
		}
		$this->view->id = $id;
	}	
	
	public function editAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_dbmodel = new Loan_Model_DbTable_DbNewSchedule();
				$_dbmodel->updateRepaymentSchedule($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/repaymentschedule/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$_db = new Loan_Model_DbTable_DbNewSchedule();
		$data_row = $_db->getRescheduleById($id); 
		$this->view->rsresult = $data_row;
// 		$rs = $db_g->getLoanFundExist($id);
// 		if($rs==true){
// 			Application_Form_FrmMessage::Sucessfull("LOAN_FUND_EXIST","/loan/repaymentschedule/index");
// 		}
// 		$db = new Loan_Model_DbTable_DbLoanIL();
// 		$row = $db->getTranLoanByIdWithBranch($id,1,1);
// 		if(empty($row)){ Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST","/loan/repaymentschedule/index"); }
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
			$db=new Loan_Model_DbTable_DbNewSchedule();
			$row=$db->getLaoForRepaymentSchedule($data['member_id']);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function previewreschedulAction(){//use only  new schedule only
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Loan_Model_DbTable_DbNewSchedule();
			$rows_return=$_dbmodel->addScheduleTestPayment($_data);
			print_r(Zend_Json::encode($rows_return));
			exit();
		}
	}
}

