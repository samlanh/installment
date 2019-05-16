<?php
class Invest_WithdrawbrokerController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
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
						'adv_search'=>'',
						'status' => -1,
						'broker_search'=>0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Invest_Model_DbTable_DbWithdrawBroker();
			$rs_rows= $db->getAllInvestmentReceipt($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("RECIEPT_NO","INVESTOR","INVEST_NO","AMOUNT_RETURN","TOTAL_AMOUNT_RETURN","RECEIVE_AMOUNT","PAY_DATE","DATE_INPUT","PAYMENT_OPTION","BY_USER","STATUS");
			$link=array('module'=>'invest','controller'=>'investment','action'=>'edit',);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array(),0);
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frm = new Invest_Form_FrmWithdraw();
		$frm_loan=$frm->FrmAddWithdrawBroker();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
  }
  function addAction()
  {
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			
			try {
				$_dbmodel = new Invest_Model_DbTable_DbWithdrawBroker();
				$_dbmodel->addInvestmentWithdrawal($_data);
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/invest/withdrawbroker");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/invest/withdrawbroker/add");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Invest_Form_FrmWithdraw();
		$frm_loan=$frm->FrmAddWithdrawBroker();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
		
	}
	
	function deleteAction(){
	
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Invest_Model_DbTable_DbWithdrawBroker();
		$payment_il = $db->getReceiptByID($id);
		if (!empty($payment_il)){
			if ($payment_il['is_closed']==1){
				Application_Form_FrmMessage::Sucessfull("Can not delete this record","/invest/withdrawbroker");
			}
		}
		$delete_sms=$tr->translate('CONFIRM_DELETE');
	
		echo "<script language='javascript'>
		var txt;
		var r = confirm('$delete_sms');
		if (r == true) {";
			echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/invest/withdrawbroker/deletereceipt/id/$id';";
			echo "}";
			echo "else {";
			echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/invest/withdrawbroker';";
			echo "}
		</script>";
	}
	function deletereceiptAction(){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$action=$request->getActionName();
		$controller=$request->getControllerName();
		$module=$request->getModuleName();
			
		$id = $this->getRequest()->getParam("id");
		$db = new Invest_Model_DbTable_DbWithdrawBroker();
		try {
			$dbacc = new Application_Model_DbTable_DbUsers();
			$rs = $dbacc->getAccessUrl($module,$controller,'delete');
			if(!empty($rs)){
				$row = $db->checkifExistingDelete($id);
				if(!empty($row)){
					$db->deleteReceipt($id);
					Application_Form_FrmMessage::Sucessfull("DELETE_SUCCESS","/invest/withdrawbroker");
				}else{
					Application_Form_FrmMessage::Sucessfull("has been delete","/invest/withdrawbroker");
				}
			}
			Application_Form_FrmMessage::Sucessfull("You no permission to delete","/invest/withdrawbroker");
		}catch (Exception $e) {
			Application_Form_FrmMessage::message("INSERT_FAIL");
			echo $e->getMessage();
		}
	}
	
	function getScheduleInvestorAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Invest_Model_DbTable_DbWithdrawBroker();
			$row = $db->getBrokerPaymentScheduleByID($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getAllinvestdetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Invest_Model_DbTable_DbWithdrawBroker();
			$row = $db->getAllBrokerPaymentScheduleById($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getInvesthaswithdrawAction(){//បង្ហាញប្រវត្តប្រាក់បានបងសរុបtab3
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Invest_Model_DbTable_DbWithdrawBroker();
			$investment_id = $data["investment_id"];
			$row = $db->getBrokerHasPayByID($investment_id);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
}

