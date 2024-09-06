<?php
class Issue_PlonglistController extends Zend_Controller_Action {
	
	const REDIRECT_URL_ADD ='/items/measure/add';
	const REDIRECT_URL_CLOSE ='/items/measure/index';
    public function init()
	{
     /* Initialize action controller here */
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
		    if($this->getRequest()->isPost()){
 				$search = $this->getRequest()->getPost();
 			}else{
				$search = array(
					'txt_search'=>'',
					'client_name'=> -1,
					'branch_id' => -1,
					'land_id'=>-1,
					'status' => -1,
					'process_status'=>0,
					'plong_type'=>0,
					'status_plong'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$db = new Issue_Model_DbTable_Dbplonglist();
			$rs_rows= $db->plongListData($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","PROPERTY_CODE","SOFT_TITLE","HARD_TITLE","CLIENT_NAME","PHONE",
			"SOLD_PRICE","BALANCE","PROCCESSING","STATUS");
			$link_info=array('module'=>'issue','controller'=>'plonglist','action'=>'edit',);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array(),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$this->view->rssearch = $search;
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frm = new Issue_Form_FrmPlongStep();
		$frm_loan=$frm->FrmPlongStep();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_searchplog = $frm_loan;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			try{
				$_dbmodel = new Issue_Model_DbTable_Dbplonglist();
				$_dbmodel->addPlongList($data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/issue/plonglist/add");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->rsBranch = $db->getAllBranchName(null,null);
	}
	function editAction(){
		Application_Form_FrmMessage::redirector("/issue/plonglist/index");
	}
	
	
}

