<?php
class Loan_IlpaymentController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
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
								'status'=>"",
								'payment_method'=>-1,);
					}
			$rs_rows= $db->getAllIndividuleLoan($search);
			$result = array();
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_NAME","HOUSE_NO","STREET","RECIEPT_NO","PRINCIPAL","TOTAL_INTEREST","PENALIZE AMOUNT","SERVICE","TOTAL_PAYMENT","RECEIVE_AMOUNT",
					"PAY_DATE","DATE","STATUS",'PRINT','DELETE');
			$link=array('module'=>'loan','controller'=>'ilpayment','action'=>'edit',);
			$linkprint=array('module'=>'report','controller'=>'loan','action'=>'receipt',);
			$link_delete=array('module'=>'loan','controller'=>'ilpayment','action'=>'delete',);
			$this->view->list=$list->getCheckList(2, $collumns, $rs_rows,array('លុប'=>$link_delete,'Delete'=>$link_delete,'branch_name'=>$link,'land_id'=>$link,'team_group'=>$link,
					'client_name'=>$link,'receipt_no'=>$link,'branch'=>$link,'street'=>$link,'បោះពុម្ភ'=>$linkprint));
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
  		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
	 			$db->deleteReceipt($id);
	 			Application_Form_FrmMessage::Sucessfull("DELETE_SUCCESS","/loan/ilpayment");
	 		}
	 		Application_Form_FrmMessage::Sucessfull("You no permission to delete","/loan/ilpayment");
	 	}catch (Exception $e) {
	 		Application_Form_FrmMessage::message("INSERT_FAIL");
	 		echo $e->getMessage();
	 	}
	 }
  function addAction()
  {
  	$db = new Loan_Model_DbTable_DbLoanILPayment();
  	$db_global = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$receipt = $db->addILPayment($_data);
				if($_data['extrapayment']>0){
					$_data['receipt_id'] =$receipt;
					$db->addExtrapayment($_data);
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
				Application_Form_FrmMessage::Sucessfull("មិនមានទិន្នន័យសម្រាប់បង់ប្រាក់ទេ!","/loan");
			}
		}
		$frm = new Loan_Form_FrmIlPayment();
		$frm_loan=$frm->FrmAddIlPayment();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_ilpayment = $frm_loan;
				
		$db_keycode = new Application_Model_DbTable_DbKeycode();
		$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
		
		$this->view->graiceperiod = $db_keycode->getSystemSetting(9);
		$this->view->interest = $db_keycode->getSystemSetting(8);
		$this->view->client = $db->getAllClient();
		$this->view->clientCode = $db->getAllClientCode();
		
		$session_user=new Zend_Session_Namespace('authinstall');
		$this->view->user_name = $session_user->first_name .' '.$session_user->last_name;
		$this->view->loan_number = $db_global->getSaleNumberByBranch();
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}	
	function editAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Loan_Model_DbTable_DbLoanILPayment();
		$db_global = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
// 				print_r($_data);exit();
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
		
		$session_user=new Zend_Session_Namespace('authinstall');
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
		
		$session_user=new Zend_Session_Namespace('authinstall');
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
	function getLoanHasPayByLoanNumberAction(){//បង្ហាញប្រវត្តប្រាក់បានបងសរុប
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
			$row = $db->getAllLoanNumberByBranch($data["branch_id"]);
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
}