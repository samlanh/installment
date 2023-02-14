<?php
class Product_ClosingstockController  extends Zend_Controller_Action {
	const REDIRECT_URL = '/product/closingstock';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$db = new Product_Model_DbTable_DbPreCountProduct();
		$rs_rows=array();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		
		$rs_rows= $db->getAllProductClosing($search);
		
		$list = new Application_Form_Frmtable();
		$collumns = array("PROJECT_NAME","DATE","BY_USER","NOTE","STATUS");
		$link=array('module'=>'product','controller'=>'closingstock','action'=>'edit');
		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array(
				'projectName'=>$link,'inputDate'=>$link,'user_name'=>$link));
		
		$frm = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frm = new Product_Form_Frmproduct();
		$frm = $frm->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmSearchProduct = $frm;
	}
	function addAction(){
		$db = new Product_Model_DbTable_DbPreCountProduct();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->addPreCountProduct($data);
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$frm = new Product_Form_FrmPreCount();
    	$frm->FrmPreCountProduct(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	function editAction(){
		$db = new Product_Model_DbTable_DbPreCountProduct();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->updateData($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/product/closingstock/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$row = $db->getDataRow($id);

		$rows = $db->GetRowDetail($id);
		$this->view->rowDetail = $rows;
		
		// if(empty($id) OR empty($row)){
		// 	Application_Form_FrmMessage::Sucessfull("NO_DATA","/product/closingstock/index",2);
		// }
		
		// if($row['isClosed']=1){
		// 	Application_Form_FrmMessage::Sucessfull("Can not edit this data","/product/closingstock/",2);
		// }
	
		$frm = new Product_Form_FrmPreCount();
    	$frm->FrmPreCountProduct($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	function viewAction(){
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$db = new Product_Model_DbTable_DbinitilizeQtybyProject();
		
		$row = $db->getDataRow($id);
		if(empty($id) OR empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/product/initqty/",2);
		}
		$this->view->rsProLocation = $row;
		$proId = $row['proId'];
		$projectId = $row['projectId'];
			
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$dbs = new Product_Model_DbTable_DbProduct();
		
		$result = $dbs->getProductDetailbyId($proId);
		if(empty($id) OR empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/product/index",2);
		}
		$this->view->rsProduct = $result;
	
		$this->view->productMovement = $db->getProductMovement($proId,$projectId);
		$this->view->getProductCosting = $db->getProductCosting($proId,$projectId);
	}
}

