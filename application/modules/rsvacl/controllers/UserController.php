<?php

class RsvAcl_UserController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/rsvacl';
	const MAX_USER = 20;
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	private $user_typelist = array();
	
    public function init()
    {
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	
    	$db=new Application_Model_DbTable_DbGlobal();
    	$sql = "SELECT u.user_type_id,u.user_type FROM `rms_acl_user_type` u where u.`status`=1";
    	$results = $db->getGlobalDb($sql);
		foreach ($results as $key => $r){
			$this->user_typelist[$r['user_type_id']] = $r['user_type'];    
		}		
    }

    public function indexAction()
    {
		$db_user=new Application_Model_DbTable_DbUsers();
                
        $this->view->activelist =$this->activelist;       
        $this->view->user_typelist =$this->user_typelist;   
        $this->view->active =-1;
        
        $_data = array(
        	'active'=>-1,
        	'user_type'=>-1,
        	'txtsearch'=>''
        );
        if($this->getRequest()->isPost()){     	
        	$_data=$this->getRequest()->getPost();
        }
        $rs_rows = $db_user->getUserList($_data);
        $_rs = array();
        foreach ($rs_rows as $key =>$rs){
        	$_rs[$key] =array(
        	'id'=>$rs['id'],
        	'name'=>$rs['last_name'].' '.$rs['name'],
        	'user_name'=>$rs['user_name'],
        	'user_type'=>$this->user_typelist[$rs['user_type']],
        	'status'=>$rs['status']);
        }
        $list = new Application_Form_Frmtable();
        if(!empty($_rs)){
        	$glClass = new Application_Model_GlobalClass();
        	$rs_rows = $glClass->getImgActive($_rs, BASE_URL, true);
        }
        else{
        	$result = Application_Model_DbTable_DbGlobal::getResultWarning();
        }
        $collumns = array("LASTNAME_FIRSTNAME","USER_NAME","USER_TYPE","STATUS");
        $link=array(
        		'module'=>'rsvacl','controller'=>'user','action'=>'edit',
        );
        $this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('user_name'=>$link,'name'=>$link));
    }
    public function viewUserAction()
    {   
    	/* Initialize action controller here */
    	if($this->getRequest()->getParam('id')){
    		$db = new RsvAcl_Model_DbTable_DbUser();
    		$user_id = $this->getRequest()->getParam('id');
    		$rs=$db->getUser($user_id);
    		//print_r($rs); exit;
    		$this->view->rs=$rs;
    	}  	 
    	
    }
	public function addUserAction()
		{
			$form=new RsvAcl_Form_FrmUser();	
			$this->view->form=$form;
			
			if($this->getRequest()->isPost())
			{
				$db=new RsvAcl_Model_DbTable_DbUser();	
				$post=$this->getRequest()->getPost();			
				if(!$db->isUserExist($post['username'])){
					
						$id=$db->insertUser($post);
						  //write log file 
				             $userLog= new Application_Model_Log();
				    		 $userLog->writeUserLog($id);
				     	  //End write log file
				
						//Application_Form_FrmMessage::message('One row affected!');
						Application_Form_FrmMessage::redirector('/rsvacl/user/index');																			
				}else {
					Application_Form_FrmMessage::message('User had existed already');
				}
			}
			Application_Model_Decorator::removeAllDecorator($form);
		}
	public function addAction()
	{
			// action body
			$db_user=new Application_Model_DbTable_DbUsers();
			 
			if ($db_user->getMaxUser() > self::MAX_USER) {
				Application_Form_FrmMessage::Sucessfull('អ្នក​ប្រើ​ប្រាស់​របស់​អ្នក​បាន​ត្រឹម​តែ '.self::MAX_USER.' នាក់ ទេ!', self::REDIRECT_URL);
			}
			 
			$this->view->user_typelist =$this->user_typelist;
		
			if($this->getRequest()->isPost()){
				$userdata=$this->getRequest()->getPost();
					
				try {
					$db = $db_user->insertUser($userdata);
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
				} catch (Exception $e) {
					$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
				}
			}
	}
	public function editAction()
	    {
	        // action body
	        $us_id = $this->getRequest()->getParam('id');
	    	$us_id = (empty($us_id))? 0 : $us_id;
	    	
	        $db_user=new Application_Model_DbTable_DbUsers();
	        $this->view->user_edit = $db_user->getUserEdit($us_id);
	
	        $this->view->user_typelist =$this->user_typelist;  
	        
	    	if($this->getRequest()->isPost()){
				$userdata=$this->getRequest()->getPost();	
				
				try {
					$db = $db_user->updateUser($userdata);				
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);		
				} catch (Exception $e) {
					$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
				}
			}
    }
    
 
    public function changePasswordAction()
	{
		$session_user=new Zend_Session_Namespace('auth');
		
		if($session_user->user_id==$this->getRequest()->getParam('id') OR $session_user->level == 1){
			$form = new RsvAcl_Form_FrmChgpwd();	
			//echo $form->getElement('current_password'); exit;	
			$this->view->form=$form;
			//echo "Work"; exit; 
			
			if($this->getRequest()->isPost())
			{
				$db=new RsvAcl_Model_DbTable_DbUser();
				$user_id=$this->getRequest()->getParam('id');		
				if(!$user_id) $user_id=0;			
				$current_password=$this->getRequest()->getParam('current_password');
				$password=$this->getRequest()->getParam('password');
				if($db->isValidCurrentPassword($user_id,$current_password)){ 
					$db->changePassword($user_id, md5($password));	
					      //write log file 
					             $userLog= new Application_Model_Log();
					    		 $userLog->writeUserLog($user_id);
					     	  //End write log file		
					Application_Form_FrmMessage::message('Password has been changed');
					Application_Form_FrmMessage::redirector('/rsvacl/user/view-user/id/'.$user_id);
				}else{
					Application_Form_FrmMessage::message('Invalid current password');
				}
			}		
		}else{ 
			   Application_Form_FrmMessage::message('Access Denied!');
		       Application_Form_FrmMessage::redirector('/rsvacl');	
		}
		
	}

}
