<?php

class Group_Model_DbTable_DbProperyType extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_properties_type';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    function addPropery($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    	$arr = array(
	    			'type_nameen'=>$data['type_nameen'],
	    			//'type_namekh'=>$data['type_namekh'],
					'status'=>$data['status'],
	    			'user_id'=>$this->getUserId(),
	    			'date'=>date("Y-m-d"),
	    			'note'=>$data['note'],
	    		);
	    	$this->_name='ln_properties_type';
	    	if(!empty($data['id'])){
	    		$where = 'id = '.$data['id'];
	    		return  $this->update($arr, $where);
	    	}else{
	    		return  $this->insert($arr);
	    	}
	    	$db->commit();
    	}catch(exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
	}
	
	function geteAllPropertyType($search=null){
		$db = $this->getAdapter();

		$sql='SELECT t.`id`,t.`type_nameen`,t.`note`,
(SELECT CONCAT(u.first_name," ",u.last_name) FROM `rms_users` AS u WHERE u.id = t.`user_id`) AS user_name,
t.`status` FROM `ln_properties_type` AS t where 1 ';
		$where="";
		if($search['status_search']>-1){
			$where.=" AND t.status=".$search['status_search'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			
			$s_where[]="t.`type_nameen` LIKE'%{$s_search}%'";
			//$s_where[]="t.`type_namekh` LIKE'%{$s_search}%'";
			$s_where[]="t.`note` LIKE'%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order = " ORDER BY t.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getPropertyTypeById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `ln_properties_type` AS t WHERE t.`id`=".$id;
		return $db->fetchRow($sql);
	}
	function ajaxPropertytype($data){ // used on ProperiestypeController
		$this->_name='ln_properties_type';
		$db = $this->getAdapter();
		$arr = array(
				'type_nameen'=>$data['type_nameen'],
				'status'=>1
		);
		return $this->insert($arr);
	}
}

