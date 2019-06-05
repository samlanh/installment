<?php

class Loan_Model_DbTable_DbStepOption extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_plongstep_option';
    public function getUserId(){
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	return $dbp->getUserId();
    	 
    }
	function getAllStepOption($search=null){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql=" SELECT ps.id,ps.title";
    	
    	$sql.=$dbp->caseStatusShowImage("ps.status");
    	$sql.=" FROM `ln_plongstep_option` AS ps WHERE 1 ";
    	
    	$Order=" ORDER BY ps.id DESC ";
    	$where = '';
    	 
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = $search['adv_search'];
    		$s_where[] = " ps.title LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND ps.status = ".$search['status'];
    	}
    	$rows = $db->fetchAll($sql.$where.$Order);
    	return $rows;
    }
    function addStepOption($data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
	    	$arrr = array(
	    			'title'=>$data['title'],
	    			'note'=>$data['note'],
	    			'status'=>1,
	    			'create_date'=>date("Y-m-d H:i:s"),
	    			'modify_date'=>date("Y-m-d H:i:s"),
	    			'user_id'=>$this->getUserId()
	    	);
	    	$this->_name = "ln_plongstep_option";
	    	$id = $this->insert($arrr);
	    	
	    	$_db->commit();
	    	return $id;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    function updateStepOption($data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$arrr = array(
    				'title'=>$data['title'],
    				'note'=>$data['note'],
    				'status'=>$data['status'],
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'user_id'=>$this->getUserId()
    		);
    		$this->_name = "ln_plongstep_option";
    		$where="id = ".$data['id'];
    		$this->update($arrr, $where);
    		$id = $data['id'];
    		
    		$_db->commit();
    		return $id;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    
    }
    function getStepoptionBYID($id){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM ln_plongstep_option WHERE id=$id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getAllStepOptions(){
    	$db = $this->getAdapter();
    	$sql="SELECT id,title AS name FROM ln_plongstep_option WHERE status=1 ORDER BY id ASC";
    	return $db->fetchAll($sql);
    }
    function getLastStepoption(){
    	$db = $this->getAdapter();
    	$sql="SELECT id FROM ln_plongstep_option WHERE status=1 ORDER BY id DESC LIMIT 1";
    	return $db->fetchOne($sql);
    }
  
}

