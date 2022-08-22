<?php
class Project_LandController extends Zend_Controller_Action {
	const REDIRECT_URL='/project/land';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Project_Model_DbTable_DbLand();
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
						'type_property_sale'=>$formdata['type_property_sale'],
					);
			}
			else{
				$search = array(
						'adv_search' => '',
						'status' => -1,
						'branch_id' => -1,
						'property_type_search'=>-1,
						'type_property_sale'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'streetlist'=>''			
					);
			}
			$rs_rows= $db->getAllLandInfo($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","PROPERTY_CODE","STREET","PROPERTY_TYPE","PRICE","WIDTH","HEIGHT","SIZE","HEAD_TITLE_NO","STATUS_BUY","DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'project','controller'=>'land','action'=>'edit',
			);
			$link1=array(
					'module'=>'project','controller'=>'land','action'=>'view',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link1,'land_code'=>$link,'land_address'=>$link,'pro_type'=>$link,'price'=>$link));
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
	
	public function addAction(){
		$db = new Project_Model_DbTable_DbLand();
		if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
			try{
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				
				$id= $db->addLandinfo($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$alltob = $db->getAllTypeTob();
		array_unshift($alltob, array('id'=>'','name' => $tr->translate("SELECT_TYPE_STORE")), array('id'=>'-1', 'name'=>$tr->translate("ADD_NEW")));
		$this->view->alltobtype =$alltob;
		
		$property_type = $db->getPropertyType();
		array_unshift($property_type, array('id'=>'','name' => $tr->translate("SELECT_PROPERTY")), array('id'=>'-1', 'name'=>$tr->translate("Add New Property Type")));
		$this->view->pro_type = $property_type;
		$fm = new Project_Form_FrmLand();
		$frm = $fm->FrmLandInfo();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frmPopupPropertyType = $dbpop->frmPopupPropertyType();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$branch_opt = $db->getAllBranchByUser();
		$this->view->branch_opt = $branch_opt;
		
		$rs_street = $db->getAllStreetForOpt();
		array_unshift($rs_street, array('id'=>-1,'name' => $tr->translate("ADD_NEW")));
		$this->view->street = $rs_street;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	public function addautoAction(){
		$db = new Project_Model_DbTable_DbLand();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			try{
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				$db->addLandinfoAuto($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/addauto");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
		$alltob = $db->getAllTypeTob();
		array_unshift($alltob, array('id'=>'','name' => $tr->translate("SELECT_TYPE_STORE")), array('id'=>'-1', 'name'=>$tr->translate("ADD_NEW")));
		$this->view->alltobtype =$alltob;
		
		$property_type = $db->getPropertyType();
		array_unshift($property_type, array('id'=>'','name' => $tr->translate("SELECT_PROPERTY")), array('id'=>'-1', 'name'=>$tr->translate("Add New Property Type")));
		$this->view->pro_type = $property_type;
		$fm = new Project_Form_FrmLand();
		$frm = $fm->FrmLandInfo();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
	
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frmPopupPropertyType = $dbpop->frmPopupPropertyType();
	
		$db = new Application_Model_DbTable_DbGlobal();
		$branch_opt = $db->getAllBranchByUser();
		$this->view->branch_opt = $branch_opt;
	
		$rs_street = $db->getAllStreetForOpt();
		array_unshift($rs_street, array('id'=>-1,'name' => $tr->translate("ADD_NEW")));
		$this->view->street = $rs_street;
	
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	public function editAction(){
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$db = new Project_Model_DbTable_DbLand();
		if($this->getRequest()->isPost()){
			try{
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				
				$data = $this->getRequest()->getPost();
				$data['id']=$id;
				$db->addLandinfo($data);
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS',"/project/land");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$alltob = $db->getAllTypeTob();
		array_unshift($alltob, array('id'=>'','name' => $tr->translate("SELECT_TYPE_STORE")), array('id'=>'-1', 'name'=>$tr->translate("ADD_NEW")));
		$this->view->alltobtype =$alltob;
		
		$property_type = $db->getPropertyType();
		array_unshift($property_type, array('id'=>'','name' => $tr->translate("SELECT_PROPERTY")), array('id'=>'-1', 'name'=>$tr->translate("Add New Property Type")));
		$this->view->pro_type = $property_type;
		
		$row = $db->getClientById($id);
	        $this->view->row=$row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull('RECORD_NOTFUND',"/project/land",2);
			exit();
		}
		$fm = new Project_Form_FrmLand();
		$frm = $fm->FrmLandInfo($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frmPopupPropertyType = $dbpop->frmPopupPropertyType();
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$db = new Application_Model_DbTable_DbGlobal();		
		$rs_street = $db->getAllStreetForOpt();
		array_unshift($rs_street, array('id'=>-1,'name' => $tr->translate("Add New Property Type")));
		$this->view->street = $rs_street;
	}
	function viewAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Project_Model_DbTable_DbLand();
		$this->view->propertyinfor = $db->getPropertyInfor($id);
	}
	function deleteAction(){
		
		// Check Session Expire
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$checkses = $dbgb->checkSessionExpire();
		if (empty($checkses)){
			$dbgb->reloadPageExpireSession();
			exit();
		}
		
		$id = $this->getRequest()->getParam("id");
		$db = new Project_Model_DbTable_DbLand();
		$row = $db->getCheckPropertyInSale($id);
		if (!empty($row)){
			Application_Form_FrmMessage::Sucessfull("Can not delete this record","/project/land",2);
			exit();
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$delete_sms=$tr->translate('CONFIRM_DELETE');
		echo "<script language='javascript'>
		var txt;
		var r = confirm('$delete_sms');
		if (r == true) {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/project/land/deleterecord/id/".$id."'";
		echo"}";
		echo"else {";
		echo "window.location ='".Zend_Controller_Front::getInstance()->getBaseUrl()."/project/land'";
		echo"}
		</script>";
	}
	function deleterecordAction(){
		
		// Check Session Expire
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$checkses = $dbgb->checkSessionExpire();
		if (empty($checkses)){
			$dbgb->reloadPageExpireSession();
			exit();
		}
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$action=$request->getActionName();
		$controller=$request->getControllerName();
		$module=$request->getModuleName();
		
		$id = $this->getRequest()->getParam("id");
		$db = new Project_Model_DbTable_DbLand();
		try {
			$dbacc = new Application_Model_DbTable_DbUsers();
			$rs = $dbacc->getAccessUrl($module,$controller,'delete');
			if(!empty($rs)){
				$db->deleteLand($id);
				Application_Form_FrmMessage::Sucessfull("DELETE_SUCCESS","/project/land");
				exit();
			}
			Application_Form_FrmMessage::Sucessfull("You no permission to delete","/project/land",2);
			exit();
		}catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("DELETE_FAIL");
		}
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
			$db = new Project_Model_DbTable_DbLand();
			$dataclient=$db->CheckTitle($data);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function copyAction()
	{
		$id=$this->getRequest()->getParam("id");
		$db = new Project_Model_DbTable_DbLand();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			try{
					$id= $db->addLandinfo($data);
					Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
					Application_Form_FrmMessage::redirectUrl("/project/land/index");

			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$alltob = $db->getAllTypeTob();
		array_unshift($alltob, array('id'=>'','name' => $tr->translate("SELECT_TYPE_STORE")), array('id'=>'-1', 'name'=>$tr->translate("ADD_NEW")));
		$this->view->alltobtype =$alltob;
		
		$row = $db->getClientById($id);
		$this->view->row=$row;
		if(empty($row)){
			$this->_redirect("/project/land");
		}
		
		
		$property_type = $db->getPropertyType();
		array_unshift($property_type, array('id'=>'','name' => $tr->translate("SELECT_PROPERTY")), array('id'=>'-1', 'name'=>$tr->translate("Add New Property Type")));
		$this->view->pro_type = $property_type;
		$fm = new Project_Form_FrmLand();
		$frm = $fm->FrmLandInfo($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frmPopupPropertyType = $dbpop->frmPopupPropertyType();
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->street = $db->getAllStreetForOpt();
	}

	function getpropertyamountAction(){
		if($this->getRequest()->isPost()){
			echo 1;
			exit();
			$data = $this->getRequest()->getPost();
			$db = new Project_Model_DbTable_DbLand();
			$maxProperty=50;
			$rs=$db->countAmountProperty($data);
			if($rs>=$maxProperty){
				echo 0;
				exit();
			}
			echo 1;
			exit();
		}
	}
}