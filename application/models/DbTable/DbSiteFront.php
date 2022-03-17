<?php

class Application_Model_DbTable_DbSiteFront extends Zend_Db_Table_Abstract
{
	public function setName($name){
		$this->_name=$name;
	}
   public static function getUserId(){
		$session_user=new Zend_Session_Namespace(FRONT_SES);
		$userId = empty($session_user->user_id)?null:$session_user->user_id;
		return $userId;
	}
	public function getAccessPermissionFront($branch_str='branch_id'){
		$session_user=new Zend_Session_Namespace(FRONT_SES);
		$userId = empty($session_user->user_id)?null:$session_user->user_id;
		$result="";
		if(!empty($userId)){
			$branch_list = $session_user->branch_list;
			if(!empty($branch_list)){
				$level = $session_user->level;
				$level = 1;
				if($level==1 OR $level==2){
					$result.= "";
				}
				else{
					$result.= " AND $branch_str IN ($branch_list)";
				}
			}
		}
		return $result;
	}
	public function getAllBranchName($branch_id=null,$opt=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = $this->getAdapter();
		$sql= " SELECT 
			br_id,
			br_id AS id,
			project_name,
			project_name AS title,
		project_type,br_address,branch_code,branch_tel,displayby
		FROM `ln_project` WHERE project_name !='' AND status=1 ";
		$sql.= $this->getAccessPermissionFront('br_id');
		
		if($branch_id!=null){
			$sql.=" AND br_id=$branch_id LIMIT 1";
		}
		$sql.=" ORDER BY br_id DESC";
		$row = $db->fetchAll($sql);
		if($opt==null){
			return $row;
		}else{
			$options=array(0=> $tr->translate("SELECT_PROJECT"));
			if(!empty($row)) foreach($row as $read) $options[$read['br_id']]=$read['project_name'];
			return $options;
		}
	  }
	
