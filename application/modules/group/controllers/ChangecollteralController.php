<?php
class Group_ChangecollteralController extends Zend_Controller_Action {
	const REDIRECT_URL='/group';protected $tr;
	public function init()
	{$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
			try{
				$db = new Group_Model_DbTable_DbChangeCollteral();
			    		if($this->getRequest()->isPost()){
			    			$search=$this->getRequest()->getPost();
			    		}
			    		else{
			    			$search = array(
			    					'adv_search' => '',
			    					'status_search' => -1,
			    					'start_date'=>date('Y-m-d'),
			    					'end_date'=>date('Y-m-d'));
			    		}
			$rs_rows= $db->getAllChangeCollteral($search);//call frome model
			
			$arr=array();
			if(!empty($rs_rows))foreach ($rs_rows as $index =>$rs ){
				$arr[]=array(
					'id'=>$rs['id'],
					'branch_id'=>$rs['branch_id'],
					'client_name'=>$rs['client_name'],
					'from'=>'from',
					'to'=>'to',
					'date'=>$rs['date'],
					'note'=>$rs['id'],
					'status'=>$rs['status'],
					'user_id'=>$rs['user_id'],
					
				);
				
				$rows = $db->getColleteralById($rs['id']);
				$from_array='';
				$to_array='';
				foreach($rows as $key =>$row){
//
                                        if(!empty($rows[$key+1])){
                                                $to_array.=$row['collateral']. ' ,';
                                                $from_array.=$row['from_collateral']. ' ,';
					}else{
                                                $to_array.=$row['collateral'];
                                                $from_array.=$row['from_collateral'];
					}
					

//
				}
				$arr[$index]['from']=$from_array;
				$arr[$index]['to']=$to_array;
			}
			
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($arr, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_NAME","FROM","TO","DATE","NOTE","STATUS","BY");
			$link=array(
					'module'=>'group','controller'=>'changecollteral','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_id'=>$link,'owner_code_id'=>$link,'owner_id'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Group_Form_Frmchangecollteral();
		$frm = $fm->FrmChangeCollteral();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_changeCollteral = $frm;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Group_Model_DbTable_DbChangeCollteral();
			try {
				 $db->addChangeCollteral($data);
				if(!empty($data['save_new'])){
					Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
				}elseif(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/changecollteral/index');
				}else{
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/changecollteral/index');
				}
			} catch (Exception $e) { 
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		$fm = new Group_Form_Frmchangecollteral();
		$frm = $fm->FrmChangeCollteral();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_changeCollteral = $frm;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->allclient = $db->getAllClient();
		$this->view->allclient_number = $db->getAllClientNumber();
		$db = new Application_Model_GlobalClass();
		$this->view->collect_option = $db->getCollecteralOption();
		$this->view->owner_type = $db->getCollecteralTypeOption();
		
	}
	public function editAction()
	{
			$db = new Group_Model_DbTable_DbChangeCollteral();
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Group_Model_DbTable_DbChangeCollteral();
		try {
				$db->updateChangeCollteral($data);
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/changecollteral/index');
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		$id = $this->getRequest()->getParam('id');
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull($this->tr->translate('RECORD_NOT_EXIST'), self::REDIRECT_URL. '/changecollteral/index');
		}
		$row  = $db->getChangeCollteralbyid($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull($this->tr->translate('RECORD_NOT_EXIST'), self::REDIRECT_URL. '/changecollteral/index');
		}
		$this->view->row = $row;
		$this->view->rows = $db->getAllCollateralDetailById($id);
		
		$fm = new Group_Form_Frmchangecollteral();
		$frm = $fm->FrmChangeCollteral($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_changeCollteral = $frm;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->allclient = $db->getAllClient();
		$this->view->allclient_number = $db->getAllClientNumber();
		$db = new Application_Model_GlobalClass();
		$this->view->collect_option = $db->getCollecteralOption();
		$this->view->owner_type = $db->getCollecteralTypeOption();
    }
    public function getOwnerinfoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db =new Group_Model_DbTable_DbChangeCollteral();
    		$row=$db->getOwnerInfo($data['client_id']);
    		print_r(Zend_Json::encode($row));
    		exit();
    	}
    }
}
