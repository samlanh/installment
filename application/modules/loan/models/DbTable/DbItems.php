<?php

class Loan_Model_DbTable_DbItems extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_items_material';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function addItems($data){
    	$db = $this->getAdapter();
    	try{
	    	$arr = array(
	    			'title'=>$data['title'],
	    			'title_en'=>$data['title'],
	    			'user_id'=>$this->getUserId(),
	    			'modify_date'=>date("Y-m-d H:i:s"),
	    			'note'=>$data['note'],
	    		);
	    	$this->_name='ln_items_material';
	    	if(!empty($data['id'])){
	    		$where = 'id = '.$data['id'];
	    		$arr['status']=$data['status'];
	    		return  $this->update($arr, $where);
	    	}else{
	    		$arr['status']=1;
	    		$arr['create_date']= date("Y-m-d H:i:s");
				
	    		return  $this->insert($arr);
	    	}
    	}catch(exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	
	function geteAllItemsMaterial($search=null){
		$db = $this->getAdapter();
		$sql='SELECT t.`id`,t.`title`,t.`note`,
			(SELECT u.first_name FROM `rms_users` AS u WHERE u.id = t.`user_id`) AS user_name ';
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("t.`status`");
		$sql.=" FROM `ln_items_material` AS t WHERE 1 ";
		$where="";
		if($search['status_search']>-1){
			$where.=" AND t.status=".$search['status_search'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]="t.`type_nameen` LIKE'%{$s_search}%'";
			$s_where[]="t.`note` LIKE'%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order = " ORDER BY t.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getItemsById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `ln_items_material` AS t WHERE t.`id`=".$id;
		return $db->fetchRow($sql);
	}
	
	function getAllItemsMaterial(){
		$db=$this->getAdapter();  	 
		$sql="SELECT id,title AS name FROM `ln_items_material` WHERE status=1 ORDER BY name ASC ";
		return $db->fetchAll($sql);
	 }

}