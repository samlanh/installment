<?php

class Other_Model_DbTable_DbLoanType extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_view';
    function addViewType($data){
    	try{
    	$db = $this->getAdapter();
    	$key_code = $this->getLastKeycodeByType($data['type']);
    	$arr = array(
    			'name_en'=>$data['title_en'],
    			'name_kh'=>$data['title_kh'],
    			'status'=>$data['status'],
    			'displayby'=>$data['display_by'],
    			'key_code'=>$key_code,
    			'type'=>$data['type'],
    			
    			);
         return $this->insert($arr);
    	}catch (Exception $e){
    		echo '<script>alert('."$e".');</script>';
    	}
    }
    function getLastKeycodeByType($type){
    	$sql = "SELECT key_code FROM `ln_view` WHERE type=$type ORDER BY key_code DESC LIMIT 1 ";
    	$db =$this->getAdapter();
    	$number = $db->fetchOne($sql);
    	return $number+1;
    }
    function updatViewById($data){
    	$arr = array(
    			'name_en'=>$data['title_en'],
    			'name_kh'=>$data['title_kh'],
    			'status'=>$data['status'],
    			'displayby'=>$data['display_by'],
    			'type'=>$data['type'],
    			);
    	
    	$where=" id = ".$data['id'];
    	$this->update($arr, $where);
    }
    function getListViewById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT id,name_en AS title_en,name_kh AS title_kh,displayby ,'date',status,type FROM $this->_name where id=$id ";
    	return $db->fetchRow($sql);
    }
    function getAllviewBYType($search=null,$type=null){
    	$db = $this->getAdapter();
    	$sql=" SELECT v.id,v.name_en,v.name_kh,
    	(SELECT t.name FROM `ln_view_type` AS t WHERE t.id =v.type LIMIT 1) ,
    	v.status FROM $this->_name AS v WHERE 1";
    	if($type!=null){
    		$sql.=" AND type = $type";
    		
    	}
    	$Other=" ORDER BY v.type DESC, v.id desc ";
    	$where = '';
    	 
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = $search['adv_search'];
    		$s_where[] = " v.name_kh LIKE '%{$s_search}%'";
    		$s_where[]=" v.name_en LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status_search']>-1){
    		$where.= " AND v.status = ".$search['status_search'];
    	}
    	return $db->fetchAll($sql.$where.$Other);
    	
    }
}

