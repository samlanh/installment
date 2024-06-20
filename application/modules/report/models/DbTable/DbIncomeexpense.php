<?php
class Report_Model_DbTable_DbIncomeexpense extends Zend_Db_Table_Abstract
{
	function getAllExpenseReport($search=null,$group_by=null){
		if(empty($search['ordering'])){
			$search['ordering']=2;
		}
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		 
		$sql="
		SELECT
		id
		,(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name
		,(SELECT ls.name FROM `ln_supplier` AS ls WHERE ls.id = supplier_id LIMIT 1) AS supplier_name
		,(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=payment_id limit 1) AS payment_type
		,payment_id
		,title
			
		,CASE
		WHEN ln_expense.expensePaymentId > 0 THEN CONCAT(ln_expense.invoice,' (',(SELECT expp.receipt_no FROM `rms_expense_payment` AS expp WHERE ln_expense.expensePaymentId=expp.id LIMIT 1 ),')')
		ELSE ln_expense.invoice
		END AS invoice
			
		,is_closed
		,cheque_issuer
		,other_invoice
		,(SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name
		,cheque
		,total_amount
		,description
		,date
		,(SELECT  CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,'')) FROM rms_users WHERE id=user_id limit 1 ) AS user_name
		,status
		,cancelSale_id
		,(SELECT bank_name FROM `st_bank` WHERE  id = ln_expense.bank_id LIMIT 1) AS bank
		,bank_id AS bankId
		,CASE
		WHEN  cancelSale_id > 0 THEN (SELECT c.name_kh FROM ln_client AS c WHERE c.client_id = (SELECT s.client_id FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1 ) LIMIT 1)
		ELSE  (SELECT ls.name FROM `ln_supplier` AS ls WHERE ls.id = supplier_id LIMIT 1)
		END AS supplierOrCustomer
		,(SELECT c.name_kh FROM ln_client AS c WHERE c.client_id = (SELECT s.client_id FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1 ) LIMIT 1) AS cancelCustomer
		,(SELECT CONCAT(p.land_address,',',p.street) FROM `ln_properties` AS p WHERE p.id = (SELECT s.house_id FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1 ) LIMIT 1) AS cancelProperty
			
		FROM ln_expense
		WHERE
		status=1
		AND total_amount>0
		";
		$sql.=" AND (SELECT v.capital_widthdrawal FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = ln_expense.`category_id` LIMIT 1)=0 ";
			
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
	
		/*
		 $order="";
		if($search['ordering']==1){
		$order.=" order by date DESC";
		}
		if($search['ordering']==2){
		$order.=" order by id DESC";
		}
		*/
			
		$order=" order by branch_id DESC, date DESC";
		if(!empty($search['queryOrdering'])){
			if($search['queryOrdering']==1){
				$order =" ORDER BY branch_id DESC, date ASC ";
			}else if($search['queryOrdering']==2){
				$order =" ORDER BY branch_id DESC, date DESC ";
			}else if($search['queryOrdering']==3){
				$order =" ORDER BY branch_id DESC, id ASC ";
			}else if($search['queryOrdering']==4){
				$order =" ORDER BY branch_id DESC, id DESC ";
			}
		}
			
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " description LIKE '%{$s_search}%'";
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			$s_where[] = " other_invoice LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT ls.name FROM `ln_supplier` AS ls WHERE ls.id = supplier_id LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		//     		if(@$search['category_id_expense']>-1 AND !@empty($search['category_id_expense'])){
		//     			$where.= " AND category_id = ".$search['category_id_expense'];
		//     		}
		if(@$search['category_id_expense']>-1 AND !@empty($search['category_id_expense'])){
			$condiction = $dbp->getChildType($search['category_id_expense']);
			if(!empty($condiction)){
				$where.=" AND category_id IN ($condiction)";
			}else{
				$where.=" AND category_id=".$search['category_id_expense'];
			}
		}
		if(!empty($search['user_id']) AND $search['user_id']>0){
			$where.= " AND ln_expense.user_id = ".$search['user_id'];
		}
		if($search['branch_id']>0){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if(@$search['payment_type']>0){
			$where.= " AND payment_id = ".$search['payment_type'];
		}
		if (!empty($search['payment_method'])){
			$where.= " AND payment_id = '".$search['payment_method']."'";
		}
		if (!empty($search['supplier_id'])){
			$where.= " AND supplier_id = ".$search['supplier_id'];
		}
		if (!empty($search['cheque_issuer_search'])){
			$where.= " AND cheque_issuer = '".$search['cheque_issuer_search']."'";
		}
			
			
		$search['is_closed'] = empty($search['is_closed'])?0:$search['is_closed'];
		if (!empty($search['is_closed'])){
			if($search['is_closed']!=1){
				$search['is_closed']=0;
			}
			$where.= " AND is_closed = ".$search['is_closed']."";
		}
		if($group_by!=null){
			$where.=" group by category_id ";
		}
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllCommissionPayment($search=null){
		if(empty($search['ordering'])){
			$search['ordering']=2;
		}
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " cp.date_payment >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " cp.date_payment <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		 
		$sql=" SELECT
		cp.*,
		(SELECT  p.`project_name` FROM `ln_project` AS p WHERE (p.`br_id` = cp.`branch_id`) LIMIT 1) AS branch_name,
		(SELECT co_khname FROM `ln_staff` WHERE co_id=cp.agency_id LIMIT 1) AS staff_name,
		(SELECT sex FROM `ln_staff` WHERE co_id=cp.agency_id LIMIT 1) AS sex,
		(SELECT tel FROM `ln_staff` WHERE co_id=cp.agency_id LIMIT 1) AS tel,
		(SELECT name_kh FROM `ln_view` WHERE TYPE=13 AND key_code=cp.category LIMIT 1) AS category_name,
		(SELECT name_kh FROM `ln_view` WHERE TYPE=2 AND key_code=cp.payment_method LIMIT 1) AS payment_type,
		(SELECT  CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,'')) FROM rms_users WHERE id=cp.user_id LIMIT 1 ) AS user_name
		FROM `rms_commission_payment` AS cp WHERE 1
		";
			
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
	
		/*
		 $order="";
		if($search['ordering']==1){
		$order.=" order by cp.date_payment DESC";
		}
		if($search['ordering']==2){
		$order.=" order by cp.id DESC";
		}
		*/
			
		$order=" order by branch_id DESC, date_payment DESC";
		if(!empty($search['queryOrdering'])){
			if($search['queryOrdering']==1){
				$order =" ORDER BY cp.date_payment ASC ";
			}else if($search['queryOrdering']==2){
				$order =" ORDER BY  cp.date_payment DESC ";
			}else if($search['queryOrdering']==3){
				$order =" ORDER BY cp.id ASC ";
			}else if($search['queryOrdering']==4){
				$order =" ORDER BY cp.id DESC ";
			}
		}
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " cp.receipt_no LIKE '%{$s_search}%'";
			$s_where[] = " cp.total_paid LIKE '%{$s_search}%'";
			$s_where[] = " cp.total_due LIKE '%{$s_search}%'";
			$s_where[] = " cp.cheque_no LIKE '%{$s_search}%'";
			$s_where[] = " cp.cheque_issuer LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT co_khname FROM `ln_staff` WHERE co_id=cp.agency_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_kh FROM `ln_view` WHERE TYPE=13 AND key_code=cp.category LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['user_id']) AND $search['user_id']>0){
			$where.= " AND cp.user_id = ".$search['user_id'];
		}
		if($search['branch_id']>0){
			$where.= " AND cp.branch_id = ".$search['branch_id'];
		}
		if(@$search['payment_type']>0){
			$where.= " AND cp.payment_method = ".$search['payment_type'];
		}
		if (!empty($search['supplier_id'])){
			$where.= " AND cp.agency_id = ".$search['supplier_id'];
		}
		if (!empty($search['cheque_issuer_search'])){
			$where.= " AND cp.cheque_issuer = '".$search['cheque_issuer_search']."'";
		}
			
