<?php

class Other_LoanTypeController extends Zend_Controller_Action
{
	protected $tr;
	public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Other_Model_DbTable_DbLoanType();
			try {
				$db->addViewType($data);
				if(isset($data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/other/loantype/add");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/other/loantype");
				}
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
       $fm = new Other_Form_FrmVeiwType();
	   $frm = $fm->FrmViewType(); 
	   Application_Model_Decorator::removeAllDecorator($frm);
	   $this->view->Form_Frmcallecterall = $frm;
    }
    public function indexAction()
    {
    	try{
    		$db = new Other_Model_DbTable_DbLoanType();
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'adv_search' => '',
    					'status_search' => -1);
    		}
    		$rs_rows= $db->getAllviewBYType($search);//call frome model
    		$list = new Application_Form_Frmtable();
    		$collumns = array("NAME_EN","NAME_KH","TYPE","STATUS");
    		$link=array(
    				'module'=>'other','controller'=>'loantype','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('name_en'=>$link,'name_kh'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Other_Form_Frmcallecterall();
    	$frm = $frm->Frmcallecterall();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function editAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Other_Model_DbTable_DbLoanType();
			try {
				$db->updatViewById($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/other/loantype");
				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		
    	}
    	$id = $this->getRequest()->getParam('id');
    		
    	$db = new Other_Model_DbTable_DbLoanType();
    	$row  = $db->getListViewById($id);
    	$fm = new Other_Form_FrmVeiwType();
    	$frm = $fm->FrmViewType($row);
	    Application_Model_Decorator::removeAllDecorator($frm);
	    $this->view->Form_Frmcallecterall = $frm;
    }
    function addSaleconditionAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Other_Model_DbTable_DbLoanType();
    		$param = array(
    				'title_en'=>$data['textValue'],
    				'title_kh'=>$data['textValue'],
    				'type'=>$data['type']
    				);
    		$id = $db->addViewType($param,1);
    		print_r(Zend_Json::encode($id));
    		exit();
    	}
    }
}
?>
