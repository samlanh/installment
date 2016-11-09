<?php
class Loan_IndexController extends Zend_Controller_Action {
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
 			}
			else{
				$search = array(
						'txt_search'=>'',
						'customer_code'=> -1,
						'repayment_method' => -1,
						'branch_id' => -1,
						'co_id' => -1,
						'status' => -1,
						'currency_type'=>-1,
						'pay_every'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Loan_Model_DbTable_DbLandpayment();
			$rs_rows= $db->getAllIndividuleLoan($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("LOAN_NO","LAND_ADD","CUSTOMER_NAME","COMUNE_NAME_EN","LOAN_AMOUNT","PAID","BALANCE","DISCOUNT","TERM_BORROW","INTEREST_RATE","DATE_BUY",
				"STATUS");
			$link_info=array('module'=>'loan','controller'=>'index','action'=>'edit',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('land_code'=>$link_info,'land_address'=>$link_info,'client_number'=>$link_info,'name_en'=>$link_info,'price'=>$link_info,'total_capital'=>$link_info),0);
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
				$_dbmodel = new Loan_Model_DbTable_DbLandpayment();
				$_dbmodel->addSchedulePayment($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		//$this->view->frmpupopinfoclient = $frmpopup->frmPopupindividualclient();
		//$this->view->frmPopupCO = $frmpopup->frmPopupCO();
		
// 		$db = new Setting_Model_DbTable_DbLabel();
// 		$this->view->setting=$db->getAllSystemSetting();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
		        'id' => -1,
		        'name' => '---Add New ---',
		) );
	    $this->view->co_name=$co_name;
// 	    $db = new Application_Model_DbTable_DbGlobal();
// 	    $dataclient = $db->getAllClientNumber();
// 	    $this->view->client_code=$dataclient;
	    
// 	    $dataclient=$db->getAllClient();
// 	    array_unshift($dataclient, array('id' => "-1",'name'=>'---Add New Client---') );
// 	    $this->view->client_name=$dataclient;
	}	
	public function editAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_dbmodel = new Loan_Model_DbTable_DbLandpayment();
				$_dbmodel->updateLoanById($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/index/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db_g = new Application_Model_DbTable_DbGlobal();
		$rs = $db_g->getLoanFundExist($id);
		if($rs==true){
			Application_Form_FrmMessage::Sucessfull("LOAN_FUND_EXIST","/loan/index/index");
		}
		$db = new Loan_Model_DbTable_DbLandpayment();
		$row = $db->getTranLoanByIdWithBranch($id,null);
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		$this->view->datarow = $row;
	
		$db = new Application_Model_DbTable_DbGlobal();
		$dataclient = $db->getAllClientNumber();
		$this->view->client_code=$dataclient;
			
		$dataclient=$db->getAllClient();
		array_unshift($dataclient, array('id' => "-1",'name'=>'---Add New Client---') );
		$this->view->client_name=$dataclient;
		
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => '---Add New ---',
		) );
		$this->view->co_name=$co_name;
	
	}
	public function addloanAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoan();
			$id = $db->addNewLoanGroup($data);
			$suc = array('sms'=>'ប្រាក់ឥណទានត្រូវបានបញ្ចូលដោយជោគជ័យ !');
			print_r(Zend_Json::encode($suc));
			exit();
		}
	}
	
	public function viewAction(){
// 		$this->_helper->layout()->disableLayout();
		$id = $this->getRequest()->getParam('id');
		$db_g = new Application_Model_DbTable_DbGlobal();
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOT_FUND","/loan/index/index");
		}
		$db = new Loan_Model_DbTable_DbLoanIL();
		$row = $db->getLoanviewById($id);
		$this->view->tran_rs = $row;
	}
	function getLoanlevelAction(){
		if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				$db = new Loan_Model_DbTable_DbLoanIL();
				$row = $db->getLoanLevelByClient($data['client_id'],$data['type']);
				print_r(Zend_Json::encode($row));
			    exit();
		}
		
	}
	public function getLoaninfoAction(){//from repayment schedule
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db=new Loan_Model_DbTable_DbRepaymentSchedule();
			$row=$db->getLoanInfo($data['loan_id']);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getloanBymemberidAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db=new Loan_Model_DbTable_DbRepaymentSchedule();
			$row=$db->getLoanInfoBymemberId($data['member_id']);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getalllandAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$row = $db->getAllLandInfo($data['branch_id'],1);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
    function getloannumberAction(){
    			if($this->getRequest()->isPost()){
    				$data = $this->getRequest()->getPost();
    				$db = new Application_Model_DbTable_DbGlobal();
		            $loan_number = $db->getLoanNumber($data);
    				print_r(Zend_Json::encode($loan_number));
    				exit();
    			}
    }
	function addschedultestAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
				$_dbmodel = new Loan_Model_DbTable_DbLoanILtest();
				$rows_return=$_dbmodel->addNewLoanILTest($_data);
				print_r(Zend_Json::encode($rows_return));
				exit();
		}
		
	}
function addNewloantypeAction(){
	if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['status']=1;
			$data['display_by']=1;
			$db = new Other_Model_DbTable_DbLoanType();
			$id = $db->addViewType($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	
}

