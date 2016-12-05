<?php

class Group_Model_DbTable_DbProject extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_project';
    function addbranch($_data){
    	$_arr = array(
    			'project_name'=>$_data['branch_namekh'],
    			//'project_type'=>$_data['project_type'],
    			'prefix'=>$_data['prefix_code'],
    			'br_address'=>$_data['br_address'],
    			'branch_code'=>$_data['branch_code'],
    			'branch_tel'=>$_data['branch_tel'],
    			'fax'=>$_data['fax'],
    			'other'=>$_data['branch_note'],
    			'status'=>$_data['branch_status'],
    			'displayby'=>$_data['branch_display'],
    			'p_manager_namekh'=>$_data['project_manager_namekh'],
    			'p_manager_nationality'=>$_data['project_manager_nationality'],
    			'p_manager_nation_id'=>$_data['project_manager_nation_id'],
    			'p_current_address'=>$_data['current_address'],
    			);
    	$this->insert($_arr);//insert data
//     	$where = 'id = 1';
//     	$this->delete($where);
    }
    public function updateBranch($_data,$id){
    	$_arr = array(
    			'project_name'=>$_data['branch_namekh'],
    			//'project_type'=>$_data['project_type'],
    			'prefix'      =>      $_data['prefix_code'],
    			'br_address'=>$_data['br_address'],
    			'branch_code'=>$_data['branch_code'],
    			'branch_tel'=>$_data['branch_tel'],
    			'fax'=>$_data['fax'],
    			'other'=>$_data['branch_note'],
    			'status'=>$_data['branch_status'],
    			'displayby'=>$_data['branch_display'],
    			'p_manager_namekh'=>$_data['project_manager_namekh'],
    			'p_manager_nationality'=>$_data['project_manager_nationality'],
    			'p_manager_nation_id'=>$_data['project_manager_nation_id'],
    			'p_current_address'=>$_data['current_address'],
    			);
    	$where=$this->getAdapter()->quoteInto("br_id=?", $id);
    	$this->update($_arr, $where);
    }
    function addbranchajax($_data){
    	$_arr = array(
    			'project_name'=>$_data['branch_namekh'],
    			'prefix'=>$_data['prefix_code'],
    			'br_address'=>$_data['br_address'],
    			'branch_tel'=>$_data['branch_tel'],
    			'status'=>1,
    			'displayby'=>1,
    			'p_manager_namekh'=>$_data['project_manager_namekh'],
    			'p_manager_nationality'=>$_data['project_manager_nationality'],
    			'p_manager_nation_id'=>$_data['project_manager_nation_id'],
    			'p_current_address'=>$_data['current_address'],
    	);
    	return $this->insert($_arr);//insert data
    }
    	
    function getAllBranch($search=null){
    	$db = $this->getAdapter();
    	$sql = "SELECT b.br_id,b.project_name,
		b.prefix,b.branch_code,b.br_address,b.branch_tel,b.fax,
		(SELECT v.name_en FROM `ln_view` AS v WHERE v.`type` = 4 AND v.key_code = b.displayby)AS displayby,b.other,b.`status` FROM $this->_name AS b  ";
    	$where = ' WHERE b.project_name !="" ';
    	
    	if($search['status_search']>-1){
    		$where.= " AND b.status = ".$search['status_search'];
    	}
    	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]=" b.prefix LIKE '%{$s_search}%'";
    		$s_where[]=" b.project_name LIKE '%{$s_search}%'";
    	
    		$s_where[]=" b.br_address LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_code LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_tel LIKE '%{$s_search}%'";
    		$s_where[]=" b.fax LIKE '%{$s_search}%'";
    		$s_where[]=" b.other LIKE '%{$s_search}%'";
    		$s_where[]=" b.displayby LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ',$s_where).')';
    	}
    	$order=' ORDER BY b.br_id DESC';
        return $db->fetchAll($sql.$where.$order);
    }
    
 function getBranchById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM
    	$this->_name ";
    	$where = " WHERE `br_id`= $id" ;
   		return $db->fetchRow($sql.$where);
    }
    public static function getBranchCode(){
    	$db = new Application_Model_DbTable_DbGlobal();
    	$sql = "SELECT COUNT(br_id) AS amount FROM `ln_project`";
    	$acc_no= $db->getGlobalDbRow($sql);
    	$acc_no=$acc_no['amount'];
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre = "";
    	for($i = $acc_no;$i<3;$i++){
    		$pre.='0';
    	}
    	return "C-".$pre.$new_acc_no;
    }
}  
	  

