<?php

class Group_Model_DbTable_DbKnowBy extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_know_by';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function addKnowBy($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
			
			$arr = array(
	    			'title'=>$data['title'],
	    			'user_id'=>$this->getUserId(),
	    			'create_date'=>date("Y-m-d H:i:s"),
	    		);
	    	$this->_name='rms_know_by';
			
	    	if(!empty($data['id'])){
				$status = empty($data['status'])?0:1;
	    		$where = 'id = '.$data['id'];
	    		$arr['status']=$status;
	    		$this->update($arr, $where);
				$id = $data['id'];
	    	}else{
	    		$arr['status']=1;
	    		$id = $this->insert($arr);
	    	}
			 
	    	$db->commit();
			return $id;
    	}catch(exception $e){
    		
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
    	}
	}
	
	function geteAllKnowByList($search=null){
		$db = $this->getAdapter();
		$sql='SELECT 
			t.`id`
			,t.`title`
			,(SELECT CONCAT(u.first_name," ",u.last_name) FROM `rms_users` AS u WHERE u.id = t.`user_id`) AS user_name 
		';
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("t.`status`");
		$sql.=" FROM `rms_know_by` AS t WHERE 1 ";
		$where="";
		if($search['status']>-1){
			$where.=" AND t.status=".$search['status'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]="t.`title` LIKE'%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order = " ORDER BY t.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getKnowById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `rms_know_by` AS t WHERE t.`id`=".$id;
		return $db->fetchRow($sql);
	}
}