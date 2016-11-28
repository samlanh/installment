<?php
class Loan_TransferprojectController extends Zend_Controller_Action {
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
						'repayment_method' => -1,
						'branch_id' => -1,
						'co_id' => -1,
						'status' => -1,
						'currency_type'=>-1,
						'pay_every'=>-1,
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d'),
						 );
			}
// 			print_r($search);
			$db = new Loan_Model_DbTable_DbLandpayment();
			$rs_rows= $db->getAllIndividuleLoan($search,1);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","SALE_NO","CLIENT_NO","CUSTOMER_NAME","COMUNE_NAME_EN","LOAN_NO","PROPERTY_NAME","STREET","ប្រភេទបង់","LOAN_AMOUNT","DISCOUNT","PAID","BALANCE","DATE_BUY",
				"STATUS");
			$link=array(
					'module'=>'loan','controller'=>'repaymentschedule','action'=>'view',
			);
			$link_info=array('module'=>'loan','controller'=>'transferproject','action'=>'edit',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('loan_number'=>$link,'payment_method'=>$link_info,'client_name_kh'=>$link_info,'client_name_en'=>$link_info,'total_capital'=>$link_info),0);
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
				$_dbmodel = new Loan_Model_DbTable_DbTransferProject();
				$_dbmodel->addChangeProject($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/transferproject");
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
        
//      $this->view->allclient = $db->getAllClient();
//      $this->view->allclient_number = $db->getAllClientNumber();
//      $frmpopup = new Application_Form_FrmPopupGlobal();
        
        $db_keycode = new Application_Model_DbTable_DbKeycode();
        $this->view->keycode = $db_keycode->getKeyCodeMiniInv();
        
//      $this->view->graiceperiod = $db_keycode->getSystemSetting(9);
        
// 		$db = new Setting_Model_DbTable_DbLabel();
// 		$this->view->setting=$db->getAllSystemSetting();
	}	

	
	
	public function editAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_dbmodel = new Loan_Model_DbTable_DbTransferProject();
				$_dbmodel->updateRepaymentSchedule($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/repaymentschedule/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$db_g = new Application_Model_DbTable_DbGlobal();
		$rs = $db_g->getLoanFundExist($id);
		if($rs==true){
			Application_Form_FrmMessage::Sucessfull("LOAN_FUND_EXIST","/loan/repaymentschedule/index");
		}
		
		$db = new Loan_Model_DbTable_DbLoanIL();
		$row = $db->getTranLoanByIdWithBranch($id,1,1);
		if(empty($row)){ Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST","/loan/repaymentschedule/index"); }
		
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->allclient = $db->getAllClient();
		$this->view->allclient_number = $db->getAllClientNumber();
		$this->view->datarow = $row;
	}
	
	function getalllandAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$action = (!empty($data['action'])?$data['action']:null);
			$row = $db->getAllLandInfo($data['branch_id'],1,$action);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
}

