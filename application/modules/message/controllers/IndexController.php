<?php
class Message_IndexController extends Zend_Controller_Action {
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
// 		try{
		$db = new Message_Model_DbTable_Dbapi();
		if(!empty($this->getRequest()->isPost())){
			$search=$this->getRequest()->getPost();
		}
		else{
			$search = array(
				'adv_search'=>'',
				'client_name'=> -1,
				'schedule_opt' => -1,
				'branch_id' => -1,
				'streetlist'=>'',
				'status' => -1,
				'co_id' => -1,
				'land_id'=>-1,
				'branch_id'=>-1,
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d'),
			);
		}
			$rs_rows= $db->getAllSentSMS($search);//call frome model
			$list = new Application_Form_Frmtable();
			$collumns = array("SMS_CONTANCE","PHONE_NUMBER","SEND_DATE","SEND_OPT","BY_USER");
			$link=array('module'=>'loan','controller'=>'income','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('branch_name'=>$link,'client_name'=>$link,'title'=>$link,'invoice'=>$link));
		
// 		}catch (Exception $e){
// 			Application_Form_FrmMessage::message("Application Error");
// 			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 		}
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	function addAction(){//list payment that collect from client
		$dbs = new Report_Model_DbTable_DbloanCollect();
		$frm = new Application_Form_FrmSearchGlobal();
		if($this->getRequest()->isPost()){
			$submit = $this->getRequest()->getPost();
			if(!empty($submit['sendSMS'])){
				$db = new Message_Model_DbTable_Dbapi();
				echo $db->SendSMSAPI($submit);
				
				$search = array(
						'branch_id'=>-1,
						'client_name'=>'',
						'last_optiontype'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'stepoption'=>0,
						'status' => -1
				);
			}else{
				$search = $submit;
			}
		}
		else{
			$search = array(
				'branch_id'=>-1,
				'client_name'=>'',
				'last_optiontype'=>-1,
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d'),
				'stepoption'=>0,
				'status' => -1
			);
		}
		$db  = new Report_Model_DbTable_DbLandreport();
		$this->view->date_show=$search['end_date'];
		$this->view->search=$search;
		$row = $dbs->getAllLnClient($search);
		$this->view->tran_schedule=$row;
		 
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	
		$this->view->list_end_dates = $search["end_date"];
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->stepoption = $db->getVewOptoinTypeByType(29);
		 
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
	}
// 	public function salebytypeAction(){
// 		$this->_helper->layout()->disableLayout();
// 		$db = new Api_Model_DbTable_Dbapi();
// 		print_r(Zend_Json::encode($db->getSalebyType()));
// 		exit();
// 	}
	
}

