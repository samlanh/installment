<?php

class Group_Model_DbTable_DbCallecteralltype extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_callecteral_type';
    function addcallecterall($data){
    	$db = $this->getAdapter();
//     	$sql=" SELECT key_code FROM ln_view WHERE type=13 AND status = 1 
//     	ORDER BY key_code DESC LIMIT 1 ";
//     	$numer_record = $db->fetchOne($sql);
    	
    	
    	$arr = array(
    			'title_en'=>$data['title_en'],
    			'title_kh'=>$data['title_kh'],
    			'date'=>$data['date'],
    			'status'=>$data['status'],
    			'displayby'=>$data['display_by'],
//     			'key_code'=>$numer_record+1,
//     			'type'=>13,
    			
    			);
         $id=$this->insert($arr);
     
    }
    function updatcallecterall($data){
    	$arr = array(
    			'title_en'=>$data['title_en'],
    			'title_kh'=>$data['title_kh'],
    			'date'=>$data['date'],
    			'status'=>$data['status'],
    			'displayby'=>$data['display_by'],
    			);
    	$where=" id = ".$data['id'];
    	$this->update($arr, $where);
    }
    function getcallecterallbyid($id){
    	$db = $this->getAdapter();
    	$sql="SELECT id,title_en,title_kh,display_by,date,status FROM $this->_name where id=$id ";
    	return $db->fetchRow($sql);
    }
    function geteAllid($search=null){
    	$db = $this->getAdapter();
    	$sql=" SELECT id,title_en,title_kh,date,status FROM $this->_name where id ORDER BY id DESC";
    	return $db->fetchAll($sql);
    	
    }
}

