<?php
class Invest_BrokerController extends Zend_Controller_Action {
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
						'document_type'=>"",
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Invest_Model_DbTable_DbBroker();
			$rs_rows= $db->getAllInvestor($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("NAME","SEX","DOB","DOCUMENT_TYPE","NUMBER","NATIONALITY","PHONE","EMAIL","STATUS");
			$link=array('module'=>'invest','controller'=>'broker','action'=>'edit',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('name'=>$link,'sex'=>$link,),0);
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
  }
  function addAction()
  {
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Invest_Model_DbTable_DbBroker();
				$_dbmodel->addBroker($_data);
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/invest/broker");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/invest/broker/add");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Invest_Form_FrmInvestor();
		$frm_loan=$frm->FrmAddBroker();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
		
	}
	
	public function editAction(){
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$_db = new Invest_Model_DbTable_DbBroker();
		$row = $_db->getBrokerById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/invest/broker");
			exit();
		}
		$this->view->row = $row;
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_db->addBroker($_data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/invest/broker");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$frm = new Invest_Form_FrmInvestor();
		$frm_loan=$frm->FrmAddBroker($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
		
	}
	
	public function getbrokerinfoAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Invest_Model_DbTable_DbBroker();
			$rows = $_dbmodel->getBrokerById($_data['broker_id']);
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
}