	public function getCountPropertyByType($search=array()){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$sql="
		SELECT COUNT(p.id) AS total,
			p.property_type,
			(SELECT pt.type_namekh FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitle,
			(SELECT pt.type_namekh FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitleKH,
			(SELECT pt.type_nameen FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitleEn,
			(SELECT pt.image_feature FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS imagePropertyType 
		FROM `ln_properties` AS p 
		WHERE p.status=1
		";
		if(!empty($search['branch_id'])){
			$sql.=" AND p.branch_id= ".$search['branch_id'];
		}
		if(!empty($search['propertyStatus'])){
			if($search['propertyStatus']==1){ //available property
				$sql.=" AND p.is_lock=0";
			}else if($search['propertyStatus']==2){
				$sql.=" AND p.is_lock=1"; //sold property
			}
		}
		$sql.= $this->getAccessPermissionFront('p.branch_id');
		if(!empty($search['property_type'])){
			$sql.=" AND p.property_type=".$search['property_type'];
			 return $db->fetchRow($sql);
		}else{
		
			$sql.=" GROUP BY p.property_type ";
			
			$order=" ";
			return $db->fetchAll($sql.$order);
		
		}
	}
	
	
	public function getCountSaleByPropertyType($search=array()){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$sql="
			SELECT 
				COUNT(s.id) AS totalSale,p.property_type 
			FROM `ln_sale` AS s,
				`ln_properties` AS p
			WHERE s.house_id = p.id 
				AND s.status=1
		";
		if(!empty($search['branch_id'])){
			$sql.=" AND s.branch_id= ".$search['branch_id'];
		}
		if(!empty($search['payment_id'])){
			$sql.=" AND s.payment_id=".$search['payment_id'];
		}
		
		if(!empty($search['property_type'])){
			$sql.=" AND p.property_type=".$search['property_type'];
		}
		
		$today = date("Y-m-d");
		if(!empty($search['isToday'])){
			$sql.=" AND s.buy_date=".$today;
		}
		$sql.= $this->getAccessPermissionFront('s.branch_id');
		$sql.=" GROUP BY p.property_type ";
		return $db->fetchRow($sql);
	}
	
	public function getCountSaleCancelByPropertyType($search=array()){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$sql="
			SELECT 
				COUNT(sc.id) AS total,
				sc.*
			FROM 
				`ln_sale_cancel` AS sc,
				`ln_properties` AS p

			WHERE 
				 sc.property_id = p.id 
				AND sc.status=1 
		";
		if(!empty($search['branch_id'])){
			$sql.=" AND sc.branch_id= ".$search['branch_id'];
		}

		if(!empty($search['property_type'])){
			$sql.=" AND p.property_type=".$search['property_type'];
		}
		$today = date("Y-m-d");
		if(!empty($search['isToday'])){
			$sql.=" AND sc.create_date=".$today;
		}
		$sql.= $this->getAccessPermissionFront('sc.branch_id');
		$sql.=" GROUP BY p.property_type ";
		return $db->fetchRow($sql);
	}
	
	 public function getAllPropertyType(){
		$db= $this->getAdapter();
		$sql="SELECT t.`id`,t.`type_namekh` AS `name` FROM `ln_properties_type` AS t WHERE t.`status`=1 ";
		$rows =  $db->fetchAll($sql);
		return $rows;
	}
	public function getAllProperty($search=array()){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$sql="
		SELECT p.*,
			CASE
				WHEN  p.is_lock = 1 THEN '".$tr->translate('SOLD_OUT')."'
				WHEN  p.is_lock = 0 THEN '".$tr->translate('AVAILABLE')."'
			END AS saleStatusTitle,
			(SELECT pt.type_namekh FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitle,
			(SELECT pt.type_namekh FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitleKH,
			(SELECT pt.type_nameen FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitleEn,
			(SELECT pt.image_feature FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS imagePropertyType,
			(SELECT pt.note FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeNote
		FROM `ln_properties` AS p WHERE p.status=1 
		";
		if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " p.land_code LIKE '%{$s_search}%'";
    		$s_where[] = " p.land_address LIKE '%{$s_search}%'";
    		$s_where[] = " p.street LIKE '%{$s_search}%'";
    		$s_where[] = " p.price LIKE '%{$s_search}%'";
    		$s_where[] = " p.land_size LIKE '%{$s_search}%'";
    		$s_where[] = " p.width LIKE '%{$s_search}%'";
    		$s_where[] = " p.height LIKE '%{$s_search}%'";
    		$sql .=' AND ('.implode(' OR ',$s_where).')';
    	}
		if(!empty($search['branch_id'])){
			$sql.=" AND p.branch_id= ".$search['branch_id'];
		}
		if(!empty($search['propertyStatus'])){
			if($search['propertyStatus']==1){ //available property
				$sql.=" AND p.is_lock=0";
			}else if($search['propertyStatus']==2){
				$sql.=" AND p.is_lock=1"; //sold property
			}
		}
		if(!empty($search['property_type'])){
			$sql.=" AND p.property_type= ".$search['property_type'];
		}
		$sql.= $this->getAccessPermissionFront('p.branch_id');
		
		$order=" ORDER BY LENGTH(p.land_address), p.land_address ASC";
		return $db->fetchAll($sql.$order);
	}
	
	
	//Dashboard Sale Agent
	function getTotalSaleByAgent(){
		$db = $this->getAdapter();
		
		$session_user=new Zend_Session_Namespace(FRONT_SES);
		$user_id = $session_user->user_id;
		$staff_id = empty($session_user->staff_id)?0:$session_user->staff_id;
		
	
		$sql="SELECT COUNT(s.`id`) AS totalsale FROM `ln_sale` AS s WHERE s.`staff_id` =$staff_id";
		return $db->fetchOne($sql);
	}
	function getTotalFullCommission(){
		
		$session_user=new Zend_Session_Namespace(FRONT_SES);
		$user_id = $session_user->user_id;
		$staff_id = empty($session_user->staff_id)?0:$session_user->staff_id;
		
		$db = $this->getAdapter();
		$sql="SELECT 
		SUM(s.`full_commission`) AS expect_commission
		 FROM `ln_sale` AS s WHERE s.`staff_id` = $staff_id";
		return $db->fetchOne($sql);
	}
	function getCommissionPiadByAgent(){
	
		$session_user=new Zend_Session_Namespace(FRONT_SES);
		$user_id = $session_user->user_id;
		$staff_id = empty($session_user->staff_id)?0:$session_user->staff_id;
		
		$db = $this->getAdapter();
		
		$sql=" SELECT 
				SUM(s.`comission`) AS total_commission,
				(SELECT SUM(c.`total_amount`) FROM `ln_comission` AS c WHERE s.`staff_id` = c.`staff_id` AND c.status =1 ) AS total_commission_get
			 	FROM `ln_sale` AS s WHERE s.`staff_id` =$staff_id ";
		return $db->fetchRow($sql);
	}
	
	function getCommissionPaymentPaidByAgent(){
	
		$session_user=new Zend_Session_Namespace(FRONT_SES);
		$user_id = $session_user->user_id;
		$staff_id = empty($session_user->staff_id)?0:$session_user->staff_id;
		
		$db = $this->getAdapter();
		$sql=" SELECT 
			SUM(s.`total_paid`) AS total_commission
			FROM `rms_commission_payment` AS s WHERE s.`agency_id` =$staff_id AND s.status = 1 ";
			return $db->fetchRow($sql);
	}
	
	 public function getAllSaleByAgent($search,$reschedule =null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$edit_sale = $tr->translate("EDITSALEONLY");
    	$session_lang=new Zend_Session_Namespace('lang');
    	$lang = $session_lang->lang_id;
    	
    	$str = 'name_en';
    	if($lang==1){
    		$str = 'name_kh';
    	}
		
		$session_user=new Zend_Session_Namespace(FRONT_SES);
		$user_id = $session_user->user_id;
		$staff_id = empty($session_user->staff_id)?0:$session_user->staff_id;
    	
    	$from_date =(empty($search['start_date']))? '1': " s.buy_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.buy_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql=" 
    	SELECT 
			`s`.`id` AS `id`,
			(SELECT `ln_project`.`project_name` FROM `ln_project`  WHERE (`ln_project`.`br_id` = `s`.`branch_id`) LIMIT 1) AS `branch_name`,
			`c`.`name_kh`         AS clientName,
			`c`.`phone`           AS clienPhone,
			
			`p`.`land_address`    AS propertyTitle,
			`p`.`street`          AS propertyStreet,
			(SELECT pt.type_namekh FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitle,
			(SELECT pt.type_namekh FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitleKH,
			(SELECT pt.type_nameen FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitleEn,
			(SELECT pt.image_feature FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS imagePropertyType,
			(SELECT pt.note FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeNote,
			
			(SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
			`s`.`price_before`    AS `price_before`,
			`s`.`discount_amount` AS `discount_amount`,
			CONCAT(`s`.`discount_percent`,'%') AS `discount_percent`,
       
			`s`.`price_sold`     AS `price_sold`,
			`s`.`buy_date`        AS `buy_date`,
			(SELECT  first_name FROM rms_users WHERE id=s.user_id limit 1 ) AS user_name,
			s.status,
			
			s.full_commission AS fullCommission,
			COALESCE((SELECT SUM(c.`total_amount`) FROM `ln_comission` AS c WHERE c.`staff_id` = s.staff_id AND c.status = 1 LIMIT 1),0) AS totalCommissionPaid,
			COALESCE((SELECT  SUM(cmp.`total_paid`) AS total_commission FROM `rms_commission_payment` AS cmp WHERE cmp.`agency_id` =s.staff_id AND cmp.status = 1 LIMIT 1),0) AS totalCommissionPayment,
			
			CASE    
				WHEN  `s`.`is_cancel` = 0 THEN ' '
				WHEN  `s`.`is_cancel` = 1 THEN '".$tr->translate("CANCELED")."'
			END AS isCancelTitle
		FROM ((`ln_sale` `s` JOIN `ln_client` `c`)
			JOIN `ln_properties` `p`)
		WHERE ((`c`.`client_id` = `s`.`client_id`)
				AND (`p`.`id` = `s`.`house_id`)) 
				AND s.status = 1
	   ";
    	
		$sql.=" AND s.staff_id=".$staff_id;
    	$db = $this->getAdapter();
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
      	 	$s_where[] = " s.receipt_no LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.sale_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " p.land_code LIKE '%{$s_search}%'";
      	 	$s_where[] = " p.land_address LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.client_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_en LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.phone LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.price_sold LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.comission LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.full_commission LIKE '%{$s_search}%'";
      	 
      	 
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	
    	
    	if(!empty($search['branch_id'])){
    		$where.= " AND s.branch_id = ".$search['branch_id'];
    	}
		if(!empty($search['schedule_opt'])){
    		$where.= " AND s.payment_id = ".$search['schedule_opt'];
    	}
    	
    	$order = " ORDER BY s.id DESC";
    	
    	
    	$where.=$this->getAccessPermissionFront("`s`.`branch_id`");
    	return $db->fetchAll($sql.$where.$order);
    }

}
?>