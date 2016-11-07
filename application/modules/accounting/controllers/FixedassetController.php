<?php
class accounting_FixedAssetController extends Zend_Controller_Action {
	const REDIRECT_URL = '/accounting/asset';
	public function init()
	{
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function addAction(){
		
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
// 			ripnt_r($data);exit();
			$db = new Accounting_Model_DbTable_DbAsset();
			try {
				$db->addasset($data);
				if(!empty($data['save_new'])){
					//Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
				}else{
					//Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/position/index');
				}
				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Accounting_Form_Frmasset();
		$frm = $fm->FrmAsset();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fixedasset = $frm;
	}
	
	

	public function indexAction()
	{
		try{
			$db = new Accounting_Model_DbTable_DbAsset();
			    		if($this->getRequest()->isPost()){
			    			$search=$this->getRequest()->getPost();
			    		}
			    		else{
			    			$search = array(
			    					'adv_search' => '',
			    					'status' => -1);
			    		}
			$rs_rows= $db->getAllAsset($search);
			
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME  ","FIXED_ASSETNAME ","FIXED_ASSET_TYPE","ASSET_COST","PAY_TYPE","USEFULL_LIFE","SALVAGEVALUE","TOTA_AMOUNT","PAYMANT_MATHOD","Status","NOTE");
			$link=array(
					'module'=>'accounting','controller'=>'FixedAsset','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('fixed_assetname'=>$link,'fixed_asset_type'=>$link,'asset_cost'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$fm = new Application_Form_FrmAdvanceSearch();
		$frm = $fm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$fms = new Accounting_Form_Frmasset();
		$frms = $fms->FrmAsset();
		Application_Model_Decorator::removeAllDecorator($frms);
		$this->view->frm_fixedasset = $frms;
	}
		public function editAction()
		{
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbAsset();
			try {
				$db->updatasset($data);
				Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			
			}
			}
			$id = $this->getRequest()->getParam('id');
			// 		if(empty($id)){
			// 			Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
			// 		}
			$db = new Accounting_Model_DbTable_DbAsset();
			$row  = $db->getassetbyid($id);
			$pructis=new Accounting_Form_Frmasset();
			$frm = $pructis->FrmAsset($row);
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_fixedasset=$frm;
			
		
	}
}
