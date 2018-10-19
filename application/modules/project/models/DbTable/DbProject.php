<?php

class Project_Model_DbTable_DbProject extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_project';
    function addbranch($_data){
    	
    	$part= PUBLIC_PATH.'/images/projects/';
    	if (!file_exists($part)) {
    		mkdir($part, 0777, true);
    	}
    	$name = $_FILES['logo']['name'];
    	$photo='';
    	if (!empty($name)){
    		$tem =explode(".", $name);
    		$new_image_name = "logo".date("Y").date("m").date("d").time().".".end($tem);
    		$tmp = $_FILES['logo']['tmp_name'];
    		if(move_uploaded_file($tmp, $part.$new_image_name)){
    			$photo = $new_image_name;
    		}
    		else
    			$string = "Image Upload failed";
    	}else{
    		$photo = $_data['old_photo'];
    	}
    	
    	$_arr = array(
    			'project_name'=>$_data['branch_namekh'],
    			//'project_type'=>$_data['project_type'],
    			'prefix'=>$_data['prefix_code'],
    			'br_address'=>$_data['br_address'],
    			'branch_code'=>$_data['branch_code'],
    			'branch_tel'=>$_data['branch_tel'],
    			'fax'=>$_data['fax'],
    			'other'=>$_data['branch_note'],
    			'map_url'=>$_data['map_url'],
    			'status'=>$_data['branch_status'],
    			//'displayby'=>$_data['branch_display'],
    			'p_manager_namekh'=>$_data['project_manager_namekh'],
    			'p_manager_nationality'=>$_data['project_manager_nationality'],
    			'p_manager_nation_id'=>$_data['project_manager_nation_id'],
    			'p_current_address'=>$_data['current_address'],
    			'w_manager_namekh'=>$_data['sc_project_manager_nameen'],
    			'w_manager_nationality'=>$_data['sc_project_manager_nationality'],
    			'w_manager_nation_id'=>$_data['sc_project_manager_nation_id'],
    			
    			'p_sex'=>$_data['p_manager_sex'],
    			'p_dob'=>$_data['dob_manager'],
    			'p_nationid_issue'=>$_data['date_iss_doc'],
    			 
    			'w_sex'=>$_data['csp_manager_sex'],
    			'w_dob'=>$_data['dob_cs_manager'],
    			'w_nation_id_issue'=>$_data['date_iss_doc_cs_manager'],
    			'w_current_address'=>$_data['cs_manager_current_address'],
    			'logo'=>$photo,
    			);
    	$branch_id = $this->insert($_arr);//insert data
    	$ids = explode(',', $_data['identity']);
    	$key = 1;
    	if(!empty($_data['identity'])){
	    	foreach ($ids as $i){
	    		$this->_name="ln_sharecapital";
	    		$datashare = array(
	    				'branch_id'=>$branch_id,
	    				'shareholder_name'=>$_data['shareholdername'.$i],
	    				'gender'=> $_data['gender'.$i],
	    				'dob'=> $_data['dob'.$i],
	    				'pob'=>$_data['pob'.$i],
	    				'nation_id'=>$_data['id_card'.$i],
	    				'tel'=>$_data['phonenumber'.$i],
	    				'percentage'=>$_data['percent'.$i],
	    		);
	    		$this->insert($datashare);
	    	}
    	}

    }
    public function updateBranch($_data,$id){
    	
    	$part= PUBLIC_PATH.'/images/projects/';
    	if (!file_exists($part)) {
    		mkdir($part, 0777, true);
    	}
    	$name = $_FILES['logo']['name'];
    	$photo='';
    	if (!empty($name)){
    		$tem =explode(".", $name);
    		$new_image_name = "logo".date("Y").date("m").date("d").time().".".end($tem);
    		$tmp = $_FILES['logo']['tmp_name'];
    		if(move_uploaded_file($tmp, $part.$new_image_name)){
    			$photo = $new_image_name;
    		}
    		else
    			$string = "Image Upload failed";
    	}else{
    		$photo = $_data['old_photo'];
    	}
    	
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
    			//'displayby'=>$_data['branch_display'],
    			'p_manager_namekh'=>$_data['project_manager_namekh'],
    			'p_manager_nationality'=>$_data['project_manager_nationality'],
    			'p_manager_nation_id'=>$_data['project_manager_nation_id'],
    			'p_current_address'=>$_data['current_address'],
    			'w_manager_namekh'=>$_data['sc_project_manager_nameen'],
    			'w_manager_nationality'=>$_data['sc_project_manager_nationality'],
    			'w_manager_nation_id'=>$_data['sc_project_manager_nation_id'],
    			
    			'p_sex'=>$_data['p_manager_sex'],
    			'p_dob'=>$_data['dob_manager'],
    			'p_nationid_issue'=>$_data['date_iss_doc'],
    			 
    			'w_sex'=>$_data['csp_manager_sex'],
    			'w_dob'=>$_data['dob_cs_manager'],
    			'w_nation_id_issue'=>$_data['date_iss_doc_cs_manager'],
    			'w_current_address'=>$_data['cs_manager_current_address'],
    			'logo'=>$photo,
    			);
    	$where=$this->getAdapter()->quoteInto("br_id=?", $id);
    	$this->update($_arr, $where);
    	
    	
    	$where="branch_id=".$id;
    	$this->_name="ln_sharecapital";
    	$this->delete($where);
    	
    	$ids = explode(',', $_data['identity']);
    	$key = 1;
    	if(!empty($_data['identity'])){
    		foreach ($ids as $i){
    			$datashare = array(
    					'branch_id'=>$id,
    					'shareholder_name'=>$_data['shareholdername'.$i],
    					'gender'=> $_data['gender'.$i],
    					'dob'=> $_data['dob'.$i],
    					'pob'=>$_data['pob'.$i],
    					'nation_id'=>$_data['id_card'.$i],
    					'tel'=>$_data['phonenumber'.$i],
    					'percentage'=>$_data['percent'.$i],
    			);
    			$this->insert($datashare);
    		}
    	}
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
		b.other,p_manager_namekh,w_manager_namekh,b.`status`,
(SELECT COUNT(p.id) FROM `ln_properties` AS p  WHERE p.branch_id = b.`br_id` AND p.status = 1) AS totalproperty,
(SELECT COUNT(p.id) FROM `ln_properties` AS p  WHERE p.branch_id = b.`br_id` AND p.status = 1 AND p.is_lock =1) AS totalpropertysold	
    	FROM $this->_name AS b  ";
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
    function getBranchHolderById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM ln_sharecapital ";
    	$where = " WHERE `branch_id`= $id" ;
    	return $db->fetchAll($sql.$where);
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
    	return $pre.$new_acc_no;
    }
}  
	  

