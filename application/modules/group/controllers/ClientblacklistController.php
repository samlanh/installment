<?php

class Group_ClientblacklistController extends Zend_Controller_Action
{protected $tr;

public function init()
    {$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Group_Model_DbTable_DbClientBlackList();
			try {
				//print_r($data);exit();
				if(isset($data['btn_save'])){
					$db->updatBlackList($data);
					Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
				}
				if(isset($data['btn_save_close'])){
					$db->updatBlackList($data);
					Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
					Application_Form_FrmMessage::redirectUrl('/group/clientblacklist');
				}
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err = $e->getMessage();
				
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
       $fm = new Group_Form_FrmClientBlackList();
	   $frm = $fm->FrmClientBlackList(); 
	   Application_Model_Decorator::removeAllDecorator($frm);
	   $this->view->Form_client_blacklist = $frm;
	   
    }
    function indexAction()
    {
    	try{
    		$db = new Group_Model_DbTable_DbClientBlackList();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'adv_search' => '',
    					'status_search' => -1,
    					'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
    		}
    		$rs_row= $db->getAllBlackListInList($search);//call frome model
    					$glClass = new Application_Model_GlobalClass();
    					$rs_rows = $glClass->getImgActive($rs_row, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","CUSTOMER_CODE","CUSTOMER_NAME","SEX","SITU_STATUS","NATIONAL_ID","NUMBER","REASON","DATE","STATUS");
    		$link=array(
    				'module'=>'group','controller'=>'clientblacklist','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('name_kh'=>$link,'name_en'=>$link,'client_number'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Group_Form_FrmClientBlackList();
    	$frm = $frm->FrmClientBlackList();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    function editAction(){
    if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Group_Model_DbTable_DbClientBlackList();
			try {
					$db->updatClientBlackList($data);
					Application_Form_FrmMessage::message($this->tr->translate('EDIT_SUCCESS'));
					Application_Form_FrmMessage::redirectUrl('/group/clientblacklist');
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$db = new Group_Model_DbTable_DbClientBlackList();
    	$id = $this->getRequest()->getParam("id");
    	$row = $db->getBlackListById($id);
    	$fm = new Group_Form_FrmClientBlackList();
    	$frm = $fm->FrmClientBlackList($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->Form_client_blacklist = $frm;
    	
    }
   
}
?>
