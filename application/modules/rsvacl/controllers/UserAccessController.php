<?php

class RsvAcl_UserAccessController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/rmsacl/useraccess';
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());  
    }
    public function indexAction()
    {
    // action body
    	try {
    		$db_tran=new Application_Model_DbTable_DbGlobal();
    		$db = new RsvAcl_Model_DbTable_DbUserType();
    		$result = $db->getAlluserType();
    		$list = new Application_Form_Frmtable();
    		if(!empty($result)){
    			$glClass = new Application_Model_GlobalClass();
    			$result = $glClass->getImgActive($result, BASE_URL, true);
    		}
    		else{
    			$result = Application_Model_DbTable_DbGlobal::getResultWarning();
    		}
    		$collumns = array("USER_TYPE","PARENT","STATUS");
    		$link=array(
    				'module'=>'rsvacl','controller'=>'useraccess','action'=>'add',
    		);
    		$this->view->list=$list->getCheckList(0,$collumns, $result,array('user_type'=>$link,'title'=>$link));
    		if (empty($result)){
    			$result = array('err'=>1, 'msg'=>'មិនទាន់មានទិន្នន័យនៅឡើយ!');
    		}		
    	} catch (Exception $e) {
    		$result = Application_Model_DbTable_DbGlobal::getResultWarning();
    	}
    }
