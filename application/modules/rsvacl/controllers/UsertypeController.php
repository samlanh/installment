<?php

class RsvAcl_UserTypeController extends Zend_Controller_Action
{
	
	const REDIRECT_URL = '/rsvacl/usertype';
	protected $tr;
	private $user_typelist = array();
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		
		$db=new Application_Model_DbTable_DbGlobal();
		$userInfo = $db->getUserInfo();
		$level = empty($userInfo['level']) ? 0 : $userInfo['level'];
    	$sql = "
				SELECT 
					u.user_type_id as id
					,u.user_type as name 
				FROM `rms_acl_user_type` u 
				WHERE u.`status`=1";
		if($level!=1){ // Not Admin
			$sql.= " AND u.`user_type_id` IN (SELECT COALESCE(ut.`user_type_id`,0) FROM `rms_acl_user_type` AS ut WHERE 1 AND (ut.`parent_id` = $level OR ut.`user_type_id`=$level) ) ";
		}
    	$this->user_typelist = $db->getGlobalDb($sql);	
    }

    public function indexAction()
    {
        // action body
    	try {
    		$db_tran=new Application_Model_DbTable_DbGlobal();
    		$db = new RsvAcl_Model_DbTable_DbUserType();
    		$result = $db->getAlluserType();
    		$list = new Application_Form_Frmtable();
    		if(empty($result)){
    			$result = Application_Model_DbTable_DbGlobal::getResultWarning();
    		}
    		$collumns = array("USER_TYPE","PARENT","STATUS");
    		$link=array(
    				'module'=>'rsvacl','controller'=>'usertype','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0,$collumns, $result,array('user_type'=>$link,'title'=>$link));
    		
    		
    		if (empty($result)){
    			$result = array('err'=>1, 'msg'=>'មិនទាន់មានទិន្នន័យនៅឡើយ!');
    		}		
    	} catch (Exception $e) {
    		$result = Application_Model_DbTable_DbGlobal::getResultWarning();
    	}
    }
    
    public function viewUserTypeAction()
    {   
    	/* Initialize action controller here */
    	if($this->getRequest()->getParam('id')){
    		$db = new RsvAcl_Model_DbTable_DbUserType();
    		$user_type_id = $this->getRequest()->getParam('id');
    		$rs=$db->getUserType($user_type_id);
    		$this->view->rs=$rs;
    	}  	 
    	
    }
	public function addAction()
		{
			if($this->getRequest()->isPost())
			{
				$db=new RsvAcl_Model_DbTable_DbUserType();	
				$post=$this->getRequest()->getPost();			
				if(!$db->isUserTypeExist($post['user_type'])){
						$id=$db->insertUserType($post);						
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/rsvacl/usertype/index');
				}else {
					Application_Form_FrmMessage::message('User type had existed already');
				}
			}
		
			$rs= $this->user_typelist;
			$options=array(''=>$this->tr->translate("PLEASE_SELECT"));
			foreach($rs as $read) $options[$read['id']]=$read['name'];
			$this->view->usertype_list= $options;
				
			
	}
	
    public function editAction()
    {	
    	if($this->getRequest()->getParam('id')){
    		$db = new RsvAcl_Model_DbTable_DbUserType();
    		$user_type_id = $this->getRequest()->getParam('id');
    		$rs=$db->getUserType($user_type_id);
    		$this->view->usertype=$rs;
    		$db1=new Application_Model_DbTable_DbGlobal();
			
	
			$sql='SELECT 
				u.user_type_id As id
				,u.user_type AS name
			FROM 
				rms_acl_user_type AS u
			WHERE u.status=1 AND u.user_type_id <> '.$user_type_id;
    		
    		$userInfo = $db1->getUserInfo();
			$level = empty($userInfo['level']) ? 0 : $userInfo['level'];
			if($level!=1){ // Not Admin
				$sql.= " AND u.`user_type_id` IN (SELECT COALESCE(ut.`user_type_id`,0) FROM `rms_acl_user_type` AS ut WHERE 1 AND (ut.`parent_id` = $level OR ut.`user_type_id`=$level) ) ";
			}
			$allusertype=$db1->getGlobalDb($sql);
			$options=array(''=>$this->tr->translate("PLEASE_SELECT"));
    		foreach($allusertype as $read) $options[$read['id']]=$read['name'];
    		$this->view->usertype_list= $options;
    	
    	}
    	else{
    		Application_Form_FrmMessage::message('User type had not existed');
    	}
    	
    	if($this->getRequest()->isPost())
		{
			$post=$this->getRequest()->getPost();
			//print_r($rs); exit;
			if($rs['user_type']==$post['user_type']){
					$db->updateUserType($post,$rs['user_type_id']);
					Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS", '/rsvacl/usertype/index');
				
			}else{
				if(!$db->isUserTypeExist($post['user_type'])){
					$db->updateUserType($post,$rs['user_type_id']);
					Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS", '/rsvacl/usertype/index');
				}else {
					Application_Form_FrmMessage::message('User had existed already');
				}
			}
		}
    }
    function addusertypeAction(){
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_dbmodel = new RsvAcl_Model_DbTable_DbUserType();
    		$_data['user_type']=$_data['user_typename'];
    		$_data['parent_id']=$_data['parent_id'];
    		$u_typeid = $_dbmodel->insertUserType($_data);
    		print_r(Zend_Json::encode($u_typeid));
    		exit();
    	}
    }

}

