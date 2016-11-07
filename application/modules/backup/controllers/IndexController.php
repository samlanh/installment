<?php

class Backup_IndexController extends Zend_Controller_Action
{
    public function init()
    {    	
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
	}

    public function indexAction()
    {
        // action body
    }
    public function addAction(){
    
    }
    public function backupAction()
    {
    	Application_Form_FrmMessage::Sucessfull("ការរក្សាទុកទិន្នន័យដោយជោគជ័យ !",'/backup/index');
    }
    public function restoreAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Backup_Model_DbTable_DbRestore();
    		$db->UploadFileDatabase($db);
    	}
    } 



}