public function addAction()
    {   
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	/* Initialize action controller here */
    	if($this->getRequest()->getParam('id')){
    	
    		$id = $this->getRequest()->getParam('id');
    		$db = new RsvAcl_Model_DbTable_DbUserType();
    		$userAccessQuery = "SELECT user_type_id, user_type, status from rms_acl_user_type where user_type_id=".$id;
    		$rows = $db->getUserTypeInfo($userAccessQuery);
    		$this->view->rs=$rows;
    	
    		//Add filter search
    		$gc = new Application_Model_GlobalClass();
    		// For list all module
    		$sql = "SELECT DISTINCT acl.`module` FROM `rms_acl_acl` AS acl";
    		$this->view->optoin_mod =  $gc->getOptonsHtmlTranslate($sql, "module", "module");
    		// For list all controller
    		$sql = "SELECT DISTINCT acl.`controller` FROM `rms_acl_acl` AS acl WHERE acl.`status` = 1";
    		$this->view->optoin_con =  $gc->getOptonsHtmlTranslate($sql, "controller", "controller");
    		// For List all action
    		$sql = "SELECT DISTINCT acl.`action` FROM `rms_acl_acl` AS acl WHERE acl.`status` = 1";
    		$this->view->optoin_act =  $gc->getOptonsHtmlTranslate($sql, "action", "action");
    		//For Status enable or disable
    		$this->view->optoin_status =  $gc->getYesNoOption();
    	
    		$where = " ";
    		$status = null;
    	
    		if($this->getRequest()->isPost()){
    			$post = $this->getRequest()->getPost();
    			 
    			if(!empty($post['fmod'])){
    				$where .= " AND acl.`module` = '" . $post['fmod'] . "' ";
    			}
    			if(!empty($post['fcon'])){
    				$where .= " AND acl.`controller` = '" . $post['fcon'] . "' ";
    			}
    			if(!empty($post['fact'])){
    				$where .= " AND acl.`action` = '" . $post['fact'] . "' ";
    			}
    			if(!empty($post['fstatus'])){
    				$status = ($post['fstatus'] === "Yes")? 1 : 0;
    				//$where .= " AND  acl.`status` = " . $st ;
    			}
    			
    			//echo $where; exit;
    		}else{
    			$post =array(
    					'fmod'=>'',
    					'fcon'=>'',
    					'fact'=>'',
    					'fstatus'=>'',
    					);
    		}
    		$this->view->data = $post;
    	
    	
    	
    		//Sophen add here
    		//to assign project list in view
    		$db_acl=new Application_Model_DbTable_DbGlobal();
    			
    		$sqlNotParentId = "SELECT user_type_id FROM `rms_acl_user_type` WHERE `parent_id` =".$id;
    		$notParentId = $db_acl->getGlobalDb($sqlNotParentId);
    		$usernotparentid = $notParentId[0]['user_type_id'];
    	
    			
    		if($id == 1){
    			//Display all for admin id = 1
    			//Do not change admin id = 1 in database
    			//Otherwise, it error
    			$sql = "select acl.acl_id,acl.controller,acl.label,acl.action,CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access
    			from rms_acl_acl as acl
    			WHERE acl.status=1 " . $where;
    		}
    		 
    		else {
    			//Display all of his/her parent access
    			$sql="SELECT acl.acl_id,acl.controller,acl.label,acl.action,CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access, acl.status
    			FROM rms_acl_user_access AS ua
    			INNER JOIN rms_acl_user_type AS ut ON (ua.user_type_id = ut.parent_id)
    			INNER JOIN rms_acl_acl AS acl ON (acl.acl_id = ua.acl_id) WHERE acl.status=1 AND ut.user_type_id =".$id . $where;
    		}
    		//echo $sql; exit;
    		$acl=$db_acl->getGlobalDb($sql);
    		$acl = (is_null($acl))? array(): $acl;
    		//print_r($acl);
    		$this->view->acl=$acl;		
    			
    		if(!$usernotparentid){
    			///Display only of his/her parent access	and not have user_type_id of user access in user type parent id
    			//ua.user_type_id != ut.parent_id
    			$sql_acl = "SELECT acl.acl_id,acl.label, CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access, acl.status
    			FROM rms_acl_user_access AS ua
    			INNER JOIN rms_acl_user_type AS ut ON (ua.user_type_id = ut.user_type_id)
    			INNER JOIN rms_acl_acl AS acl ON (acl.acl_id = ua.acl_id) WHERE acl.status=1 AND ua.user_type_id =".$id . $where;
    		}else{
    			//Display only he / she access in rsv_acl_user_access
    			$sql_acl = "SELECT acl.acl_id,acl.label, CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access, acl.status
    			FROM rms_acl_user_access AS ua
    			INNER JOIN rms_acl_user_type AS ut ON (ua.user_type_id = ut.parent_id)
    			INNER JOIN rms_acl_acl AS acl ON (acl.acl_id = ua.acl_id) WHERE acl.status=1 AND ua.user_type_id =".$id . $where;
    		}
    	
    		$acl_name=$db_acl->getGlobalDb($sql_acl);
    		$acl_name = (is_null($acl_name))? array(): $acl_name;
    			
    		$imgnone='<img src="'.BASE_URL.'/images/icon/none.png"/>';
    		$imgtick='<img src="'.BASE_URL.'/images/icon/tick.png"/>';
    			
    		$rows= array();
    		foreach($acl as $com){
    			$img='<img src="'.BASE_URL.'/images/icon/none.png" id="img_'.$com['acl_id'].'" onclick="changeStatus('.$com['acl_id'].','.$id.');" class="pointer"/>';
    			$tmp_status = 0;
    			foreach($acl_name as $read){
    				if($read['acl_id']==$com['acl_id']){
    					$img='<img src="'.BASE_URL.'/images/icon/tick.png" id="img_'.$com['acl_id'].'" onclick="changeStatus('.$com['acl_id'].', '.$id.');" class="pointer"/>';
    					$tmp_status = 1;
    					break;
    				}
    					
    			}
    			if(!empty($status) || $status === 0){
    				if($tmp_status !== $status) continue;
    			}
    			
    			$lbl_controller = $tr->translate(strtoupper($com['controller']));
    			if($com['action']!='index'){
    			  $lbl_controller=$tr->translate(strtoupper($com['action'])).$lbl_controller;
    			}
    			$rows[] = array($com['acl_id'],$lbl_controller, $com['user_access'], $img) ;
    		}
    	
//     		$list=new Application_Form_Frmlist();
    		$list = new Application_Form_Frmtable();
    		$columns=array("Label",$tr->translate('URL'), $tr->translate('STATUS'));
    		$this->view->list = $list->getCheckList('radio', $columns, $rows);
    			
    		//$this->view->acl_name=$acl_name;
    	
    	}
    }
	public function editAction()
    {	
    	$this->_redirect('rsvacl/useraccess/index');
    }
    public function updateStatusAction(){
    	if($this->getRequest()->isPost()){
    		$post=$this->getRequest()->getPost();
    		$db = new RsvAcl_Model_DbTable_DbUserAccess();
    		$user_type_id =  $post['user_type_id'];
    		$acl_id = $post['acl_id'];
    		$status = $post['status'];
    		$data=array('acl_id'=>$acl_id, 'user_type_id'=>$user_type_id);
    		if($status === "yes"){
    			$where="user_type_id='".$user_type_id."' AND acl_id='". $acl_id . "'";
    			$db->delete($where);    		
    			echo "no";	
    		}
    		elseif($status === "no"){
    			$db->insert($data);    		
    			echo "yes";
    		}
    		//write log file
    		$userLog= new Application_Model_Log();
    		$userLog->writeUserLog($acl_id);
    	}
    	exit();
    }
}

