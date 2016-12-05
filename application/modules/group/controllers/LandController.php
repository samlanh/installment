<?php
class Group_LandController extends Zend_Controller_Action {
	const REDIRECT_URL = '/group/index';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Group_Model_DbTable_DbLand();
			if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
				$search = array(
						'adv_search' => $formdata['adv_search'],
						'status'=>$formdata['status'],
						'branch_id'=>$formdata['branch_id'],
						'start_date'=> $formdata['start_date'],
						'end_date'=>$formdata['end_date'],
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
						'end_date'=>date('Y-m-d')
						);
			}
			$rs_rows= $db->getAllLandInfo($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","PROPERTY_CODE","TITLE","STREET","PROPERTY_TYPE","PRICE","WIDTH","HEIGHT","SIZE","HEAD_TITLE_NO","DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'group','controller'=>'land','action'=>'edit',
			);
			$link1=array(
					'module'=>'group','controller'=>'land','action'=>'view',
			);
			$this->view->list=$list->getCheckList(2, $collumns, $rs_rows,array('branch_name'=>$link1,'land_code'=>$link,'land_address'=>$link,'pro_type'=>$link,
					'price'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->result=$search;	
		
		$fm = new Group_Form_FrmClient();
		$frmserch = $fm->FrmLandInfo();
		Application_Model_Decorator::removeAllDecorator($frmserch);
		$this->view->frm_land = $frmserch;
	}
	public function addAction(){
		$db = new Group_Model_DbTable_DbLand();
		if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				try{
				 if(isset($data['save_new'])){
					$id= $db->addLandinfo($data);
					Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
				}
				else if (isset($data['save_close'])){
					$id= $db->addLandinfo($data);
					Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
					Application_Form_FrmMessage::redirectUrl("/group/land/index");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$property_type = $db->getPropertyType();
		array_unshift($property_type, array('id'=>'','name' => "-----ជ្រើសរើស-----"), array('id'=>'-1', 'name'=>'Add New Property Type'));
		$this->view->pro_type = $property_type;
		$fm = new Group_Form_FrmClient();
		$frm = $fm->FrmLandInfo();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frmPopupPropertyType = $dbpop->frmPopupPropertyType();
		
		$fm = new Group_Form_Frmbranch();
		$frm = $fm->Frmbranch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_branch = $frm;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$branch_opt = $db->getAllBranchByUser();
		array_unshift($branch_opt, array('id'=>'-1', 'name'=>'បន្ថែមគម្រោងថ្មី'));
		$this->view->branch_opt = $branch_opt;
	}
	public function editAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Group_Model_DbTable_DbLand();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$data['id']=$id;
				$db->addLandinfo($data);
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS',"/group/land");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$property_type = $db->getPropertyType();
		array_unshift($property_type, array('id'=>'','name' => "-----ជ្រើសរើស-----"), array('id'=>'-1', 'name'=>'Add New Property Type'));
		$this->view->pro_type = $property_type;
		
		$row = $db->getClientById($id);
	        $this->view->row=$row;
		if(empty($row)){
			$this->_redirect("/group/land");
		}
		$fm = new Group_Form_FrmClient();
		$frm = $fm->FrmLandInfo($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frmPopupPropertyType = $dbpop->frmPopupPropertyType();
		
	}
	function viewAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Group_Model_DbTable_DbLand();
		$this->view->propertyinfor = $db->getPropertyInfor($id);
	}
	public function addNewclientAction(){//ajax
		if($this->getRequest()->isPost()){
			$db = new Group_Model_DbTable_DbClient();
			$data = $this->getRequest()->getPost();
			$_data['status']=1;
			$id = $db->addClient($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function getlandinfoAction(){
		if($this->getRequest()->isPost()){
			$db = new Group_Model_DbTable_DbClient();
			$data = $this->getRequest()->getPost();
			$code = $db->getGroupCodeBYId($data);
			print_r(Zend_Json::encode($code));
			exit();
		}
	}
	function getclientcodeAction(){
		if($this->getRequest()->isPost()){
			$db = new Group_Model_DbTable_DbClient();
			$data = $this->getRequest()->getPost();
			$code = $db->getClientCode($data['branch_id']);
			print_r(Zend_Json::encode($code));
			exit();
		}
	}
	function getclientinfoAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			$db = new Group_Model_DbTable_DbClient();
			$data = $this->getRequest()->getPost();
			$code = $db->getClientDetailInfo($data);
			print_r(Zend_Json::encode($code));
			exit();
		}
	}
	function getclientcollateralAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			$db = new Group_Model_DbTable_DbClient();
			$data = $this->getRequest()->getPost();
			$code = $db->getClientCallateralBYId($data['client_id']);
			print_r(Zend_Json::encode($code));
			exit();
		}
	}
	function insertDistrictAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_district = new Other_Model_DbTable_DbDistrict();
			$district=$db_district->addDistrictByAjax($data);
			print_r(Zend_Json::encode($district));
			exit();
		}
	}
	function insertcommuneAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_commune = new Other_Model_DbTable_DbCommune();
			$commune=$db_commune->addCommunebyAJAX($data);
			print_r(Zend_Json::encode($commune));
			exit();
		}
	}
	function addVillageAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_village = new Other_Model_DbTable_DbVillage();
			$village=$db_village->addVillage($data);
			print_r(Zend_Json::encode($village));
			exit();
		}
	}
	function insertDocumentTypeAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['status']=1;
			$data['display_by']=1;
			//$data['type']=24;
			
			$db = new Other_Model_DbTable_DbLoanType();
			$id = $db->addViewType($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function insertClientAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db=new Group_Model_DbTable_DbClient();
			$row=$db->addIndividaulClient($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	
	}
	function getclientnumberbybranchAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$dataclient=$db->getAllClientNumber($data['branch_id']);
			//array_unshift($dataclient, array('id' => "-1",'branch_id'=>$data['branch_id'],'name'=>'---Add New Client---') );
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function getclientbybranchAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			 $data = $this->getRequest()->getPost();
			 $db = new Application_Model_DbTable_DbGlobal();
             $dataclient=$db->getAllClient($data['branch_id']);
             array_unshift($dataclient, array('id' => "-1",'branch_id'=>$data['branch_id'],'name'=>'---Add New Client---') );
			 print_r(Zend_Json::encode($dataclient));
			exit();
		}
	
	}
	function getGroupclientbybranchAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$dataclient=$db->getAllClientGroup($data['branch_id']);
			array_unshift($dataclient, array('id' => "-1",'branch_id'=>$data['branch_id'],'name'=>'---Add New Client---') );
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function getGoupCodebybranchAction(){//At callecteral when click client
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$dataclient=$db->getAllClientGroupCode($data['branch_id']);
			array_unshift($dataclient, array('id' => "-1",'branch_id'=>$data['branch_id'],'name'=>'---Add New Client---') );
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function getPropertyNoAction(){// by vandy get property code
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$dataclient=$db->getNewLandByBranch($data['branch_id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function checkTitleAction(){// by vandy check tilte property 
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Group_Model_DbTable_DbLand();
			$dataclient=$db->CheckTitle($data);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function copyAction()
	{
		$id=$this->getRequest()->getParam("id");
		$db = new Group_Model_DbTable_DbLand();
		if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				try{
				 if(isset($data['save_new'])){
					$id= $db->addLandinfo($data);
					Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
				}
				else if (isset($data['save_close'])){
					$id= $db->addLandinfo($data);
					Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
					Application_Form_FrmMessage::redirectUrl("/group/land/index");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row = $db->getClientById($id);
		$this->view->row=$row;
		if(empty($row)){
			$this->_redirect("/group/land");
		}
		
		$property_type = $db->getPropertyType();
		array_unshift($property_type, array('id'=>'','name' => "-----ជ្រើសរើស-----"), array('id'=>'-1', 'name'=>'Add New Property Type'));
		$this->view->pro_type = $property_type;
		$fm = new Group_Form_FrmClient();
		$frm = $fm->FrmLandInfo($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frmPopupPropertyType = $dbpop->frmPopupPropertyType();
	}
	
}

