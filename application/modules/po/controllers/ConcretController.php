<?php
class Po_ConcretController extends Zend_Controller_Action {
	const REDIRECT_URL = '/po/concret';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$rs_rows=array();
		$db = new Po_Model_DbTable_DbConcret();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'supplierId'=>-1,
					'status'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			
			$rs_rows= $db->getAllReceiveStockConcret($search);
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","SUPPLIER","DN_NO","RECEIVE_DATE","PRODUCT_NAME","QTY","UNIT_PRICE","TOTAL","NOTE","BY_USER","STATUS");
			$link=array('module'=>'po','controller'=>'concret','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('projectName'=>$link,'supplierName'=>$link,'dnNumber'=>$link,
					'proName'=>$link,'qtyReceive'=>$link));
			
			$frm_search = new Application_Form_FrmAdvanceSearchStock();
			$frm = $frm_search->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
		
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{		
				$db = new Po_Model_DbTable_DbConcret();
				$db->addReceiveStock($_data);
				if(isset($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL);
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Po_Form_FrmConcretStock();
		$frm = $fm->Frmconcret();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	function editAction(){
		$db = new Po_Model_DbTable_DbConcret();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->updateConcreteReceive($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL);
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$row = $db->getDNById($id);
		if(empty($id) OR empty($row) OR ($row['isIssueInvoice']==1)){
			Application_Form_FrmMessage::Sucessfull("ALREADY_INVOICE",self::REDIRECT_URL,2);
		}
		$fm = new Po_Form_FrmConcretStock();
		$frm = $fm->Frmconcret($row);
		$this->view->rs = $row;
		
		$this->view->rows = $db->getDNDetailById($id);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;

		$dbg = new Application_Model_DbTable_DbGlobalStock();	
		$work_type = $dbg->getWorkTypeOpt();
		$this->view->worktype = $work_type;
	}

	
}

