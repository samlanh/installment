<?php
class Capital_CapitalTransferController extends Zend_Controller_Action {
	public function init()
	{
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
			$db = new Capital_Model_DbTable_DbCapitalTransfer();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'brance_from' => -1,
						'brance_to' => -1,
						'status' => -1);
			}
			$rs_rows= $db->getAllTransfer($search);
			$glClass = new Application_Model_GlobalClass();//status
 			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL,true);
			$list = new Application_Form_Frmtable();
			$collumns = array("ពីសាខា","ទៅសាខា","ថ្ងៃ","ចំនួនប្រាក់ផ្ទេរជាដុល្លា","ចំនួនប្រាក់ផ្ទេរជារៀល","ចំនួនប្រាក់ផ្ទេរបាត","សម្គាល់","ស្ថានភាព");
			$link=array(
					'module'=>'capital','controller'=>'capital-transfer','action'=>'edit'
			);
			$this->view->list=$list->getCheckList(0,$collumns,$rs_rows,array('from_branch'=>$link,'to_branch'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Capital_Form_FrmCapitale();
		$frm = $fm->frmSearch($search);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	public function addAction()
	{
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Capital_Model_DbTable_DbCapitalTransfer();
			try {
				$db->addTransfer($data);
				if(isset($data["save"])){
					print_r($data);exit();
					$db->addTransfer($data);
					//Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/capital-transfer/add');
				}elseif (isset($data["save_close"])){
					$db->addTransfer($data);
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/capital-transfer');
				}else {
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/capital-transfer');
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				
			}
		}
		$fm = new Capital_Form_FrmCapitale();
		$frm = $fm->frmCapitalTransfer();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm= $frm;
	}
	function getamountbybranceidAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$brance_id = $data["brance_id"];
			$db = new Capital_Model_DbTable_DbCapitalTransfer();
			$row = $db->getAmountByBranceId($brance_id);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function editAction(){
		$db = new Capital_Model_DbTable_DbCapitalTransfer();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				
				if(isset($_data["save"])){
					$db->updateTransfer($_data);
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/capital-transfer/add');
				}elseif (isset($_data["save_close"])){
					$db->updateTransfer($_data);
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/capital-transfer');
				}else {
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/capital-transfer');
				}
				Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/capital-transfer');
			}catch(Exception $e){
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = $this->getRequest()->getParam("id");
		$row = $db->getTransferByID($id);
		$fm=new Capital_Form_FrmCapitale();
		$frm = $fm->frmCapitalTransfer($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm=$frm;
	}
	
}
