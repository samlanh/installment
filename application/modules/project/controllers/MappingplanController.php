<?php
class Project_MappingplanController extends Zend_Controller_Action {
	
	const REDIRECT_URL='/project';
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Project_Model_DbTable_Dbpinmap();
			if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
				$search = array(
						'adv_search' => $formdata['adv_search'],
						'status'=>$formdata['status'],
						'branch_id'=>$formdata['branch_id'],
						'start_date'=> $formdata['start_date'],
						'end_date'=>$formdata['end_date'],
						'streetlist'=>$formdata['streetlist'],
						'property_type_search'=>$formdata['property_type_search'],
				);
			}
			else{
				$search = array(
						'adv_search' => '',
						'status' => -1,
						'branch_id' => -1,
						'property_type_search'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'streetlist'=>''
				);
			}
			$rs_rows= $db->getAllLandInfo($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","PROPERTY_CODE","STREET","PROPERTY_TYPE","MAP_WIDTH","MAP_HEIGHT","MAP_TRANSFORM","MAP_TOP","MAP_LEFT");
			$link=array(
					'module'=>'project','controller'=>'mappingplan','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'land_code'=>$link,'land_address'=>$link,'pro_type'=>$link,'street'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->result=$search;
		
		$fm = new Project_Form_FrmLand();
		$frmserch = $fm->FrmLandInfo();
		Application_Model_Decorator::removeAllDecorator($frmserch);
		$this->view->frm_land = $frmserch;
  
	}
	
	function addAction()
	{
		$this->_helper->layout()->disableLayout();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
		
				$_dbmodel = new Project_Model_DbTable_Dbpinmap();
				$_dbmodel->addMapPin($_data);
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
					//Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/project/mappingplan");
				
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$fm = new Project_Form_FrmMappin();
		$frm = $fm->FrmAddMapBox();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_map = $frm;
		
		$id=$this->getRequest()->getParam("id");
		$this->view->branch_id = empty($id)?0:$id;
		
	}
	function editAction(){
		
	}
		
	
}