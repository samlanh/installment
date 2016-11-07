<?php
class Group_PositionController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	const REDIRECT_URL = '/payroll';
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Other_Model_DbTable_DbPosition();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status_search' => -1);
			}
			$rs_rows= $db->getAllStaffPosition($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("POSITION_EN","POSITION_KH","DISPLAY_BY","STATUS");
			$link=array(
					'module'=>'group','controller'=>'position','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('position_en'=>$link,'position_kh'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		

		$frm = new Other_Form_FrmPosition();
		 $frm_co=$frm->FrmAddPosition();
		 Application_Model_Decorator::removeAllDecorator($frm_co);
		 $this->view->frm_position = $frm_co;
	}
	

 function addAction(){
	  if($this->getRequest()->isPost()){
	  	$_data = $this->getRequest()->getPost();
	  	$db = new Other_Model_DbTable_DbMyPosition();
	  	try {
	  	        $db->addPosition($_data);
	  			if(!empty($_data['save_new'])){
					Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
				}else{
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/group/index');
				}
	  	}catch(Exception $e){
	   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
	   			$err =$e->getMessage();
	   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		}
	  }
	   //catch id form select table
		   	$frm = new Other_Form_FrmPosition();
		   	$frm_co=$frm->FrmAddPosition();
		   	Application_Model_Decorator::removeAllDecorator($frm_co);
		   	$this->view->frm_position = $frm_co;
   }
   function getpositionAction(){
   	
			   	 $db=new Other_Model_DbTable_DbMyPosition();
			     $rows=$db->getallPosition();
   	             $this->view->list=$rows;
   }
  
   //end
   function editAction(){
    $db = new Other_Model_DbTable_DbMyPosition();
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		try{
	   			$db->upDatePosition($_data);
	   			Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/group/position');
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
	   			$err =$e->getMessage();
	   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		}
	   	}
	   	$id = $this->getRequest()->getParam("id");//ចាប់ id from ln_position ;
	   	$row = $db->getPositionById($id);
	   	if(empty($row)){
	   		$this->_redirect('group/co');
	   	}
// 	   	if(!empty($row['save_new'])){
// 	   		Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
// 	   	}else{
// 	   		Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/position/index');
// 	   	}
        $frm = new Other_Form_FrmPosition();
		$frm_co=$frm->FrmAddPosition($row);
		Application_Model_Decorator::removeAllDecorator($frm_co);
		$this->view->frm_position = $frm_co;
		   	
   }
}

