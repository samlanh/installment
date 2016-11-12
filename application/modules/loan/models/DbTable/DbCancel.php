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
            s.`paid_amount`,s.`balance`,s.`discount_amount`,s.`other_fee`,s.`payment_id`,s.`graice_period`,s.`total_duration`,s.`buy_date`,s.`end_line`,
			s.`client_id`,
			s.`house_id`,p.`id` as property_id,p.`land_code`,p.`land_address`,p.`land_size`,p.`width`,p.`height`,p.`street`,p.`land_price`,p.`house_price`
			,p.`street`,(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type` LIMIT 1) AS pro_type
			
			FROM `ln_sale` AS s ,`ln_client` AS c,`ln_properties` AS p
			WHERE c.`client_id` = s.`client_id` AND p.`id`=s.`house_id` AND s.id =".$sale_id;
			return $db->fetchRow($sql);
	}
	public function getCancelSale($search=null){
		$db = $this->getAdapter();
		$sql ='SELECT c.`id`,c.`cancel_code`,
			s.`sale_number`,
			clie.`client_number`,
			CONCAT(clie.`name_kh`," ",clie.`name_en`) AS client_name,
			p.`project_name`,pro.`land_code`,c.`create_date`
			FROM `ln_sale_cancel` AS c , `ln_sale` AS s, `ln_project` AS p,`ln_properties` AS pro,
			`ln_client` AS clie
			WHERE s.`id` = c.`sale_id` AND p.`br_id` = c.`branch_id` AND pro.`id` = c.`property_id` AND
			clie.`client_id` = s.`client_id`';
		return $db->fetchAll($sql);
	}
	public function addCancelSale($data){
		try{
			$db= $this->getAdapter();
			$arr = array(
					'cancel_code'=>$data['cancel_code'],
					'branch_id'=>$data['branch_id'],
					'sale_id'=>$data['sale_no'],
					'property_id'=>$data['property_id'],
					'create_date'=>date("Y-m-d"),
					'user_id'=>$this->getUserId(),
					'status'=>1,
					);
			$this->_name="ln_sale_cancel";
			 $this->insert($arr);
			 
			 $arr_1 = array(
			 		'is_lock'=>0,
			 		);
			 $this->_name="ln_properties";
			 $where1 =" id = ".$data['property_id'];
			 $this->update($arr_1, $where1);
			 
			 $arr_ = array(
			 		'is_cancel'=>1,
			 		);
			 $this->_name="ln_sale";
			 $where =" id = ".$data['sale_no'];
			 $this->update($arr_, $where);
			 
			 
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
}

