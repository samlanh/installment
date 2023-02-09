<?php

class Po_IncomeController extends Zend_Controller_Action
{
    public function init()
    {
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Registrar_Model_DbTable_DbIncome();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					"adv_search"=>'',
    					"cate_income"=>'',
    					'studentId'=>'',
    					"branch_id"=>'',
    					'option_type'=>0,
    					"status"=>-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		
    		$this->view->adv_search = $search;
    		$_db = new Application_Model_DbTable_DbGlobal();
			$rs_rows= $db->getAllIncome($search);//call frome model
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","STUDENT_NAME","INCOME_CATEGORY","INCOME_TITLE","RECEIPT_NO","PAYMENT_METHOD","BANK_NAME","CHEQUE_NO","INCOME_OPTION","TOTAL_INCOME","NOTE","PAID_DATE","STATUS");
    		$link=array(
    				'module'=>'registrar','controller'=>'income','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('branch_name'=>$link,'studentName'=>$link,'cate_name'=>$link,'title'=>$link,'invoice'=>$link,'payment_method'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$form=new Registrar_Form_FrmSearchInfor();
    	$form->FrmSearchRegister();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Po_Model_DbTable_DbIncome();				
			try {
				$db->addIncome($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/po/income");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/po/income/add");
				}				
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/po/income/add");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}

		$frm = new Po_Form_FrmIncome();
    	$frm = $frm->FrmIncome(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_income = $frm;
    	
    }
    public function editAction()
    {
    	if($this->getRequest()->isPost()){
    		$id = $this->getRequest()->getParam('id');
			$data=$this->getRequest()->getPost();	
			$data['id']=$id;
			$db = new Registrar_Model_DbTable_DbIncome();				
			try {
				$db->updateIncome($data);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', "/registrar/income");		
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("APPLICATION_ERRRO");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$_db = new Application_Model_DbTable_DbGlobal();
		$id = $this->getRequest()->getParam('id');
		$db = new Registrar_Model_DbTable_DbIncome();
		$row  = $db->getIncomeById($id);
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$user_type_id = $session_user->level;
		$payment_date = date("Y-m-d",strtotime($row['date']));
		$current_date = date("Y-m-d");
		if($user_type_id!=1 AND $current_date>$payment_date){
			Application_Form_FrmMessage::Sucessfull("you data is more then a day.so can not edit",'/registrar/income');
		}
		
		$this->view->rs = $row;
    	$db = new Registrar_Model_DbTable_DbIncome();
    	$payment_method = $db->getPaymentMethod(8); // 8 = rms_view type
    	$this->view->payment_method = $payment_method;
    	 
    	$_db = new Registrar_Model_DbTable_DbCateIncome();
    	$cate_income = $_db->getParentCateIncome();
    	array_unshift($cate_income, array('id'=>'-1','name'=>$this->tr->translate("ADD_NEW")));
    	array_unshift($cate_income, array('id'=>'0','name'=>$this->tr->translate("SELECT_CATEGORY")));
    	$this->view->cate_income = $cate_income;
    	$this->view->parent = $_db->getParentCateIncome();
    	
    	
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$branch_income= $db->getAllBranch();
    	$this->view->branch_name = $branch_income;
    	
    	$this->view->rs_bank = $db->getAllBank();
    	
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->officailreceipt = $frmpopup->receiptOtherIncome();
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    }
    function getReceiptNumberAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
	    	$db = new Registrar_Model_DbTable_DbRegister();
	    	$branch_id = empty($data['branch_id'])?null:$data['branch_id'];
	    	$receipt = $db->getRecieptNo($branch_id);
	    	print_r(Zend_Json::encode($receipt));
	    	exit();
    	}
    }
    function addCateIncomeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbIncome();
    		$cate_income = $db->addNewCateIncome($data);
    		print_r(Zend_Json::encode($cate_income));
    		exit();
    	}
    }
}