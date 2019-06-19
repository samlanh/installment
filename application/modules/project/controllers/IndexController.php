<?php
class Project_indexController extends Zend_Controller_Action {
	
	const REDIRECT_URL='/project';
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
    	$db = new Project_Model_DbTable_DbProject();
    	 if($this->getRequest()->isPost()){
    	$search=$this->getRequest()->getPost();
   		}
     else{
   		 $search = array(
      		'adv_search' => '',
      		'status_search' => -1);
  		 }
           $rs_rows= $db->getAllBranch($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME","PREFIX_CODE","BRANCH_CODE","ADDRESS","TEL","FAX","OTHER","SELLER_NAME","SELLER_NAME_WITH","STATUS");
			$link=array(
					      'module'=>'project','controller'=>'project','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(2, $collumns, $rs_rows,array('project_name'=>$link,'project_type'=>$link));
			
			$this->view->row = $rs_rows;
		}catch (Exception $e){
			Application_Form_FrmMessage::message($this->tr->translate("APPLICATION_ERROR"));
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Project_Form_FrmProject();
		$frm = $fm->Frmbranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
  
	}
	
	function addAction()
	{
		//$this->_redirect("/project/index");
		$_dbmodel = new Project_Model_DbTable_DbProject();
		$allpro = $_dbmodel->countProject();
// 		if ($allpro>=6){
// 			$this->_redirect("/project/index");
// 		}
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Project_Model_DbTable_DbProject();
			try {
				$_data['branch_status']=1;
				$_dbmodel->addbranch($_data);
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_SUCCESS"));
			}catch (Exception $e) {
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Project_Form_FrmProject();
		$frm = $fm->Frmbranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
	}
	function editAction(){
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost())
		{
			$data = $this->getRequest()->getPost();
			$db = new Project_Model_DbTable_DbProject();
			try{
				$db->updateBranch($data,$id);
				Application_Form_FrmMessage::Sucessfull($this->tr->translate("EDIT_SUCCESS"),self::REDIRECT_URL."/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message($this->tr->translate("EDIT_FAIL"));
				$err=$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$db=new Project_Model_DbTable_DbProject();
		$row=$db->getBranchById($id);
		
		
		if ($id>=6){
			$this->_redirect("/project/index");
		}
		
		$this->view->row = $row;
		$frm= new Project_Form_FrmProject();
		$update=$frm->FrmBranch($row);
		$this->view->frm_branch=$update;
		Application_Model_Decorator::removeAllDecorator($update);
		$this->view->rsshare = $db->getBranchHolderById($id);
	}
	public function addbranchajaxAction(){//ajax
		if($this->getRequest()->isPost()){
			$db = new Project_Model_DbTable_DbProject();
			$data = $this->getRequest()->getPost();
			$id = $db->addbranchajax($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	public function viewAction(){
		$id=$this->getRequest()->getParam("id");
		$db= new Project_Model_DbTable_DbProject();
		$this->view->rs = $db->getBranchById($id);
		$this->view->shareholder = $db->getBranchHolderById($id);
	}
	function copyAction()
	{
		$_dbmodel = new Project_Model_DbTable_DbProject();
		$allpro = $_dbmodel->countProject();
		if ($allpro>=6){
			$this->_redirect("/project/index");
		}
		$id=$this->getRequest()->getParam("id");
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Project_Model_DbTable_DbProject();
			$_dbmodel->addbranch($_data);
			try {
				Application_Form_FrmMessage::Sucessfull($this->tr->translate("INSERT_SUCCESS"),self::REDIRECT_URL . "/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$db=new Project_Model_DbTable_DbProject();
		$row=$db->getBranchById($id);
		$this->view->row = $row;
		
		$fm = new Project_Form_FrmProject();
		$frm = $fm->Frmbranch($row,"1");
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
		$this->view->rsshare = $db->getBranchHolderById($id);
	}
}