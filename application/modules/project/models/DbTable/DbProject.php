<?php

class Project_Model_DbTable_DbProject extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_project';
    function countProject(){
    	$db =$this->getAdapter();
    	$sql="SELECT COUNT(br_id) AS pro FROM ln_project";
    	return $db->fetchOne($sql);
    }
    function addbranch($_data){
    	
    	$part= PUBLIC_PATH.'/images/projects/';
    	if (!file_exists($part)) {
    		mkdir($part, 0777, true);
    	}
    	
    	$record = $this->recordhistory($_data);
    	$activityold = $record['activityold'];
    	$after_edit_info = $record['after_edit_info'];
    	
    	$name = $_FILES['logo']['name'];
    	$photo='logo.png';
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
    			
    			'p_manager_sex'=>$_data['p_manager_sex'],
    			'p_dob'=>$_data['dob_manager'],
    			'p_nationid_issue'=>$_data['date_iss_doc'],
    			 
    			'w_sex'=>$_data['csp_manager_sex'],
    			'w_dob'=>$_data['dob_cs_manager'],
    			'w_nation_id_issue'=>$_data['date_iss_doc_cs_manager'],
    			'w_current_address'=>$_data['cs_manager_current_address'],
    			'logo'=>$photo,
    			
    			'position'=>$_data['position'],
    			'w_position'=>$_data['w_position'],
    			'contact_contruction'=>$_data['contact_contruction'],
    			
    			);
		    	if (!empty($_data['budget_amount'])){
		    		$_arr['budget_amount']=$_data['budget_amount'];
		    	}
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
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$_datas = array('description'=>'Create New Project','activityold'=>$activityold,'after_edit_info'=>$after_edit_info);
    	$dbgb->addActivityUser($_datas);
    }
    public function updateBranch($_data,$id){
    	
    	$part= PUBLIC_PATH.'/images/projects/';
    	if (!file_exists($part)) {
    		mkdir($part, 0777, true);
    	}
    	$_data['id']=$id;
    	$record = $this->recordhistory($_data);
    	$activityold = $record['activityold'];
    	$after_edit_info = $record['after_edit_info'];
    	
    	$name = $_FILES['logo']['name'];
    	$photo='logo.png';
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
    			//'displayby'=>$_data['branch_display'],
    			'prefix'      =>      $_data['prefix_code'],
    			'br_address'=>$_data['br_address'],
    			'branch_code'=>$_data['branch_code'],
    			'branch_tel'=>$_data['branch_tel'],
    			'fax'=>$_data['fax'],
    			'other'=>$_data['branch_note'],
    			'status'=>$_data['branch_status'],
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
    			'position'=>$_data['position'],
    			'w_position'=>$_data['w_position'],
    			
    			'contact_contruction'=>$_data['contact_contruction'],
    			);
		    	if (!empty($_data['budget_amount'])){
		    		$_arr['budget_amount']=$_data['budget_amount'];
		    	}
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
    	
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$_datas = array('description'=>'Edit New Project','activityold'=>$activityold,'after_edit_info'=>$after_edit_info);
    	$dbgb->addActivityUser($_datas);
    }
    function recordhistory($_data){
    	$arr=array();
    	$stringold="";
    	$string="";
    	if (!empty($_data['id'])){
    
    		$row=$this->getBranchById($_data['id']);
    		$stringold="Project : ".$row['project_name']."<br />";
    		$stringold.="Address : ".$row['br_address']."<br />";
    		$stringold.="Branch Tel : ".$row['branch_tel']."<br />";
    		$stringold.="Project Manager : ".$row['p_manager_namekh']."<br />";
    		$stringold.="Nationality : ".$row['p_manager_nationality']."<br />";
    		$stringold.="Current Address : ".$row['p_current_address']."<br />";
    
    		$string="Project : ".$_data['branch_namekh']."<br />";
    		$string.="Address : ".$_data['br_address']."<br />";
    		$string.="Branch Tel : ".$_data['branch_tel']."<br />";
    		$string.="Project Manager : ".$_data['project_manager_namekh']."<br />";
    		$string.="Nationality : ".$_data['project_manager_nationality']."<br />";
    		$string.="Current Address : ".$_data['current_address']."<br />";
    
    
    	}else{
    		$string="";
    		$stringold="Project : ".$_data['branch_namekh']."<br />";
    		$stringold.="Address : ".$_data['br_address']."<br />";
    		$stringold.="Branch Tel : ".$_data['branch_tel']."<br />";
    		$stringold.="Project Manager : ".$_data['project_manager_namekh']."<br />";
    		$stringold.="Nationality : ".$_data['project_manager_nationality']."<br />";
    		$stringold.="Current Address : ".$_data['current_address']."<br />";
    	}
    	$arr['activityold']=$stringold;
    	$arr['after_edit_info']=$string;
    	return $arr;
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
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	
    	$sql = "SELECT b.br_id,b.project_name,
		b.prefix,b.branch_code,b.br_address,b.branch_tel,b.fax,
		b.other,p_manager_namekh,w_manager_namekh	
    	";
    	
    	$sql.=$dbp->caseStatusShowImage("b.`status`").",";
    	$sql.="
    	(SELECT COUNT(p.id) FROM `ln_properties` AS p  WHERE p.branch_id = b.`br_id` AND p.status = 1) AS totalproperty,
    	(SELECT COUNT(p.id) FROM `ln_properties` AS p  WHERE p.branch_id = b.`br_id` AND p.status = 1 AND p.is_lock =1) AS totalpropertysold
    	FROM $this->_name AS b ";
    	
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
    	
    	$where.=$dbp->getAccessPermission("b.br_id");
    	
    	$order=' ORDER BY b.br_id DESC';
        return $db->fetchAll($sql.$where.$order);
    }
    
 function getBranchById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * ,
    		(SELECT name_en FROM `ln_view` WHERE TYPE =11 AND p_sex=key_code LIMIT 1) AS sex,
    		(SELECT name_en FROM `ln_view` WHERE TYPE =11 AND w_sex=key_code LIMIT 1) AS sex_w
    		FROM		
    	$this->_name ";
    	$where = " WHERE `br_id`= $id" ;
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("br_id");
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
	  

