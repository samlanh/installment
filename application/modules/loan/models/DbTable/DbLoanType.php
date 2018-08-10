<?php

class Loan_Model_DbTable_DbLoanType extends Zend_Db_Table_Abstract
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
    			'parent_id'=>$data['parent_id'],
    			'displayby'=>1,
    			'key_code'=>$key_code,
    			'type'=>$data['type'],
    			
    			);
         return $this->insert($arr);
    	}catch (Exception $e){
    		echo '<script>alert('."$e".');</script>';
    	}
    }
    function getLastKeycodeByType($type){
    	$sql = "SELECT count(key_code) FROM `ln_view` WHERE type=$type ORDER BY key_code DESC LIMIT 1 ";
    	$db =$this->getAdapter();
    	$number = $db->fetchOne($sql);
    	return $number+1;
    }
    function updatViewById($data){
    	$arr = array(
    			'name_en'=>$data['title_en'],
    			'name_kh'=>$data['title_kh'],
    			'parent_id'=>$data['parent_id'],
    			'status'=>$data['status'],
    			'displayby'=>1,
    			'type'=>$data['type'],
    			);
    	
    	$where=" id = ".$data['id'];
    	$this->update($arr, $where);
    }
    function getListViewById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT id,name_en AS title_en,name_kh AS title_kh,displayby ,'date',status,type,parent_id FROM $this->_name where id=$id ";
    	return $db->fetchRow($sql);
    }
    function getAllviewBYType($search=null,$type=null,$parent = 0, $spacing = '', $cate_tree_array = ''){
    	$db = $this->getAdapter();
    	$sql=" SELECT v.id,
    	v.name_kh as name,
    	v.key_code,
    	(SELECT ve.name_kh FROM ln_view AS ve WHERE ve.key_code = v.parent_id AND ve.type = v.type LIMIT 1) AS parent,
    	v.name_en,
    	
    	(SELECT t.name FROM `ln_view_type` AS t WHERE t.id =v.type LIMIT 1) as type ,
    	v.status
    	 FROM $this->_name AS v WHERE (v.type=12 OR v.type=13) AND v.`parent_id` = $parent";
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
    	if($search['type']>-1){
    		$where.= " AND v.type = ".$search['type'];
    	}
    	if($search['status']>-1){
    		$where.= " AND v.status = ".$search['status'];
    	}
    	$rows = $db->fetchAll($sql.$where.$Other);
    	if (!is_array($cate_tree_array))
    		$cate_tree_array = array();
    	if (count($rows) > 0) {
    		foreach ($rows as $row){
    			$cate_tree_array[] = array("id" => $row['id'],"parent" => $row['parent'], "name" => $spacing . $row['name'],"name_en" => $spacing . $row['name_en'],"key_code" => $row['key_code'],"type" => $row['type'],"status" => $row['status']);
    			$cate_tree_array = $this->getAllviewBYType($search,$type,$row['key_code'], $spacing . ' - ', $cate_tree_array);
    		}
    	}
    	return $cate_tree_array;
    }
}

