<?php
class Invpayment_IssuechequeController extends Zend_Controller_Action {
	const REDIRECT_URL = '/invpayment/issuecheque';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		//$db = new ();
		try{
			$db = new Invpayment_Model_DbTable_DbIssueCheque();
			
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'status'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$rs_rows=array();
			$rs_rows= $db->getAllIssueChequePayment($search);//
			
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("PROJECT_NAME","DATE","RECEIVED_CHEQUE_NAME","PAYMENT_NO","SUPPLIER","WITHDRAW_STATUS","STATUS","BY");
    		$link=array(
    				'module'=>'invpayment','controller'=>'issuecheque','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('branch_name'=>$link,'receiverName'=>$link,));
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}

		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
	}
	function addAction(){
		$db = new Invpayment_Model_DbTable_DbIssueCheque();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				$db->issueChequePaymentInvoice($_data);
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		
    	$frm = new Invpayment_Form_FrmCheque();
    	$frm->FrmCheque(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	
	function editAction(){
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Invpayment_Model_DbTable_DbIssueCheque();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				$db->editIssueChequePaymentInvoice($_data);
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","//",2);
		}
		$row = $db->getDataRowIsseueCheque($id);
		$this->view->row = $row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull($tr->translate('NO_DATA'), self::REDIRECT_URL."/index",2);
			exit();
		}
		if ($row['status']==0){
    		Application_Form_FrmMessage::Sucessfull($tr->translate('ALREADY_VOID'), self::REDIRECT_URL."/index",2);
    		exit();
    	}
		if (!empty($row['drawUserId'])){
    		Application_Form_FrmMessage::Sucessfull($tr->translate('ALREADY_WITHDRAW_CHEQUE'), self::REDIRECT_URL."/index",2);
    		exit();
    	}
		
    	$frm = new Invpayment_Form_FrmCheque();
    	$frm->FrmCheque($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	
	function getallissuechequeAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row =$db->getAllIssueChequeRecord($data);
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			array_unshift($_row,array(
					'id' => 0,
					'name' => $tr->translate("SELECT_ISSUECHEQUE"),
			) );
			print_r(Zend_Json::encode($_row));
			exit();
			
		}
	}
	
	function getissueinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Invpayment_Model_DbTable_DbIssueCheque();
			$issueId = empty($data['issueId'])?0:$data['issueId'];
			$_row =$db->getDataRowIsseueCheque($issueId);
			print_r(Zend_Json::encode($_row));
			exit();
			
		}
	}
}

