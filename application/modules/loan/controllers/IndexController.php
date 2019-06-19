<?php
class Loan_IndexController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
						'client_name'=> -1,
						'schedule_opt' => -1,
						'branch_id' => -1,
						'streetlist'=>'',
						'status' => -1,
						'co_id' => -1,
						'land_id'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Loan_Model_DbTable_DbLandpayment();
			$rs_rows= $db->getAllIndividuleLoan($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_NAME","TEL","PROPERTY_CODE","STREET","PAYMENT_TYPE","PRINCIPLE_PICE","DISCOUNT_PERCENT","DISCOUNT","SOLD_PRICE",
					"PAID","BALANCE","DATE_BUY","BY_USER","STATUS","IS_CANCEL");
			$link_info=array('module'=>'loan','controller'=>'index','action'=>'edit',);

			$link_editsale=array('module'=>'loan','controller'=>'index','action'=>'editsale');
			$agreement=array('module'=>'report','controller'=>'paramater','action'=>'rpt-agreement',);
			$reschedule=array('module'=>'loan','controller'=>'repaymentschedule','action'=>'add',);
			$payment=array('module'=>'loan','controller'=>'ilpayment','action'=>'add',);
			$this->view->list=$list->getCheckList(11, $collumns, $rs_rows,array(),0);
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm_search = new Loan_Form_FrmSearchLoan();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->rssearch = $search;
		$db = new Application_Model_DbTable_DbGlobal();
		//$db->resetBegeningLoan();
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
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/index/add");
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
		$this->view->footer = $frmpopup->getFooterReceipt();
		$this->view->officailreceipt = $frmpopup->getOfficailReceipt();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($co_name,array(
		        'id' => -1,
		        'name' =>$tr->translate("ADD_NEW"),
		) );
	    $this->view->co_name=$co_name;
	    
	    $this->view->stepoption = $db->getOptionStepPayment();
	    
	    $interest = $db->getAllInterestratestore();
	    array_unshift($interest,array(
		    'id' => -1,
		    'name' =>$tr->translate("ADD_NEW"),
	    ) );
	    $this->view->rs_interest = $interest;
	    
	    $key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	function addsoldAction()
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
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => '---Add New ---',
		) );
		$this->view->co_name=$co_name;
		 
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->client_doc_type = $db->getclientdtype();
		 
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}	
	public function editAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_dbmodel = new Loan_Model_DbTable_DbLandpayment();
				$_dbmodel->updateLoanById($_data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/loan/index/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db = new Loan_Model_DbTable_DbLandpayment();
		$rs = $db->getSalePaidExist($id,null);
		if(count($rs)>=2){
			Application_Form_FrmMessage::Sucessfull("ទិន្នន័យនេះមានប្រវត្តិបង់ប្រាក់ច្រើនជាង១ដងរួចហើយ មិនអាចកែប្រែបានទេ","/loan/index/index");
		}
		$row = $db->getTranLoanByIdWithBranch($id,null);
		if(empty($row)){Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index");}
		$rs = array();
		//if($row['payment_id']==6 OR $row['payment_id']==4){
		 if($row['payment_id']!=1){
		 	$this->_redirect("/loan/index");
		 }
		
		if($row['payment_id']!=6 OR $row['payment_id']==4){
			$rs = $db->getSaleScheduleById($id,$row['payment_id']);
		}
		$this->view->rs = $rs;
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		$this->view->datarow = $row;
		$this->view->amount_price = $row['balance']+$row['paid_amount']-$row['other_fee'];
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->client_code=array();//$dataclient;
		$this->view->client_name=array();
		
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => '---Add New ---',
		) );
		$this->view->co_name=$co_name;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	public function editsaleAction(){
		$rightclick = $this->getRequest()->getParam('rightclick');
		$rightclick = empty($rightclick)?"":$rightclick;
		$this->view->rightclick = $rightclick;
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_dbmodel = new Loan_Model_DbTable_DbLandpayment();
				$_dbmodel->updateSaleOnlyById($_data);
				if($rightclick=="true"){
					Application_Form_FrmMessage::message('UPDATE_SUCCESS');
					echo "<script>window.close();</script>";exit();
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/loan/index/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$db = new Loan_Model_DbTable_DbLandpayment();
		$row = $db->getTranLoanByIdWithBranch($id,null);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index");
			exit();
		}
		if($row['is_cancel']==1){
			Application_Form_FrmMessage::message('This Sale already cancel');
			echo "<script>window.close();</script>";
// 			Application_Form_FrmMessage::Sucessfull("This Sale already cancel","/loan");
		}
		
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		$this->view->datarow = $row;
		$this->view->amount_price = $row['balance']+$row['paid_amount']-$row['other_fee'];
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->client_code=array();//$dataclient;
		$this->view->client_name=array();
	
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => '---Add New ---',
		) );
		$this->view->co_name=$co_name;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
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
		$db = new Loan_Model_DbTable_DbLandpayment();
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
			$row=$db->getLoanInfoById($data['sale_id']);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getsaleinfoAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db=new Loan_Model_DbTable_DbRepaymentSchedule();
			$row=$db->getSaleInfoById($data['sale_id']);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getalllandAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$action = (!empty($data['action'])?$data['action']:null);
			$propertytype= empty($data['property_type'])?null:$data['property_type'];
			$row = $db->getAllLandInfo($data['branch_id'],1,$action,$propertytype);
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			array_unshift($row,array(
					'id' => -1,
					'name' => $tr->translate("SELECT_PROPERTY"),
			) );
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
    function getReceiptNumberAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$loan_number = $db->getReceiptByBranch($data);
    		print_r(Zend_Json::encode($loan_number));
    		exit();
    	}
    }
	function addschedultestAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
				$_dbmodel = new Loan_Model_DbTable_DbLandpayment();
				$rows_return=$_dbmodel->addScheduleTestPayment($_data);
				print_r(Zend_Json::encode($rows_return));
				exit();
		}
	}
	function demoscheduleAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Loan_Model_DbTable_DbLandpayment();
			$rows_return=$_dbmodel->demoSchedule($_data);
			print_r(Zend_Json::encode($rows_return));
			exit();
		}
	}
	 
	function previewreschedulAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Loan_Model_DbTable_DbLandpayment();
			$rows_return=$_dbmodel->addScheduleTestPayment($_data);
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
	function addStaffAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLandpayment();
			$id = $db->addStaff($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function addClientAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLandpayment();
			$id = $db->addClient($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function rptUpdatepaymentAction(){
		if($this->getRequest()->isPost()){
	  		$_data = $this->getRequest()->getPost();
	  		try {
	  			$_dbmodel = new Report_Model_DbTable_DbLandreport();
	  			$_dbmodel->updatePaymentStatus($_data);
	  			Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/loan/index/");
	  		}catch (Exception $e) {
	  			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	  		}
	  	}
	  	$id =$this->getRequest()->getParam('id');
	  	if(!empty($id)){
	  		$db = new Loan_Model_DbTable_DbLandpayment();
	  		$rs = $db->getTranLoanByIdWithBranch($id,null);
	  		if(empty($rs)){
	  			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index");
	  			exit();
	  		}
	  		
	  		if($rs['is_cancel']==1){
	  			Application_Form_FrmMessage::message('This Sale already cancel');
				echo "<script>window.close();</script>";
	  		}
	  	}
	  	
	  	$db = new Application_Model_DbTable_DbGlobal();
	  	$rs = $db->getClientByMemberId($id);
	  	$this->view->client =$rs;
	  	
	  	$this->view->stepoption = $db->getOptionStepPayment();
	  	
	  	$steppay = $db->getVewOptoinTypeByType(29);
	  	$this->view->steppay =$steppay;
	  	
	  	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
	  	$row = $db->getPaymentupdateSchedule($id,$rs['payment_id']);
	  	$this->view->tran_schedule=$row;
	
	  	$db = new Application_Model_DbTable_DbGlobal();
	  	$key = new Application_Model_DbTable_DbKeycode();
	  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	  	$this->view->id = $id;
	  	$this->view->payment_option = $db->getVewOptoinTypeByType(25,null,null,1);
	  	$db = new Application_Model_DbTable_DbGlobal();
	  	$this->view->customer =  $db->getAllClient();
	}
}