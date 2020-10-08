<?php

class Incexp_Model_DbTable_DbComission extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_sale_cancel';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}

	public function getComissionSale($search=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		
		try{
		$from_date =(empty($search['from_date_search']))? '1': "c.`for_date` >= '".$search['from_date_search']." 00:00:00'";
		$to_date = (empty($search['to_date_search']))? '1': "c.`for_date` <= '".$search['to_date_search']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql ='SELECT c.`id`,
		    p.`project_name`,
			clie.`name_kh` AS client_name,
			(SELECT protype.type_nameen FROM `ln_properties_type` AS protype WHERE protype.id = pro.`property_type` LIMIT 1) AS property_type,
			pro.`land_address`,pro.`street`,
			s.price_sold,
			(SELECT co_khname FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS staff_name,
			c.total_amount,
			(SELECT name_kh from ln_view where type=13 AND key_code=c.category_id LIMIT 1) as expense_type,
			c.`for_date` ';
		$sql.=$dbp->caseStatusShowImage("c.`status`");
		$sql.=" ,(SELECT  first_name FROM rms_users WHERE rms_users.id=c.user_id limit 1 ) AS user_name 
				FROM `ln_comission` AS c , `ln_sale` AS s, `ln_project` AS p,`ln_properties` AS pro,
			`ln_client` AS clie
			WHERE s.`id` = c.`sale_id` AND p.`br_id` = c.`branch_id` AND pro.`id` = s.`house_id` AND
			clie.`client_id` = s.`client_id` ";
		
		if($search['branch_id_search']>-1){
			$where.= " AND c.branch_id = ".$search['branch_id_search'];
		}
		if($search['staff_id']>0){
// 			$where.= " AND c.staff_id = ".$search['staff_id'];
			$condiction = $dbp->getChildAgency($search['staff_id']);
			if (!empty($condiction)){
				$where.=" AND c.staff_id IN ($condiction)";
			}else{
				$where.=" AND c.staff_id=".$search['staff_id'];
			}
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
		
		if($search['land_id']>0){
			$where.= " AND (pro.`id` = ".$search['land_id']." OR pro.old_land_id LIKE '%".$search['land_id']."%')";
		}
		if($search['status']>-1){
			$where.= " AND c.status = ".$search['status'];
		}
		
		$where.=$dbp->getAccessPermission("c.`branch_id`");
		$order=' ORDER BY c.`for_date` DESC,c.id DESC ';
		return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function addSaleComission($data){
		try{
			$db= $this->getAdapter();
			$expenid='';
			if($data['return_back']>0){
				$dbexpense = new Incexp_Model_DbTable_DbExpense();
				$invoice = $dbexpense->getInvoiceNo($data['branch_id']);
				$dbsale = new Loan_Model_DbTable_DbLandpayment();
				$sale_id = empty($data['sale_no'])?$data['sale_client']:$data['sale_no'];
				$row = $dbsale->getTranLoanByIdWithBranch($sale_id,null);
				$title="";
				$arr1 = array(
					'branch_id'		=>$data['branch_id'],
					'sale_id'	    =>$sale_id,
					'title'			=>$row['sale_number'].$title,
					'total_amount'	=>$data['return_back'],
					'invoice'		=>$invoice,
					'category_id'	=>$data['income_category'],
					'date'			=>$data['date'],
					'status'		=>1,
					'description'	=>$data['note'],
					'staff_id'		=>$data['staff_id'],
					'for_date'		=>$data['date'],
					'user_id'		=>$this->getUserId(),
					'create_date'	=>date('Y-m-d'),
					'property_id'=>	$data['property_id'],
					'cheque'=>	$data['cheque'],
					'payment_id'=>	$data['payment_type'],
					'cheque_issuer'=>	$data['cheque_issuer']
						);
				$this->_name="ln_comission";
				$this->insert($arr1);
			}
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function editCommission($data){
		try{
			$db = $this->getAdapter();
			$dbexpense = new Incexp_Model_DbTable_DbExpense();
			$invoice = $dbexpense->getInvoiceNo($data['branch_id']);
			$dbsale = new Loan_Model_DbTable_DbLandpayment();
			$sale_id = empty($data['sale_no'])?$data['sale_client']:$data['sale_no'];
			$row = $dbsale->getTranLoanByIdWithBranch($sale_id,null);
			$title="";
			$arr1 = array(
					'branch_id'	  =>$data['branch_id'],
					'sale_id'	  => $sale_id,
					'title'		  => $row['sale_number'].$title,
					'total_amount'=> $data['return_back'],
// 					'invoice'	  => $invoice,
					'category_id' => $data['income_category'],
					'for_date'		  => $data['date'],
					'status'	  => 1,
					'description' => $data['note'],
					'staff_id'	  => $data['staff_id'],
					'user_id'	  => $this->getUserId(),
// 					'create_date' => date('Y-m-d'),
					'property_id' => $data['property_id'],
					'status' => $data['status'],
					
					'cheque'=>	$data['cheque'],
					'payment_id'=>	$data['payment_type'],
					'cheque_issuer'=>	$data['cheque_issuer']
			);
			$this->_name="ln_comission";
			$where = " id = ".$data['id'];
			$this->update($arr1, $where);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getComissionById($id){
		$db = $this->getAdapter();
		$sql= "SELECT * FROM `ln_comission` AS c WHERE c.`id`= ".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("c.branch_id");
		return $db->fetchRow($sql);
	}
	
	public function getSaleNoByProject($branch_id,$sale_id){
		$db = $this->getAdapter();
		$sale='';
		if(!empty($sale_id)){
			$sale=' OR s.`id`= '.$sale_id;
		}
		$sql="SELECT s.`id`,
		CONCAT((SELECT c.name_kh FROM `ln_client` AS c WHERE c.client_id = s.`client_id` LIMIT 1),' (',
		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id=s.`house_id` LIMIT 1),')' ) AS `name`
		FROM `ln_sale` AS s
		WHERE s.`is_completed` =0 AND (s.`is_cancel` =0 ".$sale." ) AND s.`branch_id` =".$branch_id;
		return $db->fetchAll($sql);
	}
	
	function getAllChequeIssue(){
		$db = $this->getAdapter();
		$sql = " SELECT DISTINCT cheque_issuer as name,cheque_issuer as id FROM `ln_comission` WHERE cheque_issuer!='' ORDER BY cheque_issuer ASC ";
		return $db->fetchAll($sql);
	}
}

