<?php

class Loan_Model_DbTable_DbCancel extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_sale_cancel';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	
	}
	public function getCientAndPropertyInfo($sale_id){
		$db = $this->getAdapter();
		$sql="SELECT s.`id`,s.`sale_number` AS `name`,
			c.`client_number`,c.`name_en`,c.`name_kh`,
			s.`price_before`,s.`price_sold`,
            s.`paid_amount`,
            (SELECT SUM(total_principal_permonthpaid) FROM `ln_client_receipt_money` WHERE land_id=$sale_id AND status=1 LIMIT 1) AS total_principal,
            (SELECT COUNT(id) FROM `ln_client_receipt_money` WHERE status=1 AND is_completed=1 AND land_id=$sale_id LIMIT 1) as installment_paid, 
            s.`balance`,s.`discount_amount`,s.`other_fee`,s.`payment_id`,s.`graice_period`,s.`total_duration`,s.`buy_date`,s.`end_line`,
			s.`client_id`,
			s.`house_id`,p.`id` as property_id,p.`land_code`,p.`land_address`,p.`land_size`,p.`width`,p.`height`,p.`street`,p.`land_price`,p.`house_price`
			,p.`street`,(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type` LIMIT 1) AS pro_type,
			s.`comission`,s.`create_date`,s.`note` AS sale_note
			FROM `ln_sale` AS s ,`ln_client` AS c,`ln_properties` AS p
			WHERE c.`client_id` = s.`client_id` AND p.`id`=s.`house_id` AND s.id =".$sale_id;
			return $db->fetchRow($sql);
	}
	public function getCancelSale($search=null){
		$db = $this->getAdapter();
		$from_date =(empty($search['from_date_search']))? '1': "c.`create_date` >= '".$search['from_date_search']." 00:00:00'";
		$to_date = (empty($search['to_date_search']))? '1': "c.`create_date` <= '".$search['to_date_search']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql ='SELECT c.`id`,
		    p.`project_name`,
			s.`sale_number`,
			clie.`client_number`,
			clie.`name_kh` AS client_name,
			(SELECT protype.type_nameen FROM `ln_properties_type` AS protype WHERE protype.id = pro.`property_type` LIMIT 1) AS property_type,
			pro.`land_code`,pro.`land_address`,pro.`street`,
			s.price_sold,c.installment_paid,c.paid_amount,c.`create_date`
			FROM `ln_sale_cancel` AS c , `ln_sale` AS s, `ln_project` AS p,`ln_properties` AS pro,
			`ln_client` AS clie
			WHERE s.`id` = c.`sale_id` AND p.`br_id` = c.`branch_id` AND pro.`id` = c.`property_id` AND
			clie.`client_id` = s.`client_id`';
		if($search['branch_id_search']>-1){
			$where.= " AND c.branch_id = ".$search['branch_id_search'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " clie.`client_number` LIKE '%{$s_search}%'";
			$s_where[] = " clie.`name_kh` LIKE '%{$s_search}%'";
			$s_where[] = " s.`sale_number` LIKE '%{$s_search}%'";
			$s_where[] = " c.`installment_paid` LIKE '%{$s_search}%'";
			$s_where[] = " c.`installment_paid` LIKE '%{$s_search}%'";
			$s_where[] = " p.`project_name` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`land_code` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where);
	}
	public function addCancelSale($data){
		try{
			$db= $this->getAdapter();
			$arr = array(
					//'cancel_code'=>$data['cancel_code'],
					'branch_id'=>$data['branch_id'],
					'sale_id'=>$data['sale_no'],
					'property_id'=>$data['property_id'],
					'create_date'=>date("Y-m-d"),
					'user_id'=>$this->getUserId(),
					'status'=>1,
					'reason'=>$data['reason'],
					'paid_amount'=>$data['paid_amount'],
					'installment_paid'=>$data['installment_paid']
					);
			$this->_name="ln_sale_cancel";
			 $this->insert($arr);
			 
			 $arr_1 = array(
			 		'is_lock'=>0, //property can sell
			 		);
			 $this->_name="ln_properties";
			 $where1 =" id = ".$data['property_id'];
			 $this->update($arr_1, $where1);
			 
			 $arr_ = array(
			 		'is_cancel'=>1, //sale was cancel
			 		);
			 $this->_name="ln_sale";
			 $where =" id = ".$data['sale_no'];
			 $this->update($arr_, $where);
			 
			 
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function editCancelSale($data){
		try{
			$db= $this->getAdapter();
			if ($data['sale_no']==$data['old_sale_id']){
				if ($data['status_using']==0){
					$arr_1 = array(
							'is_lock'=>1, //property can't sell
					);
					$this->_name="ln_properties";
					$where1 =" id = ".$data['property_id'];
					$this->update($arr_1, $where1);
					
					$arr_ = array(
							'is_cancel'=>0,// sale not cancel
					);
					$this->_name="ln_sale";
					$where =" id = ".$data['sale_no'];
					$this->update($arr_, $where);
				}else{
					$arr_1 = array(
							'is_lock'=>0, //property can sell
					);
					$this->_name="ln_properties";
					$where1 =" id = ".$data['property_id'];
					$this->update($arr_1, $where1);
						
					$arr_ = array(
							'is_cancel'=>1,// sale cancel
					);
					$this->_name="ln_sale";
					$where =" id = ".$data['sale_no'];
					$this->update($arr_, $where);
				}
				$arr = array(
						'branch_id'=>$data['branch_id'],
						'sale_id'=>$data['sale_no'],
						'property_id'=>$data['property_id'],
						'create_date'=>date("Y-m-d"),
						'reason'=>$data['reason'],
						'user_id'=>$this->getUserId(),
						'status'=>$data['status_using'],
						'paid_amount'=>$data['paid_amount'],
						'installment_paid'=>$data['installment_paid']
				);
				$this->_name="ln_sale_cancel";
				$where ="id = ".$data['id'];
				$this->update($arr, $where);
			}else{
				$arr_1 = array(
						'is_lock'=>0, //property can sell
				);
				$this->_name="ln_properties";
				$where1 =" id = ".$data['property_id'];
				$this->update($arr_1, $where1);
				
				$arr_ = array(
						'is_cancel'=>1, //sale was cancel 
				);
				$this->_name="ln_sale";
				$where =" id = ".$data['sale_no'];
				$this->update($arr_, $where);
				
				$arr = array(
						//'cancel_code'=>$data['cancel_code'],
						'branch_id'=>$data['branch_id'],
						'sale_id'=>$data['sale_no'],
						'property_id'=>$data['property_id'],
						'create_date'=>date("Y-m-d"),
						'user_id'=>$this->getUserId(),
						'status'=>$data['status_using'],
						'paid_amount'=>$data['paid_amount'],
						'installment_paid'=>$data['installment_paid']
				);
				$this->_name="ln_sale_cancel";
				$where ="id = ".$data['id'];
				$this->update($arr, $where);
				
				$arr_1old = array(//old property update
						'is_lock'=>1,
				);
				$this->_name="ln_properties";
				$where1 =" id = ".$data['old_property_id'];
				$this->update($arr_1old, $where1);
				
				$arr_old = array( //old sale update
						'is_cancel'=>0,
				);
				$this->_name="ln_sale";
				$where =" id = ".$data['old_sale_id'];
				$this->update($arr_old, $where);
			}
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getCancelById($id){
		$db = $this->getAdapter();
		$sql= "SELECT * FROM `ln_sale_cancel` AS c WHERE c.`id`=".$id;
		return $db->fetchRow($sql);
	}
	
	public function getSaleNoByProject($branch_id){
		$db = $this->getAdapter();
		$sql="SELECT s.`id`,CONCAT((SELECT c.name_kh FROM `ln_client` AS c WHERE c.client_id = s.`client_id` LIMIT 1),' (',
		s.`sale_number`,')' ) AS `name`
		FROM `ln_sale` AS s
		WHERE s.`is_completed` =0 AND s.`branch_id` =".$branch_id;
		return $db->fetchAll($sql);
	}
}

