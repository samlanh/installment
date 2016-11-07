<?php

class Other_Model_DbTable_DbMyPosition extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_position';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    
    function addPosition($_data){
    	$arr = array(
    			'position_en'=>$_data['position_en'],
    			'position_kh'=>$_data['position_kh'],
    			'status'=>$_data['status'],
    			'date'=>date('Y-m-d'),
    			'displayby'=>$_data['display'],
    			'user_id'=>$this->getUserId(),
    			);
    	$this->insert($arr);//insert data
    }
    function upDatePosition($_data){
    	$arr = array(
    			'position_en'=>$_data['position_en'],
    			'position_kh'=>$_data['position_kh'],
    			'status'=>$_data['status'],
    			'date'=>date('Y-m-d'),
    			'displayby'=>$_data['display'],
    			'user_id'=>$this->getUserId(),
    	);
    	$where = " id = ".$_data['id'];
    	$this->update($arr, $where);//insert data
    }
    public function getPositionById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM $this->_name WHERE id = ".$db->quote($id);
    	$sql.=" LIMIT 1 ";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
    function getallPosition(){
    	  $db = $this->getAdapter();
          $sql="SELECT id,position_en,position_kh,date,status,user_id,displayby  
                FROM ln_position ORDER BY id";
          return $db->fetchAll($sql);
    	
    }
    function getPById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT id,position_en,position_kh,date,status,user_id,displayby
    	FROM ln_position where id = $id";
    	return $db->fetchRow($sql);   	 
    }
//     function getAllBranch($search=null){
//     	$db = $this->getAdapter();
//     	$sql = "SELECT br_id,branch_namekh,branch_nameen,branch_address,branch_code,branch_tel,fax,other,status,display FROM 
//     	$this->_name ";
//     	$where = ' WHERE branch_namekh!="" AND branch_nameen !="" ';
    
//     	if($search['status']>-1){
//     		$where.= " AND status = ".$search['status'];
//     	}
//     	if(!empty($search['adv_search'])){
//     		$s_where = array();
//     		$search = ($search['adv_search']);
//     		$s_where[] = " co_code LIKE '%{$search}%'";
//     		$s_where[] = "co_khname LIKE '%{$search}%'";
//     		$s_where[] = " co_firstname LIKE '%{$search}%'";
//     		$s_where[] = "co_lastname LIKE '%{$search}%'";
//     		$s_where[] = " tel LIKE '%{$search}%'";
//     		$s_where[] = "email LIKE '%{$search}%'";
//     		$s_where[] = "address LIKE '%{$search}%'";
//     		$where .=' AND ('.implode(' OR ',$s_where).')';
//     	}
    
    	// 		echo $sql.$where;
//     	return $db->fetchAll($sql.$where);
//     }

   
    
	
   
}

