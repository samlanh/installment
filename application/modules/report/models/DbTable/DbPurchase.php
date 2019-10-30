<?php
class Report_Model_DbTable_DbPurchase extends Zend_Db_Table_Abstract
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
    function getProductsByLocId($loc_id){
    	$db=$this->getAdapter();
    	$sql="SELECT p.pro_code,p.pro_name,
				       pl.pro_qty,p.pro_price,pl.total_amount,p.date,
				       (SELECT name_kh FROM rms_view WHERE rms_view.key_code=p.status AND rms_view.type=1) AS `status`
				FROM rms_product AS p,rms_product_location AS pl
				WHERE p.id=pl.pro_id AND pl.pro_id=$loc_id";
    	return $db->fetchAll($sql);
    }
    function getPurchaseCodeSuplier($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT s.sup_name,s.tel,
				(SELECT project_name FROM ln_project WHERE ln_project.br_id=sp.branch_id LIMIT 1) AS branch_name,
				sp.*,
				(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = sp.user_id LIMIT 1) as user_name
				FROM rms_purchase AS sp,rms_supplier AS s 
			    WHERE sp.sup_id=s.id  ";//AND sp.status=1
    	
    	$from_date =(empty($search['start_date']))? '1': " sp.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " sp.supplier_no LIKE '%{$s_search}%'";
    		$s_where[]= " sp.invoice_no LIKE '%{$s_search}%'";
    		$s_where[]=" s.sup_name LIKE '%{$s_search}%'";
    		$s_where[]=" s.tel LIKE '%{$s_search}%'";
    		$s_where[]= " sp.amount_due LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND sp.branch_id=".$search['location'];
    	}
    	if($search['supplier_id']>0){
    		$where.=" AND sp.sup_id=".$search['supplier_id'];
    	}
    	if(!empty($search['status'])){
    		if($search['status']==1){
    			$where.=' AND sp.status=0';
    		}else if($search['status']==2){
    			$where.=' AND sp.amount_due > sp.amount_due_after AND sp.amount_due_after>0 ';
    		}else if($search['status']==2){
    			$where.=' AND sp.is_paid=1';
    		}
    	} 
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$orderby=' ORDER BY sp.id DESC ';
    	return $db->fetchAll($sql.$where.$orderby);
    }
    function getPurchaseSupplierById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT pro_id,qty,qty,cost,amount,note,STATUS FROM rms_purchase_detail WHERE supproduct_id=1";
    	return $db->fetchRow($sql);
    }
    function getPurchaseName($id){
    	$db=$this->getAdapter();
    	$sql="SELECT sp.supplier_no,s.sup_name,
		       (SELECT branch_namekh FROM rms_branch WHERE rms_branch.br_id=sp.branch_id ) AS brand_name 
		       FROM rms_supplier AS s,rms_purchase AS sp 
		       WHERE s.id=sp.sup_id AND sp.sup_id=$id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getPruchaseById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT 
    				sp.*,
		    	 	(SELECT project_name FROM ln_project WHERE ln_project.br_id=sp.branch_id LIMIT 1) AS branch_name,
		    		s.sup_name,
		    		s.purchase_no,
		    		s.sex,
		    		s.tel,
		    		s.email,
		    		s.address,
		    		sp.amount_due,
		    		sp.branch_id,
		    		sp.status
    			FROM 
    				rms_supplier AS s,
    				rms_purchase AS sp
    			WHERE 
    				s.id=sp.sup_id 
    				AND sp.id=$id
    		";
    	return $db->fetchRow($sql);
    }
    function getPurchaseProductDetail($pro_id,$search=null){
    	$db=$this->getAdapter();
    	$sql=" SELECT  
    				sp.supplier_no,
    				s.sup_name,
		       		(SELECT project_name FROM ln_project WHERE ln_project.br_id=sp.branch_id LIMIT 1) AS branch_name,
		       		(SELECT d.title FROM `rms_product` AS d WHERE d.items_type=3 AND d.id = spd.pro_id LIMIT 1) AS pro_id,
		          	spd.qty,
		          	spd.qty,
		           	spd.cost,
		           	spd.amount,
		           	spd.date,
					(SELECT name_kh FROM ln_view WHERE ln_view.key_code=spd.status AND ln_view.type=1) AS `status`
				FROM 
					rms_purchase AS sp,
					rms_purchase_detail AS spd,
					rms_supplier As s 
				WHERE 
    				sp.id=spd.supproduct_id 
    				AND s.id=sp.sup_id 
    				AND spd.supproduct_id=$pro_id
    		";
    	$where="";
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " sp.supplier_no LIKE '%{$s_search}%'";
    		$s_where[]= " s.sup_name LIKE '%{$s_search}%'";
    		$s_where[]=" spd.qty LIKE '%{$s_search}%'";
    		$s_where[]= " spd.cost LIKE '%{$s_search}%'";
    		$s_where[]= " spd.amount LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND sp.branch_id=".$search['location'];
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND spd.status=".$search['status_search'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	return $db->fetchAll($sql.$where);
    }
    function  getAllPurchase($search=null){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$lang = $_db->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    		$grade = "title";
    		$degree = "it.title";
    	}else{ // English
    		$label = "name_en";
    		$grade = "title_en";
    		$degree = "it.title_en";
    	}
    	$sql=" SELECT  
    				sp.id,
    				sp.supplier_no,s.sup_name,s.tel,
    				sp.invoice_no,sp.status,
					(SELECT project_name FROM ln_project WHERE ln_project.br_id=sp.branch_id LIMIT 1) AS branch_name,
		            pro.$grade AS pro_id,
		           	(SELECT $degree FROM `rms_product_cate` AS it WHERE it.id = pro.items_id LIMIT 1) AS cate_name,
		            spd.qty,spd.qty,spd.cost,spd.amount,spd.date,
		       		(SELECT $label FROM ln_view WHERE ln_view.key_code=sp.status AND ln_view.type=1 LIMIT 1) AS `status_po`
       			FROM 
       				rms_purchase AS sp,
	       			rms_purchase_detail AS spd,
	       			rms_supplier AS s,
	       			rms_product AS pro
       			WHERE 
       				sp.id=spd.supproduct_id  
    				AND s.id=sp.sup_id
    				AND pro.id=spd.pro_id	
    		";
		$from_date =(empty($search['start_date']))? '1': " spd.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " spd.date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " sp.supplier_no LIKE '%{$s_search}%'";
    		$s_where[]= " sp.invoice_no LIKE '%{$s_search}%'";
    		$s_where[]= " s.sup_name LIKE '%{$s_search}%'";
    		$s_where[]=" spd.qty LIKE '%{$s_search}%'";
    		$s_where[]= " spd.cost LIKE '%{$s_search}%'";
    		$s_where[]= " spd.amount LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND sp.branch_id=".$search['location'];
    	}
    	if(!empty($search['product'])){
    		$where.=" AND spd.pro_id=".$search['product'];
    	}
    	if($search['category_id']>0){
    		$where.=" AND pro.items_id =".$search['category_id'];
    	}
    	if($search['product_type']>0){
    		$where.=" AND pro.product_type =".$search['product_type'];
    	}
    	if($search['status_search']>0){
    		$where.=" AND sp.status=".$search['status_search'];
    	}
    	if($search['supplier_id']>0){
    		$where.=" AND sp.sup_id=".$search['supplier_id'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('sp.branch_id');
    	//echo $sql.$where;
    	return $db->fetchAll($sql.$where);
    }
    
    // Start Blog Action Purchase Payment
    function getAllPurchasePayment($search){
    	$db = $this->getAdapter();
    	try{
    		$sql="
    		SELECT
    		pp.*,
    		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name,
    		pp.receipt_no,
    		(SELECT s.sup_name FROM `rms_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) AS supplier_name,
    		pp.balance,
    		pp.total_paid,pp.total_due,
    		(SELECT v.name_kh FROM `rms_view` AS v WHERE v.key_code = pp.paid_by AND v.type=8 LIMIT 1) AS paid_by,
    		pp.date_payment,
    		pp.status,
    		(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = pp.closed_by LIMIT 1) as user_close,
	    	(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = pp.user_id LIMIT 1) as user_enter
    		FROM `rms_purchase_payment` AS pp WHERE 1
    		";
    		$from_date =(empty($search['start_date']))? '1': " pp.date_payment >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " pp.date_payment <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    		$sql.= " AND  ".$from_date." AND ".$to_date;
    		$where="";
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search=addslashes(trim($search['adv_search']));
    			$s_where[]= " pp.receipt_no LIKE '%{$s_search}%'";
    			$s_where[]= " pp.balance LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_paid LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_due LIKE '%{$s_search}%'";
    
    			$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
    		if(!empty($search['supplier_search'])){
    			$where.=" AND pp.supplier_id=".$search['supplier_search'];
    		}
    		if(!empty($search['status'])){
    			if($search['status']==1){
    				$where.=' AND pp.status=0';
    			}else if($search['status']==2){
    				$where.=' AND pp.is_closed=1';
    			}
    		}
    		if(!empty($search['branch_search'])){
    			$where.=" AND pp.branch_id=".$search['branch_search'];
    		}
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$where.=$dbp->getAccessPermission('pp.branch_id');
    		$order=" ORDER BY pp.id DESC";
    
    		return $db->fetchAll($sql.$where.$order);
    
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getAllPurchasePaymentForClose($search){
    	$db = $this->getAdapter();
    	try{
    		$sql="
    		SELECT
    		pp.*,
    		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name,
    		pp.receipt_no,
    		(SELECT s.sup_name FROM `rms_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) AS supplier_name,
    		pp.balance,
    		pp.total_paid,pp.total_due,
    		(SELECT v.name_kh FROM `rms_view` AS v WHERE v.key_code = pp.paid_by AND v.type=8 LIMIT 1) AS paid_by,
    		pp.date_payment,
    		pp.status,
    		(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = pp.closed_by LIMIT 1) as user_close,
    		(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = pp.user_id LIMIT 1) as user_enter
    		FROM `rms_purchase_payment` AS pp WHERE pp.status=1
    		AND pp.is_closed = 0
    		";
    		$from_date =(empty($search['start_date']))? '1': " pp.date_payment >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " pp.date_payment <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    		$sql.= " AND  ".$from_date." AND ".$to_date;
    		$where="";
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search=addslashes(trim($search['adv_search']));
    			$s_where[]= " pp.receipt_no LIKE '%{$s_search}%'";
    			$s_where[]= " pp.balance LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_paid LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_due LIKE '%{$s_search}%'";
    
    			$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
    		if(!empty($search['supplier_search'])){
    			$where.=" AND pp.supplier_id=".$search['supplier_search'];
    		}
    		if(!empty($search['branch_search'])){
    			$where.=" AND pp.branch_id=".$search['branch_search'];
    		}
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$where.=$dbp->getAccessPermission('pp.branch_id');
    		$user = $dbp->getUserInfo();
    		if ($user['level']!=1){
    			$where.=" AND pp.user_id=".$user['user_id'];
    		}
    		if(!empty($search['user_id'])){
    			$where.=" AND pp.user_id=".$search['user_id'];
    		}
    		$order=" ORDER BY pp.id DESC";
    
    		return $db->fetchAll($sql.$where.$order);
    
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getPurchasePaymentById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT pp.*,
    	(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name,
		(SELECT s.sup_name FROM `rms_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) AS supplier_name,
		(SELECT s.tel FROM `rms_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) AS tel,
		(SELECT s.email FROM `rms_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) AS email
    	FROM `rms_purchase_payment` AS pp WHERE pp.id = $id ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('pp.branch_id');
    	return $db->fetchRow($sql);
    }
    function getPurchasePaymentDetail($payment_id){
    	$db = $this->getAdapter();
    	$sql="SELECT pd.*,
    	(SELECT p.supplier_no FROM `rms_purchase` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS supplier_no
    	FROM `rms_purchase_payment_detail` AS pd WHERE pd.id =$payment_id ";
    	return $db->fetchAll($sql);
    } 

    function closingPurchasePayment($_data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		if (!empty($_data['selector'])) foreach ( $_data['selector'] as $rs){
    			if (!empty($rs)){
    				$_arr = array(
    						'is_closed'=> 1,
    						'modify_date'=> date("Y-m-d H:i:s"),
    						'closed_by'=> $dbg->getUserId(),
    				);
    				$this->_name ="rms_purchase_payment";
    				$where = $_db->quoteInto("id=?", $rs);
    				$this->update($_arr, $where);
    			}
    		}
    
    		$_db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$_db->rollBack();
    		echo $e->getMessage(); exit();
    	}
    }
    
    // End Blog Action Purchase Payment
    
    function getSuplierPuchaseBalance($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT sp.branch_id,sp.id,sp.supplier_no,s.sup_name,s.tel,
    		(SELECT branch_namekh FROM rms_branch WHERE rms_branch.br_id=sp.branch_id LIMIT 1) AS branch_name,
    		(SELECT SUM(payment_amount) FROM `rms_purchase_payment_detail` WHERE purchase_id=sp.id LIMIT 1) AS paid_amount,
    		sp.amount_due,sp.amount_due_after,sp.date,
    		(SELECT name_kh FROM rms_view WHERE rms_view.key_code=sp.status AND rms_view.type=1 LIMIT 1) AS `status`
    	FROM 
    		rms_purchase AS sp,rms_supplier AS s
    			WHERE sp.sup_id=s.id AND sp.status=1 AND sp.is_paid=0 ";
    	 
    	$from_date =(empty($search['start_date']))? '1': " sp.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " sp.supplier_no LIKE '%{$s_search}%'";
    		$s_where[]=" s.sup_name LIKE '%{$s_search}%'";
    		$s_where[]=" s.tel LIKE '%{$s_search}%'";
    		$s_where[]= " sp.amount_due LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['location'])){
    		$where.=" AND sp.branch_id=".$search['location'];
    	}
    	if($search['supplier_id']>0){
    		$where.=" AND sp.sup_id=".$search['supplier_id'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	return $db->fetchAll($sql.$where);
    }
}