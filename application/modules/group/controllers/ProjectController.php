<?php
class Group_ProjectController extends Zend_Controller_Action {
	const REDIRECT_URL='/group';
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
      try{
    	$db = new Group_Model_DbTable_DbProject();
    	 if($this->getRequest()->isPost()){
    	$search=$this->getRequest()->getPost();
   		}
     else{
   		 $search = array(
      		'adv_search' => '',
      		'status_search' => -1);
  		 }
           $rs_rows= $db->getAllBranch($search);
           $glClass = new Application_Model_GlobalClass();
			$rs_rowshow = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME","PREFIX_CODE","CODE","ADDRESS","TEL","FAX","DISPLAY","OTHER","STATUS");
			$link=array(
					      'module'=>'group','controller'=>'project','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(2, $collumns, $rs_rowshow,array('project_name'=>$link,'project_type'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Group_Form_Frmbranch();
		$frm = $fm->Frmbranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
  
	}
	
	function addAction()
	{
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Group_Model_DbTable_DbProject();
			try {
				$_dbmodel->addbranch($_data);
				if(!empty($_data['save_new'])){
					Application_Form_FrmMessage::message($this->tr->translate("INSERT_SUCCESS"));
				}else{
					Application_Form_FrmMessage::Sucessfull($this->tr->translate("INSERT_SUCCESS"),self::REDIRECT_URL . "/project/index");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Group_Form_Frmbranch();
		$frm = $fm->Frmbranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
	}
	function editAction(){
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost())
		{
			$data = $this->getRequest()->getPost();
			$db = new Group_Model_DbTable_DbProject();
			try{
				$db->updateBranch($data,$id);
				Application_Form_FrmMessage::Sucessfull($this->tr->translate("EDIT_SUCCESS"),self::REDIRECT_URL."/project/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message($this->tr->translate("EDIT_FAIL"));
				$err=$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$db=new Group_Model_DbTable_DbProject();
		$row=$db->getBranchById($id);
	
		$frm= new Group_Form_Frmbranch();
		$update=$frm->FrmBranch($row);
		$this->view->frm_branch=$update;
		Application_Model_Decorator::removeAllDecorator($update);
	}
	public function addbranchajaxAction(){//ajax
		if($this->getRequest()->isPost()){
			$db = new Group_Model_DbTable_DbProject();
			$data = $this->getRequest()->getPost();
			$id = $db->addbranchajax($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function copyAction()
	{
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Group_Model_DbTable_DbProject();
			try {
				$_dbmodel->addbranch($_data);
				if(!empty($_data['save_new'])){
					Application_Form_FrmMessage::message($this->tr->translate("INSERT_SUCCESS"));
				}else{
					Application_Form_FrmMessage::Sucessfull($this->tr->translate("INSERT_SUCCESS"),self::REDIRECT_URL . "/project/index");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$db=new Group_Model_DbTable_DbProject();
		$row=$db->getBranchById($id);
		
		$fm = new Group_Form_Frmbranch();
		$frm = $fm->Frmbranch($row,"1");
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
	}
}

