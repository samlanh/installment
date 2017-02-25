<?php
class Report_Model_DbTable_DbParamater extends Zend_Db_Table_Abstract
{
      public function getAllHoliday($search=null){
    	$db = $this->getAdapter();		
          $sql="SELECT id,holiday_name,amount_day,start_date,end_date,status,modify_date,note FROM ln_holiday ";
//           $where = '';
          $from_date =(empty($search['start_date']))? '1': "start_date >= '".$search['start_date']." 00:00:00'";
          $to_date = (empty($search['end_date']))? '1': "end_date <= '".$search['end_date']." 23:59:59'";
          $where = " WHERE ".$from_date." AND ".$to_date;
          if($search['search_status']>-1){
          	$where.= " AND status = ".$search['search_status'];
          }
          elseif(!empty($search['adv_search'])){
          	$s_where = array();
          	$s_search = $search['adv_search'];
          	$s_where[] = " holiday_name LIKE '%{$s_search}%'";
          	$s_where[]=" start_date LIKE '%{$s_search}%'";
          	$s_where[]=" end_date LIKE '%{$s_search}%'";
          	$s_where[]=" amount_day LIKE '%{$s_search}%'";
          	$s_where[]=" note LIKE '%{$s_search}%'";
          	$where .=' AND '.implode(' OR ',$s_where).'';
          }      
          return $db->fetchAll($sql.$where);
    }
    public function getALLzone($search = null){
    	$db = $this->getAdapter();
//     	$sql="SELECT sale_id,(SELECT ln_sale.price_sold FROM `ln_sale` WHERE ln_sale.id=sale_id ) AS sold_price,
//     		begining_balance FROM `ln_saleschedule` GROUP BY sale_id ";
$sql=" SELECT s.id,price_sold,SUM(sl.`principal_permonth`) AS principal_permonth FROM `ln_sale` AS s,`ln_saleschedule` AS sl WHERE s.id=sl.sale_id 
GROUP BY sl.sale_id ";
    	$Other =" ";
    	$where = '';
//     	if($search['search_status']>-1){
//     		$where.= " AND status = ".$search['search_status'];
//     	}
//     	if(!empty($search['adv_search'])){
//     		$s_where = array();
//     		$s_search = $search['adv_search'];
//     		$s_where[] = " zone_name LIKE '%{$s_search}%'";
//     		$s_where[]=" zone_num LIKE '%{$s_search}%'";
//     		$s_where[]=" modify_date LIKE '%{$s_search}%'";
//     		$where .=' AND '.implode(' OR ',$s_where).'';
//     	}
    	//echo $sql.$where.$Other;
    	return $db->fetchAll($sql.$where.$Other);
    }
    public function getALLstaff($search = null){
    	$db = $this->getAdapter();
    	$where="";
    	$sql="SELECT co_id,co_code,co_khname,co_firstname,
    	(SELECT name_kh FROM ln_view WHERE type = 11 AND key_code=sex limit 1 ) AS sex
    	,email,contract_no,shift,workingtime,
    	tel,basic_salary,national_id,address,pob,
    	(SELECT project_name FROM ln_project WHERE br_id = branch_id limit 1) AS branch_name,
    	note FROM ln_staff WHERE 1 ";
    	$Other =" ORDER BY co_id DESC ";
    	if($search['co_khname']>0){
    		$where.= " AND co_id = ".$search['co_khname'];
    	}
		if($search['co_sex']>-1){
    		$where.= " AND sex = ".$search['co_sex'];
    	}
    	if($search['branch_id']>0){
    		$where.= " AND branch_id = ".$search['branch_id'];
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] =" co_code LIKE '%{$s_search}%'";
    		$s_where[]=" co_khname LIKE '%{$s_search}%'";
    		$s_where[]=" co_firstname LIKE '%{$s_search}%'";
    		$s_where[]=" email LIKE '%{$s_search}%'";
    		$s_where[]=" tel LIKE '%{$s_search}%'";
    		$s_where[]=" address LIKE '%{$s_search}%'";
    		$s_where[]=" national_id LIKE '%{$s_search}%'";
    		$where .=' AND '.implode(' OR ',$s_where). '';
    	}
    	return $db->fetchAll($sql.$where.$Other);
    }
    public function getAllVillage($search= null){
    	$db = $this->getAdapter();
    	$from_date =(empty($search['from_date']))? '1': "modify_date >= '".$search['from_date']." 00:00:00'";
    	$to_date = (empty($search['to_date']))? '1': "modify_date <= '".$search['to_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql = "SELECT
				v.vill_id,v.village_namekh,v.village_name,v.displayby,
				(SELECT commune_name FROM ln_commune WHERE v.commune_id=com_id LIMIT 1) AS commune_name,
				d.district_name,p.province_en_name
				,v.modify_date,v.status,
				(SELECT first_name FROM rms_users WHERE id=v.user_id LIMIT 1) AS user_name
				FROM ln_village AS v,`ln_commune` AS c, `ln_district` AS d , `ln_province` AS p
				WHERE v.commune_id = c.com_id AND c.district_id = d.dis_id AND d.pro_id = p.province_id ";
    	
        if($search['province_name']>0){
        	$where.= " AND p.province_id = ".$search['province_name'];
        }
        if(!empty($search['district_name'])){
        	$where.= " AND d.dis_id = ".$search['district_name'];
        }        
		if($search['search_status']>-1){
			$where.= " AND v.status = ".$search['search_status'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[] = " v.village_name LIKE '%{$s_search}%'";
			$s_where[]=" v.village_namekh LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order= ' ORDER BY v.vill_id DESC ';
		return $db->fetchAll($sql.$where.$order);
    }
function getAllBranch($search=null){
    		$db = $this->getAdapter();
    	$sql = "SELECT b.br_id,b.branch_namekh,b.branch_nameen,b.br_address,b.branch_code,b.branch_tel,b.fax,
(SELECT v.name_en FROM `ln_view` AS v WHERE v.`type` = 4 AND v.key_code = b.displayby)AS displayby,b.other,b.`status` FROM ln_branch AS b  ";
    	$where = ' WHERE b.branch_namekh!="" AND b.branch_nameen !="" ';
    	if($search['select_branch_nameen']>0){
    		$where.= " AND b.br_id = ".$search['select_branch_nameen'];
    	}
    	if($search['status_search']>-1){
    		$where.= " AND b.status = ".$search['status_search'];
    	}
    	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=$search['adv_search'];
    		$s_where[]=" b.branch_namekh LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_nameen LIKE '%{$s_search}%'";
    		$s_where[]=" b.br_address LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_code LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_tel LIKE '%{$s_search}%'";
    		$s_where[]=" b.fax LIKE '%{$s_search}%'";
    		$s_where[]=" b.other LIKE '%{$s_search}%'";
    		$s_where[]=" b.displayby LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ',$s_where).')';
    	}
    	$order=' ORDER BY b.br_id DESC';
   //echo $sql.$where;
   return $db->fetchAll($sql.$where.$order);
    	}
    function getAllProperties($search=null){
    		$db = $this->getAdapter();
    		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
    		$sql = "SELECT p.`id`,
    		   (SELECT project_name FROM ln_project WHERE br_id = p.`branch_id` limit 1) AS branch_name,
    		    p.`land_code`,p.`land_address`,p.`property_type`,p.`street`,p.note,
				(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type`) AS pro_type,
				p.`width`,p.`height`,p.`land_size`,p.`price`,p.`land_price`,p.`house_price`,p.`is_lock`
				 FROM `ln_properties` AS p WHERE p.`status`=1 ";
    		if(!empty($search['property_type'])){
    			$where.= " AND p.`property_type` = ".$search['property_type'];
    		}
    		if($search['type_property_sale']>-1){
    			$where.= " AND p.`is_lock` = ".$search['type_property_sale'];
    		}
    		if($search['branch_id']>0){
    			$where.= " AND p.`branch_id` = ".$search['branch_id'];
    		}
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search= addslashes(trim($search['adv_search']));
    			$s_where[]=" p.`land_code` LIKE '%{$s_search}%'";
    			$s_where[]=" p.`land_address` LIKE '%{$s_search}%'";
    			$s_where[]=" p.`land_size` LIKE '%{$s_search}%'";
				$s_where[]=" p.street LIKE '%{$s_search}%'";
    			$s_where[]=" p.`height` LIKE '%{$s_search}%'";
    			$s_where[]=" p.width LIKE '%{$s_search}%'";
    			$s_where[]=" p.`price` LIKE '%{$s_search}%'";
    			$s_where[]=" p.`land_price` LIKE '%{$s_search}%'";
    			$s_where[]=" p.`house_price` LIKE '%{$s_search}%'";
    			$where.=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['streetlist'])){
    			$where.= " AND street ='".$search['streetlist']."'";
    		}
    		$where.=" ORDER BY p.`property_type` "; 
    		return $db->fetchAll($sql.$where);
    	}
    	function getCancelSale($search=null){
    		$db = $this->getAdapter();
    		$from_date =(empty($search['from_date_search']))? '1': "c.`create_date` >= '".$search['from_date_search']." 00:00:00'";
    		$to_date = (empty($search['to_date_search']))? '1': "c.`create_date` <= '".$search['to_date_search']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
    		$sql='SELECT 
    		    c.`id`,c.return_back,
    		    (SELECT project_name FROM `ln_project` WHERE br_id=c.`branch_id` LIMIT 1) AS `project_name`,
    		    c.paid_amount,c.installment_paid,c.reason,c.`create_date`,
				s.`sale_number`,
				s.price_sold,s.other_fee,
				clie.`client_number`,
				(clie.`name_kh`) AS client_name,
				pro.`land_code`,
				(SELECT pt.`type_nameen` FROM `ln_properties_type` AS pt WHERE pt.`id` = pro.`property_type` LIMIT 1) AS type_name,
				pro.`property_type`,pro.`land_address`,pro.`street`
				FROM `ln_sale_cancel` AS c , 
				`ln_sale` AS s, 
				`ln_properties` AS pro,
				`ln_client` AS clie
				WHERE s.`id` = c.`sale_id` AND pro.`id` = c.`property_id` AND
				clie.`client_id` = s.`client_id`';
    		$order = " ORDER BY c.`branch_id` DESC";
    		if($search['branch_id_search']>-1){
    			$where.= " AND c.branch_id = ".$search['branch_id_search'];
    		}
    		if(!empty($search['property_type'])){
    			$where.= " AND pro.`property_type` = ".$search['property_type'];
    		}
    		if($search['client_name']>0){
    			$where.= " AND s.`client_id` = ".$search['client_name'];
    		}
    		if(!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['adv_search']));
    			$s_where[] = " clie.`client_number` LIKE '%{$s_search}%'";
    			$s_where[] = " s.`sale_number` LIKE '%{$s_search}%'";
    			$s_where[] = " p.`project_name` LIKE '%{$s_search}%'";
    			$s_where[] = " pro.`land_code` LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		echo $sql.$where.$order;
    		return $db->fetchAll($sql.$where.$order);
    		
    	}
    	function getAllIncome($search=null){
    		$db = $this->getAdapter();
    		$session_user=new Zend_Session_Namespace('auth');
    		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
    	
    		$sql=" SELECT id,
    		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
    		title, invoice,branch_id,
    		(SELECT name_kh FROM `ln_view` WHERE type=12 and key_code=category_id limit 1) AS category_name,
    		(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id=ln_income.client_id limit 1) AS client_name,
    		cheque,total_amount,description,date,status FROM ln_income WHERE status=1 ";
    	
    		if (!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = trim(addslashes($search['adv_search']));
    			$s_where[] = " description LIKE '%{$s_search}%'";
    			$s_where[] = " title LIKE '%{$s_search}%'";
    			$s_where[] = " total_amount LIKE '%{$s_search}%'";
    			$s_where[] = " invoice LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if($search['branch_id']>0){
    			$where.= " AND branch_id = ".$search['branch_id'];
    		}
    		if(@$search['category_id']>-1 AND !@empty($search['category_id'])){
    			$where.= " AND category_id = ".$search['category_id'];
    		}
    		$order=" order by id desc ";
//     		echo $sql.$where.$order;exit();
    		return $db->fetchAll($sql.$where.$order);
    	}
    	function getAllExpense($search=null){
    		$db = $this->getAdapter();
    		$session_user=new Zend_Session_Namespace('auth');
    		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
    	
    		$sql=" SELECT id,
    		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
    		(SELECT name_kh FROM `ln_view` WHERE type=26 and key_code=payment_id limit 1) AS payment_type,
    		title,invoice,
    	
    		(SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name,
    		cheque,total_amount,description,date,status FROM ln_expense WHERE status=1 ";
    	
    		if (!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = trim(addslashes($search['adv_search']));
    			$s_where[] = " description LIKE '%{$s_search}%'";
    			$s_where[] = " title LIKE '%{$s_search}%'";
    			$s_where[] = " total_amount LIKE '%{$s_search}%'";
    			$s_where[] = " invoice LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if(@$search['category_id_expense']>-1 AND !@empty($search['category_id_expense'])){
    			$where.= " AND category_id = ".$search['category_id_expense'];
    		}
    		if($search['branch_id']>0){
    			$where.= " AND branch_id = ".$search['branch_id'];
    		}
    		if($search['payment_type']>0){
    			$where.= " AND payment_id = ".$search['payment_type'];
    		}
    		$order=" order by id desc ";
    		return $db->fetchAll($sql.$where.$order);
    	}
    	function getSoldIncome($search=null){
    		$db= $this->getAdapter();
    		$where='';
    		$sql = "SELECT * FROM v_soldreport WHERE 1";
    		$from_date =(empty($search['start_date']))? '1': " buy_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " buy_date <= '".$search['end_date']." 23:59:59'";
    		$where.= " AND ".$from_date." AND ".$to_date;
    		if($search['branch_id']>0){
    			$where.= " AND branch_id = ".$search['branch_id'];
    		}
    		if (!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = trim(addslashes($search['adv_search']));
    			$s_where[] = " sale_number LIKE '%{$s_search}%'";
    			$s_where[] = " price_before LIKE '%{$s_search}%'";
    			$s_where[] = " name_kh LIKE '%{$s_search}%'";
    			$s_where[] = " name_en LIKE '%{$s_search}%'";
    			$s_where[] = " client_number LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		return $db->fetchAll($sql.$where);
    	}
    	function getCollectPayment($search=null){
    		$db= $this->getAdapter();
    		//$where='';
    		$sql = "SELECT * FROM v_getcollectmoney WHERE 1";
    		$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
	      	$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
	      	$where = " AND ".$from_date." AND ".$to_date;
	      	if($search['branch_id']>0){
	      		$where.= " AND branch_id = ".$search['branch_id'];
	      	}
	      	if($search['client_name']>0){
	      		$where.=" AND client_id = ".$search['client_name'];
	      	}
	      	
	      	if (!empty($search['adv_search'])){
	      		$s_where = array();
	      		$s_search = trim(addslashes($search['adv_search']));
	      		$s_where[] = " client_number LIKE '%{$s_search}%'";
	      		$s_where[] = " name_kh LIKE '%{$s_search}%'";
	      		$s_where[] = " client_name LIKE '%{$s_search}%'";
	      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
	      		$where .=' AND ('.implode(' OR ',$s_where).')';
	      	}
    		return $db->fetchAll($sql.$where);
    	}
    	function getTermCodiction(){
    		$db =$this->getAdapter();
    		$sql="SELECT * FROM `ln_termcondiction` AS t WHERE t.`status`=1 LIMIT 1";
    		return $db->fetchRow($sql);
    	}
    	function getSaleHistory($search=null){
    		$db= $this->getAdapter();
    		$sql="SELECT * FROM `v_getsalehistory` WHERE 1 ";
    		$order =' ORDER BY house_id,is_cancel ASC';
    		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
    		
    		if($search['branch_id']>0){
    			$where.= " AND branch_id = ".$search['branch_id'];
    		}
    		if (!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = trim(addslashes($search['adv_search']));
    			$s_where[] = " sale_number LIKE '%{$s_search}%'";
    			$s_where[] = " project_name LIKE '%{$s_search}%'";
    			$s_where[] = " client_code LIKE '%{$s_search}%'";
    			$s_where[] = " client_name_kh LIKE '%{$s_search}%'";
    			$s_where[] = " client_name_en LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['land_id'])){
    			$where.= " AND house_id = ".$search['land_id'];
    		}
    		if(!empty($search['client_name'])){
    			$where.= " AND client_id = ".$search['client_name'];
    		}
    		
    		return $db->fetchAll($sql.$where.$order);
    	}
   function getAgreementBySaleID($id=null){
    		$db = $this->getAdapter();
    		$sql="
    		SELECT
				  `s`.`id`              AS `id`,
				  `s`.`sale_number`     AS `sale_number`,
				  `s`.`payment_id`      AS `payment_id`,
				  `s`.`branch_id`       AS `branch_id`,
				  `s`.`client_id`       AS `client_id`,
				  `s`.`price_before`    AS `price_before`,
				  `s`.`discount_amount` AS `discount_amount`,
				  `s`.`price_sold`      AS `price_sold`,
				  `s`.`other_fee`       AS `other_fee`,
				  `s`.`admin_fee`       AS `admin_fee`,
				  `s`.`paid_amount`     AS `paid_amount`,
				  `s`.`balance`         AS `balance`,
				  `s`.`amount_collect`  AS `amount_collect`,
				  `s`.`interest_rate`   AS `interest_rate`,
				  `s`.`total_duration`  AS `total_duration`,
				  s.is_reschedule,
				  s.land_price,
				   s.buy_date,
				   s.agreement_date,
			      `p`.`project_name`,
			      `p`.`br_address` AS `project_location`,
			      `p`.`p_manager_namekh` AS `project_manager_namekh`,
			      `p`.`p_manager_nationality` AS `project_manager_nationality`,
				  `p`.`p_manager_nation_id` AS `project_manager_nation_id`,
                  `p`.`p_current_address` AS `project_manager_p_current_address`,
                   p.w_manager_namekh ,
                   p.w_manager_nation_id,
                  `c`.`client_number` AS `client_code`,
     			  `c`.`name_kh` AS `client_namekh`,
     			  `c`.`name_en` AS `client_nameen`,
     			  c.hname_kh,
  				  `c`.`nationality` AS `client_nationality`,
     			  `c`.`nation_id` AS `client_nation_id`,
                  `c`.`phone` AS `client_phone`,
  				  `c`.`house` AS `client_house_no`,
                  `c`.`street` AS `client_street`,
                  c.phone,
				  (SELECT
				     `village`.`village_name`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`village_id`)
				   LIMIT 1) AS `client_village_en`,
					  (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`village_id`
					                                 )
					   LIMIT 1) AS `client_village_kh`,
				  (SELECT
				     `comm`.`commune_name` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`com_id`)
				   LIMIT 1) AS `client_commune_en`,
				   
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`com_id`)
				   LIMIT 1) AS `client_commune_kh`,
				  (SELECT
				     `dist`.`district_name`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`dis_id`) LIMIT 1) AS `client_district`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`dis_id`)
				   LIMIT 1) AS `client_districtkh`,
				  (SELECT
				     `provi`.`province_en_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`pro_id`) LIMIT 1) AS `client_province_en`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`pro_id`)
				   LIMIT 1) AS `client_province_kh`,
				  (SELECT `property`.`land_code`
					   FROM `ln_properties` `property`
					   WHERE (`property`.`id` = `s`.`house_id`)
					   LIMIT 1) AS `property_code`,
				  (SELECT
						     `property`.`land_address`
						   FROM `ln_properties` `property`
						   WHERE (`property`.`id` = `s`.`house_id`)
						   LIMIT 1) AS `property_title`,
				  (SELECT
				     `prope_type`.`type_nameen`
				   FROM `ln_properties_type` `prope_type`
				   WHERE (`prope_type`.`id` = (SELECT
				                                 `property`.`property_type`
				                               FROM `ln_properties` `property`
				                               WHERE (`property`.`id` = `s`.`house_id`)
				                               LIMIT 1))
				   LIMIT 1) AS `property_type_en`,
				  (SELECT
				     `prope_type`.`type_namekh`
				   FROM `ln_properties_type` `prope_type`
				   WHERE (`prope_type`.`id` = (SELECT
				                                 `property`.`property_type`
				                               FROM `ln_properties` `property`
				                               WHERE (`property`.`id` = `s`.`house_id`)
				                               LIMIT 1))
				   LIMIT 1) AS `property_type_kh`,
			  (SELECT
			     `property`.`width`
			   FROM `ln_properties` `property`
			   WHERE (`property`.`id` = `s`.`house_id`)
			   LIMIT 1) AS `property_width`,
			  (SELECT
			     `property`.`height`
			   FROM `ln_properties` `property`
			   WHERE (`property`.`id` = `s`.`house_id`)
			   LIMIT 1) AS `property_height`,
  (SELECT
     `property`.`street`
   FROM `ln_properties` `property`
   WHERE (`property`.`id` = `s`.`house_id`)
   LIMIT 1) AS `property_street`
		FROM `ln_sale` AS `s`,ln_project AS p ,`ln_client` AS c
			WHERE `p`.`br_id` = `s`.`branch_id` 
			AND `c`.`client_id` = `s`.`client_id`
			AND s.id=".$id;
    		return $db->fetchRow($sql);
    	}
    	function getScheduleBySaleID($id=null,$payment_id){
    		$db = $this->getAdapter();
    		$sql=" SELECT * FROM `ln_saleschedule` AS sc WHERE sc.`sale_id`= ".$id;
    		if($payment_id==4){
    			$sql.=" AND sc.is_installment=1 ";
    		}
    		$order = ' AND is_rescheule=0 ORDER BY sc.`date_payment` ASC';
    		return $db->fetchAll($sql.$order);
    	}
    	
		public function getALLCommissionStaff($search = null){
    	$db = $this->getAdapter();
    	$where="";
    	$sql="SELECT *,
    		(SELECT land_address FROM `ln_properties` WHERE id=s.house_id) AS land_name,
    		(SELECT street FROM `ln_properties` WHERE id=s.house_id) AS street,
			st.`branch_id`,(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = st.`branch_id`) AS project_name
			,st.`co_khname`,st.`co_lastname`,st.`co_code`,st.`sex`
			 FROM ln_sale AS s , `ln_staff` AS st WHERE s.`comission` !=0 AND st.`co_id` = s.`staff_id`";
    	$Other =" ORDER BY s.`id` DESC ";
		$from_date =(empty($search['start_date']))? '1': " s.`buy_date` >= '".$search['start_date']." 00:00:00'";
	    $to_date = (empty($search['end_date']))? '1': " s.`buy_date` <= '".$search['end_date']." 23:59:59'";
	    $where.= " AND ".$from_date." AND ".$to_date;
    	if($search['co_khname']>0){
    		$where.= " AND s.`staff_id` = ".$search['co_khname'];
    	}
    	if($search['branch_id']>0){
    		$where.= " AND st.`branch_id` = ".$search['branch_id'];
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] =" s.`sale_number` LIKE '%{$s_search}%'";
    		$s_where[]=" s.`receipt_no` LIKE '%{$s_search}%'";
    		$s_where[]=" st.`co_khname` LIKE '%{$s_search}%'";
    		$s_where[]=" st.`co_code` LIKE '%{$s_search}%'";
    		//$s_where[]=" tel LIKE '%{$s_search}%'";
    		//$s_where[]=" address LIKE '%{$s_search}%'";
    		//$s_where[]=" national_id LIKE '%{$s_search}%'";
    		$where .=' AND '.implode(' OR ',$s_where). '';
    	}
    	return $db->fetchAll($sql.$where.$Other);
    }
}

