<?php
class Group_CoController extends Zend_Controller_Action {
	const REDIRECT_URL = '/group';
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Other_Model_DbTable_DbCreditOfficer();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status_search' => -1,
						'branch_id' => '',
						'position' => '',
						'degree' => '');
			}
			$rs_rows= $db->getAllCreditOfficer($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","STAFF_CODE","NAME_KH","SEX","NATIONAL_ID","ADDRESS","PHONE","EMAIL","BY_USER","STATUS");
			$link=array('module'=>'group','controller'=>'co','action'=>'edit',);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_name'=>$link,'co_code'=>$link,'co_khname'=>$link,'co_engname'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Other_Form_FrmCO();
   		$frm_co=$fm->FrmAddCO();
   		Application_Model_Decorator::removeAllDecorator($frm_co);
   		$this->view->frm_co = $frm_co;
	}
	
   function addAction(){
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		$db_co = new Other_Model_DbTable_DbCreditOfficer();
	   		try{
	   			// Check Session Expire
	   			$dbgb = new Application_Model_DbTable_DbGlobal();
	   			$checkses = $dbgb->checkSessionExpire();
	   			if (empty($checkses)){
	   				$dbgb->reloadPageExpireSession();
	   				exit();
	   			}
	   			
	   			$db_co->addCreditOfficer($_data);
	   				if(!empty($_data['save_new'])){
						Application_Form_FrmMessage::message('INSERT_SUCCESS');
					}else{
						Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/co/index');
					}
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("INSERT_FAIL");
	   			$err =$e->getMessage();
	   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		}
	   	}
	   	$frm = new Other_Form_FrmCO();
	   	$frm_co=$frm->FrmAddCO();
	   	Application_Model_Decorator::removeAllDecorator($frm_co);
	   	$this->view->frm_co = $frm_co;
   }
   function editAction(){
   	$db_co = new Other_Model_DbTable_DbCreditOfficer();
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
   			
   			$db_co->addCreditOfficer($_data);
   			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",'/group/co');
   		}catch(Exception $e){
   			Application_Form_FrmMessage::message("INSERT_FAIL");
   			$err =$e->getMessage();
   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
   	}
   	$id = $this->getRequest()->getParam("id");
   	$row = $db_co->getCOById($id);
   	$this->view->photo = $row['photo'];
   	if(empty($row)){
   		$this->_redirect('group/co');
   	}
   	$this->view->datainuser = $db_co->getUserByStaffID($id);
   	$frm = new Other_Form_FrmCO();
   	$frm_co=$frm->FrmAddCO($row);
   	Application_Model_Decorator::removeAllDecorator($frm_co);
   	$this->view->frm_co = $frm_co;
   
   }
   public function addNewcoAction(){
   	if($this->getRequest()->isPost()){
   		$data = $this->getRequest()->getPost();
   		$data['status']=1;
   		$data['co_id']='';
   		$data['name_kh']='';
   		$db_co = new Other_Model_DbTable_DbCreditOfficer();
   		$id = $db_co->addCoByAjax($data);
   		print_r(Zend_Json::encode($id));
   		exit();
   	}
   }
   public function addnewdepartmentAction(){
   	if($this->getRequest()->isPost()){
   		$db = new Group_Form_FrmDepartment();
   		$_data = $this->getRequest()->getPost();
   		$id = $db->addDepartmentPop($_data);
   		print_r(Zend_Json::encode($id));
   		exit();
   	}
   }
   function getstaffcodeAction(){
   	if($this->getRequest()->isPost()){
   		$db = new Application_Model_DbTable_DbGlobal();
   		$_data = $this->getRequest()->getPost();
   		$id = $db->getStaffNumberByBranch($_data['branch_id']);
   		print_r(Zend_Json::encode($id));
   		exit();
   	}
   }
   function checkusernameAction(){
   	if($this->getRequest()->isPost()){
   		$db = new Other_Model_DbTable_DbCreditOfficer();
   		$_data = $this->getRequest()->getPost();
   		$id = $db->checkusername($_data['user_name']);
   		print_r(Zend_Json::encode($id));
   		exit();
   	}
   }
}

