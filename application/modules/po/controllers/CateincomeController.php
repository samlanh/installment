<?php

class Po_CateincomeController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/po/cateincome';
	
    public function init()
    {
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{
    		$db = new Po_Model_DbTable_DbCateIncome();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					"adv_search"=>'',
    					"status"=>-1,
    			);
    		}
    		$this->view->adv_search = $search;

    		$rs_rows=array();
			$rs_rows= $db->getAllCateIncome($search);//call frome model

			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","ACCOUNT_CODE","CREATE_DATE","USER","STATUS");
			$link=array(
				'module'=>'po','controller'=>'cateincome','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows, array('name'=>$link,'accountCode'=>$link,));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		echo $e->getMessage();
    	}
		

    	$frm = new Po_Form_FrmSearchexpense();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Po_Model_DbTable_DbCateIncome();				
			try{
			
				$cate = $db->addCateIncome($data);
				if($cate==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",'/po/cateincome/index');
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",'/po/cateincome/add');
				}				
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		$db = new Po_Model_DbTable_DbCateIncome();
		$this->view->parent = $db->getParentCateIncome();
    }
 
    public function editAction()
    {
    	if($this->getRequest()->isPost()){
    		$id = $this->getRequest()->getParam('id');
			$data=$this->getRequest()->getPost();	
			$data['id']=$id;
			$db = new Po_Model_DbTable_DbCateIncome();				
			try {
				$db->updateCateIncome($data);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', "/po/cateincome");		
			} catch (Exception $e) {
				$this->view->msg = 'EDIT_FAIL';
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$db = new Po_Model_DbTable_DbCateIncome();
		$row  = $db->getCateIncomeById($id);
		$this->view->rs = $row;
		$this->view->parent = $db->getParentCateIncome($id);
    }

	function getallcateincomeAction(){

		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$results=$db->getAllCateIncome();
				
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
			array_unshift($results, array ('id' => 0,'name' =>$tr->translate("SELECT_CATE_INCOME")));
			if(!empty($data['noBtnNew'])){
			}else{
				array_unshift($results, array ('id' => -1,'name' =>$tr->translate("ADD_NEW")));
			}
				
			print_r(Zend_Json::encode($results));
			exit();
		}
	}

}







