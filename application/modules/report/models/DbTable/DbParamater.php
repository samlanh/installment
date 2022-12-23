<?php
class Report_Model_DbTable_DbParamater extends Zend_Db_Table_Abstract
{
      public function getAllHoliday($search=null){
    	$db = $this->getAdapter();		
          $sql="SELECT id,holiday_name,amount_day,start_date,end_date,status,modify_date,note FROM ln_holiday ";
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
    	note,
		(SELECT  first_name FROM rms_users WHERE id = ln_staff.user_id LIMIT 1 ) AS user_name
    	FROM ln_staff WHERE 1 ";
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
   return $db->fetchAll($sql.$where.$order);
    	}
    function getAllProperties($search=null){
    		$db = $this->getAdapter();
    		$to_enddate = (empty($search['end_date']))? '1': " s.buy_date <= '".$search['end_date']." 23:59:59'";
    		$sql = "SELECT p.`id`,
    		   (SELECT project_name FROM ln_project WHERE br_id = p.`branch_id` limit 1) AS branch_name,
    		    p.`land_code`,p.`land_address`,p.`property_type`,p.`street`,p.note,
				(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type` LIMIT 1) AS pro_type,
				p.south,p.north,p.west,p.east,
				p.`width`,p.`height`,p.`land_size`,p.`price`,p.`land_price`,p.`house_price`, ";
    			$sql.="CASE
				WHEN  is_lock =1 THEN (SELECT 1 FROM `ln_sale` AS s WHERE s.house_id =  p.`id`  AND s.status=1 AND s.is_cancel = 0 AND $to_enddate LIMIT 1)
				ELSE  0
				END AS is_lock,
				p.`width`,p.`height`,p.`land_size`,p.`price`,p.`land_price`,p.`house_price`,p.`is_lock`, ";		

    		$sql.="(SELECT first_name FROM `rms_users` WHERE id=p.user_id LIMIT 1) AS user_name,
					(SELECT s.price_sold FROM `ln_sale` AS s WHERE s.house_id =  p.id  AND s.is_cancel = 0  LIMIT 1) AS price_sold
				
			 FROM `ln_properties` AS p WHERE p.`status`=1 ";
    		
    		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
    		$where=" AND ".$from_date." AND ".$to_date;
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("p.`branch_id`");
    		
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
    			$s_where[]=" p.`land_address` LIKE '%{$s_search}%'";
				$s_where[]=" p.street LIKE '%{$s_search}%'";
    			$where.=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['streetlist'])){
    			$where.= " AND street ='".$search['streetlist']."'";
    		}

//     		$where.=" ORDER BY p.`property_type`,p.`street` ASC, cast(land_address as unsigned) "; 
// 			$where.=" IF(land_address RLIKE '^[a-z]', 1, 2), land_address ASC";
    		$where.= " ORDER BY p.branch_id,p.`property_type`,p.`street` ASC, LENGTH(land_address), land_address ASC  ";
    		
    		return $db->fetchAll($sql.$where);
    	}
    	function getCancelSale($search=null){
    		$db = $this->getAdapter();
    		$from_date =(empty($search['from_date_search']))? '1': "c.`create_date` >= '".$search['from_date_search']." 00:00:00'";
    		$to_date = (empty($search['to_date_search']))? '1': "c.`create_date` <= '".$search['to_date_search']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		$sql="
				SELECT 
					c.`id`,c.return_back,
					(SELECT project_name FROM `ln_project` WHERE br_id=c.`branch_id` LIMIT 1) AS `project_name`,
					c.paid_amount,c.installment_paid,c.reason,c.`create_date`,
					s.`sale_number`,
					s.price_sold,
					clie.`client_number`,
					(clie.`name_kh`) AS client_name,
					pro.`land_code`,
					(SELECT pt.`type_nameen` FROM `ln_properties_type` AS pt WHERE pt.`id` = pro.`property_type` LIMIT 1) AS type_name,
					pro.`property_type`,pro.`land_address`,pro.`street`,
					(SELECT first_name FROM `rms_users` WHERE id=c.user_id LIMIT 1) AS user_name,
					c.cancel_type,
					c.condition_return,
					c.date_for_return,
					CASE
						WHEN  c.cancel_type =1 THEN '".$tr->translate('CANCEL_WITHOUT_RETURN')."'
						WHEN  c.cancel_type =2 THEN '".$tr->translate('CANCEL_WITH_RETURN_AMOUNT')."'
						ELSE  ''
					END AS cancelTypeTitle,
					(SELECT v.name_kh FROM `ln_view` AS v WHERE v.`type` = 32 AND v.key_code = c.condition_return LIMIT 1) AS conditionReturnTitle,
					(SELECT SUM(e.total_amount) FROM ln_expense AS e WHERE e.cancelSale_id = c.id AND e.status=1 LIMIT 1) AS totalReturnPaid
				
				FROM `ln_sale_cancel` AS c, 
					`ln_sale` AS s, 
					`ln_properties` AS pro,
					`ln_client` AS clie
				WHERE s.`id` = c.`sale_id` AND pro.`id` = c.`property_id` AND
					clie.`client_id` = s.`client_id` AND c.status=1";
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("c.branch_id");
    		
    		$order = " ORDER BY c.`branch_id` DESC,c.id DESC ";
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
    			$s_where[] = " clie.`name_kh` LIKE '%{$s_search}%'";
    			$s_where[] = " s.`sale_number` LIKE '%{$s_search}%'";
    			$s_where[] = " pro.`land_code` LIKE '%{$s_search}%'";
    			$s_where[] = " pro.`land_address` LIKE '%{$s_search}%'";
    			$s_where[] = " pro.`street` LIKE '%{$s_search}%'";
    			$s_where[] = " pro.`reason` LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['cancelTypeSearch'])){
    			$where.= " AND c.cancel_type = ".$search['cancelTypeSearch'];
    		}
    		return $db->fetchAll($sql.$where.$order);
    		
    	}
    	function getAllIncome($search=null){
    		if(empty($search['ordering'])){$search['ordering']=2;}
    		$db = $this->getAdapter();
    		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
    	
    		$sql=" SELECT id,
    		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id = branch_id LIMIT 1) AS branch_name,
    		 title, invoice,branch_id,
    		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id =ln_income.house_id LIMIT 1) as house_name,
    		(SELECT name_kh FROM `ln_view` WHERE type=12 and key_code=category_id LIMIT 1) AS category_name,
    		(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=payment_id LIMIT 1) AS payment_type,
			(SELECT bank_name FROM `st_bank` WHERE  id=ln_income.bank_id LIMIT 1) AS bank,
			payment_id,
    		(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id=ln_income.client_id limit 1) AS client_name,
    		 cheque,total_amount,description,date,is_closed,
    		(SELECT  first_name FROM rms_users WHERE rms_users.id=ln_income.user_id LIMIT 1 ) AS user_name,
    		status 
			FROM ln_income WHERE status=1 ";
    		
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
    	function getAllExpense($search=null,$group_by=null){
    		if(empty($search['ordering'])){
    			$search['ordering']=2;
    		}
    		$db = $this->getAdapter();
    		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
    	
    		$sql=" 
				SELECT id,
					(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
					(SELECT ls.name FROM `ln_supplier` AS ls WHERE ls.id = supplier_id LIMIT 1) AS supplier_name,
					(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=payment_id limit 1) AS payment_type,
					payment_id,
					title,invoice,is_closed,
					cheque_issuer,other_invoice,
					(SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name,
					cheque,total_amount,description,date,
					(SELECT  CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,'')) FROM rms_users WHERE id=user_id limit 1 ) AS user_name,
					status,
					cancelSale_id,
					CASE
						WHEN  cancelSale_id > 0 THEN (SELECT c.name_kh FROM ln_client AS c WHERE c.client_id = (SELECT s.client_id FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1 ) LIMIT 1) 
						ELSE  (SELECT ls.name FROM `ln_supplier` AS ls WHERE ls.id = supplier_id LIMIT 1)
					END AS supplierOrCustomer,
					(SELECT c.name_kh FROM ln_client AS c WHERE c.client_id = (SELECT s.client_id FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1 ) LIMIT 1) AS cancelCustomer,
					(SELECT CONCAT(p.land_address,',',p.street) FROM `ln_properties` AS p WHERE p.id = (SELECT s.house_id FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1 ) LIMIT 1) AS cancelProperty
					
    			FROM ln_expense WHERE status=1 AND total_amount>0 ";
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
    	//This below function tempory closed because query and query again than make proccessing too slow
//     	function getCollectPayment($search=null){
//     		$db= $this->getAdapter();
//     		$sql = "SELECT v_getcollectmoney.*,
// 				(SELECT  first_name FROM rms_users WHERE id=user_id limit 1 ) AS user_name
//     		FROM v_getcollectmoney WHERE status=1 ";
//     		$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
// 	      	$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
// 	      	$where = " AND ".$from_date." AND ".$to_date;
	      	
// 	      	$dbp = new Application_Model_DbTable_DbGlobal();
// 	      	$where.=$dbp->getAccessPermission("branch_id");
	      	
// 	      	if($search['branch_id']>0){
// 	      		$where.= " AND branch_id = ".$search['branch_id'];
// 	      	}
// 	      	if(!empty($search['user_id']) AND $search['user_id']>0){
// 	      		$where.= " AND user_id = ".$search['user_id'];
// 	      	}
// 	      	if($search['client_name']>0){
// 	      		$where.=" AND client_id = ".$search['client_name'];
// 	      	}
// 	      	if($search['payment_method']>0){
// 	      		$where.=" AND payment_methodid = ".$search['payment_method'];
// 	      	}
	      	
// 	      	if (!empty($search['adv_search'])){
// 	      		$s_where = array();
// 	      		$s_search = trim(addslashes($search['adv_search']));
// 	      		$s_where[] = " client_number LIKE '%{$s_search}%'";
// 	      		$s_where[] = " name_kh LIKE '%{$s_search}%'";
// 	      		$s_where[] = " client_name LIKE '%{$s_search}%'";
// 	      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
// 	      		$where .=' AND ('.implode(' OR ',$s_where).')';
// 	      	}
	      	
// 	      	if (!empty($search['streetlist'])){
// 	      		$where.=" AND street = '".$search['streetlist']."'";
// 	      	}
//     		return $db->fetchAll($sql.$where);
//     	}
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
    	function getTermCodiction(){
    		$db =$this->getAdapter();
    		$sql="SELECT * FROM `ln_termcondiction` AS t WHERE t.`status`=1 LIMIT 1";
    		return $db->fetchRow($sql);
    	}
    	function getSaleHistory($search=null){
    		$db= $this->getAdapter();
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$session_lang=new Zend_Session_Namespace('lang');
			$lang = $session_lang->lang_id;
			
			$str = 'name_en';
			if($lang==1){
				$str = 'name_kh';
			}
		
			$from_date =(empty($search['start_date']))? '1': " s.buy_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " s.buy_date <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
			$sql=" 
				SELECT 
					`s`.`id` AS `id`,
					`s`.`branch_id`,
					`s`.`house_id`,
					(SELECT
						 `ln_project`.`project_name`
					   FROM `ln_project`
					   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
					   LIMIT 1) AS `branch_name`,
					 c.client_number,
					`c`.`name_kh`         AS `name_kh`,
					`c`.`phone`         AS `phone`,
					`p`.`land_address`    AS `land_address`,
					`p`.`street`          AS `street`,
					(SELECT  pt.type_nameen FROM ln_properties_type AS pt  WHERE pt.`id` = p.property_type LIMIT 1) AS propertyTypeName,
					(SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
					`s`.`price_before`    AS `price_before`,
					`s`.`discount_amount` AS `discount_amount`,
					CONCAT(`s`.`discount_percent`,'%') AS `discount_percent`,
			   
					`s`.`price_sold`     AS `price_sold`,
					(SELECT SUM((`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`)) + ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1))
					   FROM `ln_client_receipt_money` `cr`
					   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `totalpaid_amount`,   
				   
			   (SELECT
				 (`s`.`price_sold`-SUM(`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`) - ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1)) )
			   FROM `ln_client_receipt_money` `cr`
			   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `balance_remain`,   
				`s`.`buy_date`        AS `buy_date`,
				(SELECT  first_name FROM rms_users WHERE id=s.user_id limit 1 ) AS user_name,
				 s.status,
				 CASE    
						WHEN  `s`.`is_cancel` = 0 THEN ' '
						WHEN  `s`.`is_cancel` = 1 THEN '".$tr->translate("CANCELED")."'
						END AS isCancelTitle,
				is_cancel						
				FROM ((`ln_sale` `s`
					JOIN `ln_client` `c`)
				   JOIN `ln_properties` `p`)
				WHERE ((`c`.`client_id` = `s`.`client_id`)
			   AND (`p`.`id` = `s`.`house_id`)) ";
	   
    		//$sql="SELECT * FROM `v_getsalehistory` WHERE 1 ";
    		//$order =' ORDER BY house_id,is_cancel ASC';
    		//$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
    		//$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
    		//$where = " AND ".$from_date." AND ".$to_date;
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$where.=$dbp->getAccessPermission("s.branch_id");
    		
    		if($search['branch_id']>0){
    			$where.= " AND s.branch_id = ".$search['branch_id'];
    		}
    		if (!empty($search['adv_search'])){
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
				$s_where[] = " s.total_duration LIKE '%{$s_search}%'";
				$where .=' AND ( '.implode(' OR ',$s_where).')';
    		}
    		if($search['land_id']>0){
				$where.= " AND (s.house_id = ".$search['land_id']." OR p.old_land_id LIKE '%".$search['land_id']."%')";
//     			$where.= " AND house_id = ".$search['land_id'];
//    			$where.= " AND ( house_id = ".$search['land_id']." OR (SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = v_getsalehistory.house_id LIMIT 1) LIKE '%".$search['land_id']."%')";
    		}
    		if(!empty($search['client_name'])){
    			$where.= " AND `s`.`client_id` = ".$search['client_name'];
    		}
    		$order = " ORDER BY s.id DESC";
    		return $db->fetchAll($sql.$where.$order);
    	}
    function getFirstDepositAgreement($sale_id){
    	$db = $this->getAdapter();
    	$sql="SELECT crm.`recieve_amount` AS recieve_amount,
    			crm.date_pay,
    			(SELECT percent_agree FROM `ln_saleschedule` WHERE is_installment = 1 AND sale_id=$sale_id ORDER BY id ASC  LIMIT 1) AS percent_agree,
    			(SELECT percent FROM `ln_saleschedule` WHERE is_installment = 1 AND sale_id=$sale_id ORDER BY id DESC  LIMIT 1) AS percent,
    			(SELECT second_depostit FROM  ln_sale WHERE id = $sale_id ) AS  second_depostit,
    			(SELECT second_depostit FROM  ln_sale WHERE id = $sale_id ) AS  second_depostit,
    			(SELECT validate_date FROM  ln_sale WHERE id = $sale_id ) AS  validate_date
    		FROM `ln_client_receipt_money` AS crm WHERE crm.status=1 AND crm.field3=1
				AND sale_id = $sale_id ORDER BY crm.id ASC";
    	return $db->fetchRow($sql);
    }
   function getAgreementBySaleID($id=null){//bppt,natha,longny,moul mith
    		$db = $this->getAdapter();
			
			$dbp = new Application_Model_DbTable_DbGlobal();
			$currentUserId=$dbp->getUserId();
			$currentUserId = empty($currentUserId)?0:$currentUserId;
		
    		$sql="SELECT
				  `s`.`id`              AS `id`,
				  `s`.`sale_number`     AS `sale_number`,
				  `s`.`payment_id`      AS `payment_id`,
				  `s`.`branch_id`       AS `branch_id`,
				  `s`.`client_id`       AS `client_id`,
				  `s`.`price_before`    AS `price_before`,
				  `s`.`discount_amount` AS `discount_amount`,
				   s.discount_percent,
				  `s`.`price_sold`      AS `price_sold`,
				   s.oversold_price,
				  `s`.`other_fee`       AS `other_fee`,
				  `s`.`admin_fee`       AS `admin_fee`,
				  `s`.`paid_amount`     AS `paid_amount`,
				  `s`.`balance`         AS `balance`,
				  `s`.`amount_collect`  AS `amount_collect`,
				  `s`.`interest_rate`   AS `interest_rate`,
				  `s`.`total_duration`  AS `total_duration`,
				  `s`.`first_payment`   AS `first_payment`,
				   s.lastpayment_amount,
				   s.is_reschedule,
				   s.land_price,
				   s.amount_build,
				   s.build_start,
				   s.buy_date,
				   s.`startcal_date`,
				   s.`end_line`,
    			   s.validate_date,
				   s.agreement_date,
				   s.note_agreement,
				   s.is_verify,
				   s.store_number,
				    s.second_depostit,
				    s.witness_i,
				    s.witness_ii,
				   (SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id = s.user_id LIMIT 1 ) AS user_name,
				   (SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE rms_users.id = $currentUserId LIMIT 1 ) AS currentUserName,
				   (SELECT co_khname FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS staff_name,
				   (SELECT name_kh FROM `ln_view` WHERE type=25 and key_code=s.payment_id limit 1) AS payment_type,
				   
				   (SELECT name_kh FROM `ln_view` WHERE type=25 and key_code=s.pre_schedule_opt limit 1) AS preScheduleOptTitle,
				   s.pre_schedule_opt,
				   s.pre_percent_payment,
				   s.pre_percent_installment,
				   s.pre_amount_month,
				   s.pre_amount_year,
				   s.pre_fix_payment,
				   
				   s.agreement_for,
				   (SELECT name_kh FROM `ln_view` WHERE type=31 and key_code=s.agreement_for limit 1) AS titlePlong,
				  `p`.`project_name`,
				  `p`.`logo` AS project_logo,
				  `p`.`branch_tel`,
				  `p`.`other` as p_other,
			      `p`.`br_address` AS `project_location`,
				  
				   (SELECT ci.nameKh FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS project_manager_namekh,
				   (SELECT ci.nationality FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS project_manager_nationality,
				   (SELECT ci.nationId FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS project_manager_nation_id,
				   (SELECT ci.nationIdIssueDate FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS p_nationid_issue,
				   (SELECT ci.currentAddress FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS project_manager_p_current_address,
				   (SELECT ci.position FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS position,
				   (SELECT ci.tel FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_tel,
				   (SELECT ci.tel FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS manager_tel,
				   (SELECT ci.dob FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS manager_dob,
				   (SELECT ci.sex FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS p_sex,
				   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=(SELECT ci.sex FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) limit 1) AS manager_sex ,
				  
				   (SELECT ci.nameKhWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_managername1,
				   (SELECT ci.nameKhWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_namekh,
				   (SELECT ci.nationalityWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_nationality,
				   (SELECT ci.nationIdWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_nation_id,
				   (SELECT ci.nationIdIssueDateWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_nation_id_issue,
				   (SELECT ci.currentAddressWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_current_address,
				   (SELECT ci.positionWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_position,
				   (SELECT ci.positionWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_position,
				   (SELECT ci.positionWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_position1,
				   (SELECT ci.telWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_tel1,
				   (SELECT ci.telWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS with_manager_tel,
				   (SELECT ci.dobWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_dob,
				   (SELECT ci.sexWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_sex,
				   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=(SELECT ci.sexWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) limit 1) AS sc_manager_sex ,
				  

                  `c`.`client_number` AS `client_code`,
     			  `c`.`name_kh` AS `client_namekh`,
     			  `c`.`name_en` AS `client_nameen`,
     			   c.dob,
     			   c.dob AS client_dob ,
     			   c.hname_kh,
     			   c.hname_kh AS with_client_name,
     			   c.sex,
    			   c.ksex,
    			    c.join_type,
    			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.ksex limit 1) AS partner_gender,
     			   c.dob_buywith,
     			   c.rid_no,
     			   c.rid_no AS with_client_nation_id,
     			   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`sex` LIMIT 1) AS client_sex,
     			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.sex limit 1) AS sexKh,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.joint_doc_type limit 1) AS joint_doc_type,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS client_d_type,
     			   
     			   c.p_nationality,
     			   c.p_nationality AS with_client_nationlity ,
  				  `c`.`nationality` AS `client_nationality`,
     			  `c`.`nation_id` AS `client_nation_id`,
     			   c.client_issuedateid,
     			   c.join_issuedateid,
                  `c`.`phone` AS `client_phone`,
  				  `c`.`house` AS `client_house_no`,
                  `c`.`street` AS `client_street`,
                  c.phone,
                  c.lphone as with_phone,
                  c.ghouse as with_house,
                  c.dstreet AS w_street,
                   c.arid_no AS witnesses,
                   c.remark AS clientNote,
				  (SELECT
				     `village`.`village_namekh`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`qvillage`)
				   LIMIT 1) AS `joint_village`,
    			  (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `join_commune`,
    			 (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `join_district`,
    			(SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `join_province`,	
    				
				  (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`village_id`
					                                 )
					   LIMIT 1) AS `client_village_kh`,
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`com_id`)
				   LIMIT 1) AS `client_commune_kh`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`dis_id`)
				   LIMIT 1) AS `client_districtkh`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`pro_id`)
				   LIMIT 1) AS `client_province_kh`,
				   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS client_buywith_sex,
				    (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS with_client_sex ,
				   c.hname_kh AS w_client_namekh,
				   c.dob_buywith AS w_client_dob_buywith,
				   c.p_nationality AS w_client_nationality,
				   c.`ghouse` AS w_client_house,
					c.lphone AS w_client_phone,
				  (SELECT
				     `village`.`village_name`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`qvillage`)
				   LIMIT 1) AS `w_client_village_en`,
					  (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`qvillage`
					                                 )
					   LIMIT 1) AS `w_client_village_kh`,
				  (SELECT
				     `comm`.`commune_name` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `w_client_commune_en`,
				   
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `w_client_commune_kh`,
				  (SELECT
				     `dist`.`district_name`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `w_client_district`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`)
				   LIMIT 1) AS `w_client_districtkh`,
				  (SELECT
				     `provi`.`province_en_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `w_client_province_en`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`)
				   LIMIT 1) AS `w_client_province_kh`,
				  
			(SELECT
				     `prope_type`.`type_nameen`
				   FROM `ln_properties_type` `prope_type`
				   WHERE (`prope_type`.`id` =`pp`.`property_type`)
				   LIMIT 1) AS `property_type_en`,
			(SELECT
				     `prope_type`.`type_namekh`
				   FROM `ln_properties_type` `prope_type`
				   WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `property_type_kh`,
		   (SELECT
			 `prope_type`.`note`
		   FROM `ln_properties_type` `prope_type`
		   WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `propertyTypeNote`,
		   (SELECT
			 `prope_type`.`serviceFee`
		   FROM `ln_properties_type` `prope_type`
		   WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `propertyTypeFee`,
		   (SELECT
			 `prope_type`.`serviceFeeYear`
		   FROM `ln_properties_type` `prope_type`
		   WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `propertyTypeFeeYearly`,
		   
			`pp`.`land_size` AS `property_land_size`,
			
			 pp.`hardtitle`,
			`pp`.`hardtitle` AS `layoutNumber`,
			
			`pp`.`width` AS `property_width`,
		    `pp`.`height` AS `property_height`,
		     pp.`land_size`,
		    
		     
		    `pp`.`property_type`,
		    `pp`.`type_tob`,
		    `pp`.`land_code` AS `property_code`,
		    `pp`.`land_address` AS `property_title`,
 			 pp.`street` AS `property_street`,
 			 
 			 pp.land_width,
 			 pp.land_height,
 			 pp.`full_size`,
			 
			 pp.land_width AS houseWidth,
 			 pp.land_height AS houseHeight,
 			 pp.`full_size` AS houseFullSize,
 			 
 			 pp.floor,
 			 pp.living,
 			 pp.`bedroom`,
 			 pp.dinnerroom,
 			 pp.buidingyear,
 			 pp.buidingyear AS periodBuildDescription,
 			 pp.`parkingspace`,
 			 pp.`note` as `property_note`,
	(SELECT
    	`property`.`land_size`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1) AS `property_size`,
 			 pp.`north` AS border_north,
 			 pp.`south` AS border_south,
 			 pp.`east` AS border_east,
 			 pp.`west` AS border_west,
 			 pp.`old_land_id`,
 	(SELECT
	    	`a`.`description`
	    	FROM `ln_sale_conditionagreement` `a`
	    		WHERE (`a`.`saleId` = `s`.`id`)
	    	LIMIT 1) AS `additinalContract`,
	    	gendertitle,
	    	gendertitle1 
		FROM 
			`ln_sale` AS `s`,
			ln_project AS p ,
			`ln_client` AS c,
			ln_properties as pp
			
			WHERE 
			`p`.`br_id` = `s`.`branch_id` 
			AND `c`.`client_id` = `s`.`client_id`
			AND `pp`.`id` = `s`.`house_id`
			AND s.id=".$id;
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("`s`.`branch_id`");
    		
    		return $db->fetchRow($sql);
    }
    function addOversoldPrice($data){
    	$_db = $this->getAdapter();
    	$this->_name='ln_sale';
    	try{
    		$_arr = array(
    			'oversold_price' =>$data['oversold_price'],
    		);
    		$where="id = ".$data['sale_id'];
    		$this->update($_arr, $where);
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getIssueHouseAgreement($id=null){//bppt,natha,longny,moul mith
    	$db = $this->getAdapter();
    	$sql="
    	SELECT
    	s.buy_date,
    	s.agreement_date,
    	(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE rms_users.id = ih.user_id LIMIT 1 ) AS user_name,
    	(SELECT co_khname FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS staff_name,
    	 `p`.`logo` AS project_logo,
		`p`.`project_name`,
    	 p.p_sex,
    	`p`.`br_address` AS `project_location`,
    	`p`.`p_manager_namekh` AS `project_manager_namekh`,
    	`p`.`p_manager_nation_id` AS `project_manager_nation_id`,
    	`p`.`p_current_address` AS `project_manager_p_current_address`,
		`p`.`branch_tel`,
		`p`.`other` as p_other,
		`p`.`p_dob` as pManagerDob,
		`p`.`p_nationid_issue` as pManagerNationidIssue,
    	`c`.`name_kh` AS `client_namekh`,
    	c.hname_kh,
    	`c`.`nationality` AS `client_nationality`,
    	`c`.`nation_id` AS `client_nation_id`,
    	`c`.`house` AS `client_house_no`,
    	`c`.`street` AS `client_street`,
    	`c`.`sex` AS `clientSex`,
    	`c`.`ksex` AS `withClientSex`,
    	c.dob,
    	c.dob_buywith,
    	c.phone,
    	c.phone AS client_phone,
		c.lphone AS w_client_phone,
    	c.dstreet AS w_street,
    	c.client_issuedateid AS client_issuedateid,
		
		 (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS client_d_type,
		 
		(SELECT
    	`village`.`village_namekh`
    	FROM `ln_village` `village`
    	WHERE (`village`.`vill_id` = `c`.`village_id`
    	)
    	LIMIT 1) AS `client_village_kh`,
    	(SELECT
    	`comm`.`commune_namekh` FROM `ln_commune` `comm`
    	WHERE (`comm`.`com_id` = `c`.`com_id`)
    	LIMIT 1) AS `client_commune_kh`,
    	(SELECT
    	`provi`.`province_kh_name`
    	FROM `ln_province` `provi`
    	WHERE (`provi`.`province_id` = `c`.`pro_id`)
    	LIMIT 1) AS `client_province_kh`,
    	
    	(SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`dis_id`)
				   LIMIT 1) AS `client_districtkh`,
    	(SELECT
    	`prope_type`.`type_nameen`
    	FROM `ln_properties_type` `prope_type`
    	WHERE (`prope_type`.`id` =`pp`.`property_type`)
    	LIMIT 1) AS `property_type_en`,
    	`pp`.`property_type`,
    	`pp`.`land_address` AS `property_title`,
    	pp.`street` AS `property_street`,
    	ih.payment_id as installment_type,
    	ih.*
    	
    	FROM
	    	`ln_sale` AS `s`,
	    	ln_issue_house as ih,
	    	ln_project AS p ,
	    	`ln_client` AS c,
	    	ln_properties as pp
    	WHERE
	    	`p`.`br_id` = `s`.`branch_id`
	    	AND `c`.`client_id` = `s`.`client_id`
	    	AND `pp`.`id` = `s`.`house_id`
	    	AND ih.sale_id = s.id
	    	AND ih.id=".$id;
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("`s`.`branch_id`");
    
    	return $db->fetchRow($sql);
    }
    function getIssueHouseAgreementEnglish($id=null){//bppt,natha,longny,moul mith
    	$db = $this->getAdapter();
    	$sql="SELECT
	    	`p`.`map_url` AS `project_location_en`,
	    	(SELECT name_en FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS client_doc_type,
	    		
	    	(SELECT
	    	`village`.`village_name`
	    	FROM `ln_village` `village`
	    	WHERE (`village`.`vill_id` = `c`.`village_id`
	    	)
	    	LIMIT 1) AS `village_en`,
	    	
	    	(SELECT
	    	`comm`.`commune_name` FROM `ln_commune` `comm`
	    	WHERE (`comm`.`com_id` = `c`.`com_id`)
	    	LIMIT 1) AS `commune_en`,
	    	
	    	(SELECT
	    	`dist`.`district_name`
	    	FROM `ln_district` `dist`
	    	WHERE (`dist`.`dis_id` = `c`.`dis_id`)
	    	LIMIT 1) AS `district_en`,
	    	
	    	(SELECT
	    	`provi`.`province_en_name`
	    	FROM `ln_province` `provi`
	    	WHERE (`provi`.`province_id` = `c`.`pro_id`)
	    	LIMIT 1) AS `province_en`,
	    	 
	    	
	    	
	    	(SELECT
	    	`prope_type`.`type_nameen`
	    	FROM `ln_properties_type` `prope_type`
	    	WHERE (`prope_type`.`id` =`pp`.`property_type`)
	    	LIMIT 1) AS `property_type_en`
	    	
    	FROM
	    	`ln_sale` AS `s`,
	    	ln_project AS p ,
	    	`ln_client` AS c,
	    	ln_properties as pp
    	WHERE
	    	`p`.`br_id` = `s`.`branch_id`
	    	AND `c`.`client_id` = `s`.`client_id`
	    	AND `pp`.`id` = `s`.`house_id`
	    	AND s.id=".$id;
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("`s`.`branch_id`");
    
    	return $db->fetchRow($sql);
    }
//     function getAgreementBySaleID($id=null){//tbongkhmom
//     	$db = $this->getAdapter();
//     	$sql="
//     	SELECT
//     	`s`.`id`              AS `id`,
//     	`s`.`sale_number`     AS `sale_number`,
//     	`s`.`payment_id`      AS `payment_id`,
//     	`s`.`branch_id`       AS `branch_id`,
//     	`s`.`client_id`       AS `client_id`,
//     	`s`.`price_before`    AS `price_before`,
//     	`s`.`discount_amount` AS `discount_amount`,
//     	s.discount_percent,
//     	`s`.`price_sold`      AS `price_sold`,
//     	`s`.`other_fee`       AS `other_fee`,
//     	`s`.`admin_fee`       AS `admin_fee`,
//     	`s`.`paid_amount`     AS `paid_amount`,
//     	`s`.`balance`         AS `balance`,
//     	`s`.`amount_collect`  AS `amount_collect`,
//     	`s`.`interest_rate`   AS `interest_rate`,
//     	`s`.`total_duration`  AS `total_duration`,
//     	`s`.`first_payment`  AS `first_payment`,
//     	s.is_reschedule,
//     	s.land_price,
//     	s.amount_build,
//     	s.build_start,
//     	s.buy_date,
//     	s.agreement_date,
//     	s.`end_line`,
//     	s.`payment_id`,
//     	(SELECT name_kh FROM `ln_view` WHERE TYPE=25 AND key_code=s.payment_id LIMIT 1) AS payment_type,
//     	`p`.`project_name`,
//     	`p`.`br_address` AS `project_location`,
//     	`p`.`p_manager_namekh` AS `project_manager_namekh`,
//     	`p`.`p_manager_nationality` AS `project_manager_nationality`,
//     	`p`.`p_manager_nation_id` AS `project_manager_nation_id`,
//     	`p`.`p_current_address` AS `project_manager_p_current_address`,
//     	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=p.`p_sex` LIMIT 1) AS manager_sex,
//     	p.`p_dob` AS manager_dob,
//     	p.`p_nationid_issue`,
//     	p.w_manager_namekh ,
//     	p.w_manager_nation_id,
//     	p.`w_manager_nationality`,
//     	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=p.`w_sex` LIMIT 1) AS sc_manager_sex,
//     	p.`w_dob`,
//     	p.`w_current_address`,
//     	p.`w_nation_id_issue`,
//     	`c`.`client_number` AS `client_code`,
//     	`c`.`name_kh` AS `client_namekh`,
//     	`c`.`name_en` AS `client_nameen`,
//     	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`sex` LIMIT 1) AS client_sex,
//     	c.dob,
//     	c.hname_kh,
//     	c.dob_buywith,
//     	c.rid_no,
//     	c.p_nationality,
//     	`c`.`nationality` AS `client_nationality`,
//     	`c`.`nation_id` AS `client_nation_id`,
//     	`c`.`phone` AS `client_phone`,
//     	`c`.`house` AS `client_house_no`,
//     	`c`.`street` AS `client_street`,
//     	c.phone,
//     	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS client_buywith_sex,
//     	(SELECT
//     	`village`.`village_name`
//     	FROM `ln_village` `village`
//     	WHERE (`village`.`vill_id` = `c`.`village_id`)
//     	LIMIT 1) AS `client_village_en`,
//     	(SELECT
//     	`village`.`village_namekh`
//     	FROM `ln_village` `village`
//     	WHERE (`village`.`vill_id` = `c`.`village_id`
//     	)
//     	LIMIT 1) AS `client_village_kh`,
//     	(SELECT
//     	`comm`.`commune_name` FROM `ln_commune` `comm`
//     	WHERE (`comm`.`com_id` = `c`.`com_id`)
//     	LIMIT 1) AS `client_commune_en`
// 		FROM 
// 			`ln_sale` AS `s`,
// 			ln_project AS p ,
// 			`ln_client` AS c,
// 			ln_properties as pp
// 			WHERE 
// 			`p`.`br_id` = `s`.`branch_id` 
// 			AND `c`.`client_id` = `s`.`client_id`
// 			AND `pp`.`id` = `s`.`house_id`
// 			AND s.id=".$id;
//     		return $db->fetchRow($sql);
//     }
//     function getAgreementBySaleID($id=null){//tbongkhmom
//     	$db = $this->getAdapter();
//     	$sql="
//     	SELECT
//     	`s`.`id`              AS `id`,
//     	`s`.`sale_number`     AS `sale_number`,
//     	`s`.`payment_id`      AS `payment_id`,
//     	`s`.`branch_id`       AS `branch_id`,
//     	`s`.`client_id`       AS `client_id`,
//     	`s`.`price_before`    AS `price_before`,
//     	`s`.`discount_amount` AS `discount_amount`,
//     	s.discount_percent,
//     	`s`.`price_sold`      AS `price_sold`,
//     	`s`.`other_fee`       AS `other_fee`,
//     	`s`.`admin_fee`       AS `admin_fee`,
//     	`s`.`paid_amount`     AS `paid_amount`,
//     	`s`.`balance`         AS `balance`,
//     	`s`.`amount_collect`  AS `amount_collect`,
//     	`s`.`interest_rate`   AS `interest_rate`,
//     	`s`.`total_duration`  AS `total_duration`,
//     	`s`.`first_payment`  AS `first_payment`,
//     	s.is_reschedule,
//     	s.land_price,
//     	s.amount_build,
//     	s.build_start,
//     	s.buy_date,
//     	s.agreement_date,
//     	s.`end_line`,
//     	s.`payment_id`,
//     	(SELECT name_kh FROM `ln_view` WHERE TYPE=25 AND key_code=s.payment_id LIMIT 1) AS payment_type,
//     	`p`.`project_name`,
//     	`p`.`br_address` AS `project_location`,
//     	`p`.`p_manager_namekh` AS `project_manager_namekh`,
//     	`p`.`p_manager_nationality` AS `project_manager_nationality`,
//     	`p`.`p_manager_nation_id` AS `project_manager_nation_id`,
//     	`p`.`p_current_address` AS `project_manager_p_current_address`,
//     	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=p.`p_sex` LIMIT 1) AS manager_sex,
//     	p.`p_dob` AS manager_dob,
//     	p.`p_nationid_issue`,
//     	p.w_manager_namekh ,
//     	p.w_manager_nation_id,
//     	p.`w_manager_nationality`,
//     	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=p.`w_sex` LIMIT 1) AS sc_manager_sex,
//     	p.`w_dob`,
//     	p.`w_current_address`,
//     	p.`w_nation_id_issue`,
//     	`c`.`client_number` AS `client_code`,
//     	`c`.`name_kh` AS `client_namekh`,
//     	`c`.`name_en` AS `client_nameen`,
//     	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`sex` LIMIT 1) AS client_sex,
//     	c.dob,
//     	c.hname_kh,
//     	c.dob_buywith,
//     	c.rid_no,
//     	c.p_nationality,
//     	`c`.`nationality` AS `client_nationality`,
//     	`c`.`nation_id` AS `client_nation_id`,
//     	`c`.`phone` AS `client_phone`,
//     	`c`.`house` AS `client_house_no`,
//     	`c`.`street` AS `client_street`,
//     	c.phone,
//     	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS client_buywith_sex,
//     	(SELECT
//     	`village`.`village_name`
//     	FROM `ln_village` `village`
//     	WHERE (`village`.`vill_id` = `c`.`village_id`)
//     	LIMIT 1) AS `client_village_en`,
//     	(SELECT
//     	`village`.`village_namekh`
//     	FROM `ln_village` `village`
//     	WHERE (`village`.`vill_id` = `c`.`village_id`
//     	)
//     	LIMIT 1) AS `client_village_kh`,
//     	(SELECT
//     	`comm`.`commune_name` FROM `ln_commune` `comm`
//     	WHERE (`comm`.`com_id` = `c`.`com_id`)
//     	LIMIT 1) AS `client_commune_en`,
    		
//     	(SELECT
//     	`comm`.`commune_namekh` FROM `ln_commune` `comm`
//     	WHERE (`comm`.`com_id` = `c`.`com_id`)
//     	LIMIT 1) AS `client_commune_kh`,
//     	(SELECT
//     	`dist`.`district_name`
//     	FROM `ln_district` `dist`
//     	WHERE (`dist`.`dis_id` = `c`.`dis_id`) LIMIT 1) AS `client_district`,
//     	(SELECT
//     	`dist`.`district_namekh`
//     	FROM `ln_district` `dist`
//     	WHERE (`dist`.`dis_id` = `c`.`dis_id`)
//     	LIMIT 1) AS `client_districtkh`,
//     	(SELECT
//     	`provi`.`province_en_name`
//     	FROM `ln_province` `provi`
//     	WHERE (`provi`.`province_id` = `c`.`pro_id`) LIMIT 1) AS `client_province_en`,
//     	(SELECT
//     	`provi`.`province_kh_name`
//     	FROM `ln_province` `provi`
//     	WHERE (`provi`.`province_id` = `c`.`pro_id`)
//     	LIMIT 1) AS `client_province_kh`,
//     	c.hname_kh AS w_client_namekh,
//     	c.dob_buywith AS w_client_dob_buywith,
//     	c.p_nationality AS w_client_nationality,
//     	c.`ghouse` AS w_client_house,
//     	c.lphone AS w_client_phone,
//     	(SELECT
//     	`village`.`village_name`
//     	FROM `ln_village` `village`
//     	WHERE (`village`.`vill_id` = `c`.`qvillage`)
//     	LIMIT 1) AS `w_client_village_en`,
//     	(SELECT
//     	`village`.`village_namekh`
//     	FROM `ln_village` `village`
//     	WHERE (`village`.`vill_id` = `c`.`qvillage`
//     	)
//     	LIMIT 1) AS `w_client_village_kh`,
//     	(SELECT
//     	`comm`.`commune_name` FROM `ln_commune` `comm`
//     	WHERE (`comm`.`com_id` = `c`.`dcommune`)
//     	LIMIT 1) AS `w_client_commune_en`,
    		
//     	(SELECT
//     	`comm`.`commune_namekh` FROM `ln_commune` `comm`
//     	WHERE (`comm`.`com_id` = `c`.`dcommune`)
//     	LIMIT 1) AS `w_client_commune_kh`,
//     	(SELECT
//     	`dist`.`district_name`
//     	FROM `ln_district` `dist`
//     	WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `w_client_district`,
//     	(SELECT
//     	`dist`.`district_namekh`
//     	FROM `ln_district` `dist`
//     	WHERE (`dist`.`dis_id` = `c`.`adistrict`)
//     	LIMIT 1) AS `w_client_districtkh`,
//     	(SELECT
//     	`provi`.`province_en_name`
//     	FROM `ln_province` `provi`
//     	WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `w_client_province_en`,
//     	(SELECT
//     	`provi`.`province_kh_name`
//     	FROM `ln_province` `provi`
//     	WHERE (`provi`.`province_id` = `c`.`cprovince`)
//     	LIMIT 1) AS `w_client_province_kh`,
//     	(SELECT
//     	`prope_type`.`type_nameen`
//     	FROM `ln_properties_type` `prope_type`
//     	WHERE (`prope_type`.`id` =`pp`.`property_type`)
//     	LIMIT 1) AS `property_type_en`,
//     	(SELECT
//     	`prope_type`.`type_namekh`
//     	FROM `ln_properties_type` `prope_type`
//     	WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `property_type_kh`,
//     	`pp`.`width` AS `property_width`,
//     	`pp`.`height` AS `property_height`,
//     	`pp`.`land_code` AS `property_code`,
//     	`pp`.`land_address` AS `property_title`,
//     	pp.`street` AS `property_street`,
//     	pp.land_width,
//     	pp.land_height,
//     	pp.`land_size`,
//     	pp.`north` AS border_north,
//     	pp.`south` AS border_south,
//     	pp.`east` AS border_east,
//     	pp.`west` AS border_west
//     	FROM
//     	`ln_sale` AS `s`,
//     	ln_project AS p ,
//     	`ln_client` AS c,
//     	ln_properties AS pp
//     	WHERE
//     	`p`.`br_id` = `s`.`branch_id`
//     	AND `c`.`client_id` = `s`.`client_id`
//     	AND `pp`.`id` = `s`.`house_id`
//     	AND s.id=".$id;
//     	return $db->fetchRow($sql);
//     }
    function getAgreementBNGBySaleID($id=null){//BNG
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
    	s.discount_percent,
    	`s`.`price_sold`      AS `price_sold`,
    	`s`.`other_fee`       AS `other_fee`,
    	`s`.`admin_fee`       AS `admin_fee`,
    	`s`.`paid_amount`     AS `paid_amount`,
    	`s`.`balance`         AS `balance`,
    	`s`.`amount_collect`  AS `amount_collect`,
    	`s`.`interest_rate`   AS `interest_rate`,
    	`s`.`total_duration`  AS `total_duration`,
    	`s`.`first_payment`  AS `first_payment`,
    	s.is_reschedule,
    	s.land_price,
    	s.amount_build,
    	s.build_start,
    	s.buy_date,
    	s.agreement_date,
    	s.`end_line`,
    	s.`payment_id`,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=25 AND key_code=s.payment_id LIMIT 1) AS payment_type,
    	`p`.`project_name`,
    	`p`.`br_address` AS `project_location`,
    	`p`.`p_manager_namekh` AS `project_manager_namekh`,
    	`p`.`p_manager_nationality` AS `project_manager_nationality`,
    	`p`.`p_manager_nation_id` AS `project_manager_nation_id`,
    	`p`.`p_current_address` AS `project_manager_p_current_address`,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=p.`p_sex` LIMIT 1) AS manager_sex,
    	p.`p_dob` AS manager_dob,
    	p.`p_nationid_issue`,
    	p.w_manager_namekh ,
    	p.w_manager_nation_id,
    	p.`w_manager_nationality`,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=p.`w_sex` LIMIT 1) AS sc_manager_sex,
    	p.`w_dob`,
    	p.`w_current_address`,
    	p.`w_nation_id_issue`,
    	`c`.`client_number` AS `client_code`,
    	`c`.`name_kh` AS `client_namekh`,
    	`c`.`name_en` AS `client_nameen`,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`sex` LIMIT 1) AS client_sex,
    	c.dob,
    	c.hname_kh,
    	c.dob_buywith,
    	c.rid_no,
    	c.p_nationality,
    	`c`.`nationality` AS `client_nationality`,
    	`c`.`nation_id` AS `client_nation_id`,
    	`c`.`phone` AS `client_phone`,
    	`c`.`house` AS `client_house_no`,
    	`c`.`street` AS `client_street`,
    	c.phone,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS client_buywith_sex,
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
    	c.hname_kh AS w_client_namekh,
    	c.dob_buywith AS w_client_dob_buywith,
    	c.p_nationality AS w_client_nationality,
    	c.`ghouse` AS w_client_house,
    	c.lphone AS w_client_phone,
    	(SELECT
    	`village`.`village_name`
    	FROM `ln_village` `village`
    	WHERE (`village`.`vill_id` = `c`.`qvillage`)
    	LIMIT 1) AS `w_client_village_en`,
    	(SELECT
    	`village`.`village_namekh`
    	FROM `ln_village` `village`
    	WHERE (`village`.`vill_id` = `c`.`qvillage`
    	)
    	LIMIT 1) AS `w_client_village_kh`,
    	(SELECT
    	`comm`.`commune_name` FROM `ln_commune` `comm`
    	WHERE (`comm`.`com_id` = `c`.`dcommune`)
    	LIMIT 1) AS `w_client_commune_en`,
    
    	(SELECT
    	`comm`.`commune_namekh` FROM `ln_commune` `comm`
    	WHERE (`comm`.`com_id` = `c`.`dcommune`)
    	LIMIT 1) AS `w_client_commune_kh`,
    	(SELECT
    	`dist`.`district_name`
    	FROM `ln_district` `dist`
    	WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `w_client_district`,
    	(SELECT
    	`dist`.`district_namekh`
    	FROM `ln_district` `dist`
    	WHERE (`dist`.`dis_id` = `c`.`adistrict`)
    	LIMIT 1) AS `w_client_districtkh`,
    	(SELECT
    	`provi`.`province_en_name`
    	FROM `ln_province` `provi`
    	WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `w_client_province_en`,
    	(SELECT
    	`provi`.`province_kh_name`
    	FROM `ln_province` `provi`
    	WHERE (`provi`.`province_id` = `c`.`cprovince`)
    	LIMIT 1) AS `w_client_province_kh`,
    	(SELECT
    	`prope_type`.`type_nameen`
    	FROM `ln_properties_type` `prope_type`
    	WHERE (`prope_type`.`id` =`pp`.`property_type`)
    	LIMIT 1) AS `property_type_en`,
    	(SELECT
    	`prope_type`.`type_namekh`
    	FROM `ln_properties_type` `prope_type`
    	WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `property_type_kh`,
    	`pp`.`width` AS `property_width`,
    	`pp`.`height` AS `property_height`,
    	`pp`.`land_code` AS `property_code`,
    	`pp`.`land_address` AS `property_title`,
    	pp.`street` AS `property_street`,
    	pp.land_width,
    	pp.land_height,
    	pp.`land_size`,
    	pp.`north` AS border_north,
    	pp.`south` AS border_south,
    	pp.`east` AS border_east,
    	pp.`west` AS border_west
    	FROM
    	`ln_sale` AS `s`,
    	ln_project AS p ,
    	`ln_client` AS c,
    	ln_properties AS pp
    	WHERE
    	`p`.`br_id` = `s`.`branch_id`
    	AND `c`.`client_id` = `s`.`client_id`
    	AND `pp`.`id` = `s`.`house_id`
    	AND s.id=".$id;
    	return $db->fetchRow($sql);
    }
    /*function getAgreementBySaleID($id=null){//for evergreen 100% right
    	$db = $this->getAdapter();
    	$sql="SELECT
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
    	p.w_manager_position,
    	p.w_manager_tel,
    	 
    	p.w_managername1,
    	p.w_manager_position1,
    	p.w_manager_tel1,
    	 
    	`c`.`client_number` AS `client_code`,
    	`c`.`name_kh` AS `client_namekh`,
    	`c`.`name_en` AS `client_nameen`,
    	`c`.`dob_buywith` AS `dob_buywith`,
    	`c`.`rid_no` AS `rid_no`,
    	c.dob as client_dob,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.sex) as client_sex,
    	hname_kh AS with_client_name,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.ksex) as with_client_sex,
    	c.p_nationality AS with_client_nation_id,
    	c.hname_kh,
    	`c`.`nationality` AS `client_nationality`,
    	`c`.`nation_id` AS `client_nation_id`,
    	`c`.`phone` AS `client_phone`,
    	`c`.`house` AS `client_house_no`,
    	`c`.`street` AS `client_street`,
    	c.p_nationality AS with_client_nationlity,
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
    	`property`.`land_size`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1) AS `property_size`,
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
    }*/
    function getFirstDepositBySaleID($id=null,$payment_id){//សម្រាប់លីមហេង ទាយយកថាត្រូវកក់ %?
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM `ln_saleschedule` AS sc WHERE sc.`sale_id`= ".$id;
    	if($payment_id==4){
    		$sql.=" AND sc.is_installment=1 ";
    	}
    	$order = ' AND is_rescheule=0 ORDER BY sc.no_installment ASC, sc.`date_payment` ASC ';
    	return $db->fetchRow($sql.$order);
    }
    function getScheduleBySaleID($id=null,$payment_id){
    		$db = $this->getAdapter();
    		$sql=" SELECT *,(SELECT name_kh FROM ln_view WHERE type =29 AND key_code = sc.ispay_bank LIMIT 1) AS payment_type FROM `ln_saleschedule` AS sc WHERE sc.`sale_id`= ".$id;
    		if($payment_id==4){
    			$sql.=" AND sc.is_installment=1 ";
    		}
    		$order = ' AND is_rescheule=0 ORDER BY sc.no_installment ASC, sc.`date_payment` ASC ';
    		//ORDER BY no_installment ASC,date_payment ASC, collect_by ASC, status DESC
    		return $db->fetchAll($sql.$order);
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
//     function getIncomeChangehouse($search){
//     	$db = $this->getAdapter();
//     	$sql="SELECT ic.`category_id`,    	
//     	SUM(ic.`total_amount`) AS total_amount,
//     	(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type =12 AND v.key_code = ic.`category_id` LIMIT 1) AS category_name,
//     	ic.`date` FROM `ln_otherincome` AS ic WHERE 1 ";
//     	$order =" GROUP BY ic.`category_id` ORDER BY ic.`category_id` ";
//     	$where="";
//     	$from_date =(empty($search['start_date']))? '1': " ic.`date` >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': " ic.`date` <= '".$search['end_date']." 23:59:59'";
//     	$where = " AND ".$from_date." AND ".$to_date;
//     	if($search['branch_id']>0){
//     		$where.=" AND branch_id=".$search['branch_id'];
//     	}
//     	return $db->fetchAll($sql.$where.$order);
//     }
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
    function getAllCommission($search){
    		$db = $this->getAdapter();
    		$from_date =(empty($search['start_date']))? '1': "c.`for_date` >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "c.`for_date` <= '".$search['end_date']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
    		$sql ='SELECT c.`id`,
    			s.id AS saleid,
	    		p.`project_name`,
	    		s.`sale_number`,
	    		s.full_commission,
	    		clie.`name_kh` AS client_name,
	    		(SELECT protype.type_nameen FROM `ln_properties_type` AS protype WHERE protype.id = pro.`property_type` LIMIT 1) AS property_type,
	    		pro.`land_address`,pro.`street`,
	    		s.price_sold,
	    		c.cheque,
	    		c.cheque_issuer,
	    		(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=c.payment_id limit 1) AS payment_type,
	    		(SELECT co_khname FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS staff_name,
	    		(SELECT co_code FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS co_code,
	    		(SELECT sex FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS sex,
	    		(SELECT tel FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS tel,
	    		c.total_amount,
	    		c.invoice,
	    		c.payment_id,
	    		for_date AS `create_date`, c.`status`,c.is_closed,
	    		(SELECT CONCAT(COALESCE(last_name,"")," ",COALESCE(first_name,""))  FROM rms_users WHERE id = c.user_id LIMIT 1 ) AS user_name
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
				WHERE full_commission>0 AND s.is_cancel = 0 ";
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
    	function getCustomerRequirement($search=null){
    		$db = $this->getAdapter();
    		$sql="SELECT c.*,
	    			(SELECT title FROM `rms_know_by` WHERE rms_know_by.id=c.know_by LIMIT 1) as know_by,			
					(SELECT  first_name FROM rms_users WHERE id = c.user_id LIMIT 1 ) AS user_name,
				STATUS FROM in_customer AS c WHERE c.`status`=1 ";
    		$where ="";
    		$from_date =(empty($search['start_date']))? '1': " c.`date` >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " c.`date` <= '".$search['end_date']." 23:59:59'";
    		$where.= " AND ".$from_date." AND ".$to_date;
    		if(!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['adv_search']));
    			$s_where[] =" c.`name` LIKE '%{$s_search}%'";
    			$s_where[]=" c.`phone` LIKE '%{$s_search}%'";
    			$s_where[]=" c.`from_price` LIKE '%{$s_search}%'";
    			$s_where[]=" c.`to_price` LIKE '%{$s_search}%'";
    			$s_where[]=" c.`type` LIKE '%{$s_search}%'";
    			$s_where[]=" (SELECT  first_name FROM rms_users WHERE id = c.user_id LIMIT 1 ) LIKE '%{$s_search}%'";
    			$where .=' AND ( '.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['user'])){
    			$where.= " AND c.user_id = ".$search['user'];
    		}
    		if(!empty($search['statusreq'])){
    			$where.= " AND statusreq = '".$search['statusreq']."'";
    		}
    		if($search['know_by']>0){
    			$where.= " AND know_by = ".$search['know_by'];
    		}
    		
    		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    		$userid = $session_user->user_id;
    		
    		$db_user=new Application_Model_DbTable_DbUsers();
    		$user_info = $db_user->getUserInfo($userid);
    		if (!empty($user_info['staff_id'])){
    			$where.= " AND user_id = ".$userid;
    		}
    		
    		$dbgb = new Application_Model_DbTable_DbGlobal();
    		$userinfo = $dbgb->getUserInfo();
    		if($userinfo['level']!=1 AND $userinfo['level']!=2){
    			$where.= " AND user_id = ".$userinfo['user_id'];
    		}
    		$where.=" ORDER BY c.id DESC ";
    		return $db->fetchAll($sql.$where);
    	}
    	
    	function getNumberInkhmer($number){
    		$khmernumber = array("០","១","២","៣","៤","៥","៦","៧","៨","៩");
    		$spp = str_split($number);
    		$num="";
    		foreach ($spp as $ss){
    	
    			if (!empty($khmernumber[$ss])){
    				$num.=$khmernumber[$ss];
    			}else{
    				$num.=$ss;
    			}
    		}
    		return $num;
    	}
	function verifyAgreement($data){
 		$db = $this->getAdapter();
 		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
 		$user_id = $session_user->user_id;
		$this->_name='ln_sale';
		$arr = array(
				'is_verify'=>1,
				'verify_by'=>$user_id
				);
		$where=' id='.$data['sale_id'];
		$this->update($arr, $where);
	}
	
	function getProject($search=null){
		$db = $this->getAdapter();
		$sql="SELECT p.* FROM `ln_project` as p WHERE 1";
		if ($search['branch_id']>0){
			$sql.=" AND p.br_id=".$search['branch_id'];
		}
		return $db->fetchAll($sql);
	}
	function getExpensByMonth($branch,$date,$monthlytype=1){
		$db = $this->getAdapter();
		if ($monthlytype==1){
			$date = date("Y-m",strtotime($date));
			$sql="SELECT 
				SUM(ex.total_amount) AS totalbymonth
				FROM `ln_expense` AS ex
				WHERE ex.status=1 AND DATE_FORMAT(ex.date,'%Y-%m') ='$date'";
			if (!empty($branch)){
				$sql.=" AND ex.branch_id=".$branch;
			}
			$total_expense = $db->fetchOne($sql);
			if(empty($total_expense)){$total_expense=0;}
			
			
			$sql="SELECT SUM(total_amount) FROM `ln_comission` WHERE status=1 
				AND DATE_FORMAT(for_date,'%Y-%m') ='$date'";
			if (!empty($branch)){
				$sql.=" AND branch_id=".$branch;
			}
			$total_commission = $db->fetchOne($sql);
			if(empty($total_commission)){
				$total_commission=0;
			}
			return $total_expense+$total_commission;
		}else{
			$date = date("Y",strtotime($date));
			$sql="SELECT
			SUM(ex.total_amount) AS totalbymonth
			FROM `ln_expense` AS ex
			WHERE ex.status=1 AND DATE_FORMAT(ex.date,'%Y') ='$date'";
			if (!empty($branch)){
				$sql.=" AND ex.branch_id=".$branch;
			}
			$total_expense = $db->fetchOne($sql);
			if(empty($total_expense)){
				$total_expense=0;
			}
			
			$sql="SELECT SUM(total_amount) FROM `ln_comission` WHERE status=1
			AND DATE_FORMAT(for_date,'%Y') ='$date'";
			if (!empty($branch)){
				$sql.=" AND branch_id=".$branch;
			}
			$total_commission = $db->fetchOne($sql);
			if(empty($total_commission)){
				$total_commission=0;
			}
			return $total_expense+$total_commission;
		}
		
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
		$sql="SELECT op.id,
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
	
	function getTotalPrinciplePaidById($id){
		$db = $this->getAdapter();
		$sql="SELECT SUM(c.total_principal_permonthpaid) FROM `ln_client_receipt_money` AS c
			WHERE c.sale_id = $id AND c.status=1";
		return $db->fetchOne($sql);
	}
	function getLastDatePaidById($id){
		$db = $this->getAdapter();
		$sql="SELECT c.date_pay FROM `ln_client_receipt_money` AS c
			WHERE c.sale_id = $id AND c.status=1 AND c.recieve_amount>0 ORDER BY c.id DESC LIMIT 1";
		return $db->fetchOne($sql);
	}
	
	function getAllPlongStep($search){
		 
		$dbp = new Application_Model_DbTable_DbGlobal();
		$from_date =(empty($search['start_date']))? '1': " pr.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " pr.date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql="SELECT `pr`.*,
		(SELECT `ln_project`.`project_name`   	FROM `ln_project`  	WHERE (`ln_project`.`br_id` = `pr`.`branch_id`)	LIMIT 1) AS `branch_name`,
		`c`.`name_kh`         AS `name_kh`,
		`p`.`land_address`    AS `land_address`,
		`p`.`street`          AS `street`,
		p.hardtitle,
		c.phone,
		CASE
		WHEN  pr.process_status = 1 THEN '1.HQ-P'
		WHEN  pr.process_status = 2 THEN '2.P-HQ'
		WHEN  pr.process_status = 3 THEN '3.HQ-T'
		WHEN  pr.process_status = 4 THEN '4.HQ-P'
		WHEN  pr.process_status = 5 THEN '5.HQ-C'
		END AS processing
		";
		$sql.=$dbp->caseStatusShowImage("pr.status");
		$sql.="
		FROM (`ln_processing_plong` `pr`,
		`ln_client` `c`
		JOIN `ln_properties` `p`)
		WHERE (`c`.`client_id` = `pr`.`customer_id`)
		AND (`p`.`id` = `pr`.`property_id`)
		";
		if(!empty($search['adv_search'])){
			$s_where = array();
		}
		if(!empty($search['client_name']) AND ($search['client_name'])>0){
			$where.= " AND `c`.`client_id`=".$search['client_name'];
		}
		if(($search['branch_id'])>0){
			$where.= " AND pr.branch_id = ".$search['branch_id'];
		}
		if($search['land_id']>0){
			$where.= " AND pr.property_id = ".$search['land_id'];
		}
		if (!empty($search['process_status'])){
			$where.= " AND pr.process_status = ".$search['process_status'];
		}
		$where.=$dbp->getAccessPermission("`pr`.`branch_id`");
	
		$order = " ORDER BY pr.id DESC";
		$db = $this->getAdapter();
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getPlongStepDetailByID($id,$step=1){
		$db = $this->getAdapter();
		$sql="SELECT * FROM ln_processing_plong_detail WHERE processplong_id =$id AND process_status = $step ORDER BY id DESC LIMIT 1";
		return $db->fetchRow($sql);
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
	
	function checkLandSaleNotUpdateLock($pro_id){
		$db = $this->getAdapter();
		$sql="SELECT p.land_address FROM `ln_properties` AS p WHERE p.id =$pro_id AND p.is_lock =0 LIMIT 1";
		return $db->fetchOne($sql);
		
	}
	function getAllHeadproperties($search=null){
		$db = $this->getAdapter();
		$to_enddate = (empty($search['end_date']))? '1': " s.buy_date <= '".$search['end_date']." 23:59:59'";
		$sql = "SELECT p.`id`,
			(SELECT project_name FROM ln_project WHERE br_id = p.`branch_id` limit 1) AS branch_name,
			p.`land_code`,p.`land_address`,p.`property_type`,p.`street`,p.hardtitle ,
			p.noteForLayout,
			(SELECT ps.title FROM `ln_plongstep_option` AS ps,ln_processing_plong AS pr WHERE ps.id = pr.process_status AND `p`.`id` = `pr`.`property_id` ORDER BY pr.id DESC limit 1 ) AS processing,
			(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type` LIMIT 1) AS pro_type,
			rp.layout_type,rp.date AS received_date,
			(SELECT cl.name_kh FROM ln_client AS cl WHERE cl.`client_id` = rp.`customer_id` LIMIT 1) AS client_name,
			(SELECT cl.phone FROM ln_client AS cl WHERE cl.`client_id` = rp.`customer_id` LIMIT 1) AS tel, 
			s.price_sold,
			(SELECT SUM(crm.total_principal_permonthpaid+crm.extra_payment) FROM `ln_client_receipt_money` AS crm WHERE crm.sale_id=s.id LIMIT 1) AS totalPaid,
			";
		$sql.=" (SELECT first_name FROM `rms_users` WHERE id=p.user_id LIMIT 1) AS user_name
			FROM `ln_properties` AS p
			    LEFT JOIN ln_receiveplong AS rp ON p.`id` = rp.`house_id` AND rp.`status`=1 
				LEFT JOIN ln_sale AS s ON s.`house_id` = p.`id` AND s.`is_cancel`=0 AND s.`status`=1 
			WHERE p.`status`=1 ";
	
		$from_date =(empty($search['start_date']))? '1': " p.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " p.create_date <= '".$search['end_date']." 23:59:59'";
		$where=" AND ".$from_date." AND ".$to_date;
	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("p.`branch_id`");
	
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
			$s_where[]=" p.`land_address` LIKE '%{$s_search}%'";
			$s_where[]=" p.street LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['streetlist'])){
			$where.= " AND street ='".$search['streetlist']."'";
		}
		if(!empty($search['plong_type'])){
			$where.= " AND rp.`layout_type` = '".$search['plong_type']."'";
		}
		if($search['process_status']>0){
			$where.=" AND (SELECT pr.process_status FROM ln_processing_plong AS pr WHERE `p`.`id` = `pr`.`property_id` AND pr.process_status LIMIT 1) = ".$search['process_status'];
		}
		if($search['plong_processtype']>0){
			if($search['plong_processtype']==1){
				//AND p.`hardtitle` !=''
				$where.="  AND p.id NOT IN (SELECT pr.property_id FROM ln_processing_plong AS pr WHERE `p`.`id` = `pr`.`property_id`)";
				$where.="  AND p.id NOT IN (SELECT rps.house_id FROM ln_receiveplong AS rps WHERE `p`.`id` = rps.`house_id` AND rps.status=1 ) " ;
			}elseif($search['plong_processtype']==2){
				$where.=" AND p.id IN (SELECT rp.house_id FROM ln_receiveplong AS pr WHERE `p`.`id` = rp.`house_id` ) ";
				$where.=" AND p.id NOT IN (SELECT pr.property_id FROM ln_processing_plong AS pr WHERE `p`.`id` = `pr`.`property_id`) ";
			}else{
				$where.=" AND p.id IN (SELECT rps.house_id FROM ln_receiveplong AS rps WHERE `p`.`id` = rps.`house_id` AND rps.status=1 ) ";
				$where.=" AND p.id IN (SELECT pr.property_id FROM ln_processing_plong AS pr WHERE `p`.`id` = `pr`.`property_id`) ";
				//AND p.`hardtitle` !='' 
			}
		}
		
		$search['sale_status'] = empty($search['sale_status'])?0:$search['sale_status'];
		if($search['sale_status']>0){
			if($search['sale_status']==1){//full paid
				$where.=" AND s.price_sold <= ((SELECT COALESCE(SUM(rm.total_principal_permonthpaid+rm.extra_payment),0) FROM `ln_client_receipt_money` as rm WHERE rm.status=1 AND rm.sale_id=s.id LIMIT 1)) ";
			}else if($search['sale_status']==2){
				$where.=" AND s.price_sold > ((SELECT COALESCE(SUM(rm.total_principal_permonthpaid+rm.extra_payment),0) FROM `ln_client_receipt_money` as rm WHERE rm.status=1 AND rm.sale_id=s.id LIMIT 1) ) ";
			}else if($search['sale_status']==3){
				$where.=" AND s.is_cancel = 0 ";
			}else if($search['sale_status']==4){
				$where.=" AND s.is_cancel = 1 ";
			}else{
			}
		}
		
		$where.= " ORDER BY p.branch_id,p.`property_type`,p.`street` ASC, LENGTH(land_address), land_address ASC  ";
		return $db->fetchAll($sql.$where);
	}
	
	
	
	function getAllIssueHouse($search = null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
		$from_date =(empty($search['start_date']))? '1': " rs.issue_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " rs.issue_date <= '".$search['end_date']." 23:59:59'";
		$where="";
	
		if (!empty($search['start_date'])){
			$where = " AND ".$from_date." AND ".$to_date;
		}
		// 		$sql = "SELECT rs.id,
		// 		(SELECT ln_project.project_name FROM `ln_project` WHERE ln_project.br_id = rs.branch_id LIMIT 1) AS branch_name,
		// 		(SELECT name_kh FROM ln_client AS c WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1) customer_name,
		// 		(SELECT phone FROM ln_client AS c WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1) AS tel,
		// 		p.land_address ,
		// 		p.street  AS street,
		// 		CASE
		// 		WHEN  `rs`.`payment_id` = 1 THEN '".$tr->translate("IS_PAYOFF")."'
		// 		WHEN  `rs`.`payment_id` = 2 THEN '".$tr->translate("PAY_INSTALLMENT")."'
		// 		END AS payment_id,
		// 		rs.electric_start,rs.water_start,rs.issue_date,
		// 		(SELECT  first_name FROM rms_users WHERE rms_users.id=rs.user_id LIMIT 1) AS user_name,
		// 		rs.note
		// 		FROM
		// 		ln_sale AS s,
		// 		`ln_properties` `p`,
		// 		ln_issue_house AS rs
		// 		WHERE s.id = rs.sale_id AND p.id = s.house_id AND rs.status=1 ";
		$sql = "SELECT
		s.id AS sale_id,
		rs.id,
		(SELECT ln_project.project_name FROM `ln_project` WHERE ln_project.br_id = p.branch_id LIMIT 1) AS branch_name,
		(SELECT name_kh FROM ln_client AS c WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1) customer_name,
		(SELECT phone FROM ln_client AS c WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1) AS tel,
		p.land_address ,
		p.street  AS street,
		CASE
		WHEN  `rs`.`payment_id` = 1 THEN '".$tr->translate("IS_PAYOFF")."'
		WHEN  `rs`.`payment_id` = 2 THEN '".$tr->translate("PAY_INSTALLMENT")."'
		END AS payment_id,
		rs.electric_start,rs.water_start,rs.issue_date,
		(SELECT  first_name FROM rms_users WHERE rms_users.id=rs.user_id LIMIT 1) AS user_name,
		rs.note
		FROM ln_properties AS p
		LEFT JOIN ln_sale AS s
		ON  p.id  = s.house_id AND s.is_cancel=0 AND s.status=1
		LEFT JOIN ln_issue_house AS rs
		ON s.id = rs.sale_id AND rs.status=1
		WHERE s.is_cancel=0 ";
	
		$string="";
		if(!empty($search['give_status'])){
			if ($search['give_status']==1){
				$sql.=" AND rs.id IS NULL";
			}else if ($search['give_status']==2){
				$sql.=" AND  rs.status=1";
			}
		}
		$sql.=$string;
	
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " p.land_address LIKE '%{$s_search}%'";
			$s_where[] = " p.street LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_kh FROM ln_client AS c WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['streetlist'])){
			$where.= " AND street ='".$search['streetlist']."'";
		}
		if(!empty($search['land_id']) AND $search['land_id']>-1){
			$where.= " AND (s.house_id = ".$search['land_id']." OR p.old_land_id LIKE '%".$search['land_id']."%')";
		}
		if($search['branch_id']>0){
			$where.= " AND p.branch_id = ".$search['branch_id'];
		}
		if($search['payment_id']>0){
			$where.= " AND rs.payment_id = ".$search['payment_id'];
		}
		if(!empty($search['client_name'])){
			$where.= " AND `s`.`client_id` = ".$search['client_name'];
		}
	
		$where.=$dbp->getAccessPermission("rs.branch_id");
		$order=" ORDER BY s.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getCustomerReceivedPlong($search=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$plogtitle = $tr->translate('PLONG_TITLE');
		
		$from_date =(empty($search['from_date_search']))? '1': "c.`create_date` >= '".$search['from_date_search']." 00:00:00'";
		$to_date = (empty($search['to_date_search']))? '1': "c.`create_date` <= '".$search['to_date_search']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql ='SELECT c.`id`,
				    p.`project_name` as branch_name,
					clie.`name_kh` AS client_name,
				    (SELECT protype.type_nameen FROM `ln_properties_type` AS protype WHERE protype.id = pro.`property_type` LIMIT 1) AS property_type,
					pro.`land_address`,pro.`street`,`layout_type`,c.date,
					c.create_date,c.note,c.`status`,"'.$plogtitle.'",
					pro.`hardtitle`,
					(SELECT first_name FROM `rms_users` WHERE  id=c.user_id LIMIT 1) AS user_name
			FROM `ln_receiveplong` AS c ,`ln_project` AS p,`ln_properties` AS pro,
				`ln_client` AS clie
			WHERE p.`br_id` = c.`branch_id` AND pro.`id` = c.`house_id` AND
				clie.`client_id` = c.`customer_id` AND c.`status`=1';
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("c.`branch_id`");
		
		if($search['branch_id']>0){
			$where.= " AND c.branch_id = ".$search['branch_id'];
		}
		if($search['land_id']>0){
			$where.= " AND c.house_id = ".$search['land_id'];
		}
		if($search['client_name']>0){
			$where.= " AND c.customer_id = ".$search['client_name'];
		}
		if(!empty($search['plong_type'])){
			$where.= " AND c.layout_type = '".$search['plong_type']."'";
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " clie.`name_kh` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`land_address` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`street` LIKE '%{$s_search}%'";
			$s_where[] = " c.`note` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`hardtitle` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("c.`branch_id`");
		
		$where.=" ORDER BY c.`id` DESC ";
		return $db->fetchAll($sql.$where);
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
	
	function getCommissionPaymentDetailList($search){
    	$db = $this->getAdapter();
    	$sql="SELECT pd.*,
				(SELECT  p.`project_name` FROM `ln_project` AS p WHERE (p.`br_id` = p.`branch_id`) LIMIT 1) AS branchName,
				(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=s.`client_id` LIMIT 1) AS customerName,
				(SELECT land_address FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS landCode,
				(SELECT street FROM `ln_properties` WHERE ln_properties.id=s.house_id LIMIT 1) AS street,
				(SELECT co_khname FROM `ln_staff` WHERE co_id=p.agency_id LIMIT 1) AS agencyNname,
				(SELECT tel FROM `ln_staff` WHERE co_id=p.agency_id LIMIT 1) AS agencyTel,
				(SELECT sex FROM `ln_staff` WHERE co_id=p.agency_id LIMIT 1) AS sex,
				s.full_commission,
				p.date_payment,
				p.receipt_no,
				(SELECT  CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,'')) FROM rms_users WHERE id=p.user_id LIMIT 1 ) AS userName
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
	public function getCustomerRequirmentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT *,(SELECT kn.title FROM rms_know_by as kn WHERE kn.id = know_by LIMIT 1) as know_bytitle FROM in_customer WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function AllHistoryContact($crm_id){
		$db = $this->getAdapter();
		$sql="SELECT c.*,
		(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE c.user_contact=id LIMIT 1 ) AS user_contact_name
		FROM `ln_history_contact` AS c WHERE customer_id = $crm_id ORDER BY c.id DESC";
		return $db->fetchAll($sql);
	}
	public function AllHistoryContactList($search){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT c.*,
				(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE c.user_contact=id LIMIT 1 ) AS user_contact_name,
				name, phone,
				(SELECT title FROM `rms_know_by` WHERE rms_know_by.id=know_by LIMIT 1) as know_by,
				 from_price,to_price,requirement,type,description,	
				statusreq
			
		";
		$sql.=", CASE
		WHEN  c.proccess = 0 THEN '".$tr->translate("DROPPED")."'
		WHEN c.proccess = 1 THEN '".$tr->translate("PROCCESSING")."'
		WHEN c.proccess = 2 THEN '".$tr->translate("WAITING_RESPONSE")."'
		WHEN c.proccess = 3 THEN '".$tr->translate("COMPLETED_CONTACT")."'
		
		END AS proccess ";
		$sql.=" FROM `ln_history_contact` AS c,
				in_customer AS ct ";
		
		$from_date =(empty($search['start_date']))? '1': " c.contact_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " c.contact_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;		
		$where.=" AND ct.id = c.customer_id ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " ct.name LIKE '%{$s_search}%'";
			$s_where[] = " c.feedback LIKE '%{$s_search}%'";
			$s_where[] = " ct.phone LIKE '%{$s_search}%'";
			
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['proccessSearch']>-1){
			$where.= " AND c.proccess = '".$search['proccessSearch']."'";
		}
		if($search['know_by']>0){
			$where.= " AND ct.know_by = ".$search['know_by'];
		}	
		if(!empty($search['user'])){
			$where.= " AND c.user_contact = ".$search['user'];
		}
		$order=" ORDER BY c.contact_date DESC,c.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getAgreementByChangeOwnerSaleID($id=null){
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
				   s.discount_percent,
				  `s`.`price_sold`      AS `price_sold`,
				  `s`.`other_fee`       AS `other_fee`,
				  `s`.`admin_fee`       AS `admin_fee`,
				  `s`.`paid_amount`     AS `paid_amount`,
				  `s`.`balance`         AS `balance`,
				  `s`.`amount_collect`  AS `amount_collect`,
				  `s`.`interest_rate`   AS `interest_rate`,
				  `s`.`total_duration`  AS `total_duration`,
				  `s`.`first_payment`  AS `first_payment`,
				   s.lastpayment_amount,
				   s.is_reschedule,
				   s.land_price,
				   s.amount_build,
				   s.build_start,
				   s.buy_date,
				   s.`end_line`,
    			   s.validate_date,
				   s.agreement_date,
				   s.note_agreement,
				   s.is_verify,
				   s.store_number,
				    s.second_depostit,
				    s.witness_i,
				    s.witness_ii,
				   (SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id = s.user_id LIMIT 1 ) AS user_name,
				   (SELECT co_khname FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS staff_name,
				   (SELECT name_kh FROM `ln_view` WHERE type=25 and key_code=s.payment_id limit 1) AS payment_type,
				  `p`.`project_name`,
				   `p`.`logo` AS project_logo,
				  `p`.`branch_tel`,
				  `p`.`other` as p_other,
				   p.p_sex,
			      `p`.`br_address` AS `project_location`,
			      `p`.`p_manager_namekh` AS `project_manager_namekh`,
			      `p`.`p_manager_nationality` AS `project_manager_nationality`,
				  `p`.`p_manager_nation_id` AS `project_manager_nation_id`,
                  `p`.`p_current_address` AS `project_manager_p_current_address`,
                   p.w_manager_namekh ,
                   p.`position`,
                   p.w_manager_nation_id,
                   p.p_dob as manager_dob,
                   p.`p_nationid_issue`,
                    p.w_manager_namekh ,
                   p.w_manager_nation_id,
                   p.`w_manager_nationality`,
                   p.`w_sex`,
                   
                   p.w_manager_nation_id,
			    	p.w_manager_position,
			    	p.w_manager_tel,
			    	p.w_manager_tel AS manager_tel,
    	
                   p.w_managername1,
			    	p.w_manager_position1,
			    	p.w_position,
			    	p.w_manager_tel1,
			    	p.w_manager_tel1 AS with_manager_tel,
			    	cho.change_date AS changeDate,
    	
                   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=p.`w_sex` LIMIT 1) AS sc_manager_sex,
	                  p.`w_dob`,
	                  p.`w_current_address`,
	                  p.`w_nation_id_issue`,
                   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=p.p_sex limit 1) AS manager_sex ,
				   
                  `c`.`client_number` AS `client_code`,
     			  `c`.`name_kh` AS `client_namekh`,
     			  `c`.`name_en` AS `client_nameen`,
     			   c.dob,
     			   c.dob AS client_dob ,
     			   c.hname_kh,
     			   c.hname_kh AS with_client_name,
     			   c.sex,
    			   c.ksex,
    			    c.join_type,
    			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.ksex limit 1) AS partner_gender,
     			   c.dob_buywith,
     			   c.rid_no,
     			   c.rid_no AS with_client_nation_id,
     			   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`sex` LIMIT 1) AS client_sex,
     			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.sex limit 1) AS sexKh,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.joint_doc_type limit 1) AS joint_doc_type,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS client_d_type,
     			   
     			   c.p_nationality,
     			   c.p_nationality AS with_client_nationlity ,
  				  `c`.`nationality` AS `client_nationality`,
     			  `c`.`nation_id` AS `client_nation_id`,
     			   c.client_issuedateid,
     			   c.join_issuedateid,
                  `c`.`phone` AS `client_phone`,
  				  `c`.`house` AS `client_house_no`,
                  `c`.`street` AS `client_street`,
                  c.phone,
                  c.lphone as with_phone,
                  c.ghouse as with_house,
                  c.dstreet AS w_street,
                   c.arid_no AS witnesses,
				  (SELECT
				     `village`.`village_namekh`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`qvillage`)
				   LIMIT 1) AS `joint_village`,
    			  (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `join_commune`,
    			 (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `join_district`,
    			(SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `join_province`,	
    				
				  (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`village_id`
					                                 )
					   LIMIT 1) AS `client_village_kh`,
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`com_id`)
				   LIMIT 1) AS `client_commune_kh`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`dis_id`)
				   LIMIT 1) AS `client_districtkh`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`pro_id`)
				   LIMIT 1) AS `client_province_kh`,
				   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS client_buywith_sex,
				    (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS with_client_sex ,
				   c.hname_kh AS w_client_namekh,
				   c.dob_buywith AS w_client_dob_buywith,
				   c.p_nationality AS w_client_nationality,
				   c.`ghouse` AS w_client_house,
					c.lphone AS w_client_phone,
				  (SELECT
				     `village`.`village_name`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`qvillage`)
				   LIMIT 1) AS `w_client_village_en`,
					  (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`qvillage`
					                                 )
					   LIMIT 1) AS `w_client_village_kh`,
				  (SELECT
				     `comm`.`commune_name` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `w_client_commune_en`,
				   
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `w_client_commune_kh`,
				  (SELECT
				     `dist`.`district_name`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `w_client_district`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`)
				   LIMIT 1) AS `w_client_districtkh`,
				  (SELECT
				     `provi`.`province_en_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `w_client_province_en`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`)
				   LIMIT 1) AS `w_client_province_kh`,
				   
				   toc.`client_number` 			AS `to_client_code`,
				   toc.`name_kh` 				AS `to_client_namekh`,
				   toc.`name_en` 				AS `to_client_nameen`,
     			   toc.dob						AS `to_dob`,
     			   toc.dob 						AS 	to_client_dob ,
     			   toc.hname_kh					AS 	to_hname_kh,
     			   toc.hname_kh 				AS 	to_with_client_name,
     			   toc.sex						AS 	to_sex,
    			   toc.ksex						AS 	to_ksex,
    			   toc.join_type				AS 	to_join_type,
    			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=toc.ksex limit 1) AS to_partner_gender,
     			   toc.dob_buywith				AS to_dob_buywith,
     			   toc.rid_no					AS to_rid_no,
     			   toc.rid_no 					AS to_with_client_nation_id,
     			   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=toc.`sex` LIMIT 1) 			AS to_client_sex,
     			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=toc.sex limit 1) 				AS to_sexKh,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=toc.joint_doc_type limit 1) 	AS to_joint_doc_type,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=toc.client_d_type limit 1) 	AS to_client_d_type,
				   
				   toc.p_nationality  		AS to_p_nationality,
     			   toc.p_nationality 		AS to_with_client_nationlity ,
  				   toc.`nationality` 		AS to_client_nationality,
     			   toc.`nation_id` 			AS to_client_nation_id,
     			   toc.client_issuedateid	AS to_client_issuedateid,
     			   toc.join_issuedateid		AS to_join_issuedateid,
                   toc.phone 				AS to_client_phone,
  				   toc.house 				AS to_client_house_no,
                   toc.street 				AS to_client_street,
                   toc.lphone 				AS to_with_phone,
                   toc.ghouse 				AS to_with_house,
                   toc.dstreet 				AS to_w_street,
                   toc.arid_no 				AS to_witnesses,
				   
				  (SELECT `village`.`village_namekh` FROM `ln_village` `village` WHERE (`village`.`vill_id` = toc.qvillage)  LIMIT 1) 		AS to_joint_village,
    			  (SELECT `comm`.`commune_namekh`    FROM `ln_commune` `comm`    WHERE (`comm`.`com_id` = toc.dcommune) LIMIT 1) 			AS to_join_commune,
    			  (SELECT `dist`.`district_namekh`   FROM `ln_district` `dist`   WHERE (`dist`.`dis_id` = toc.adistrict) LIMIT 1) 			AS to_join_district,
    			  (SELECT `provi`.`province_kh_name` FROM `ln_province` `provi`  WHERE (`provi`.`province_id` = toc.cprovince) LIMIT 1) 	AS to_join_province,	
    				
				  (SELECT `village`.`village_namekh` FROM `ln_village` `village` WHERE (`village`.`vill_id` = toc.village_id) LIMIT 1) 	AS to_client_village_kh,
				  (SELECT `comm`.`commune_namekh` FROM `ln_commune` `comm` WHERE (`comm`.`com_id` = toc.com_id) LIMIT 1) 				AS to_client_commune_kh,
				  (SELECT `dist`.`district_namekh` FROM `ln_district` `dist` WHERE (`dist`.`dis_id` = toc.dis_id) LIMIT 1) 				AS to_client_districtkh,
				  (SELECT `provi`.`province_kh_name` FROM `ln_province` `provi` WHERE (`provi`.`province_id` = toc.pro_id) LIMIT 1) 	AS to_client_province_kh,
				  
				  (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=toc.`ksex` LIMIT 1) AS to_client_buywith_sex,
				  (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=toc.`ksex` LIMIT 1) AS to_with_client_sex ,
				  
				   toc.hname_kh 			AS to_w_client_namekh,
				   toc.dob_buywith 			AS to_w_client_dob_buywith,
				   toc.p_nationality 		AS to_w_client_nationality,
				   toc.`ghouse` 			AS to_w_client_house,
				   toc.lphone 				AS to_w_client_phone,
				   
				  (SELECT `village`.`village_name` FROM `ln_village` `village` WHERE (`village`.`vill_id` = toc.qvillage)  LIMIT 1) 	AS `to_w_client_village_en`,
				  (SELECT `village`.`village_namekh` FROM `ln_village` `village` WHERE (`village`.`vill_id` = toc.qvillage)  LIMIT 1) 	AS `to_w_client_village_kh`,
				  (SELECT `comm`.`commune_name` FROM `ln_commune` `comm` WHERE (`comm`.`com_id` = toc.`dcommune`)  LIMIT 1) 			AS `to_w_client_commune_en`,
				   
				  (SELECT `comm`.`commune_namekh` FROM `ln_commune` `comm` WHERE (`comm`.`com_id` = toc.`dcommune`) LIMIT 1) 			 				AS `to_w_client_commune_kh`,
				  (SELECT `dist`.`district_name` FROM `ln_district` `dist` WHERE (`dist`.`dis_id` = toc.`adistrict`) LIMIT 1) 			 				AS `to_w_client_district`,
				  (SELECT `dist`.`district_namekh` FROM `ln_district` `dist` WHERE (`dist`.`dis_id` = toc.`adistrict`) LIMIT 1) 		 				AS `to_w_client_districtkh`,
				  (SELECT `provi`.`province_en_name` FROM `ln_province` `provi` WHERE (`provi`.`province_id` = toc.`cprovince`) LIMIT 1) 				AS `to_w_client_province_en`,
				  (SELECT `provi`.`province_kh_name` FROM `ln_province` `provi` WHERE (`provi`.`province_id` = toc.`cprovince`) LIMIT 1) 				AS `to_w_client_province_kh`,
				  
				  (SELECT `prope_type`.`type_nameen` FROM `ln_properties_type` `prope_type` WHERE (`prope_type`.`id` =`pp`.`property_type`) LIMIT 1) 	AS `property_type_en`,
				  (SELECT `prope_type`.`type_namekh` FROM `ln_properties_type` `prope_type` WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `property_type_kh`,
				
				`pp`.`land_size` AS `property_land_size`,
				
				`pp`.`width` AS `property_width`,
				`pp`.`height` AS `property_height`,
				 pp.`land_size`,
				 
				`pp`.`property_type`,
				`pp`.`type_tob`,
				`pp`.`land_code` AS `property_code`,
				`pp`.`land_address` AS `property_title`,
				 pp.`street` AS `property_street`,
				 
				 pp.land_width,
				 pp.land_height,
				 pp.`full_size`,
				 
				 pp.floor,
				 pp.living,
				 pp.`bedroom`,
				 pp.dinnerroom,
				 pp.buidingyear,
				 pp.`parkingspace`,
				 pp.`note` as `property_note`,
				(SELECT `property`.`land_size` FROM `ln_properties` `property` WHERE (`property`.`id` = `s`.`house_id`) LIMIT 1) AS `property_size`,
				 pp.`north` AS border_north,
				 pp.`south` AS border_south,
				 pp.`east` AS border_east,
				 pp.`west` AS border_west,
				 pp.`old_land_id`
			FROM 
				ln_change_owner 	AS cho,
				ln_sale 			AS `s`,
				ln_project 			AS p ,
				ln_client 			AS c,
				ln_client 			AS toc,
				ln_properties 		AS pp
			WHERE 
				cho.sale_id = s.id
				AND `p`.`br_id` = cho.branch_id
				AND `c`.`client_id` = cho.from_customer
				AND  toc.`client_id` = cho.to_customer
				AND `pp`.`id` = `s`.`house_id`
				AND cho.id=".$id;
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("cho.branch_id");
    		
    		return $db->fetchRow($sql);
    }
	
	function getUserActivity($search=null){
    		$db = $this->getAdapter();
    		$sql="SELECT 
					u.*
				 FROM 
					`rns_user_activity` AS u
				WHERE 1 ";
    		$where ="";
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$where.=$dbp->getAccessPermission("s.`branch_id`");
    		
    		$from_date =(empty($search['start_date']))? '1': " u.`date_time` >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " u.`date_time` <= '".$search['end_date']." 23:59:59'";
    		$where.= " AND ".$from_date." AND ".$to_date;
    		
    		//if($search['branch_id']>0){
    		//	$where.= " AND u.`branch_id` = ".$search['branch_id'];
    		//}
    		
    		if(!empty($search['adv_search'])){
    			$s_where = array();
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
    			$s_where[] =" REPLACE(u.`description`,' ','') LIKE '%{$s_search}%'";
    			$where .=' AND ( '.implode(' OR ',$s_where).')';
    		}
			if(!empty($search['keyword'])){
    			$s_where = array();
				$s_search = str_replace(' ', '', addslashes(trim($search['keyword'])));
    			$s_where[] =" REPLACE(u.`description`,' ','') LIKE '%{$s_search}%'";
    			$where .=' AND ( '.implode(' OR ',$s_where).')';
    		}
    		if($search['user_id']>0){
    			$where.= " AND u.`user_id` = ".$search['user_id'];
    		}
    		$groupby =" GROUP BY u.`id` DESC";
    		return $db->fetchAll($sql.$where.$groupby);
    	}
	function getExpenseDocumentbyid($search){
		$db = $this->getAdapter();
		$id = empty($search['id'])?0:$search['id'];
		$sql=" SELECT * FROM ln_expense_document WHERE exspense_id=$id ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[]=" REPLACE(title,' ','')   	LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql);
	}
	
	function getAllPurchasePayment($search){
    	$db = $this->getAdapter();
    	try{
    		$sql="
    		SELECT
    		pp.*,
    		(SELECT b.project_name FROM `ln_project` AS b  WHERE b.br_id = pp.branch_id LIMIT 1) AS branch_name,
    		pp.receipt_no,
    		(SELECT s.name FROM `ln_supplier` AS s WHERE s.id = pp.supplier_id LIMIT 1 ) AS supplier_name,
    		pp.balance,
    		pp.total_paid,pp.total_due,
			 pp.paid_by as paid_by_id,
    		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.key_code = pp.paid_by AND v.type=26 LIMIT 1) AS paid_by,
    		pp.date_payment,
			(SELECT  first_name FROM rms_users WHERE id=pp.user_id limit 1 ) AS user_name,
    		pp.status
    		FROM `rms_expense_payment` AS pp WHERE pp.status=1 
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
	function getPurchasePaymentDetail($payment_id){
    	$db = $this->getAdapter();
    	$sql="SELECT pd.*,
    	(SELECT p.invoice FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS supplier_no,
    	(SELECT p.other_invoice FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS other_invoice
    	FROM `rms_expense_payment_detail` AS pd WHERE pd.payment_id =$payment_id ";
    	return $db->fetchAll($sql);
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
	
	function totalExpensePayment($search=null){
		$db = $this->getAdapter();
		$sql="SELECT SUM(p.`total_paid`) AS totalAmount
			FROM `rms_expense_payment` AS p 
			WHERE p.`status` =1 ";
			
    	$from_date =(empty($search['start_date']))? '1': " p.`date_payment` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " p.`date_payment` <= '".$search['end_date']." 23:59:59'";
    	$sql.= " AND ".$from_date." AND ".$to_date;
    	if($search['branch_id']>0){
    		$sql.=" AND p.branch_id=".$search['branch_id'];
    	}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("p.branch_id");
		
		return $db->fetchOne($sql);
	}
	
	function getExpenseDetail($id){
    	$db=$this->getAdapter();
    	$sql="SELECT *,
		(SELECT ide.title FROM `rms_product` AS ide WHERE ide.id = pro_id LIMIT 1) AS pro_name
    	FROM ln_expense_detail WHERE expense_id=$id";
    	return $db->fetchAll($sql);
    }
	
	
	function getRefundLetterByID($id=null){
    		$db = $this->getAdapter();
    		$sql="SELECT
				  `s`.`id`              AS `id`,
				  `s`.`sale_number`     AS `sale_number`,
				  `s`.`payment_id`      AS `payment_id`,
				  `s`.`branch_id`       AS `branch_id`,
				  `s`.`client_id`       AS `client_id`,
				  `s`.`price_before`    AS `price_before`,
				  `s`.`discount_amount` AS `discount_amount`,
				   s.discount_percent,
				  `s`.`price_sold`      AS `price_sold`,
				   s.oversold_price,
				  `s`.`other_fee`       AS `other_fee`,
				  `s`.`admin_fee`       AS `admin_fee`,
				  `s`.`paid_amount`     AS `paid_amount`,
				  `s`.`balance`         AS `balance`,
				  `s`.`amount_collect`  AS `amount_collect`,
				  `s`.`interest_rate`   AS `interest_rate`,
				  `s`.`total_duration`  AS `total_duration`,
				  `s`.`first_payment`   AS `first_payment`,
				   s.lastpayment_amount,
				   s.is_reschedule,
				   s.land_price,
				   s.amount_build,
				   s.build_start,
				   s.buy_date,
				   s.`end_line`,
    			   s.validate_date,
				   s.agreement_date,
				   s.note_agreement,
				   s.is_verify,
				   s.store_number,
				    s.second_depostit,
				    s.witness_i,
				    s.witness_ii,
				   (SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id = s.user_id LIMIT 1 ) AS user_name,
				   (SELECT co_khname FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS staff_name,
				   (SELECT name_kh FROM `ln_view` WHERE type=25 and key_code=s.payment_id limit 1) AS payment_type,
				   
				   (SELECT name_kh FROM `ln_view` WHERE type=25 and key_code=s.pre_schedule_opt limit 1) AS preScheduleOptTitle,
				   s.pre_schedule_opt,
				   s.pre_percent_payment,
				   s.pre_percent_installment,
				   s.pre_amount_month,
				   s.pre_amount_year,
				   s.pre_fix_payment,
				   
				   s.agreement_for,
				   (SELECT name_kh FROM `ln_view` WHERE type=31 and key_code=s.agreement_for limit 1) AS titlePlong,
				  `p`.`project_name`,
				   `p`.`logo` AS project_logo,
				  `p`.`branch_tel`,
				  `p`.`other` as p_other,
				   p.p_sex,
			      `p`.`br_address` AS `project_location`,
			      `p`.`p_manager_namekh` AS `project_manager_namekh`,
			      `p`.`p_manager_nationality` AS `project_manager_nationality`,
				  `p`.`p_manager_nation_id` AS `project_manager_nation_id`,
                  `p`.`p_current_address` AS `project_manager_p_current_address`,
                   p.w_manager_namekh ,
                   p.`position`,
                   p.w_manager_nation_id,
                   p.p_dob as manager_dob,
                   p.`p_nationid_issue`,
                    p.w_manager_namekh ,
                   p.w_manager_nation_id,
                   p.`w_manager_nationality`,
                   p.`w_sex`,
                   
                   p.w_manager_nation_id,
			    	p.w_manager_position,
			    	p.w_manager_tel,
			    	p.w_manager_tel AS manager_tel,
    	
                   p.w_managername1,
			    	p.w_manager_position1,
			    	p.w_position,
			    	p.w_manager_tel1,
			    	p.w_manager_tel1 AS with_manager_tel,
    	
                   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=p.`w_sex` LIMIT 1) AS sc_manager_sex,
	                  p.`w_dob`,
	                  p.`w_current_address`,
	                  p.`w_nation_id_issue`,
                   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=p.p_sex limit 1) AS manager_sex ,
                  `c`.`client_number` AS `client_code`,
     			  `c`.`name_kh` AS `client_namekh`,
     			  `c`.`name_en` AS `client_nameen`,
     			   c.dob,
     			   c.dob AS client_dob ,
     			   c.hname_kh,
     			   c.hname_kh AS with_client_name,
     			   c.sex,
    			   c.ksex,
    			    c.join_type,
    			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.ksex limit 1) AS partner_gender,
     			   c.dob_buywith,
     			   c.rid_no,
     			   c.rid_no AS with_client_nation_id,
     			   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`sex` LIMIT 1) AS client_sex,
     			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.sex limit 1) AS sexKh,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.joint_doc_type limit 1) AS joint_doc_type,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS client_d_type,
     			   
     			   c.p_nationality,
     			   c.p_nationality AS with_client_nationlity ,
  				  `c`.`nationality` AS `client_nationality`,
     			  `c`.`nation_id` AS `client_nation_id`,
     			   c.client_issuedateid,
     			   c.join_issuedateid,
                  `c`.`phone` AS `client_phone`,
  				  `c`.`house` AS `client_house_no`,
                  `c`.`street` AS `client_street`,
                  c.phone,
                  c.lphone as with_phone,
                  c.ghouse as with_house,
                  c.dstreet AS w_street,
                   c.arid_no AS witnesses,
                   c.remark AS clientNote,
				  (SELECT
				     `village`.`village_namekh`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`qvillage`)
				   LIMIT 1) AS `joint_village`,
    			  (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `join_commune`,
    			 (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `join_district`,
    			(SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `join_province`,	
    				
				  (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`village_id`
					                                 )
					   LIMIT 1) AS `client_village_kh`,
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`com_id`)
				   LIMIT 1) AS `client_commune_kh`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`dis_id`)
				   LIMIT 1) AS `client_districtkh`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`pro_id`)
				   LIMIT 1) AS `client_province_kh`,
				   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS client_buywith_sex,
				    (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS with_client_sex ,
				   c.hname_kh AS w_client_namekh,
				   c.dob_buywith AS w_client_dob_buywith,
				   c.p_nationality AS w_client_nationality,
				   c.`ghouse` AS w_client_house,
					c.lphone AS w_client_phone,
				  (SELECT
				     `village`.`village_name`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`qvillage`)
				   LIMIT 1) AS `w_client_village_en`,
					  (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`qvillage`
					                                 )
					   LIMIT 1) AS `w_client_village_kh`,
				  (SELECT
				     `comm`.`commune_name` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `w_client_commune_en`,
				   
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `w_client_commune_kh`,
				  (SELECT
				     `dist`.`district_name`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `w_client_district`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`)
				   LIMIT 1) AS `w_client_districtkh`,
				  (SELECT
				     `provi`.`province_en_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `w_client_province_en`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`)
				   LIMIT 1) AS `w_client_province_kh`,
				  
			(SELECT
				     `prope_type`.`type_nameen`
				   FROM `ln_properties_type` `prope_type`
				   WHERE (`prope_type`.`id` =`pp`.`property_type`)
				   LIMIT 1) AS `property_type_en`,
			(SELECT
				     `prope_type`.`type_namekh`
				   FROM `ln_properties_type` `prope_type`
				   WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `property_type_kh`,
		   (SELECT
			 `prope_type`.`note`
		   FROM `ln_properties_type` `prope_type`
		   WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `propertyTypeNote`,
			`pp`.`land_size` AS `property_land_size`,
			
			`pp`.`width` AS `property_width`,
		    `pp`.`height` AS `property_height`,
		     pp.`land_size`,
		     
		    `pp`.`property_type`,
		    `pp`.`type_tob`,
		    `pp`.`land_code` AS `property_code`,
		    `pp`.`land_address` AS `property_title`,
 			 pp.`street` AS `property_street`,
 			 
 			 pp.land_width,
 			 pp.land_height,
 			 pp.`full_size`,
			 
			 pp.land_width AS houseWidth,
 			 pp.land_height AS houseHeight,
 			 pp.`full_size` AS houseFullSize,
 			 
 			 pp.floor,
 			 pp.living,
 			 pp.`bedroom`,
 			 pp.dinnerroom,
 			 pp.buidingyear,
 			 pp.`parkingspace`,
 			 pp.`note` as `property_note`,
			 
 			 sC.`cancel_code` as `cancelCode`,
 			 sC.`reason` as `cancelReason`,
 			 sC.`paid_amount` as `paidAmount`,
 			 sC.`return_back` as `amountReturnBack`,
			 
	(SELECT
    	`property`.`land_size`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1) AS `property_size`,
 			 pp.`north` AS border_north,
 			 pp.`south` AS border_south,
 			 pp.`east` AS border_east,
 			 pp.`west` AS border_west,
 			 pp.`old_land_id`,
 	(SELECT
	    	`a`.`description`
	    	FROM `ln_sale_conditionagreement` `a`
	    		WHERE (`a`.`saleId` = `s`.`id`)
	    	LIMIT 1) AS `additinalContract`,
	    	gendertitle,
	    	gendertitle1 
		FROM 
			`ln_sale` AS `s`,
			`ln_sale_cancel` AS `sC`,
			ln_project AS p ,
			`ln_client` AS c,
			ln_properties as pp
			
			WHERE 
			`p`.`br_id` = `s`.`branch_id` 
			AND `c`.`client_id` = `s`.`client_id`
			AND `sC`.`sale_id` = `s`.`id`
			AND `pp`.`id` = `s`.`house_id` 
			AND `sC`.`status` = 1 
			AND sC.id=".$id;
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("sC.`branch_id`");
    		
    		return $db->fetchRow($sql);
    }
	
	function getLastRecordPaymentSchdule($saleId){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$userId=$dbp->getUserId();
		$sql = "SELECT 
			sch.*,
			(SELECT v.name_kh FROM ln_view AS v WHERE v.type =29 AND v.key_code = sch.ispay_bank LIMIT 1) AS lastPaymentTitle,
			(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE rms_users.id = $userId LIMIT 1 ) AS currentUserName
		FROM `ln_saleschedule` AS sch 
		WHERE 
			sch.sale_id = $saleId 
			AND sch.status=1
		ORDER BY sch.no_installment DESC 
		LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	
	public function updateNotePropertyLayoutNote($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
	
			$arr = array(
					'noteForLayout'=>$data['noted'],
			);
			$where=" id = ".$data['id'];
			$this->_name="ln_properties";
			$this->update($arr, $where);
	
			$db->commit();
			return 1;
		}catch (Exception $e){
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			$db->rollBack();
		}
	}
}