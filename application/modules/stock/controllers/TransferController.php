<?php
class Stock_TransferController extends Zend_Controller_Action {
	const REDIRECT_URL = '/stock/transfer';
	public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}

    public function indexAction()
    {	
    	$db = new Stock_Model_DbTable_DbTransferstock();
    	try{
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    			$this->view->row_ace=$search;
    		}
    		else{
    			$search=array(
    					'title' => '',
    					'start_date' =>date("Y-m-d"),
    					'end_date' =>date("Y-m-d"),
    			);
    		}
    		$rs_rows= $db->getAllTransfer($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("TRANSFER_NUMBER","TRANSFER_DATE","FROM_LOCATION","TO_LOCATION","NOTE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'stock','controller'=>'transfer','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('transfer_no'=>$link,'transfer_date'=>$link,'fromlocation'=>$link,'tolocation'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		echo $e->getMessage();
    	}
    	 
    	$form=new Stock_Form_FrmSearchProduct();
    	$form->FrmSearchProduct();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
	public function addAction(){
		$db = new Stock_Model_DbTable_DbTransferstock();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db->addTransferStock($data);
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$this->view->tran_no = $db->getTransferNo();
		$db = new Application_Model_DbTable_DbGlobal();
		
		$branch = $db->getAllBranchByUser();
    	$this->view->branchopt = $branch;
    	//print_r($branch);
		
		$db = new Stock_Model_DbTable_DbProduct();
		$d_row= $db->getAllProductsNormal();
		$this->view->rsproduct =$d_row;
	}
	public function editAction(){
		$db = new Stock_Model_DbTable_DbTransferstock();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db->updateTransferStock($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/stock/transfer");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$row = $db->getTransferById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/stock/transfer",2);
			exit();
		}
		$key = new Application_Model_DbTable_DbKeycode();
		$keydata=$key->getKeyCodeMiniInv(TRUE);
		$condictionTransfer = empty($keydata['trasfer_st_cut'])?0:$keydata['trasfer_st_cut'];//0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive
		if ($condictionTransfer==1){
			if ($row['is_received']==1){
				Application_Form_FrmMessage::Sucessfull("TRANSFER_ALREADY_RECEIVED", self::REDIRECT_URL."",2);
				exit();
			}
		}
		$this->view->rs = $row;
		$this->view->rsdetail = $db->getTransferByIdDetail($id,$row['from_location']);
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->rsbranch = $db->getAllBranchByUser();
		
		$db = new Stock_Model_DbTable_DbProduct();
		$d_row= $db->getAllProductsNormal();
		$this->view->rsproduct =$d_row;
	}
	function getcurrentproductAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Stock_Model_DbTable_DbTransferstock();
			$rs = $db->getProductLocation($data['pro_id'],$data['location_id']);
			if(empty($rs)){$rs = array('pro_qty'=>0);}
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	public function showBarcodesAction(){
		$this->_helper->layout()->disableLayout();
		$id = $this->getRequest()->getParam('id');
		if(!empty($id)){
			$ids=explode(',', $id);
			$this->view->pro_id = $ids;
		}
		else{
			//$this->_redirect("/product/index/index");
		}
	
	}
	public function generatebarcodeAction(){
		$this->_helper->layout()->disableLayout();
		$loan_code = $this->getRequest()->getParam('loan_code');		
		header('Content-type: image/png');
		
		//$barcodeOptions = array('text' => "$_itemcode",'barHeight' => 30);
		$barcodeOptions = array('text' => "$loan_code",'barHeight' => 40);
		//'font' => 4(set size of label),//'barHeight' => 40//set height of img barcode
		$rendererOptions = array();
		$renderer = Zend_Barcode::factory(
				'code128', 'image', $barcodeOptions, $rendererOptions
		)->render();
	
	}
}