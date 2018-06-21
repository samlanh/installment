<?php

class Loan_Model_DbTable_DdReceived extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_receiveplong';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('authinstall');
		return $session_user->user_id;
	}
	public function getCustomerReceivedPlong($search=null){
		$db = $this->getAdapter();
		$from_date =(empty($search['from_date_search']))? '1': "c.`create_date` >= '".$search['from_date_search']." 00:00:00'";
		$to_date = (empty($search['to_date_search']))? '1': "c.`create_date` <= '".$search['to_date_search']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql ='SELECT c.`id`,
		    p.`project_name` as branch_name,
			clie.client_number,
			clie.`name_kh` AS client_name,
			(SELECT protype.type_nameen FROM `ln_properties_type` AS protype WHERE protype.id = pro.`property_type` LIMIT 1) AS property_type,
			pro.`land_address`,pro.`street`,c.date,c.create_date,c.note,c.`status`,"ប័ណ្ណប្រគល់ប្លង់កម្មសិទ្ធ"
			FROM `ln_receiveplong` AS c ,`ln_project` AS p,`ln_properties` AS pro,
			`ln_client` AS clie
			WHERE p.`br_id` = c.`branch_id` AND pro.`id` = c.`house_id` AND
			clie.`client_id` = c.`customer_id` ';
		if($search['branch_id']>0){
			$where.= " AND c.branch_id = ".$search['branch_id'];
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
					'layout_type' => $data['plong_type'],
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
					'layout_type' => $data['plong_type'],
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
	function getLayoutType(){
		$db = $this->getAdapter();
		$sql="SELECT DISTINCT rp.`layout_type` AS `name`,rp.`layout_type` AS `id`  FROM `ln_receiveplong` AS rp WHERE rp.`status`=1 AND rp.`layout_type` !='' ORDER BY rp.layout_type ASC";
		return $db->fetchAll($sql);
	}
	function getRecivePlongInfo($id){
		$db = $this->getAdapter();
		$sql="SELECT 
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =c.`branch_id` LIMIT 1) AS branch_name,
		cl.name_kh AS client_namekh,
		cl.name_en AS client_nameen,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.key_code = cl.`sex` AND v.`type`=11 LIMIT 1 ) AS sexKh,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.key_code = cl.`client_d_type` AND v.`type`=23 LIMIT 1 ) AS client_d_typekh,
		cl.`nation_id`,
		cl.`house`,
		cl.`street`,
		(SELECT p.province_kh_name FROM `ln_province` AS p WHERE p.province_id = cl.`pro_id` LIMIT 1) AS province_kh_name,
		(SELECT d.district_namekh FROM `ln_district` AS d WHERE d.dis_id = cl.`dis_id` LIMIT 1) AS district_namekh,
		(SELECT com.commune_namekh FROM `ln_commune` AS com WHERE com.com_id = cl.`com_id` LIMIT 1) AS commune_namekh,
		(SELECT vil.village_namekh FROM `ln_village` AS vil WHERE vil.vill_id = cl.`village_id` LIMIT 1) AS village_namekh,
		(SELECT CONCAT(pro.land_address,'-',pro.street) FROM `ln_properties` AS pro WHERE pro.id = s.`house_id` LIMIT 1) AS propertyinfo,
		c.*
		 FROM `ln_receiveplong` AS c,
		`ln_sale` AS s,
		`ln_client` AS cl
		  WHERE 
		  s.`id` = c.`sale_id`
		  AND cl.`client_id` = s.`client_id`
		  AND c.`id`=5 LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getSaleInfo($id){
		$db = $this->getAdapter();
		$sql="
			SELECT 
			cl.name_kh AS client_namekh,
			cl.name_en AS client_nameen,
			(SELECT v.name_kh FROM `ln_view` AS v WHERE v.key_code = cl.`sex` AND v.`type`=11 LIMIT 1 ) AS sexKh,
			(SELECT v.name_kh FROM `ln_view` AS v WHERE v.key_code = cl.`client_d_type` AND v.`type`=23 LIMIT 1 ) AS client_d_typekh,
			cl.`nation_id`,
			cl.`house`,
			cl.`street`,
			(SELECT p.province_kh_name FROM `ln_province` AS p WHERE p.province_id = cl.`pro_id` LIMIT 1) AS province_kh_name,
			(SELECT d.district_namekh FROM `ln_district` AS d WHERE d.dis_id = cl.`dis_id` LIMIT 1) AS district_namekh,
			(SELECT com.commune_namekh FROM `ln_commune` AS com WHERE com.com_id = cl.`com_id` LIMIT 1) AS commune_namekh,
			(SELECT vil.village_namekh FROM `ln_village` AS vil WHERE vil.vill_id = cl.`village_id` LIMIT 1) AS village_namekh,
			CONCAT(p.land_address,'-',p.street) AS propertyInfo,
			s.* 
			FROM `ln_sale` AS s,
			`ln_client` AS cl,
			`ln_properties` AS p
			WHERE cl.`client_id` = s.`client_id`
			AND p.`id` = s.`house_id` AND s.`id` =$id LIMIT 1
		";
		return $db->fetchRow($sql);
	}
}

