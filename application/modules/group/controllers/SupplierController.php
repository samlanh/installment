<?php
class Group_SupplierController extends Zend_Controller_Action {
	const REDIRECT_URL = '/group';
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Group_Model_DbTable_DbSupplier();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status_search' => -1,
						'branch_id' => '',
						);
			}
			$rs_rows= $db->getAllSupplier($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CODE","NAME","ADDRESS","PHONE",
					"EMAIL","STATUS");
			$link=array(
					'module'=>'group','controller'=>'supplier','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_name'=>$link,'supplier_code'=>$link,'name'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$fm = new Group_Form_FrmSupplier();
   		$frm_co=$fm->FrmAddCO();
   		Application_Model_Decorator::removeAllDecorator($frm_co);
   		$this->view->frm_co = $frm_co;
	
	}
	
   function addAction(){
	   	$page = $this->getRequest()->getParam("page");
	   	$page = empty($page)?"":$page;
	   	$this->view->page = $page;
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		$db_co = new Group_Model_DbTable_DbSupplier();
	   		 
	   		try{
	   			// Check Session Expire
	   			$dbgb = new Application_Model_DbTable_DbGlobal();
	   			$checkses = $dbgb->checkSessionExpire();
	   			if (empty($checkses)){
	   				$dbgb->reloadPageExpireSession();
	   				exit();
	   			}
	   			
	   			$db_co->addSupplier($_data);
		   			if (!empty($_data['page'])){
		   				Application_Form_FrmMessage::message('INSERT_SUCCESS');
		   				echo "<script>window.close();</script>";
		   			}
	   				if(!empty($_data['save_new'])){
						Application_Form_FrmMessage::message('INSERT_SUCCESS');
					}else{
						Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/supplier/index');
					}
					
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("INSERT_FAIL");
	   			$err =$e->getMessage();
	   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		}
	   	}
	   	
	   	$frm = new Group_Form_FrmSupplier();
	   	$frm_co=$frm->FrmAddCO();
	   	Application_Model_Decorator::removeAllDecorator($frm_co);
	   	$this->view->frm_co = $frm_co;
   }
   function editAction(){
   	$db_co = new Group_Model_DbTable_DbSupplier();
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		try{
   			// Check Session Expire
   			$dbgb = new Application_Model_DbTable_DbGlobal();
   			$checkses = $dbgb->checkSessionExpire();
   			if (empty($checkses)){
   				$dbgb->reloadPageExpireSession();
   				exit();
   			}
   			
   			$db_co->addSupplier($_data);
   			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",'/group/supplier');
   		}catch(Exception $e){
   			Application_Form_FrmMessage::message("EDIT_FAIL");
   			$err =$e->getMessage();
   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
   	}
   	$id = $this->getRequest()->getParam("id");
   	$row = $db_co->getCOById($id);
   	if(empty($row)){
   		$this->_redirect('group/supplier');
   	}
   	$frm = new Group_Form_FrmSupplier();
   	$frm_co=$frm->FrmAddCO($row);
   	Application_Model_Decorator::removeAllDecorator($frm_co);
   	$this->view->frm_co = $frm_co;
   }
   function getstaffcodeAction(){
   	if($this->getRequest()->isPost()){
   		$db = new Application_Model_DbTable_DbGlobal();
   		$_data = $this->getRequest()->getPost();
   		$id = $db->getSupplierCodeByBranch($_data['branch_id']);
   		print_r(Zend_Json::encode($id));
   		exit();
   	}
   }
}