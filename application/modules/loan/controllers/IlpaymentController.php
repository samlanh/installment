<?php
class Loan_IlpaymentController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	private $sex=array(1=>'M',2=>'F');
	
	public function indexAction(){
		try{
			$db = new Loan_Model_DbTable_DbLoanILPayment();
				if($this->getRequest()->isPost()){
						$search=$this->getRequest()->getPost();
					}
					else{
						$search = array(
								'adv_search' => '',
								'client_name' => -1,
								'start_date'=> date('Y-m-d'),
								'end_date'=>date('Y-m-d'),
								'branch_id'		=>	-1,
								'paymnet_type'	=> -1,
								'land_id'=>-1,
								'status'=>-1,
								'payment_method'=>-1,);
					}
				$this->view->search = $search;
			$rs_rows= $db->getAllIndividuleLoan($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$result = array();
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_NAME","PROPERTY_CODE","STREET","RECIEPT_NO","PRINCIPAL","TOTAL_INTEREST","PENALIZE AMOUNT","SERVICE","TOTAL_PAYMENT","RECEIVE_AMOUNT",
					"PAY_DATE","DATE","PAYMENT_OPTION","BY_USER","STATUS");
			$link=array('module'=>'loan','controller'=>'ilpayment','action'=>'edit',);
			$linkprint=array('module'=>'report','controller'=>'loan','action'=>'receipt',);
			$link_delete=array('module'=>'loan','controller'=>'ilpayment','action'=>'delete',);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array());
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm = new Loan_Form_FrmSearchGroupPayment();
		$fm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($fm);
		$this->view->frm_search = $fm;
}
  function deleteAction(){
  		$id = $this->getRequest()->getParam("id");
  		$id = empty($id)?0:$id;
  		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  		$db = new Loan_Model_DbTable_DbLoanILPayment();
  		$payment_il = $db->getIlPaymentByID($id);
  		if (!empty($payment_il)){
  			if ($payment_il['is_closed']==1){
  				Application_Form_FrmMessage::Sucessfull("Can not delete this record","/loan/ilpayment");
  			}
  		}
  		$delete_sms=$tr->translate('CONFIRM_DELETE');
		echo "<script language='javascript'>
		var txt;
		var r = confirm('$delete_sms');
		if (r == true) {";
			echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/loan/ilpayment/deletereceipt/id/".$id."'";
		echo"}";
		echo"else {";
			echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/loan/ilpayment'";
		echo"}
		</script>";
  }
	 function deletereceiptAction(){
	 	$request=Zend_Controller_Front::getInstance()->getRequest();
	 	$action=$request->getActionName();
	 	$controller=$request->getControllerName();
	 	$module=$request->getModuleName();
	 	
	 	$id = $this->getRequest()->getParam("id");
	 	$db = new Loan_Model_DbTable_DbLoanILPayment();
	 	try {
	 		$dbacc = new Application_Model_DbTable_DbUsers();
	 		$rs = $dbacc->getAccessUrl($module,$controller,'delete');
	 		if(!empty($rs)){
	 			$row = $db->checkifExistingDelete($id);
	 			if(!empty($row)){
	 				$db->deleteReceipt($id);
	 				$db->recordhistory($id);
	 				Application_Form_FrmMessage::Sucessfull("DELETE_SUCCESS","/loan/ilpayment");
	 			}else{
	 				Application_Form_FrmMessage::Sucessfull("has been delete","/loan/ilpayment");
	 			}
	 		}
	 		Application_Form_FrmMessage::Sucessfull("You no permission to delete","/loan/ilpayment");
	 	}catch (Exception $e) {
	 		Application_Form_FrmMessage::message("INSERT_FAIL");
	 		echo $e->getMessage();
	 	}
	 }
  function addAction()
  {
  	$rightclick = $this->getRequest()->getParam('rightclick');
  	$rightclick = empty($rightclick)?"":$rightclick;
  	$this->view->rightclick = $rightclick;
  	
  	$db = new Loan_Model_DbTable_DbLoanILPayment();
  	$db_global = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			// Check Session Expire
			$checkses = $db_global->checkSessionExpire();
			if (empty($checkses)){
				$db_global->reloadPageExpireSession();
				exit();
			}
			$_data = $this->getRequest()->getPost();
			try {
				$receipt = $db->addILPayment($_data);
				$db->recordHistoryReceipt($_data, $receipt);
				if($rightclick=="true"){
					Application_Form_FrmMessage::message('INSERT_SUCCESS');
					echo "<script>window.close();</script>";exit();
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/ilpayment/");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = $this->getRequest()->getParam('id');
		if(!empty($id)){
			$dbp = new Loan_Model_DbTable_DbLandpayment();
			$rs = $dbp->getTranLoanByIdWithBranch($id,null);
			$this->view->rsresult =  $rs;
			if($rs['payment_id']==1 || $rs['is_cancel']==1){
				Application_Form_FrmMessage::message('មិនមានទិន្នន័យសម្រាប់បង់ប្រាក់ទេ!');
				echo "<script>window.close();</script>";
			}
		}
		$frm = new Loan_Form_FrmIlPayment();
		$frm_loan=$frm->FrmAddIlPayment();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_ilpayment = $frm_loan;
				
// 		$db_keycode = new Application_Model_DbTable_DbKeycode();
// 		$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
// 		$this->view->graiceperiod = $db_keycode->getSystemSetting(9);
		//$this->view->interest = $db_keycode->getSystemSetting(8);
		
		$this->view->client = $db->getAllClient();
		$this->view->clientCode = $db->getAllClientCode();
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$this->view->user_name = $session_user->first_name .' '.$session_user->last_name;
		$this->view->loan_number = $db_global->getSaleNumberByBranch();
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footer = $frmpopup->getFooterReceipt();
		$this->view->officailreceipt = $frmpopup->getOfficailReceipt();
	}	
	function editAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Loan_Model_DbTable_DbLoanILPayment();
		$db_global = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->updateIlPayment($_data,$_data['id']);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/ilpayment/");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$payment_il = $db->getIlPaymentByID($id);
		$this->view->ilPaymentById= $payment_il;
		
		$receipt_money_detail = $db->getAllReceiptMoneyDetail($id);
		$this->view->receipt_money_detail= $receipt_money_detail;
		
		$frm = new Loan_Form_FrmIlPayment();
		$frm_loan=$frm->FrmAddIlPayment($payment_il);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_ilpayment = $frm_loan;
		
		$db_keycode = new Application_Model_DbTable_DbKeycode();
		$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
		
		$this->view->graiceperiod = $db_keycode->getSystemSetting(9);
		$this->view->interest = $db_keycode->getSystemSetting(8);
		$this->view->client = $db->getAllClient();
		$this->view->clientCode = $db->getAllClientCode();
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$this->view->user_name = $session_user->first_name .' '.$session_user->last_name;
		$test = $this->view->loan_number = $db_global->getSaleNumberByBranch();
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	function editoldAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db_global = new Application_Model_DbTable_DbGlobal();
		$db = new Loan_Model_DbTable_DbLoanILPayment();
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$identify = $_data["identity"];
			try {
				if($identify==""){
					Application_Form_FrmMessage::Sucessfull("Client no laon to pay!","/loan/ilpayment/");
				}else{
					$db->updateIlPayment($_data);
					//Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/loan/ilpayment/");
				}
			}catch (Exception $e) {
				//echo $e->getMessage();
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$payment_il = $db->getIlPaymentByID($id);
		$this->view->ilPaymentById= $payment_il;
		
		$getIlDetail = $db->getIlDetail($id);
		
		$frm = new Loan_Form_FrmIlPayment();
		$frm_loan=$frm->FrmAddIlPayment($payment_il);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_ilpayment = $frm_loan;
		$this->view->ilPayent = $getIlDetail;
		$this->view->client_id=$payment_il["group_id"];
		$this->view->client_code=$payment_il["group_id"];
		$this->view->branch_id=$payment_il["branch_id"];
		$this->view->loan_number=$payment_il["loan_numbers"];
		
		$this->view->client = $db->getAllClient();
		$this->view->clientCode = $db->getAllClientCode();
		
		$db_keycode = new Application_Model_DbTable_DbKeycode();
		$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
		
		$this->view->graiceperiod = $db_keycode->getSystemSetting(9);
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$this->view->user_name = $session_user->last_name .' '. $session_user->first_name;		
		$this->view->loan_numbers = $db->getAllLoanNumberByBranch(1);
	}
	function getLoannumberAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanIL();
			$row = $db->getLoanPaymentByLoanNumber($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getLastPayDateAction(){// get last payment date in loan fundetail by for caculate interest in payoff for client 
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getLastPayDate($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	
	}
	function getLastPaymentDateByLoanAction(){// get last payment in client reciept money for caculate penalize for client 
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getLastPaymentDate($data);
			print_r(Zend_Json::encode($row));
			exit();
		}	
	}
	public function showBarcodesAction(){
		$this->_helper->layout()->disableLayout();
		$id = $this->getRequest()->getParam('id');
		if(!empty($id)){
			$ids=explode(',', $id);
			$this->view->pro_id = $ids;
		}
		else{
		}
	
	}
	public function generateBarcodeAction(){
			$loan_code = $this->getRequest()->getParam('loan_code');
				header('Content-type: image/png');
				
				$this->_helper->layout()->disableLayout();
				$barcodeOptions = array('text' => "$loan_code",'barHeight' => 40);
				//'font' => 4(set size of label),//'barHeight' => 40//set height of img barcode
				$rendererOptions = array();
				$renderer = Zend_Barcode::factory(
						'code128', 'image', $barcodeOptions, $rendererOptions
				)->render();		
		}
	
	function getIlloandetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getLoanPaymentByLoanNumber($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getLoanpaymentschedulehistoryAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getLoanPaymentschedulehistory($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getIlloandetailEditAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getLoanPaymentByLoanNumberEdit($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getAllIlLoanDetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getAllLoanPaymentByLoanNumber($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getLoanHasPayByLoanNumberAction(){//បង្ហាញប្រវត្តប្រាក់បានបងសរុបtab3
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$loan_number = $data["loan_number"];
			$row = $db->getLaonHasPayByLoanNumber($loan_number);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getSaleNumberAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$is_issueplong = 0;
			if(!empty($data["is_issueplong"])){
				$is_issueplong=$data["is_issueplong"];
			}
			$row = $db->getAllLoanNumberByBranch($data["branch_id"],$is_issueplong);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getSaleNumberEditAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getAllLoanNumberByBranchEdit($data["branch_id"]);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getPropertyinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getPropertyInfo($data["property_id"]);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getLastpaiddateAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getLastDatePayment($data["loan_number"]);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getReceiptNumberAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$db = new Application_Model_DbTable_DbGlobal();
			$row = $db->getReceiptByBranch(array("branch_id"=>$data["branch_id"]));
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
	function getbranchAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$dbGB = new Application_Model_DbTable_DbGlobal();
			$branch = $dbGB->getAllBranchInfoByID($data['branch_id']);
			print_r(Zend_Json::encode($branch));
			exit();
		}
	}
	
	function checkpermissionAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$dbGB = new Loan_Model_DbTable_DbLoanILPayment();
			$branch = $dbGB->checkUserPermission($data);
			print_r(Zend_Json::encode($branch));
			exit();
		}
	}
	function updatereceiptAction(){
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		$db  = new Report_Model_DbTable_DbLandreport();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Report_Model_DbTable_DbLandreport();
				$_dbmodel->updateReceipt($_data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/report/loan/receipt/id/".$_data['id']);
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		 
		$id = $this->getRequest()->getParam('id');
		if(!empty($id)){
			$receipt = $db->getReceiptByID($id);
			$this->view->rs = $receipt;
			if(empty($receipt['name_kh'])){
				$this->_redirect("/loan/ilpayment");
			}
			if ($receipt['is_closed']==1){
				Application_Form_FrmMessage::message("CANNOT_EDIT");
				echo "<script>window.close();</script>";
			}
		}else{
			$this->_redirect("/loan/ilpayment");
		}
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->customer =  $db->getAllClient();
	}
}