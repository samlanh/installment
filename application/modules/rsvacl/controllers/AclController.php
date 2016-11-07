<?php
class RsvAcl_AclController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/rsvacl/acl';
	
	public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
        try {
          $db = new RsvAcl_Model_DbTable_DbAcl();
          $rs_rows = $db->getAllAclList();
          if(!empty($rs_rows)){
          	$glClass = new Application_Model_GlobalClass();
          	$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
          }
          else{
          	$rs_rows = Application_Model_DbTable_DbGlobal::getResultWarning();
          }
          $list = new Application_Form_Frmtable();
          $collumns = array("VIEW","MODULE_NAME","CONTROLLER","ACTION","STATUS");
          $link=array(
          		'module'=>'rsvacl','controller'=>'acl','action'=>'edit',
          );
        	if (empty($rs_rows)){
        		$result = Application_Model_DbTable_DbUserLog::writeMessageError('');
        	}
        	$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('module'=>$link,'controller'=>$link,'action'=>$link));
        } catch (Exception $e) {
        	$result = Application_Model_DbTable_DbUserLog::writeMessageError('');
        }
    }
    
    public function viewAclAction()
    {   
    	/* Initialize action controller here */
    	if($this->getRequest()->getParam('id')){
    		$db = new RsvAcl_Model_DbTable_DbAcl();
    		$acl_id = $this->getRequest()->getParam('id');
    		//echo $acl_id; exit;
    		$rs=$db->getAcl($acl_id);
    		//print_r($rs); exit;
    		$this->view->acl_data=$rs;
    	}  	 
    	
    }
	public function addAction()
		{
			$form = new RsvAcl_Form_FrmAcl();	
			$this->view->form=$form;
			
			if($this->getRequest()->isPost())
			{
				$db=new RsvAcl_Model_DbTable_DbAcl();	
				$post=$this->getRequest()->getPost();
							
// 				if(!$db->isActionExist($post['action'])){
					  
						$id=$db->insertAcl($post);
						 //write log file 
				             $userLog= new Application_Model_Log();
				    		 $userLog->writeUserLog($id);
				     	  //End write log file
				
						Application_Form_FrmMessage::message('INSERT_SUCCESS');
// 						Application_Form_FrmMessage::redirector('/rsvAcl/acl/index');																			
// 				}else {
// 					Application_Form_FrmMessage::message('Action had existed already');
// 				}
			}
		}
    public function editAction()
    {	
    	$acl_id=$this->getRequest()->getParam('id');
    	if(!$acl_id)$acl_id=0;  
    	   	  
   		$form = new RsvAcl_Form_FrmAcl();
    	     	
    	$db = new RsvAcl_Model_DbTable_DbAcl();
    	
        $rs = $db->getUserInfo('SELECT * FROM rms_acl_acl where acl_id='.$acl_id);
        $this->view->acl_data = $rs[0];
    	$this->view->acl_id = $acl_id;
    	
    	if($this->getRequest()->isPost())
		{
			$post=$this->getRequest()->getPost();
			if($rs[0]['action']==$post['action']){																			
					$db->updateAcl($post,$rs[0]['acl_id']);
					  //write log file 
				             $userLog= new Application_Model_Log();
				    		 $userLog->writeUserLog($acl_id);
				     	  //End write log file
					//Application_Form_FrmMessage::message('One row affected!');
					Application_Form_FrmMessage::redirector('/rsvacl/acl/index');																																				
				
			}else{
				if(!$db->isActionExist($post['action'])){
					$db->updateAcl($post,$rs[0]['acl_id']);
					 //write log file 
				             $userLog= new Application_Model_Log();
				    		 $userLog->writeUserLog($acl_id);
				     	  //End write log file
					//Application_Form_FrmMessage::message('One row affected!');
					Application_Form_FrmMessage::redirector('/rsvacl/acl/index');						
				}else {
					Application_Form_FrmMessage::message('Action had existed already');
				}
			}
		}
    }


}

