<?php
class Project_IssuerController extends Zend_Controller_Action {
	const REDIRECT_URL = '/project';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Project_Model_DbTable_DbIssuer();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search' => '',
					'status_search' => -1,);
			}
			$rs_rows= $db->geteAllContractIssuer($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("NAME","SEX","NATIONALITY","NATIONALITY_NO","WITHNAME","SEX","NATIONALITY","NATIONALITY_NO","USER_NAME","STATUS");
			$link=array(
					'module'=>'project','controller'=>'issuer','action'=>'edit',
			);
 			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('nameKh'=>$link,'sex'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Project_Form_FrmIssuer();
   		$frm_pro=$frm->FrmIssuer();
   		Application_Model_Decorator::removeAllDecorator($frm_pro);
   		$this->view->frmIssuer = $frm_pro;
   }
   function addAction(){
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		$db = new Project_Model_DbTable_DbIssuer();
	   		try{
	   			// Check Session Expire
	   			$dbgb = new Application_Model_DbTable_DbGlobal();
	   			$checkses = $dbgb->checkSessionExpire();
	   			if (empty($checkses)){
	   				$dbgb->reloadPageExpireSession();
	   				exit();
	   			}
	   			if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/project/issuer",2);
					exit();
				}
				
	   			$db->addContractIssuer($_data);
	   				if(!empty($_data['save_new'])){
						Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/issuer/add');
					}else{
						Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/issuer');
					}
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("INSERT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	} 	
	   	$frm = new Project_Form_FrmIssuer();
	   	$frm_pro=$frm->FrmIssuer();
	   	Application_Model_Decorator::removeAllDecorator($frm_pro);
	   	$this->view->frmIssuer = $frm_pro;
   }
   function editAction(){
	   	$id = $this->getRequest()->getParam("id");
	   	$db_co = new Project_Model_DbTable_DbIssuer();
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
				if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/project/issuer",2);
					exit();
				}
	   			
	   			$db_co->addContractIssuer($_data);
	   			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",'/project/issuer');
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("INSERT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	}
	   	$row = $db_co->getContractIssuerById($id);
	   	$this->view->row = $row;
	   	$frm = new Project_Form_FrmIssuer();
	   	$frm_pro=$frm->FrmIssuer($row);
   		Application_Model_Decorator::removeAllDecorator($frm_pro);
   		$this->view->frmIssuer = $frm_pro;
   }
   
   public function getcontractnameAction(){//ajax
		if($this->getRequest()->isPost()){
			$db = new Application_Model_DbTable_DbGlobal();
			$data = $this->getRequest()->getPost();
			$contractName = $db->getAllContractIssuer();
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			array_unshift($contractName,array(
					'id' => -1,
					'name' => $tr->translate("ADD_NEW"),
			));
			print_r(Zend_Json::encode($contractName));
			exit();
		}
	}
  
}