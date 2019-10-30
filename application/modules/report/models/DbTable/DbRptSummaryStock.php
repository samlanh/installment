<?php

class Report_Model_DbTable_DbRptSummaryStock extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_product_catedetail';
    function getAllProductName(){
    	$db = $this->getAdapter();
    	$sql="select id , CONCAT(code,' => ',title) as name FROM rms_product_catedetail WHERE status=1 AND items_type=3";
    	return $db->fetchAll($sql);
    }
    function getAllProductType(){
    	$db = $this->getAdapter();
    	$sql="select id , title as name FROM rms_product_cate WHERE status=1 AND type=3";
    	return $db->fetchAll($sql);
    }
    function getAllRequestProduct($search=null){
    	$db = $this->getAdapter();
    	$sql="SELECT
			    	re.*,
			    	(select title from rms_request_for as rf where rf.id = request_for) as request_for,
			    	(select title from rms_for_section as fs where fs.id = for_section) as for_section,
			    	(SELECT project_name FROM ln_project WHERE ln_project.br_id=re.branch_id LIMIT 1) AS branch_name,
			    	(select CONCAT(last_name,' ',first_name) from rms_users as u where u.id = re.user_id) as user,
			    	re.status
		    	FROM
		    		rms_request_order AS re
		    	WHERE
	    			1
    		";
    	$where="";
    
    	$from_date =(empty($search['start_date']))? '1': " re.request_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " re.request_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
    		$s_where[]= " REPLACE(re.request_no,' ','') LIKE '%{$s_search}%'";
    		$s_where[]="  REPLACE(re.request_name,' ','') LIKE '%{$s_search}%'";
    		$s_where[]= " REPLACE(re.purpose,' ','') LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['request_for']>0){
    		$where.=" AND request_for=".$search['request_for'];
    	}
    	if($search['for_section']>0){
    		$where.=" AND for_section=".$search['for_section'];
    	}
    	if($search['status_search']>-1){
    		$where.=" AND status=".$search['status_search'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND re.branch_id=".$search['branch_id'];
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$order=" ORDER BY re.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getRequestProductById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT
    	re.*,
    	(select title from rms_request_for as rf where rf.id = request_for) as request_for,
    	(select title from rms_for_section as fs where fs.id = for_section) as for_section,
    	(select branch_namekh from rms_branch where br_id = branch_id) as branch_name,
    	(select first_name from rms_users as u where u.id = re.user_id) as user,
    	(select name_en from rms_view where type=1 and key_code = re.status) as status
    	FROM
    	rms_request_order AS re
    	WHERE
    	re.id = $id
    	";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	return $db->fetchRow($sql);
    }
    
    function getAllRequestProductDetailById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT
    	*,
    	(select branch_namekh from rms_branch where br_id = branch_id) as branch_name,
    	(SELECT d.title FROM `rms_product_catedetail` AS d WHERE d.items_type=3 AND d.id = pro_id LIMIT 1) AS pro_name
    	FROM
    	rms_request_orderdetail
    	WHERE
    	request_id = $id
    	";
    	return $db->fetchAll($sql);
    }
    
    
    public function getAllProduct($search){
    	try{
	    	$db = $this->getAdapter();
	    	$_db = new Application_Model_DbTable_DbGlobal();
	    	$level = $_db->getUserType();
	    	$lang = $_db->currentlang();
	    	if($lang==1){// khmer
	    		$label = "name_kh";
	    		$branch = "branch_namekh";
	    		$grade = "d.title";
	    		$degree = "it.title";
	    	}else{ // English
	    		$label = "name_en";
	    		$branch = "branch_nameen";
	    		$grade = "d.title_en";
	    		$degree = "it.title_en";
	    	}
	    	if($level==4){
	    		$branch_id = $_db->getAccessPermission("brand_id");
	    	}else{
	    		$branch_id = "";
	    	}
	    	$from_date =(empty($search['start_date']))? '1': $search['start_date']." 00:00:00";
	    	$to_date = (empty($search['end_date']))? '1': $search['end_date']." 23:59:59";
	    	$sql="SELECT 
	    			d.*,
	    			$grade as pro_name,
					(SELECT b.$branch FROM rms_branch AS b WHERE b.br_id = pl.brand_id LIMIT 1) AS branch_name,
					(SELECT $degree FROM `rms_product_cate` AS it WHERE it.id = d.items_id LIMIT 1) AS category,
					(SELECT  
							SUM(pd.qty) 
						FROM 
							rms_purchase_detail AS pd,
							rms_purchase AS pu 
						WHERE 
							pu.id = pd.supproduct_id 
						    AND pd.pro_id = d.id 
						    AND pu.branch_id = pl.`brand_id`
						    AND pu.date >= '$from_date' 
						    AND pu.date <= '$to_date' 
						    AND pu.status = 1 
						GROUP BY pd.pro_id
					  	LIMIT 1
					) AS purchaseQty,
					(SELECT SUM(ctd.qty_receive) FROM `rms_cutstock` AS ct,`rms_cutstock_detail` AS ctd WHERE ct.id = ctd.cutstock_id AND ctd.product_id = d.id AND ct.branch_id = pl.brand_id AND ct.received_date >= '$from_date' AND ct.received_date <= '$to_date' AND ct.status=1 LIMIT 1) AS saleqty,
					(SELECT 
					    	SUM(qty_receive) 
					  	FROM
						    rms_request_order AS req,
						    rms_request_orderdetail AS req_d 
					  	WHERE 
					  		req.id = req_d.request_id 
					  		and req_d.pro_id = d.id
					  		and req_d.branch_id = pl.brand_id
					  		AND req.request_date >= '$from_date' 
						    AND req.request_date <= '$to_date'
						    and req.status=1
					  	LIMIT 1) AS request,
				  	(SELECT SUM(difference) FROM rms_adjuststock AS adj, rms_adjuststock_detail AS adj_d WHERE adj.id = adj_d.adjuststock_id AND adj_d.pro_id = d.id AND adj_d.branch_id = pl.brand_id AND adj.request_date >= '$from_date' AND adj.request_date <= '$to_date' AND adj.status=1 LIMIT 1) AS adjustQty, 
				  	(SELECT SUM(qty) FROM `rms_transferstock` AS tra, rms_transferdetail AS tra_d WHERE tra.id = tra_d.transferid AND tra_d.pro_id = d.id AND tra.from_location = pl.brand_id AND tra.transfer_date >= '$from_date' AND tra.transfer_date <= '$to_date' AND tra.status=1 LIMIT 1) AS tran_out, 
					(SELECT SUM(qty) FROM `rms_transferstock` AS tra, rms_transferdetail AS tra_d WHERE tra.id = tra_d.transferid AND tra_d.pro_id = d.id AND tra.to_location = pl.brand_id AND tra.transfer_date >= '$from_date' AND tra.transfer_date <= '$to_date' AND tra.status=1 LIMIT 1) AS tran_in, 
					pl.pro_qty
				FROM 
					`rms_product_catedetail` AS d,
					rms_product_location AS pl
				WHERE 
					d.id = pl.pro_id
					AND d.items_type=3
					AND d.status=1
	    	";
	    	$where=' ';
	    	$group_by = " ";
	    	$order=" ORDER BY pl.brand_id ASC ";
	    	
	    	if($search['sort_by']==1){
	    		$order.=" , d.items_id ASC ";
	    	}else if($search['sort_by']==2){
	    		$order.=" , $grade ASC ";
	    	}
	    	
	    	if(!empty($search['title'])){
	    		$s_where = array();
	    		$s_search = addslashes(trim($search['title']));
	    		$s_where[] = " d.title LIKE '%{$s_search}%'";
	    		$s_where[] = " d.code LIKE '%{$s_search}%'";
	    		$where .=' AND ( '.implode(' OR ',$s_where).')';
	    	}
	    	
	    	if(!empty($search['branch_id'])){
	    		$where .=' AND pl.brand_id = '.$search['branch_id'];
	    	}
	    	if(!empty($search['category_id'])){
	    		$where .=' AND d.items_id = '.$search['category_id'];
	    	}
	    	if(!empty($search['product'])){
	    		$where .=' AND d.id = '.$search['product'];
	    	}
	    	if(!empty($search['product_type'])){
	    		$where .=' AND d.product_type = '.$search['product_type'];
	    	}
	    	$dbp = new Application_Model_DbTable_DbGlobal();
	    	$where.=$dbp->getAccessPermission("pl.brand_id");
	    	//echo  $sql.$where.$order;
	    	return $db->fetchAll($sql.$where.$group_by.$order);
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function getAllStudentProduct($search,$new=null){
    	try{
    		$db = $this->getAdapter();
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$currentLang = $dbp->currentlang();
    		$stuname=" CONCAT(s.stu_enname,' ',s.last_name)";
    		if ($currentLang==1){
    			$stuname="s.stu_khname";
    		}
    		
    		$from_date =(empty($search['start_date']))? '1': $search['start_date']." 00:00:00";
    		$to_date = $search['end_date'];
    		$sql ="
			SELECT 
			(SELECT CONCAT(b.branch_nameen) FROM rms_branch AS b WHERE b.br_id=sp.branch_id LIMIT 1) AS branch_name,
			(SELECT b.photo FROM rms_branch AS b WHERE b.br_id=sp.branch_id LIMIT 1) AS branch_logo,
			sp.student_id as stu_id,
			$stuname AS student_name,
			s.stu_khname ,
			s.stu_enname AS stu_enname,
			s.last_name  AS last_name,
			s.stu_code AS stu_code,
			(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS academic,
			(SELECT `title` FROM `rms_product_cate` WHERE `id`=s.degree AND type=1 LIMIT 1) AS degree,
			(SELECT CONCAT(`title`) FROM `rms_product_catedetail` WHERE `id`=s.grade AND items_type=1 LIMIT 1) AS grade,
			s.tel AS tel,
			s.photo AS photo,
			(SELECT ie.title FROM `rms_product_catedetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS items_name,
			(SELECT ie.images FROM `rms_product_catedetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS pro_images,
			(SELECT ie.items_type FROM `rms_product_catedetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) AS items_type,
			spd.*,
			sp.branch_id,
			sp.receipt_number,
			sp.create_date AS payment_date,
			(SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) AS remide_date
			FROM `rms_student_payment` AS sp,
			`rms_student_paymentdetail` AS spd,
			`rms_student` AS s
			WHERE spd.payment_id = sp.id
			AND s.stu_id = sp.student_id
			AND (SELECT ie.items_type FROM `rms_product_catedetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) =3
			AND is_void=0
			AND qty_balance >0
					";
    		$where="";
    		if(!empty($search['title'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['title']));
    			$s_where[] = " s.stu_code LIKE '%{$s_search}%'";
    			$s_where[] = " s.stu_enname LIKE '%{$s_search}%'";
    			$s_where[] = " s.stu_khname LIKE '%{$s_search}%'";
    			$s_where[] = " s.last_name LIKE '%{$s_search}%'";
    			$s_where[] = " (SELECT ie.title FROM `rms_product_catedetail` AS ie WHERE ie.id = spd.itemdetail_id LIMIT 1) LIKE '%{$s_search}%'";
    			$where .=' AND ( '.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['study_year'])){
    			$where.=' AND s.academic_year='.$search['study_year'];
    		}
    		if(!empty($search['group'])){
    			$where.=' AND s.group_id='.$search['group'];
    		}
    		if(!empty($search['grade_all'])){
    			$where.=' AND s.grade='.$search['grade_all'];
    		}
    		if($search['degree']>0){
    			$where.=' AND s.degree='.$search['degree'];
    		}
    		if(($search['branch_id'])>0){
    			$where.=' AND sp.branch_id='.$search['branch_id'];
    		}
    		$order="";
    		if (!empty($new)){
    			// 			$where = " AND spd.id NOT IN (SELECT ctd.student_paymentdetail_id FROM `rms_cutstock_detail` AS ctd ) ";
    			$where.= " AND spd.qty_balance = spd.qty ";
    			$order = " ORDER BY sp.id DESC";
    		}else{
    			$to_date = (empty($to_date))? '1': " (SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) <= '".$to_date." 23:59:59'";
    			$where.= " AND ".$to_date;
    			$order=" ORDER BY (SELECT ctd.remide_date FROM `rms_cutstock_detail` AS ctd WHERE ctd.student_paymentdetail_id=spd.id ORDER BY ctd.remide_date DESC LIMIT 1 ) DESC ";
    		
    		}
    		
    		$where.=$dbp->getAccessPermission("sp.branch_id");
    		return $db->fetchAll($sql.$where.$order);
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getAllCutStock($search){
    	$db = $this->getAdapter();
    	try{
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$currentLang = $dbp->currentlang();
    		$stuname=" CONCAT(s.stu_enname,' ',s.last_name)";
    		if ($currentLang==1){
    			$stuname="s.stu_khname";
    		}
    		$sql="
    		SELECT
			(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name,
			s.stu_khname,
			$stuname AS student_name,
			s.stu_enname,
			s.last_name,
			s.stu_code,
			s.tel,
			(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS academic,
			(SELECT `title` FROM `rms_product_cate` WHERE `id`=s.degree AND type=1 LIMIT 1) AS degree,
			(SELECT CONCAT(`title`) FROM `rms_product_catedetail` WHERE `id`=s.grade AND items_type=1 LIMIT 1) AS grade,
			pp.*,
			(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = pp.closed_by LIMIT 1) as user_close,
	    		(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = pp.user_id LIMIT 1) as user_enter
			FROM `rms_cutstock` AS pp,
			`rms_student` AS s
			WHERE s.stu_id = pp.student_id
    		";
    		$from_date =(empty($search['start_date']))? '1': " pp.received_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " pp.received_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    		$sql.= " AND  ".$from_date." AND ".$to_date;
    		$where="";
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search=addslashes(trim($search['adv_search']));
    			$s_where[]= " pp.serailno LIKE '%{$s_search}%'";
    			$s_where[]= " pp.balance LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_received LIKE '%{$s_search}%'";
    			$s_where[]= " pp.total_qty_due LIKE '%{$s_search}%'";
    
    			$where.=' AND ('.implode(' OR ', $s_where).')';
    		}
    		if(!empty($search['student_id'])){
    			$where.=" AND pp.student_id=".$search['student_id'];
    		}
    		if(!empty($search['status_search'])){
    			$where.=" AND pp.status=".$search['status_search'];
    		}
    		if(!empty($search['branch_search'])){
    			$where.=" AND pp.branch_id=".$search['branch_search'];
    		}
    		if(!empty($search['status'])){
    			if($search['status']==1){
    				$where.=' AND pp.status=0';
    			}else if($search['status']==2){
    				$where.=' AND pp.is_closed=1';
    			}
    		}
    		$where.=$dbp->getAccessPermission('pp.branch_id');
    		$order=" ORDER BY pp.id DESC";
    		return $db->fetchAll($sql.$where.$order);
    
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getAllCutStockForClose($search){
    	$db = $this->getAdapter();
	    	try{
	    		$dbp = new Application_Model_DbTable_DbGlobal();
	    		$currentLang = $dbp->currentlang();
	    		$stuname=" CONCAT(s.stu_enname,' ',s.last_name)";
	    		if ($currentLang==1){
	    			$stuname="s.stu_khname";
	    		}
	    		$sql="
	    		SELECT
	    		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name,
	    		s.stu_khname,
	    		$stuname AS student_name,
	    		s.stu_enname,
	    		s.last_name,
	    		s.stu_code,
	    		s.tel,
	    		(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee WHERE rms_tuitionfee.id=s.academic_year LIMIT 1) AS academic,
	    		(SELECT `title` FROM `rms_product_cate` WHERE `id`=s.degree AND type=1 LIMIT 1) AS degree,
	    		(SELECT CONCAT(`title`) FROM `rms_product_catedetail` WHERE `id`=s.grade AND items_type=1 LIMIT 1) AS grade,
	    		pp.*,
	    		(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = pp.closed_by LIMIT 1) as user_close,
	    		(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = pp.user_id LIMIT 1) as user_enter
	    		FROM `rms_cutstock` AS pp,
	    		`rms_student` AS s
	    		WHERE s.stu_id = pp.student_id
	    		AND pp.is_closed = 0
	    		AND pp.status = 1
	    		";
	    		$from_date =(empty($search['start_date']))? '1': " pp.received_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
	    		$to_date = (empty($search['end_date']))? '1': " pp.received_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
	    		$sql.= " AND  ".$from_date." AND ".$to_date;
	    		$where="";
	    		if(!empty($search['adv_search'])){
	    		$s_where=array();
	    				$s_search=addslashes(trim($search['adv_search']));
	    				$s_where[]= " pp.serailno LIKE '%{$s_search}%'";
	    				$s_where[]= " pp.balance LIKE '%{$s_search}%'";
	    		$s_where[]= " pp.total_received LIKE '%{$s_search}%'";
	    		$s_where[]= " pp.total_qty_due LIKE '%{$s_search}%'";
	    
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    		}
	    		if(!empty($search['student_id'])){
	    		$where.=" AND pp.student_id=".$search['student_id'];
	    	}
	    	if(!empty($search['status_search'])){
	    	$where.=" AND pp.status=".$search['status_search'];
	    	}
	    	if(!empty($search['branch_search'])){
	    		$where.=" AND pp.branch_id=".$search['branch_search'];
	    	}
	    
	    	$where.=$dbp->getAccessPermission('pp.branch_id');
	    	
	    	$user = $dbp->getUserInfo();
	    	if ($user['level']!=1){
	    		$where.=" AND pp.user_id=".$user['user_id'];
	    	}
	    	
	    	if(!empty($search['user_id'])){
	    		$where.=" AND pp.user_id=".$search['user_id'];
	    	}
	    	$order=" ORDER BY pp.id DESC";
// 	    	echo $sql.$where.$order;exit();
	    	$dbp = new Application_Model_DbTable_DbGlobal();
	    	$where.=$dbp->getAccessPermission('pp.branch_id');
	    	return $db->fetchAll($sql.$where.$order);
	    
	    	}catch(Exception $e){
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    function closingStuProduct($_data){
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
    				$this->_name ="rms_cutstock";
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
    
    function getProductSold($search){
    	$db = $this->getAdapter();
    	$sql="SELECT 
					(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = sp.branch_id LIMIT 1) AS branch_name,
					sp.receipt_number,
					spd.*,
					sum(spd.qty) as qty,
					sum(spd.subtotal) as subtotal,
					(SELECT it.title FROM `rms_product_cate` AS it WHERE it.id = i.items_id LIMIT 1) AS category,
					i.title AS items_name,
					i.code AS code
			 	FROM 
			 		`rms_student_payment` AS sp,
					`rms_student_paymentdetail` AS spd,
					rms_product_catedetail as i
				WHERE 
					sp.id = spd.payment_id
					AND i.id = spd.itemdetail_id
					AND i.items_type = 3
		    		AND sp.status=1
		    		AND sp.is_void = 0
    		";
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$sql.= " AND  ".$from_date." AND ".$to_date;
    	$where="";
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " i.title LIKE '%{$s_search}%'";
    		$s_where[] = " i.code LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['category_id'])){
    		$where.= " AND i.items_id = ".$db->quote($search['category_id']);
    	}
    	if(!empty($search['product'])){
    		$where.= " AND spd.itemdetail_id= ".$db->quote($search['product']);
    	}
    	if(!empty($search['branch_id'])){
    		$where.= " AND sp.branch_id= ".$db->quote($search['branch_id']);
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('sp.branch_id');
    	$where.=" GROUP BY sp.branch_id, spd.itemdetail_id";
    	return $db->fetchAll($sql.$where);
    }
    function getAllProductSold($search){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$currentLang = $dbp->currentlang();
    	$stuname=" CONCAT(s.stu_enname,' ',s.last_name)";
    	if ($currentLang==1){
    		$stuname="s.stu_khname";
    	}
    	$sql="SELECT
		    		(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = sp.branch_id LIMIT 1) AS branch_name,
		    		s.stu_khname,
		    		$stuname AS student_name,
		    		s.stu_enname,
		    		s.last_name,
		    		s.stu_code,
		    		s.tel,
    				(SELECT CONCAT(from_academic,'-',to_academic) FROM rms_tuitionfee WHERE rms_tuitionfee.id=sp.academic_year LIMIT 1) AS academic,
		    		(SELECT `title` FROM `rms_product_cate` WHERE `id`=s.degree AND type=1 LIMIT 1) AS degree,
		    		(SELECT CONCAT(`title`) FROM `rms_product_catedetail` WHERE `id`=s.grade AND items_type=1 LIMIT 1) AS grade,
			    	sp.receipt_number,
			    	sp.create_date,
			    	spd.*,
			    	(SELECT i.title FROM `rms_product_cate` AS i WHERE i.id = i.items_id LIMIT 1) AS category,
			    	i.title AS items_name,
			    	i.code AS code,
			    	i.cost AS cost, 
			    	(SELECT CONCAT(first_name,' ',last_name) FROM rms_users as u where u.id = sp.user_id LIMIT 1) as user
    			FROM 
    				`rms_student_payment` AS sp,
			    	`rms_student_paymentdetail` AS spd,
			    	`rms_student` AS s,
			    	rms_product_catedetail as i
    			WHERE 
    				sp.id = spd.payment_id
			    	AND s.stu_id = sp.student_id
			    	AND i.id = spd.itemdetail_id
			    	AND i.items_type = 3
			    	AND sp.status = 1
			    	AND sp.is_void = 0
    		";
    	$from_date =(empty($search['start_date']))? '1': " sp.create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
    	$sql.= " AND  ".$from_date." AND ".$to_date;
    	$where="";
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " i.title LIKE '%{$s_search}%'";
    		$s_where[] = " i.code LIKE '%{$s_search}%'";
    		$s_where[] = " sp.receipt_number LIKE '%{$s_search}%'";
    		$sql .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['category_id'])){
    		$where.= " AND i.items_id = ".$db->quote($search['category_id']);
    	}
    	if(!empty($search['product'])){
    		$where.= " AND spd.itemdetail_id= ".$db->quote($search['product']);
    	}
    	if(!empty($search['product_type'])){
    		$where.= " AND i.product_type = ".$db->quote($search['product_type']);
    	}
    	if(!empty($search['branch_id'])){
    		$where.= " AND sp.branch_id= ".$db->quote($search['branch_id']);
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission('sp.branch_id');
//     	echo $sql.$where;exit();
    	return $db->fetchAll($sql.$where);
    }
}