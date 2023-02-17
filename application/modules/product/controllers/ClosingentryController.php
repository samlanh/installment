<?php
class Product_ClosingentryController extends Zend_Controller_Action {
	const REDIRECT_URL='/product/closingentry';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$rs_rows=array();
		$db = new Product_Model_DbTable_DbClosingStock();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'branch_id'=>-1,
					'adjustDate'=>'',
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			
			$rs_rows= $db->getAllClosingStock($search);
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$this->view->search = $search;
			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME","CLOSING_DATE","END_DATE","NOTE","ADJUST_DATE","BY_USER");
			$link=array('module'=>'product','controller'=>'closingentry','action'=>'index');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('projectName'=>$link,'adjustDate'=>$link,
			'user_name'=>$link,'status'=>$link));
			$frm = new Application_Form_FrmAdvanceSearch();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
		
	}
	function addAction(){
		$db = new Product_Model_DbTable_DbClosingStock();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->addClosingEntry($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$frm = new Stockinout_Form_FrmAdjustStock();
    	$frm->FrmAdjust(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	function getAllclosingdateAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Product_Model_DbTable_DbClosingStock();
			$results=$db->getAllClosingDate($data);
				
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				
			array_unshift($results, array ('id' => 0,'name' =>$tr->translate("SELECT_REPORT_DATE")));
			print_r(Zend_Json::encode($results));
			exit();
		}
	}
// 	function editAction(){
// 		$db = new Stockinout_Model_DbTable_DbAdjustStock();
// 		if($this->getRequest()->isPost()){
// 			$_data = $this->getRequest()->getPost();
// 			try {
// 				$db->upateAdjustStock($_data);
// 				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
// 			}catch(Exception $e){
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 			}
// 		}
// 		$id = $this->getRequest()->getParam('id');
// 		$id = empty($id)?0:$id;
// 		$row = $db->getDataRow($id);
// 		if(empty($id) OR empty($row)){
// 			Application_Form_FrmMessage::Sucessfull("NO_DATA","/stockinout/adjuststock/",2);
// 		}
// 		$this->view->rs = $row;
// 		$this->view->results = $db->getDataAllRow($id);
// 	}
}

