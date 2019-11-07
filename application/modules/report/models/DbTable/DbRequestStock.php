<?php
class Report_Model_DbTable_DbRequestStock extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_request_order';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	
	
	function getAllRequestProductDetail($search=null){
		$db = $this->getAdapter();
		$sql="SELECT 
					req_d.*,
					req_d.id,
					(SELECT project_name FROM ln_project WHERE ln_project.br_id=req.branch_id LIMIT 1) AS branch_name,
					(SELECT title from rms_request_for as rf where rf.id = req.request_for) as request_for,
    				(SELECT title from rms_for_section as fs where fs.id = req.for_section) as for_section,
					
					(SELECT title FROM `rms_product_catedetail` WHERE id=req_d.`pro_id` limit 1) as pro_name,
					(SELECT code FROM `rms_product_catedetail` WHERE id=req_d.`pro_id` limit 1) as pro_code,
					
					SUM(req_d.`qty_request`) AS total_request,
					SUM(req_d.`qty_receive`) AS total_receive,
					SUM(req_d.`price`*req_d.`qty_receive`) AS total_price
				FROM
					rms_request_order AS req,
					`rms_request_orderdetail` AS req_d 
				WHERE 
					req.`id` = req_d.`request_id`
					AND req.`status`=1
			";
	
		$where="";
	
		$from_date =(empty($search['start_date']))? '1': " req.request_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " req.request_date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$from_date." AND ".$to_date;
	
		$group = " GROUP BY req_d.branch_id, req_d.pro_id";
		$order = " ORDER BY req_d.branch_id ASC, req_d.`pro_id` ASC";
		
	
		if($search['request_for']>0){
			$where.=" AND request_for=".$search['request_for'];
		}
		if($search['for_section']>0){
			$where.=" AND for_section=".$search['for_section'];
		}
		if($search['branch_id']>0){
			$where.=" AND req_d.branch_id=".$search['branch_id'];
		}
	
		if($search['category_id']>0){
			$where.=" AND (SELECT items_id FROM `rms_product_catedetail` AS p WHERE p.id = req_d.`pro_id` limit 1) =".$search['category_id'];
		}
		if($search['product']>0){
			$where.=" AND (SELECT p.id FROM `rms_product_catedetail` AS p WHERE p.id = req_d.`pro_id`) =".$search['product'];
		}
		if($search['product_type']>0){
			$where.=" AND (SELECT p.product_type FROM `rms_product_catedetail` AS p WHERE p.id = req_d.`pro_id`) =".$search['product_type'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('req_d.branch_id');
		
		//echo $sql.$where.$group.$order;
		
		return $db->fetchAll($sql.$where.$group.$order);
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
	
	
	function getAllAdjustStockDetail($search=null){
		$db = $this->getAdapter();
		$sql="SELECT 
					ad.adjust_no,
					ad.request_name,
					ad.note,
					ad.request_date,
					ad.create_date,
			        (SELECT b.project_name FROM ln_project AS b WHERE b.br_id=adj.branch_id LIMIT 1)AS branch_name,
			        (SELECT it.title FROM `rms_product_cate` AS it WHERE it.id = i.items_id LIMIT 1) AS category,
			       	i.title AS pro_name,
			         adj.qty_befor,
			         adj.qty_after,
			         adj.difference,
		      		 ad.status,
					(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id=ad.user_id LIMIT 1) AS user_name
				FROM 
					rms_adjuststock AS ad,
					rms_adjuststock_detail AS adj,
					rms_product as i
				WHERE 
					ad.id=adj.adjuststock_id
					AND i.id = adj.pro_id
			";
		$from_date =(empty($search['start_date']))? '1': " ad.request_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " ad.request_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['title'])){
			$s_where=array();
			$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
			$s_where[]= " REPLACE(ad.adjust_no,' ','') LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(ad.request_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(ad.note,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(i.title,' ','') LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if(!empty($search['product'])){
    		$where.=" AND adj.pro_id=".$search['product'];
    	}
    	if($search['category_id']>0){
    		$where.=" AND i.items_id =".$search['category_id'];
    	}
    	if($search['product_type']>0){
    		$where.=" AND i.product_type =".$search['product_type'];
    	}
		if($search['branch_id']){
			$where.=" AND adj.branch_id=".$search['branch_id'];
		}
		if($search['user']){
			$where.=" AND ad.user_id=".$search['user'];
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		$order=" ORDER BY ad.id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllBrokenStockDetail($search=null){
		$db = $this->getAdapter();
		$sql="SELECT
					ad.broken_no,
					ad.request_name,
					ad.note,
					ad.request_date,
					ad.create_date,
					(SELECT b.project_name FROM ln_project AS b WHERE b.br_id=adj.branch_id LIMIT 1)AS branch_name,
					(SELECT it.title FROM `rms_product_cate` AS it WHERE it.id = i.items_id LIMIT 1) AS category,
					i.title AS pro_name,
					adj.qty_before,
					adj.qty_broken,
					adj.qty_after,
					ad.status,
					(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id=ad.user_id LIMIT 1) AS user_name
				FROM
					rms_brokenstock AS ad,
					rms_brokenstock_detail AS adj,
					rms_product as i
				WHERE
					ad.id=adj.brokenstock_id
					AND i.id = adj.pro_id
			";
		$from_date =(empty($search['start_date']))? '1': " ad.request_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " ad.request_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['title'])){
			$s_where=array();
			$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
			$s_where[]= " REPLACE(ad.adjust_no,' ','') LIKE '%{$s_search}%'";
			$s_where[]="  REPLACE(ad.request_name,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(ad.note,' ','') LIKE '%{$s_search}%'";
			$s_where[]= " REPLACE(i.title,' ','') LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if(!empty($search['product'])){
			$where.=" AND adj.pro_id=".$search['product'];
		}
		if($search['category_id']>0){
			$where.=" AND i.items_id =".$search['category_id'];
		}
		if($search['product_type']>0){
			$where.=" AND i.product_type =".$search['product_type'];
		}
		if($search['branch_id']){
			$where.=" AND adj.branch_id=".$search['branch_id'];
		}
		if($search['user']){
			$where.=" AND ad.user_id=".$search['user'];
		}
	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('branch_id');
		$order=" ORDER BY ad.id DESC";
		return $db->fetchAll($sql.$where.$order);
	}
    
}