		$search['is_closed'] = empty($search['is_closed'])?0:$search['is_closed'];
		if (!empty($search['is_closed'])){
			if($search['is_closed']!=1){
				$search['is_closed']=0;
			}
			$where.= " AND cp.is_closed = ".$search['is_closed']."";
		}
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllPurchasePayment($search){
		$db = $this->getAdapter();
		try{
			$sql="
			SELECT
			pp.*
			,(SELECT b.project_name FROM `ln_project` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name
				
			,CONCAT(pp.receipt_no, ' ', COALESCE((SELECT CONCAT('(',exp.invoice,')') FROM ln_expense AS exp WHERE exp.expensePaymentId = pp.id limit 1 ),'') ) AS receipt_no
			,(SELECT s.name FROM `ln_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) AS supplier_name
			,pp.balance
			,pp.total_paid,pp.total_due
			,pp.paid_by as paid_by_id
			,(SELECT v.name_kh FROM `ln_view` AS v WHERE v.key_code = pp.paid_by AND v.type=26 LIMIT 1) AS paid_by
			,pp.date_payment
			,(SELECT  first_name FROM rms_users WHERE id=pp.user_id limit 1 ) AS user_name
			,pp.status
			,(SELECT bank_name FROM `st_bank` WHERE id =pp.bank_id LIMIT 1) AS bank
			,pp.bank_id AS bankId
			FROM
			`rms_expense_payment` AS pp
			WHERE pp.status=1
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
				$s_where[]= " (SELECT s.name FROM `ln_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) LIKE '%{$s_search}%'";
	
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(!empty($search['supplier_search'])){
				$where.=" AND pp.supplier_id=".$search['supplier_search'];
			}
			if(!empty($search['status_search'])){
				$where.=" AND pp.status=".$search['status_search'];
			}
			if(!empty($search['branch_id'])){
				$where.=" AND pp.branch_id=".$search['branch_id'];
			}
			$dbp = new Application_Model_DbTable_DbGlobal();
			$where.=$dbp->getAccessPermission('pp.branch_id');
			$order=" ORDER BY pp.id DESC";
			if(!empty($search['queryOrdering'])){
				if($search['queryOrdering']==1){
					$order =" ORDER BY pp.date_payment ASC ";
				}else if($search['queryOrdering']==2){
					$order =" ORDER BY pp.date_payment DESC ";
				}else if($search['queryOrdering']==3){
					$order =" ORDER pp.id ASC ";
				}else if($search['queryOrdering']==4){
					$order =" ORDER pp.id DESC ";
				}
			}
			$search['is_closed'] = empty($search['is_closed'])?0:$search['is_closed'];
			if (!empty($search['is_closed'])){
				if($search['is_closed']!=1){
					$search['is_closed']=0;
				}
				$where.=" AND pp.is_closed = '".$search['is_closed']."'";
			}
			return $db->fetchAll($sql.$where.$order);
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getALLCommissionStaff($search = null){
		$db = $this->getAdapter();
		$where="";
		$sql="SELECT *,
		(SELECT land_address FROM `ln_properties` WHERE id=s.house_id) AS land_name,
		(SELECT street FROM `ln_properties` WHERE id=s.house_id) AS street,
		st.`branch_id`,(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = st.`branch_id`) AS project_name
		,st.`co_khname`,st.`co_lastname`,st.`co_code`,st.`sex`,
		(SELECT  CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,'')) FROM rms_users WHERE id = s.user_id LIMIT 1 ) AS user_name
		FROM ln_sale AS s ,
		`ln_staff` AS st
		WHERE s.`comission` !=0 AND st.`co_id` = s.`staff_id` ";
		 
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("st.`branch_id`");
		 
		$Other =" ORDER BY s.`id` DESC ";
		$from_date =(empty($search['start_date']))? '1': " s.`buy_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.`buy_date` <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
		if($search['co_khname']>0){
			//     		$where.= " AND s.`staff_id` = ".$search['co_khname'];
			$condiction = $dbp->getChildAgency($search['co_khname']);
			if (!empty($condiction)){
				$where.=" AND s.staff_id IN ($condiction)";
			}else{
				$where.=" AND s.staff_id=".$search['co_khname'];
			}
		}
		if($search['branch_id']>0){
			$where.= " AND st.`branch_id` = ".$search['branch_id'];
		}
		if($search['land_id']>0){
			$where.= " AND s.house_id = ".$search['land_id'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] =" s.`sale_number` LIKE '%{$s_search}%'";
			$s_where[]=" s.`receipt_no` LIKE '%{$s_search}%'";
			$s_where[]=" st.`co_khname` LIKE '%{$s_search}%'";
			$s_where[]=" st.`co_code` LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where.$Other);
	}
	function getAllCommission($search){
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "c.`for_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "c.`for_date` <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql ='
		SELECT
		c.`id`
		,s.id AS saleid
		,p.`project_name`
		,s.`sale_number`
		,s.full_commission
		,clie.`name_kh` AS client_name
		,(SELECT protype.type_nameen FROM `ln_properties_type` AS protype WHERE protype.id = pro.`property_type` LIMIT 1) AS property_type
		,pro.`land_address`
		,pro.`street`
		,s.price_sold
		,c.cheque
		,c.cheque_issuer
		,(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=c.payment_id limit 1) AS payment_type
		,(SELECT co_khname FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS staff_name
		,(SELECT co_code FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS co_code
		,(SELECT sex FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS sex
		,(SELECT tel FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS tel
		,c.total_amount
		,c.invoice
		,c.payment_id
		,(SELECT bank_name FROM `st_bank` WHERE id =c.bank_id LIMIT 1) AS bank
		,c.`bank_id` AS bankId
		,c.for_date AS `create_date`
		, c.`status`
		,c.is_closed
		,(SELECT CONCAT(COALESCE(last_name,"")," ",COALESCE(first_name,""))  FROM rms_users WHERE id = c.user_id LIMIT 1 ) AS user_name
		FROM `ln_comission` AS c ,
		`ln_sale` AS s,
		`ln_project` AS p,
		`ln_properties` AS pro,
		`ln_client` AS clie
		WHERE
		s.`id` = c.`sale_id`
		AND p.`br_id` = c.`branch_id`
		AND pro.`id` = s.`house_id`
		AND clie.`client_id` = s.`client_id`
		AND c.status=1
		AND c.total_amount>0 ';
	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("c.branch_id");
	
		if($search['branch_id']>0){
			$where.= " AND c.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['co_khname']) AND $search['co_khname']>0){
			$where.= " AND c.staff_id = ".$search['co_khname'];
		}
		if(!empty($search['land_id']) AND $search['land_id']>0){
			$where.= " AND s.house_id = ".$search['land_id'];
		}
		if(!empty($search['category_id_expense']) AND $search['category_id_expense']>0){
			$where.= " AND c.category_id = ".$search['category_id_expense'];
		}
		if (!empty($search['payment_method'])){
			$where.= " AND c.payment_id = '".$search['payment_method']."'";
		}
	
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " clie.`client_number` LIKE '%{$s_search}%'";
			$s_where[] = " clie.`name_kh` LIKE '%{$s_search}%'";
			$s_where[] = " c.`description` LIKE '%{$s_search}%'";
			$s_where[] = " s.`sale_number` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`land_address` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`land_code` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`street` LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT co_khname FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		//$where.=" ORDER BY c.id DESC ";
		$order=" ORDER BY c.id DESC ";
		if(!empty($search['queryOrdering'])){
			if($search['queryOrdering']==1){
				$order =" ORDER BY c.for_date ASC ";
			}else if($search['queryOrdering']==2){
				$order =" ORDER BY  c.for_date DESC ";
			}else if($search['queryOrdering']==3){
				$order =" ORDER BY c.id ASC ";
			}else if($search['queryOrdering']==4){
				$order =" ORDER BY c.id DESC ";
			}
		}
		$search['is_closed'] = empty($search['is_closed'])?0:$search['is_closed'];
		if (!empty($search['is_closed'])){
			if($search['is_closed']!=1){
				$search['is_closed']=0;
			}
			$where.= " AND c.is_closed = ".$search['is_closed']."";
		}
		return $db->fetchAll($sql.$where.$order);
	}
	function getCommissionPaymentDetailList($search){
		$db = $this->getAdapter();
		$sql="SELECT
		pd.*
		,(SELECT  p.`project_name` FROM `ln_project` AS p WHERE (p.`br_id` = p.`branch_id`) LIMIT 1) AS branchName
		,(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=s.`client_id` LIMIT 1) AS customerName
		,(SELECT land_address FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS landCode
		,(SELECT street FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS street
		,(SELECT co_khname FROM `ln_staff` WHERE co_id=p.agency_id LIMIT 1) AS agencyNname
		,(SELECT tel FROM `ln_staff` WHERE co_id=p.agency_id LIMIT 1) AS agencyTel
		,(SELECT sex FROM `ln_staff` WHERE co_id=p.agency_id LIMIT 1) AS sex
	
		,(SELECT v.`name_kh` FROM `ln_view` AS v WHERE v.key_code = p.payment_method AND v.type = 2 LIMIT 1) AS paymentMethod
		,(SELECT bank_name FROM `st_bank` WHERE id =p.bank_id LIMIT 1) AS bank
		,p.`payment_method` AS payment_method
		,p.`bank_id` AS bankId
		,p.`cheque_no` AS chequeNo
		,p.`cheque_issuer` AS chequeIssuer
		,s.full_commission
		,p.date_payment
		,p.receipt_no
		,(SELECT  CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,'')) FROM rms_users WHERE id=p.user_id LIMIT 1 ) AS userName
		FROM `rms_commission_payment_detail` AS pd,
		rms_commission_payment AS p,
		ln_sale AS s
		WHERE s.id = pd.sale_id
		AND p.id = pd.payment_id
		";
	
		$Other =" ORDER BY p.id DESC ";
		$where="";
		$from_date =(empty($search['start_date']))? '1': " p.`date_payment` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " p.`date_payment` <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
		 
		$dbp = new Application_Model_DbTable_DbGlobal();
		if($search['co_khname']>0){
			//     		$where.= " AND s.`staff_id` = ".$search['co_khname'];
			$condiction = $dbp->getChildAgency($search['co_khname']);
			if (!empty($condiction)){
				$where.=" AND p.agency_id IN ($condiction)";
			}else{
				$where.=" AND p.agency_id=".$search['co_khname'];
			}
		}
		if($search['branch_id']>0){
			$where.= " AND p.`branch_id` = ".$search['branch_id'];
		}
		if($search['land_id']>0){
			$where.= " AND pd.house_id = ".$search['land_id'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] =" p.`receipt_no` LIKE '%{$s_search}%'";
			$s_where[] =" (SELECT land_address FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] =" (SELECT street FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT co_khname FROM `ln_staff` WHERE co_id=p.agency_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT co_code FROM `ln_staff` WHERE co_id=p.agency_id LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where.$Other);
	}
	function getAllIncome($search=null){
		if(empty($search['ordering'])){
			$search['ordering']=2;
		}
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		 
		$sql="
		SELECT
			id
			,(SELECT project_name FROM `ln_project` WHERE ln_project.br_id = branch_id LIMIT 1) AS branch_name
			,title
			,invoice
			,branch_id
			,(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id =ln_income.house_id LIMIT 1) as house_name
			,(SELECT name_kh FROM `ln_view` WHERE type=12 and key_code=category_id LIMIT 1) AS category_name
			,(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=payment_id LIMIT 1) AS payment_type
			,(SELECT bank_name FROM `st_bank` WHERE  id=ln_income.bank_id LIMIT 1) AS bank
			,bank_id AS bankId
			,payment_id
			,(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id=ln_income.client_id limit 1) AS client_name
			,cheque
			,total_amount
			,description
			,date
			,is_closed
			,(SELECT  first_name FROM rms_users WHERE rms_users.id=ln_income.user_id LIMIT 1 ) AS user_name
			,status
		FROM ln_income
		WHERE status=1 ";
	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
	
		/*
		 $order=" order by branch_id DESC ";
		if($search['ordering']==1){
		$order.=" , date DESC";
		}
		if($search['ordering']==2){
		$order.=" , id DESC";
		}
		*/
			
		$order=" order by branch_id DESC, date DESC";
		if(!empty($search['queryOrdering'])){
			if($search['queryOrdering']==1){
				$order =" ORDER BY branch_id DESC, date ASC ";
			}else if($search['queryOrdering']==2){
				$order =" ORDER BY branch_id DESC, date DESC ";
			}else if($search['queryOrdering']==3){
				$order =" ORDER BY branch_id DESC, id ASC ";
			}else if($search['queryOrdering']==4){
				$order =" ORDER BY branch_id DESC, id DESC ";
			}
		}
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " description LIKE '%{$s_search}%'";
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT land_address FROM `ln_properties` WHERE id =ln_income.house_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT street FROM `ln_properties` WHERE id =ln_income.house_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id =ln_income.house_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_kh FROM `ln_view` WHERE type=12 and key_code=category_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_kh FROM `ln_client` WHERE ln_client.client_id=ln_income.client_id limit 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['client_name']>0){
			$where.= " AND ln_income.client_id = ".$search['client_name'];
		}
		if(!empty($search['user_id']) AND $search['user_id']>0){
			$where.= " AND ln_income.user_id = ".$search['user_id'];
		}
		if($search['land_id']>0){
			$where.= " AND ln_income.house_id = ".$search['land_id'];
		}
		if($search['branch_id']>0){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if (!empty($search['payment_method'])){
			$where.= " AND payment_id = '".$search['payment_method']."'";
		}
		//     		if(@$search['category_id']>-1 AND !@empty($search['category_id'])){
		//     			$where.= " AND category_id = ".$search['category_id'];
		//     		}
		if(@$search['category_id']>-1 AND !@empty($search['category_id'])){
			$condiction = $dbp->getChildType($search['category_id']);
			if (!empty($condiction)){
				$where.=" AND category_id IN ($condiction)";
			}else{
				$where.=" AND category_id=".$search['category_id'];
			}
		}
		if (!empty($search['streetlist'])){
			$where.=" AND (SELECT street FROM `ln_properties` WHERE id =ln_income.house_id) = '".$search['streetlist']."'";
		}
			
		$search['is_closed'] = empty($search['is_closed'])?0:$search['is_closed'];
		if (!empty($search['is_closed'])){
			if($search['is_closed']!=1){
				$search['is_closed']=0;
			}
			$where.= " AND is_closed = ".$search['is_closed']."";
		}
	
	
		return $db->fetchAll($sql.$where.$order);
	}
	function getCollectPayment($search=null){
		$db= $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql = $dbp->getCollectPaymentSqlSt();
		$sql.= " AND crm.status= 1 ";
		$from_date =(empty($search['start_date']))? '1': " `crm`.`date_pay` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " `crm`.`date_pay` <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		 
	
		$where.=$dbp->getAccessPermission("`crm`.branch_id");
		 
		if($search['branch_id']>0){
			$where.= " AND `crm`.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['user_id']) AND $search['user_id']>0){
			$where.= " AND `crm`.user_id = ".$search['user_id'];
		}
		if($search['client_name']>0){
			$where.=" AND `crm`.client_id = ".$search['client_name'];
		}
		if($search['payment_method']>0){
			$where.=" AND `crm`.`payment_method` = ".$search['payment_method'];
		}
		 
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " `c`.`client_number`  LIKE '%{$s_search}%'";
			$s_where[] = " `c`.`name_kh`   LIKE '%{$s_search}%'";
			$s_where[] = " `c`.`name_en` LIKE '%{$s_search}%'";
			$s_where[] = " `crm`.`receipt_no` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if (!empty($search['land_id']) AND $search['land_id']>0){
			$where.=" AND `sl`.`house_id` = '".$search['land_id']."'";
		}
		if (!empty($search['streetlist'])){
			$where.=" AND `l`.`street` = '".$search['streetlist']."'";
		}
		//$where.=" ORDER BY  `crm`.`date_pay` DESC, crm.id DESC";
		$order=" ORDER BY  `crm`.`date_pay` DESC, crm.id DESC";
		if(!empty($search['queryOrdering'])){
			if($search['queryOrdering']==1){
				$order =" ORDER BY `crm`.date_pay ASC ";
			}else if($search['queryOrdering']==2){
				$order =" ORDER BY `crm`.date_pay DESC ";
			}else if($search['queryOrdering']==3){
				$order =" ORDER BY `crm`.id ASC ";
			}else if($search['queryOrdering']==4){
				$order =" ORDER BY `crm`.id DESC ";
			}
		}
	
		return $db->fetchAll($sql.$where.$order);
	}
	function getExpensebyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT *,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
		(SELECT logo FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS photo,
		(SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name,
		(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE rms_users.id=ln_expense.user_id LIMIT 1) AS user_name,
		(SELECT s.name FROM `ln_supplier` AS s WHERE s.id = ln_expense.supplier_id LIMIT 1) AS supplier_name,
		(SELECT s.phone FROM `ln_supplier` AS s WHERE s.id = ln_expense.supplier_id LIMIT 1) AS supplierPhone,
		(SELECT s.email FROM `ln_supplier` AS s WHERE s.id = ln_expense.supplier_id LIMIT 1) AS supplierEmail,
		(SELECT v.name_kh FROM ln_view AS v WHERE v.type=2 AND key_code=payment_id LIMIT 1) AS payment_method,
		CASE
		WHEN  cancelSale_id > 0 THEN (SELECT c.name_kh FROM ln_client AS c WHERE c.client_id = (SELECT s.client_id FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1 ) LIMIT 1)
		ELSE  (SELECT ls.name FROM `ln_supplier` AS ls WHERE ls.id = supplier_id LIMIT 1)
		END AS supplierOrCustomer,
		(SELECT c.name_kh FROM ln_client AS c WHERE c.client_id = (SELECT s.client_id FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1 ) LIMIT 1) AS cancelCustomer,
		(SELECT CONCAT(p.land_address,',',p.street) FROM `ln_properties` AS p WHERE p.id = (SELECT s.house_id FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1 ) LIMIT 1) AS cancelProperty
			
		FROM ln_expense where id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		return $db->fetchRow($sql);
	}
	
	function getExpenseDocumentbyid($search){
		$db = $this->getAdapter();
		$id = empty($search['id'])?0:$search['id'];
		$documentforType = empty($search['documentforType'])?1:$search['documentforType'];
		$sql=" SELECT * FROM ln_expense_document WHERE exspense_id=$id AND documentforType = $documentforType ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(title,' ','')   	LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql);
	}
	
	function submitClosingEngryIncome($data){
		$db = $this->getAdapter();
	
	
		$dbgb = new Application_Model_DbTable_DbGlobal();
		if(!empty($data['id_selected'])){
			$ids = explode(',', $data['id_selected']);
			$key = 1;
			$arr = array(
					"is_closed"=>1,
			);
			$arrEE = array(
					"is_close"=>1,
			);
			foreach ($ids as $i){
				if ($data['type_record'.$i]==1){ //1= Other Income
					$this->_name="ln_income";
					$where="id= ".$i;
					$this->update($arr, $where);
				}else if ($data['type_record'.$i]==2){ //2= otherincomepayment Income
					if (!empty($data['id_'.$i])){
	
						$this->_name="ln_otherincomepayment";
						$where=" id= ".$data['id_'.$i];
						$this->update($arrEE, $where);
					}
				}
			}
		}
	}
	function submitClosingEngryExpense($data){
		$db = $this->getAdapter();
	
		$dbgb = new Application_Model_DbTable_DbGlobal();
		if(!empty($data['id_selected'])){
			$ids = explode(',', $data['id_selected']);
			$arr = array(
					"is_closed"=>1,
			);
			$arrEE = array(
					"is_close"=>1,
			);
			foreach ($ids as $i){
				if ($data['type_record'.$i]==1){ //1= Other Expense
					if (!empty($data['id_'.$i])){
						$this->_name="ln_expense";
						$where=" id= ".$data['id_'.$i];
						$this->update($arr, $where);
					}
				}else if ($data['type_record'.$i]==2){ //2= Commission
					if (!empty($data['id_'.$i])){
						$this->_name="ln_comission";
						$where=" id= ".$data['id_'.$i];
						$this->update($arr, $where);
					}
				}else if ($data['type_record'.$i]==3){ //3= Commission Payment
					if (!empty($data['id_'.$i])){
						$arr['closed_by']=$dbgb->getUserId();
						$this->_name="rms_commission_payment";
						$where=" id= ".$data['id_'.$i];
						$this->update($arr, $where);
					}
				}else if ($data['type_record'.$i]==4){ //4= otherincomepayment expense
					if (!empty($data['id_'.$i])){
	
						$this->_name="ln_otherincomepayment";
						$where=" id= ".$data['id_'.$i];
						$this->update($arrEE, $where);
					}
				}else if ($data['type_record'.$i]==5){ //5= Expense Payment
					if (!empty($data['id_'.$i])){
						$arr['closed_by']=$dbgb->getUserId();
						$this->_name="rms_expense_payment";
						$where=" id= ".$data['id_'.$i];
						$this->update($arr, $where);
					}
				}
	
			}
		}
	}
	function getPurchasePaymentById($id){
		$db=$this->getAdapter();
		$sql="SELECT pp.*,
		(SELECT b.project_name FROM `ln_project` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name,
		(SELECT s.name FROM `ln_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) AS supplier_name,
		(SELECT s.phone FROM `ln_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) AS tel,
		(SELECT s.email FROM `ln_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) AS email
		FROM `rms_expense_payment` AS pp WHERE pp.id = $id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission('pp.branch_id');
		return $db->fetchRow($sql);
	}
	
	function getExpenseDetail($id){
		$db=$this->getAdapter();
		$sql="SELECT *,
		(SELECT ide.title FROM `rms_product` AS ide WHERE ide.id = pro_id LIMIT 1) AS pro_name
		FROM ln_expense_detail WHERE expense_id=$id";
		return $db->fetchAll($sql);
	}
	function getExpenseCategory($search,$withdraw=null){
		$db = $this->getAdapter();
		$sql="SELECT ex.`category_id`,
		(SELECT v.parent_id FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = ex.`category_id` LIMIT 1) as parent,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type =13 AND v.key_code =
		(SELECT v.parent_id FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = ex.`category_id` LIMIT 1) LIMIT 1) as parent_title,
		SUM(total_amount) AS total_amount,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = ex.`category_id` LIMIT 1) AS category_name,
		ex.`date`
		FROM `ln_expense` AS ex WHERE 1 AND ex.status=1 ";
		//$sql.=" AND (SELECT v.capital_widthdrawal FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = ex.`category_id` LIMIT 1) = 0 ";
		 
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		 
		$order =" GROUP BY ex.`category_id` ORDER BY
		(SELECT v.parent_id FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = ex.`category_id` LIMIT 1) ASC,
		ex.`category_id` ASC";
		$where="";
		$from_date =(empty($search['start_date']))? '1': " ex.`date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " ex.`date` <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$where.=" AND branch_id=".$search['branch_id'];
		}
		if(!empty($withdraw)){
			if($withdraw==1){
				$where.=" AND (SELECT v.capital_widthdrawal FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = ex.`category_id` LIMIT 1)=1";
			}else if($withdraw==2){
				$where.=" AND (SELECT v.capital_widthdrawal FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = ex.`category_id` LIMIT 1)=0";
			}
		}
	
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllComissionExpense($search){//for income statement
		$db = $this->getAdapter();
		$sql="SELECT co.`category_id`,
		SUM(total_amount) AS total_amount,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = co.`category_id` LIMIT 1) AS category_name,
		co.`date` FROM `ln_comission` AS co WHERE 1 AND co.status=1 ";
		 
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		 
		$order =" GROUP BY co.`category_id` ORDER BY co.`category_id` ASC";
		$where="";
		$from_date =(empty($search['start_date']))? '1': " co.`for_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " co.`for_date` <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		 
		if (!empty($search['property_type'])){
			$where.=" AND (SELECT pt.property_type FROM `ln_properties` AS pt WHERE pt.id = (SELECT s.house_id FROM `ln_sale` AS s WHERE co.sale_id=s.id LIMIT 1 ) LIMIT 1 ) =".$search['property_type'];
		}
		if (!empty($search['streetlist'])){
			$st = explode(",", $search['streetlist']);
			$tags="";
			if (!empty($st)) foreach ($st as $ss){
				if (empty($tags)){
					$tags = "'".$ss."'";
				}else{
					if (!empty($ss)){
						$tags=$tags.",'".$ss."'";
					}
				}
			}
			//     		$where.=" AND (SELECT pt.street FROM `ln_properties` AS pt WHERE pt.id = (SELECT s.house_id FROM `ln_sale` AS s WHERE co.sale_id=s.id LIMIT 1 ) LIMIT 1  )= '".$search['streetlist']."'";
			$where.=" AND (SELECT pt.street FROM `ln_properties` AS pt WHERE pt.id = (SELECT s.house_id FROM `ln_sale` AS s WHERE co.sale_id=s.id LIMIT 1 ) LIMIT 1  ) IN ( ".$tags.")";
		}
		 
		if($search['branch_id']>0){
			$where.=" AND branch_id=".$search['branch_id'];
		}
		if(@$search['category_id_expense']>-1 AND !@empty($search['category_id_expense'])){
			$where.= " AND category_id = ".$search['category_id_expense'];
		}
		 
		return $db->fetchAll($sql.$where.$order);
	}
	function geIncomeFromSale($search,$money_type=-1){
		$db = $this->getAdapter();
		$sql="SELECT SUM(crm.`recieve_amount`) AS recieve_amount FROM `ln_client_receipt_money` AS crm WHERE 1 ";
		$where="";
		 
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("branch_id");
		 
		if($money_type>-1){
			$where.=" AND field3 = $money_type ";
		}
		 
		$from_date =(empty($search['start_date']))? '1': " crm.`date_pay` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " crm.`date_pay` <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
		 
		if (!empty($search['property_type'])){
			$where.=" AND (SELECT pt.property_type FROM `ln_properties` AS pt WHERE pt.id = (SELECT s.house_id FROM `ln_sale` AS s WHERE crm.sale_id=s.id LIMIT 1 ) LIMIT 1 ) =".$search['property_type'];
		}
		if (!empty($search['streetlist'])){
			$st = explode(",", $search['streetlist']);
			$tags="";
			if (!empty($st)) foreach ($st as $ss){
				if (empty($tags)){
					$tags = "'".$ss."'";
				}else{
					if (!empty($ss)){
						$tags=$tags.",'".$ss."'";
					}
				}
			}
			$where.=" AND (SELECT pt.street FROM `ln_properties` AS pt WHERE pt.id = (SELECT s.house_id FROM `ln_sale` AS s WHERE crm.sale_id=s.id LIMIT 1 ) LIMIT 1  ) IN ( ".$tags.")";
		}
		if($search['branch_id']>0){
			$where.=" AND branch_id=".$search['branch_id'];
		}
		return $db->fetchRow($sql.$where);
	}
	function geOtherIncome($cate_id){
		$db = $this->getAdapter();
		$sql="SELECT
		SUM(ic.`total_amount`) AS total_amount
		FROM `ln_income` AS ic WHERE ic.`category_id`=$cate_id";
		return $db->fetchOne($sql);
	}
	function geOtherExpense($cate_id){
		$db = $this->getAdapter();
		$sql="SELECT SUM(ex.`total_amount`) AS `total_amount` FROM `ln_expense` AS ex WHERE  ex.`category_id`=$cate_id";
		return $db->fetchOne($sql);
	}
	
	function getSumCommission($search){
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "c.`for_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "c.`for_date` <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql ='SELECT c.`id`,
		p.`project_name`,
		SUM(c.total_amount) AS total_amount
		FROM `ln_comission` AS c ,
		`ln_sale` AS s,
		`ln_project` AS p,
		`ln_properties` AS pro,
		`ln_client` AS clie
		WHERE s.`id` = c.`sale_id` AND p.`br_id` = c.`branch_id` AND pro.`id` = s.`house_id` AND
		clie.`client_id` = s.`client_id` ';
		if($search['branch_id']>0){
			$where.= " AND c.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['co_khname']) AND $search['co_khname']>0){
			$where.= " AND c.staff_id = ".$search['co_khname'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " clie.`client_number` LIKE '%{$s_search}%'";
			$s_where[] = " clie.`name_kh` LIKE '%{$s_search}%'";
			$s_where[] = " c.`description` LIKE '%{$s_search}%'";
			$s_where[] = " s.`sale_number` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`land_address` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`land_code` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`street` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where);
	}
	function getCommissionBalance($search=null){
		$db = $this->getAdapter();
		$sql="SELECT
		((SELECT COALESCE(SUM(c.`total_amount`),0) FROM `ln_comission` AS c WHERE s.`id` = c.`sale_id` AND c.status=1 LIMIT 1)+(SELECT COALESCE(SUM(cpd.payment_amount),0) FROM `rms_commission_payment_detail` as cpd, rms_commission_payment AS cp WHERE cp.id = cpd.payment_id AND cpd.sale_id=s.id AND cp.status=1 LIMIT 1)) AS totoal_comminssion,
		SUM(s.`comission`) AS total_sale_commission,
		s.id AS sale_id,
		s.`full_commission`,
		s.`branch_id`,
		s.buy_date,
		s.is_cancel,
		(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = s.`branch_id` LIMIT 1) AS branch_name,
		(SELECT cu.name_kh FROM `ln_client` AS cu WHERE cu.client_id = s.`client_id` LIMIT 1) AS cutomer_name,
		(SELECT p.land_code FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS land_code,
		(SELECT p.street FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS street,
		(SELECT p.land_address FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS land_address,
		(SELECT st.co_khname FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) AS co_khname,
		(SELECT st.tel FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) AS tel,
		(SELECT st.sex FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) AS sex,
		s.`price_sold`,
		(SELECT SUM(crm.total_principal_permonthpaid) FROM `ln_client_receipt_money` AS crm WHERE crm.sale_id = s.id  GROUP BY crm.sale_id LIMIT 1) AS total_sale_paid
		FROM
		`ln_sale` AS s
		WHERE full_commission>0 ";
		$where ="";
	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("s.`branch_id`");
	
		$from_date =(empty($search['start_date']))? '1': " s.`buy_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.`buy_date` <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
		if($search['co_khname']>0){
			//     			$where.= " AND s.`staff_id` = ".$search['co_khname'];
			$condiction = $dbp->getChildAgency($search['co_khname']);
			if (!empty($condiction)){
				$where.=" AND s.staff_id IN ($condiction)";
			}else{
				$where.=" AND s.staff_id=".$search['co_khname'];
			}
		}
		if($search['branch_id']>0){
			$where.= " AND s.`branch_id` = ".$search['branch_id'];
		}
		if($search['land_id']>0){
			$where.= " AND s.`house_id` = ".$search['land_id'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] =" s.`sale_number` LIKE '%{$s_search}%'";
			$s_where[]=" s.`receipt_no` LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT st.tel FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT st.co_khname FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT st.co_code FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['commission_type'])){
			if ($search['commission_type']==1){
				//$where.=" AND s.`full_commission` = (SUM(c.`total_amount`)+ s.`comission`)";
			}else if ($search['commission_type']==2){
				//$where.=" AND s.`full_commission` > (SUM(c.`total_amount`)+ s.`comission`)";
			}
		}
		$groupby =" GROUP BY s.`staff_id`,s.`id` ORDER BY s.`id` DESC";
		return $db->fetchAll($sql.$where.$groupby);
	}
	function getTotalComissionPayment($search){//for income statement
		$db = $this->getAdapter();
		$sql="SELECT SUM(total_paid) FROM `rms_commission_payment` WHERE status=1 ";
		 
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		 
		$from_date =(empty($search['start_date']))? '1': " date_payment >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		 
		if($search['branch_id']>0){
			$where.=" AND branch_id=".$search['branch_id'];
		}
		return $db->fetchOne($sql.$where);
	}
	function getSaleAmountCreditPayment($search){
		$db = $this->getAdapter();
		$sql="SELECT SUM(crm.`total_amount`) AS recieve_amount FROM `ln_credit` AS crm WHERE status=1 ";
		$where="";
			
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("branch_id");
			
		$from_date =(empty($search['start_date']))? '1': " crm.`for_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " crm.`for_date` <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
			
		if (!empty($search['property_type'])){
			$where.=" AND (SELECT pt.property_type FROM `ln_properties` AS pt WHERE pt.id = (SELECT s.house_id FROM `ln_sale` AS s WHERE crm.sale_id=s.id LIMIT 1 ) LIMIT 1 ) =".$search['property_type'];
		}
		if (!empty($search['streetlist'])){
			$st = explode(",", $search['streetlist']);
			$tags="";
			if (!empty($st)) foreach ($st as $ss){
				if (empty($tags)){
					$tags = "'".$ss."'";
				}else{
					if (!empty($ss)){
						$tags=$tags.",'".$ss."'";
					}
				}
			}
			$where.=" AND (SELECT pt.street FROM `ln_properties` AS pt WHERE pt.id = (SELECT s.house_id FROM `ln_sale` AS s WHERE crm.sale_id=s.id LIMIT 1 ) LIMIT 1  ) IN ( ".$tags.")";
		}
		if($search['branch_id']>0){
			$where.=" AND crm.branch_id=".$search['branch_id'];
		}
		return $db->fetchOne($sql.$where);
	}
	function getIncomeCategory($search){
		$db = $this->getAdapter();
		$sql="SELECT ic.`category_id`,
		(SELECT v.parent_id FROM `ln_view` AS v WHERE v.type =12 AND v.key_code = ic.`category_id` LIMIT 1) as parent,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type =12 AND v.key_code =
		(SELECT v.parent_id FROM `ln_view` AS v WHERE v.type =12 AND v.key_code = ic.`category_id` LIMIT 1) LIMIT 1) as parent_title,
		SUM(ic.`total_amount`) AS total_amount,ic.is_beginning,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type =12 AND v.key_code = ic.`category_id` LIMIT 1) AS category_name,
		ic.`date` FROM `ln_income` AS ic WHERE ic.status=1 ";
		 
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		 
		$order =" GROUP BY ic.`category_id` ,ic.is_beginning  ORDER BY
		(SELECT v.parent_id FROM `ln_view` AS v WHERE v.type =12 AND v.key_code = ic.`category_id` LIMIT 1) ASC,
		ic.`category_id` ASC";
		$where="";
		$from_date =(empty($search['start_date']))? '1': " ic.`date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " ic.`date` <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$where.=" AND branch_id=".$search['branch_id'];
		}
		return $db->fetchAll($sql.$where.$order);
	}
	function getIncomeRepairhouse($search,$typeRecord=null){
		$db = $this->getAdapter();
		$sql="SELECT icp.cate_type,icp.category AS category_id,SUM(icp.total_paid) AS total_amount,
		(SELECT v.name_kh FROM ln_view AS v WHERE v.type=icp.cate_type AND v.key_code=icp.category LIMIT 1) AS category_name
		FROM `ln_otherincomepayment` AS icp
		WHERE icp.status=1  ";//AND icp.cate_type=$cate_type
	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("icp.branch_id");
	
		$order =" GROUP BY icp.cate_type,icp.category ORDER BY icp.category ";
		$where="";
		$from_date =(empty($search['start_date']))? '1': " icp.`for_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " icp.`for_date` <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if($search['branch_id']>0){
			$where.=" AND icp.branch_id=".$search['branch_id'];
		}
		if (!empty($typeRecord)){
			// $typeRecord 12 = income,$typeRecord 13 = Expense
			$where.= " AND icp.cate_type =$typeRecord ";
		}else{
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getOtherIncomePaymentById($id){
		$db = $this->getAdapter();
		$sql="SELECT 
		op.id,
		op.branch_id,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =op.branch_id LIMIT 1) AS branch_name,
		(SELECT logo FROM `ln_project` WHERE ln_project.br_id =op.branch_id LIMIT 1) AS photo,
		(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =oi.client_id LIMIT 1) AS client_name,
		(SELECT CONCAT(p.land_address,',',p.street) FROM `ln_properties` AS p WHERE p.id=oi.house_id LIMIT 1) AS house_name,
		op.receipt_no AS invoice,
		(SELECT v.name_kh FROM ln_view AS v WHERE v.type=op.cate_type AND v.key_code=op.category LIMIT 1) AS category_name,
		op.title_income AS title,
		op.total_paid AS total_amount,
		op.cheque,
		op.note AS description,
		op.for_date AS `date`,
			
		(SELECT vt.name FROM `ln_view_type` AS vt WHERE vt.id=op.cate_type LIMIT 1) AS typecate,
		op.balance,
		op.remain,
		(SELECT v.name_kh FROM ln_view AS v WHERE v.type=2 AND key_code=op.payment_method LIMIT 1) AS payment_method,
		op.status,
		(SELECT  CONCAT(last_name,' ',first_name)FROM rms_users WHERE id=op.user_id LIMIT 1 ) AS user_name
		FROM `ln_otherincomepayment` AS op,
		`ln_otherincome` AS oi
		WHERE oi.id = op.otherincome_id
		AND op.id=$id  ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("op.branch_id");
		$sql.=" LIMIT 1 ";
	
		return $db->fetchRow($sql);
	}
	function getAllExpensebyCate($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		 
		$sql=" SELECT id,
		(SELECT v.parent_id FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = `category_id` LIMIT 1) as parent,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type =13 AND v.key_code =
		(SELECT v.parent_id FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = `category_id` LIMIT 1) LIMIT 1) as parent_title,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
		(SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name,
		SUM(total_amount) AS total_amount
		FROM ln_expense WHERE status=1 ";
		 
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
	
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " description LIKE '%{$s_search}%'";
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		//     		if(@$search['category_id_expense']>-1 AND !@empty($search['category_id_expense'])){
		//     			$where.= " AND category_id = ".$search['category_id_expense'];
		//     		}
		if(@$search['category_id_expense']>-1 AND !@empty($search['category_id_expense'])){
			$condiction = $dbp->getChildType($search['category_id_expense']);
			if (!empty($condiction)){
				$where.=" AND category_id IN ($condiction)";
			}else{
				$where.=" AND category_id=".$search['category_id_expense'];
			}
		}
		if(!empty($search['user_id']) AND $search['user_id']>0){
			$where.= " AND ln_expense.user_id = ".$search['user_id'];
		}
		if($search['branch_id']>0){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if(@$search['payment_type']>0){
			$where.= " AND payment_id = ".$search['payment_type'];
		}
		if (!empty($search['supplier_id'])){
			$where.= " AND supplier_id = ".$search['supplier_id'];
		}
		if (!empty($search['cheque_issuer_search'])){
			$where.= " AND cheque_issuer = '".$search['cheque_issuer_search']."'";
		}
		$where.=" group by category_id ";
		$order=" ORDER BY (SELECT v.parent_id FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = `category_id` LIMIT 1) ASC,
		`category_id` ASC ,date DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getIncomeById($income_id){
		$db = $this->getAdapter();
		//     		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$sql=" SELECT id,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
		(SELECT logo FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS photo,
		title, invoice,branch_id,
		(SELECT CONCAT(land_address,',',street)FROM `ln_properties` WHERE id =ln_income.house_id) as house_name,
		(SELECT name_kh FROM `ln_view` WHERE type=12 and key_code=category_id limit 1) AS category_name,
		(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id=ln_income.client_id limit 1) AS client_name,
		cheque,total_amount,description,date,
		(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE rms_users.id=ln_income.user_id LIMIT 1) AS user_name,
		(SELECT v.name_kh FROM ln_view AS v WHERE v.type=2 AND key_code=payment_id LIMIT 1) AS payment_method,
		status,
		qty,unit_price,
		amount,
		DATE_FORMAT(from_date,'%d/%m/%Y') AS from_date,
		DATE_FORMAT(next_date,'%d/%m/%Y') next_date
		FROM ln_income
		WHERE status=1 AND id =".$income_id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
	
		return $db->fetchRow($sql);
	}
	
	function getCommissionPaymentById($id){
		$db=$this->getAdapter();
		$sql="SELECT cp.*,
		(SELECT  p.`project_name` FROM `ln_project` AS p WHERE (p.`br_id` = cp.`branch_id`) LIMIT 1) AS branchName,
		(SELECT co_khname FROM `ln_staff` WHERE co_id=cp.agency_id LIMIT 1) AS agencyNname,
		(SELECT name_kh FROM `ln_view` WHERE TYPE=13 AND key_code=cp.category LIMIT 1) AS categoryName,
		(SELECT name_kh FROM `ln_view` WHERE TYPE=2 AND key_code=cp.payment_method LIMIT 1) AS paymentType,
		(SELECT  CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,'')) FROM rms_users WHERE id=cp.user_id LIMIT 1 ) AS userName
		FROM rms_commission_payment AS cp
		WHERE cp.id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("cp.`branch_id`");
		$sql.=" LIMIT 1 ";
	
		return $db->fetchRow($sql);
	}
	function getCommissionPaymentDetail($payment_id){
		$db = $this->getAdapter();
		$sql="SELECT pd.*,
		(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=s.`client_id` LIMIT 1) AS customerName,
		(SELECT land_address FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS landCode,
		(SELECT street FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS street,
		(SELECT co_khname FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS agencyNname,
		(SELECT tel FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS agencyTel,
		s.full_commission
		FROM `rms_commission_payment_detail` AS pd,
		ln_sale AS s
		WHERE s.id = pd.sale_id AND pd.payment_id =$payment_id ";
		return $db->fetchAll($sql);
	}
	function getSaleCommission($search=null){
		$db = $this->getAdapter();
		$sql="SELECT
		((SELECT COALESCE(SUM(c.`total_amount`),0) FROM `ln_comission` AS c WHERE s.`id` = c.`sale_id` AND c.status=1 )+(SELECT COALESCE(SUM(cpd.payment_amount),0) FROM `rms_commission_payment_detail` as cpd, rms_commission_payment AS cp WHERE cp.id = cpd.payment_id AND cpd.sale_id=s.id AND cp.status=1 LIMIT 1)) AS totoal_comminssion,
		SUM(s.`comission`) AS total_sale_commission,
		s.`full_commission`,
		s.`branch_id`,
		(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = s.`branch_id` LIMIT 1) AS branch_name,
		(SELECT cu.name_kh FROM `ln_client` AS cu WHERE cu.client_id = s.`client_id` LIMIT 1) AS cutomer_name,
		(SELECT p.land_code FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS land_code,
		(SELECT p.street FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS street,
		(SELECT p.land_address FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS land_address,
		(SELECT st.co_khname FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) AS co_khname,
		(SELECT st.tel FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) AS tel,
		(SELECT st.sex FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) AS sex,
		s.`price_sold`,
		(SELECT SUM(crm.total_principal_permonthpaid) FROM `ln_client_receipt_money` AS crm WHERE crm.sale_id = s.id  GROUP BY crm.sale_id LIMIT 1) AS total_sale_paid
		FROM
		`ln_sale` AS s
		WHERE full_commission>0 AND s.is_cancel = 0 AND s.`staff_id` >0 AND payment_id !=1
		AND s.`full_commission` > (SELECT CASE WHEN SUM(c.`total_amount`)  IS NULL THEN 0 ELSE SUM(c.`total_amount`) END  FROM `ln_comission` AS c WHERE s.`id` = c.`sale_id` AND c.status=1 )
		";
		$where ="";
	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("s.`branch_id`");
	
		$from_date =(empty($search['start_date']))? '1': " s.`buy_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.`buy_date` <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
		if($search['co_khname']>0){
			// 			$where.= " AND s.`staff_id` = ".$search['co_khname'];
			$condiction = $dbp->getChildAgency($search['co_khname']);
			if (!empty($condiction)){
				$where.=" AND s.staff_id IN ($condiction)";
			}else{
				$where.=" AND s.staff_id=".$search['co_khname'];
			}
		}
		if($search['branch_id']>0){
			$where.= " AND s.`branch_id` = ".$search['branch_id'];
		}
		if($search['land_id']>0){
			// 			$where.= " AND s.`house_id` = ".$search['land_id'];
			$where.= " AND (s.`house_id` = ".$search['land_id']." OR (SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) LIKE '%".$search['land_id']."%')";
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] =" s.`sale_number` LIKE '%{$s_search}%'";
			$s_where[]=" s.`receipt_no` LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT st.tel FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT st.co_khname FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[]=" (SELECT st.co_code FROM `ln_staff` AS st WHERE st.co_id = s.`staff_id` LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		// 		if(!empty($search['commission_type'])){
		// 			if ($search['commission_type']==1){
		// 				//$where.=" AND s.`full_commission` = (SUM(c.`total_amount`)+ s.`comission`)";
		// 			}else if ($search['commission_type']==2){
		// 				//$where.=" AND s.`full_commission` > (SUM(c.`total_amount`)+ s.`comission`)";
		// 			}
		// 		}
		$groupby =" GROUP BY s.`staff_id`,s.`id` ORDER BY s.`id` DESC";
		return $db->fetchAll($sql.$where.$groupby);
	}
	
	
	function getCustomerNearlyPaymentBoreyFee($search){
    	$db=$this->getAdapter();
    	
    	
		$sql= "
		SELECT 
			inc.* 
			,inc.from_date 
			,COALESCE((SELECT inc1.next_date FROM `ln_income` AS inc1 WHERE s.id = inc1.sale_id ORDER BY inc1.next_date DESC LIMIT 1 ),inc.next_date) AS nextDate
			,inc.unit_price AS unitPrice
			,(SELECT p.`project_name` FROM `ln_project` AS p WHERE p.`br_id` = inc.`branch_id` LIMIT 1) AS branchName
			,(SELECT `c`.`phone` FROM `ln_client` `c` WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1) AS `clientPhone`
			,(SELECT `c`.`name_kh` FROM `ln_client` `c` WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1) AS `clientName`
				  
			,`l`.`land_code`                AS `landCode`
			,`l`.`land_address`             AS `landAddress`
			,`l`.`street`                   AS `street`
		FROM 
			`ln_income` AS inc 
			JOIN ln_sale AS s ON s.id = inc.sale_id 
			LEFT JOIN ln_properties AS l ON `s`.`house_id` = `l`.`id`
			
		WHERE inc.`status` =1
			AND inc.`incomeType` =2
		";
		
    	
    	$from_date =(empty($search['start_date']))? '1': " COALESCE((SELECT inc1.next_date FROM `ln_income` AS inc1 WHERE s.id = inc1.sale_id ORDER BY inc1.next_date DESC LIMIT 1 ),inc.next_date) <= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " COALESCE((SELECT inc1.next_date FROM `ln_income` AS inc1 WHERE s.id = inc1.sale_id ORDER BY inc1.next_date DESC LIMIT 1 ),inc.next_date) <= '".$search['end_date']." 23:59:59'";
    	$sql.= " AND ".$from_date." AND ".$to_date;
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("inc.branch_id");
    	
		$order=" GROUP BY s.id ";
    	$orderBy=" ORDER BY inc.next_date DESC ,inc.sale_id ASC, inc.id ASC";
		if(!empty($search['queryOrdering'])){
			if($search['queryOrdering']==1){
				$orderBy =" ORDER BY inc.branch_id DESC, inc.date ASC ";
			}else if($search['queryOrdering']==2){
				$orderBy =" ORDER BY inc.branch_id DESC, inc.date DESC ";
			}else if($search['queryOrdering']==3){
				$orderBy =" ORDER BY inc.branch_id DESC, inc.id ASC ";
			}else if($search['queryOrdering']==4){
				$orderBy =" ORDER BY inc.branch_id DESC, inc.id DESC ";
			}
		}
		$order.=$orderBy;
		
		if(empty($search)){
			return $db->fetchAll($sql.$order);
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " inc.total_amount LIKE '%{$s_search}%'";
			$s_where[] = " `l`.`land_code`      LIKE '%{$s_search}%'";
			$s_where[] = " `l`.`land_address`  LIKE '%{$s_search}%'";
			$s_where[] = " `l`.`street`         LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT `c`.`name_kh` FROM `ln_client` `c` WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1)         LIKE '%{$s_search}%'";
			$sql .=' AND  ('.implode(' OR ',$s_where).')';
		}
		if($search['client_name']>0){
			$sql.= " AND inc.client_id = ".$search['client_name'];
		}
		if(!empty($search['user_id']) AND $search['user_id']>0){
			$sql.= " AND inc.user_id = ".$search['user_id'];
		}
		if($search['land_id']>0){
			$sql.= " AND inc.house_id = ".$search['land_id'];
		}
		if($search['branch_id']>0){
			$sql.= " AND inc.branch_id = ".$search['branch_id'];
		}
		
    	return $db->fetchAll($sql.$order);
    }
	
	public function getComissionById($id){
		$db = $this->getAdapter();
		$sql= "SELECT c.*,s.`full_commission`,
		(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = c.`branch_id` LIMIT 1) AS brach_name,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type=13 AND v.key_code = c.`category_id` LIMIT 1) AS cate_title,
		(SELECT cu.name_kh FROM `ln_client` AS cu WHERE cu.client_id = s.`client_id` LIMIT 1) AS cutomer_name,
		(SELECT p.land_code FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS land_code,
		(SELECT p.street FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS street,
		(SELECT p.land_address FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS land_address,
		(SELECT st.co_khname FROM `ln_staff` AS st WHERE st.co_id = c.`staff_id` LIMIT 1) AS co_khname,
		
			(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=c.payment_id limit 1) AS payment_type,
			
		(SELECT CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,'')) FROM rms_users WHERE id = c.user_id LIMIT 1 ) AS user_name
		 FROM 
		`ln_comission` AS c,
		`ln_sale` AS s
		WHERE s.`id` = c.`sale_id` AND c.`id` = ".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("c.`branch_id`");
		
		return $db->fetchRow($sql);
	}
	function getPurchasePaymentDetail($payment_id){
    	$db = $this->getAdapter();
    	$sql="SELECT pd.*,
    	(SELECT p.invoice FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS supplier_no,
    	(SELECT p.other_invoice FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS other_invoice
    	FROM `rms_expense_payment_detail` AS pd WHERE pd.payment_id =$payment_id ";
    	return $db->fetchAll($sql);
    }
	
 }