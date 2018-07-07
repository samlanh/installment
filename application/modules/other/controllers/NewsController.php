<?php
class Other_NewsController extends Zend_Controller_Action {
	const REDIRECT_URL='/other/news';
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	protected $tr;
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Other_Model_DbTable_DbNews();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status_search' => '',
						'start_date' => '',
						'end_date' => date("Y-m-d"));
			}
			$rs_rows= $db->getAllArticle($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","PUBLISH_DATE","STATUS","BY");
			$link=array(
					'module'=>'other','controller'=>'news','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('zone_name'=>$link,'zone_num'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Other_Form_FrmNews();
	   	$frm=$frm->FrmAddNews();
	   	Application_Model_Decorator::removeAllDecorator($frm);
	   	$this->view->frm_new = $frm;
	}
   function addAction(){
	   	if($this->getRequest()->isPost()){
	   		try{
	   			$_data = $this->getRequest()->getPost();
	   			$db = new Other_Model_DbTable_DbNews();
	   			$db->addArticle($_data);
	   			if(!empty($_data['save_new'])){
	   				Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
	   			}else{
	   				Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL);
	   			}
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
	   			$err =$e->getMessage();
	   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		}
	   	}
    	$frm = new Other_Form_FrmNews();
	   	$frm=$frm->FrmAddNews();
	   	Application_Model_Decorator::removeAllDecorator($frm);
	   	$this->view->frm_new = $frm;
   	
   	$dbglobal = new Application_Model_DbTable_DbGlobal();
   	$this->view->lang = $dbglobal->getLaguage();
   }
   function editAction(){
   	$db = new Other_Model_DbTable_DbNews();
   	$id = $this->getRequest()->getParam('id');
  	 	if($this->getRequest()->isPost()){
	   		try{
	   			$_data = $this->getRequest()->getPost();
	   			$db->addArticle($_data);
	   			Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'), self::REDIRECT_URL);
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
	   			$err =$e->getMessage();
	   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		}
	   	}
	   	
	   	$row = $db->getArticleById($id);
	   	$this->view->row = $row;
	   	$this->view->id = $id;
	   	
    	$frm = new Other_Form_FrmNews();
	   	$frm=$frm->FrmAddNews($row);
	   	Application_Model_Decorator::removeAllDecorator($frm);
	   	$this->view->frm_new = $frm;
   	
   	$dbglobal = new Application_Model_DbTable_DbGlobal();
   	$this->view->lang = $dbglobal->getLaguage();
   }
   
   public function deleteAction(){
   	try{
   		$request=Zend_Controller_Front::getInstance()->getRequest();
   		$action=$request->getActionName();
   		$controller=$request->getControllerName();
   		$module=$request->getModuleName();
   
   		$dbacc = new Application_Model_DbTable_DbUsers();
   		$rs = $dbacc->getAccessUrl($module,$controller,'delete');
   		if(!empty($rs)){
   			$id = $this->getRequest()->getParam('id');
   			$db = new Other_Model_DbTable_DbNews();
   			if(!empty($id)){
   				$db->deleteNews($id);
   				Application_Form_FrmMessage::Sucessfull("DELETE_SUCCESS",self::REDIRECT_URL);
   			}
   		}
   		Application_Form_FrmMessage::Sucessfull("You no permission to delete",self::REDIRECT_URL);
   	}catch (Exception $e){
   		Application_Form_FrmMessage::message("Application Error");
   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   	}
   }
}

