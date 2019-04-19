<?php

class Incexp_Model_DbTable_DbComission extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_sale_cancel';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authinstall');
		return $session_user->user_id;
	
	}
// 	public function getCientAndPropertyInfo($sale_id){
// 		$db = $this->getAdapter();
// 		$sql="SELECT s.`id`,s.`sale_number` AS `name`,
// 			c.`client_number`,c.`name_en`,c.`name_kh`,
// 			s.`price_before`,s.`price_sold`,
//             s.`paid_amount`,
//             (SELECT SUM(total_principal_permonthpaid) FROM `ln_client_receipt_money` WHERE sale_id=$sale_id AND status=1 LIMIT 1) AS total_principal,
//             (SELECT COUNT(id) FROM `ln_client_receipt_money` WHERE status=1 AND is_completed=1 AND sale_id=$sale_id LIMIT 1) as installment_paid, 
//             s.`balance`,s.`discount_amount`,s.`other_fee`,s.`payment_id`,s.`graice_period`,s.`total_duration`,s.`buy_date`,s.`end_line`,
// 			s.`client_id`,
// 			s.`house_id`,p.`id` as property_id,p.`land_code`,p.`land_address`,p.`land_size`,p.`width`,p.`height`,p.`street`,p.`land_price`,p.`house_price`
// 			,p.`street`,(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type` LIMIT 1) AS pro_type,
// 			s.`comission`,s.`create_date`,s.`note` AS sale_note
// 			FROM `ln_sale` AS s ,`ln_client` AS c,`ln_properties` AS p
// 			WHERE c.`client_id` = s.`client_id` AND p.`id`=s.`house_id` AND s.id =".$sale_id;
// 			return $db->fetchRow($sql);
// 	}
	public function getComissionSale($search=null){
		$db = $this->getAdapter();
		$from_date =(empty($search['from_date_search']))? '1': "c.`for_date` >= '".$search['from_date_search']." 00:00:00'";
		$to_date = (empty($search['to_date_search']))? '1': "c.`for_date` <= '".$search['to_date_search']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql ='SELECT c.`id`,
		    p.`project_name`,
			s.`sale_number`,
			clie.`name_kh` AS client_name,
			(SELECT protype.type_nameen FROM `ln_properties_type` AS protype WHERE protype.id = pro.`property_type` LIMIT 1) AS property_type,
			pro.`land_address`,pro.`street`,
			s.price_sold,
			(SELECT co_khname FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS staff_name,
			c.total_amount,
			c.`for_date`,c.`status`
			FROM `ln_comission` AS c , `ln_sale` AS s, `ln_project` AS p,`ln_properties` AS pro,
			`ln_client` AS clie
			WHERE s.`id` = c.`sale_id` AND p.`br_id` = c.`branch_id` AND pro.`id` = s.`house_id` AND
			clie.`client_id` = s.`client_id` ';
		if($search['branch_id_search']>-1){
			$where.= " AND c.branch_id = ".$search['branch_id_search'];
		}
		if($search['staff_id']>0){
			$where.= " AND c.staff_id = ".$search['staff_id'];
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
// 		echo $sql.$where;exit();
		return $db->fetchAll($sql.$where);
	}
	public function addSaleComission($data){
		try{
			$db= $this->getAdapter();
			$expenid='';
			if($data['return_back']>0){
				$dbexpense = new Incexp_Model_DbTable_DbExpense();
				$invoice = $dbexpense->getInvoiceNo($data['branch_id']);
				$dbsale = new Loan_Model_DbTable_DbLandpayment();
				$row = $dbsale->getTranLoanByIdWithBranch($data['sale_no'],null);
				$title="";
				$arr1 = array(
					'branch_id'		=>$data['branch_id'],
					'sale_id'	    =>$data['sale_no'],
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
					'property_id'=>	$data['property_id']
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
			$row = $dbsale->getTranLoanByIdWithBranch($data['sale_no'],null);
			$title="";
			$arr1 = array(
					'branch_id'	  =>$data['branch_id'],
					'sale_id'	  => $data['sale_no'],
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
					'status' => $data['status']
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
}

