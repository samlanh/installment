<?php

class Loan_Model_DbTable_DdReceived extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_receiveplong';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
// 	public function getCientAndPropertyInfo($sale_id){
// 		$db = $this->getAdapter();
// 		$sql="SELECT r.`id`,c.`name_kh`,
// 			s.`house_id`,p.`id` as property_id,p.`land_code`,p.`land_address`,p.`land_size`,p.`width`,p.`height`,p.`street`,p.`land_price`,p.`house_price`
// 			,p.`street`,(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type` LIMIT 1) AS pro_type,
// 			s.`comission`,s.`create_date`,s.`note` AS sale_note
// 			FROM `ln_sale` AS s ,`ln_client` AS c,`ln_properties` AS p
// 			WHERE c.`client_id` = s.`client_id` AND p.`id`=s.`house_id` AND s.id =".$sale_id;
// 			return $db->fetchRow($sql);
// 	}
	public function getCustomerReceivedPlong($search=null){
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "c.`create_date` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "c.`create_date` <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql ='SELECT c.`id`,
		    p.`project_name` as branch_name,
			clie.`name_kh` AS client_name,
			(SELECT protype.type_nameen FROM `ln_properties_type` AS protype WHERE protype.id = pro.`property_type` LIMIT 1) AS property_type,
			pro.`land_address`,pro.`street`,c.date,c.create_date,c.note,c.`status`
			FROM `ln_receiveplong` AS c ,`ln_project` AS p,`ln_properties` AS pro,
			`ln_client` AS clie
			WHERE p.`br_id` = c.`branch_id` AND pro.`id` = c.`house_id` AND
			clie.`client_id` = c.`customer_id` ';
		if($search['branch_id_search']>0){
			$where.= " AND c.branch_id = ".$search['branch_id_search'];
		}
		if($search['land_id']>0){
			$where.= " AND c.house_id = ".$search['land_id'];
		}
		if($search['client_name']>0){
			$where.= " AND c.customer_id = ".$search['client_name'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " c.`note` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$where.=" ORDER BY c.`id` DESC ";
		return $db->fetchAll($sql.$where);
	}
	public function addReceivedplong($data){
		try{
			$db= $this->getAdapter();
			$arr1 = array(
					'branch_id'	  => $data['branch_id'],
					'sale_id'	  => $data['sale_client'],
					'customer_id' => $data['customer_id'],
					'date'		  => $data['date'],
					'house_id'	  => $data['house_id'],
					'note'		  => $data['reason'],
					'user_id'	  => $this->getUserId(),
					'status'	  =>1,
					'create_date'=>date("Y-m-d")
					);
			$this->_name="ln_receiveplong";
			$this->insert($arr1);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function editReceivedplong($data){
		try{
			$db= $this->getAdapter();
			$arr1 = array(
					'branch_id'	  => $data['branch_id'],
					'sale_id'	  => $data['sale_client'],
					'customer_id' => $data['customer_id'],
					'date'		  => $data['date'],
					'house_id'	  => $data['house_id'],
					'note'		  => $data['reason'],
					'user_id'	  => $this->getUserId(),
					'status'	  =>1,
					'create_date'=>date("Y-m-d")
			);
				$this->_name="ln_receiveplong";
				$where = 'id = '.$data['id'];
				$this->update($arr1,$where);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getPlongById($id){
		$db = $this->getAdapter();
		$sql= "SELECT * FROM `ln_receiveplong` AS c WHERE c.`id`=".$id;
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
