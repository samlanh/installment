<?php

class Group_Model_DbTable_DbClientBlackList extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_client';
    
    function updatBlackList($data){
    	$arr = array(
//     			'branch_id'=>$data['branch'],
    			'is_blacklist'=>1,
    			'date_blacklist'=>$data['date'],
    			'reasonblack_list'=>$data['problem'],
    			'status_blacklist'=>$data['status'],
    			);
    	$where=" client_id = ".$data['client_name'];
    	echo $where;exit();
    	$this->update($arr, $where);
    }
    function updatClientBlackList($data){
    	$arr = array(
    			'is_blacklist'=>1,
    			'date_blacklist'=>$data['date'],
    			'reasonblack_list'=>$data['problem'],
    			'status_blacklist'=>$data['status'],
    	);
    	$where=" client_id= ".$data['id'];
    	$this->update($arr, $where);
    }
    function getAllBlackList($search=null){
    	$db=$this->getAdapter();
    	
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$sql = " SELECT * FROM v_getclientblacklist WHERE 1";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim($search['adv_search']);
    		$s_where[] = "branch_name LIKE '%{$s_search}%'";
    		$s_where[] = "client_number LIKE '%{$s_search}%'";
    		$s_where[] = "client_name LIKE '%{$s_search}%'";
    		$s_where[] = "sex LIKE '%{$s_search}%'";
    		$s_where[] = " situation LIKE '%{$s_search}%'";
    		$s_where[] = " doc_name LIKE '%{$s_search}%'";
    		$s_where[] = " id_number LIKE '%{$s_search}%'";
    		$s_where[] = " street LIKE '%{$s_search}%'";
    		$s_where[] = " house LIKE '%{$s_search}%'";
    		$s_where[] = " village_name LIKE '%{$s_search}%'";
    		$s_where[] = " commune_name LIKE '%{$s_search}%'";
    		$s_where[] = " district_name LIKE '%{$s_search}%'";
    		$s_where[] = " province_name LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$where.= " AND branch_id = ".$search['branch_id'];
    	}
    	$order = " ORDER BY id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getAllBlackListInList($search=null){
    	$db=$this->getAdapter();
    	 
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    	$sql = " SELECT id,branch_name, client_number ,client_name ,sex,situation,doc_name,id_number,reason,`date`,status FROM v_getclientblacklist WHERE 1";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim($search['adv_search']);
    		$s_where[] = "branch_name LIKE '%{$s_search}%'";
    		$s_where[] = "client_number LIKE '%{$s_search}%'";
    		$s_where[] = "client_name LIKE '%{$s_search}%'";
    		$s_where[] = "sex LIKE '%{$s_search}%'";
    		$s_where[] = " situation LIKE '%{$s_search}%'";
    		$s_where[] = " doc_name LIKE '%{$s_search}%'";
    		$s_where[] = " id_number LIKE '%{$s_search}%'";
    		$s_where[] = " note LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if(($search['status_search'])>-1){
    		$where.= " AND status = ".$search['status_search'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.= " AND branch_id = ".$search['branch_id'];
    	}
    	
    	$order = " ORDER BY id DESC ";
//     	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getBlackListById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM $this->_name WHERE client_id = ".$db->quote($id);
    	$sql.=" LIMIT 1 ";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
   
}

