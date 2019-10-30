<?php
class Report_Model_DbTable_DbProductList extends Zend_Db_Table_Abstract
{
	public  function getStudentInfo($search){
		$_db = $this->getAdapter();
		$sql = "SELECT stu_id,stu_enname,stu_khname,
		(SELECT name_kh FROM `rms_view` WHERE type=2 AND key_code = sex) as gender
		,stu_code,dob,remark,tel,(SELECT province_kh_name FROM `rms_province` WHERE `province_id`= rms_student.province_id) as pro,
		father_phone,mother_phone,email,father_khname,father_enname,father_nation,(SELECT `occu_name` FROM `rms_occupation` WHERE `occupation_id` = father_job)as far_job,(SELECT `occu_name` FROM `rms_occupation` WHERE `occupation_id` = mother_job)as mom_job,
		mother_enname,mother_khname,mother_nation,guardian_khname,guardian_nation,guardian_document,guardian_enname,address,home_num,street_num,village_name,commune_name,district_name,
		(SELECT `occu_name` FROM `rms_occupation` WHERE `occupation_id` = guardian_job)as guar_job,guardian_tel,guardian_email,remark,
		(SELECT name_kh FROM `rms_view` WHERE type=1 AND key_code = status) as status,nationality
		FROM rms_student where status = 1";
		
		$sql.='';
		if(empty($search)){
			$_db->fetchAll($sql);
		}
		if(!empty($search['txtsearch']))
		{
			$s_where = array();
			$s_search = trim($search['txtsearch']);
			$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			$s_where[] = " stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " stu_code LIKE '%{$s_search}%'";
			$s_where[] = " nationality LIKE '%{$s_search}%'";
			// 			$s_where[] = " en_name LIKE '%{$s_search}%'";
			// 			$s_where[] = " sex LIKE '%{$s_search}%'";
			//			$s_where[] = " nationality LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		return $_db->fetchAll($sql);
	}
     
    function getStudentById($score_id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,attd_id,student_id,(SELECT CONCAT(stu_enname,'-',stu_khname) FROM rms_student AS st WHERE st.stu_id=atd.student_id) AS stu_name,
				   student_code,
				   sex
				FROM rms_attendent_detail AS atd
				WHERE attd_id=$score_id  ORDER BY atd.student_id DESC ";
    	return $db->fetchAll($sql);
    }
    function getProductLocation($search=null){
    	$db=$this->getAdapter();
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "p.title";
    		$degree = "it.title";
    	}else{ // English
    		$label = "name_en";
    		$grade = "p.title_en";
    		$degree = "it.title_en";
    	}
//     	$level = $_db->getUserType();
//     	if($level==4){
//     		$branch_id = $_db->getAccessPermission("brand_id");
//     	}else{
//     		$branch_id = "";
//     	}
    	
    	$sql="SELECT 
    				p.code AS pro_code,
    				p.images,
    				$grade AS pro_name ,
    				(SELECT $degree FROM `rms_product_cate` AS it WHERE it.id = p.items_id LIMIT 1) AS category_name,
    	            (SELECT project_name FROM ln_project WHERE ln_project.br_id=pl.brand_id LIMIT 1) AS branch_name,
    	            pl.brand_id,
    				pl.pro_qty,
    				pl.note,
    				p.price,
    				pl.price AS pro_price, 
    				p.cost,
    				pl.total_amount,
			        p.create_date AS date,
			        (SELECT $label FROM ln_view WHERE ln_view.key_code=p.status AND ln_view.type=1 LIMIT 1) AS `status` 
			  FROM 
			  		`rms_product` AS p,
			  		rms_product_location AS pl
			  WHERE 
    				p.id=pl.pro_id 
    				AND p.items_type=3
    		";
    	$where=" ";
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " p.code LIKE '%{$s_search}%'";
    		$s_where[]= " p.title LIKE '%{$s_search}%'";
    		$s_where[]= " p.cost LIKE '%{$s_search}%'";
    		$s_where[]= " p.price LIKE '%{$s_search}%'";
    		$s_where[]= " pl.pro_qty LIKE '%{$s_search}%'";
    		$s_where[]= " pl.total_amount LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND pl.brand_id=".$search['location'];
    	}
    	if(!empty($search['product'])){
    		$where.=" AND p.id=".$search['product'];
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND p.status=".$search['status_search'];
    	}
    	if($search['category_id']>0){
    		$where.=" AND p.items_id=".$search['category_id'];
    	}
    	if($search['product_type']>0){
    		$where.=" AND p.product_type=".$search['product_type'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('brand_id');
    	$order = " ORDER BY pl.brand_id ASC ";
    	if($search['sort_by']==1){
    		$order.=" , p.items_id ASC ";
    	}else if($search['sort_by']==2){
    		$order.=" , $grade ASC ";
    	}else{
    		$order.=" , p.items_id ASC , p.id ASC ";
    	}
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    function getProductsByLocId($loc_id){
    	$db=$this->getAdapter();
    	$sql="SELECT p.pro_code,p.pro_name,
				       pl.pro_qty,p.pro_price,pl.total_amount,p.date,
				       (SELECT name_kh FROM rms_view WHERE rms_view.key_code=p.status AND rms_view.type=1) AS `status`
				FROM rms_product AS p,rms_product_location AS pl
				WHERE p.id=pl.pro_id AND pl.pro_id=$loc_id";
    	return $db->fetchAll($sql);
    }
    function getLocationNameById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT CONCAT(branch_nameen,'-',branch_namekh) AS NAME FROM rms_branch WHERE br_id=$id";
    	return $db->fetchRow($sql);
    }
    
}



