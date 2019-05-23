<?php

class Loan_Model_DbTable_DbLanddeposit extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_sale';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    	 
    }
    public function getAlldepositLoan($search,$reschedule =null){
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$edit_sale = $tr->translate("EDITSALEONLY");
    	$session_lang=new Zend_Session_Namespace('lang');
    	$lang = $session_lang->lang_id;
    	$str = 'name_en';
    	$str_agree = 'agreement';
    	$str_schedule = 'issue sch';
    	if($lang==1){
    		$str = 'name_kh';
    		$str_agree = 'កិច្ចសន្យា';
    	}
    	
    	$from_date =(empty($search['start_date']))? '1': " s.buy_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.buy_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql=" 
    	SELECT `s`.`id` AS `id`,
    	(SELECT
		     `ln_project`.`project_name`
		   FROM `ln_project`
		   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
		   LIMIT 1) AS `branch_name`,
	    `c`.`name_kh`         AS `name_kh`,
	    `c`.`phone`         AS `phone`,
	    `p`.`land_address`    AS `land_address`,
	    `p`.`street`          AS `street`,
	    (SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
  		`s`.`price_before`    AS `price_before`,
 		CONCAT(`s`.`discount_percent`,'%') AS `discount_percent`,
        `s`.`discount_amount` AS `discount_amount`,
 		`s`.`price_sold`     AS `price_sold`,
 		(SELECT
	     SUM((`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`))
	   FROM `ln_client_receipt_money` `cr`
	   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `totalpaid_amount`,   
        `s`.`balance`         AS `balance`,
        `s`.`buy_date`        AS `buy_date`,
        (SELECT  first_name FROM rms_users WHERE id=s.user_id limit 1 ) AS user_name,
         s.status,
         CASE    
				WHEN  `s`.`is_cancel` = 0 THEN ' '
				WHEN  `s`.`is_cancel` = 1 THEN '".$tr->translate("CANCELED")."'
				END AS cancel
		FROM ((`ln_sale` `s`
		    JOIN `ln_client` `c`)
		   JOIN `ln_properties` `p`)
		WHERE ((`c`.`client_id` = `s`.`client_id`)
       AND (`p`.`id` = `s`.`house_id`)) ";
    	
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
      	 	$s_where[] = " s.total_duration LIKE '%{$s_search}%'";
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['co_id']>0){
    		$where.= " AND s.staff_id = ".$search['co_id'];
    	}
    	if($search['status']>-1){
    		$where.= " AND s.status = ".$search['status'];
    	}
    	if(!empty($search['land_id']) AND $search['land_id']>-1){
    		$where.= " AND s.house_id = ".$search['land_id'];
    	}
    	if(($search['client_name'])>0){
    		$where.= " AND `s`.`client_id`=".$search['client_name'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND s.branch_id = ".$search['branch_id'];
    	}
    	$where.= " AND (s.payment_id = 1 OR s.payment_id=2) ";
    	if(($search['schedule_opt'])>0){
    		$where.= " AND s.payment_id = ".$search['schedule_opt'];
    	}
    		
    	$order = " ORDER BY s.id DESC";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("`s`.`branch_id`");
    	
    	return $db->fetchAll($sql.$where.$order);
    }
//     function getTranLoanByIdWithBranch($id,$is_newschedule=null){//group id
//     	$sql = " SELECT * FROM `ln_sale` AS s
// 			WHERE s.id = ".$id;
//     	$where="";
//     	if($is_newschedule!=null){
//     		$where.=" AND s.is_reschedule = 2 ";
//     	}
    	
//     	$where.=" LIMIT 1 ";
//     	$db = $this->getAdapter();
//     	return $db->fetchRow($sql.$where);
//     }
//     function getSaleScheduleById($id,$payment_id){
//     	$sql=" SELECT * FROM ln_saleschedule WHERE sale_id =$id AND status=1 AND is_completed=0 ";
//     	if($payment_id==4){$sql.=" AND is_installment=1 ";};
//     	$db = $this->getAdapter();
//     	return $db->fetchAll($sql);
//     }
//     function getSalePaidExist($id,$payment_id){
//     	$sql=" SELECT * FROM ln_saleschedule WHERE sale_id =$id AND status=1 AND is_completed=1 ";
//     	$db = $this->getAdapter();
//     	return $db->fetchAll($sql);
//     }
//     public function getLoanviewById($id){
//     	$sql = "SELECT
//     	lg.g_id
//     	,(SELECT branch_nameen FROM `ln_branch` WHERE br_id =lg.branch_id LIMIT 1) AS branch_name
//     	,lg.level,
//     	(SELECT name_en FROM `ln_view` WHERE status =1 and type=24 and key_code=lg.for_loantype) AS for_loantype
//     	,(select concat(zone_name,'-',zone_num)as dd from `ln_zone` where zone_id = lg.zone_id ) AS zone_name
//     	,(SELECT name_en FROM `ln_view` WHERE status =1 and type=14 and key_code=lg.pay_term) AS pay_term
//     	,(SELECT name_en FROM `ln_view` WHERE status =1 and type=14 and key_code=lg.collect_typeterm) AS collect_typeterm
//     	,lg.date_release
//     	,lg.total_duration
//     	,lg.first_payment
//     	,lg.time_collect
//     	,(SELECT name_en FROM `ln_view` WHERE status =1 and type=2 and key_code=lg.holiday) AS holiday
//     	,lg.date_line
//     	,lm.pay_after, lm.pay_before
//     	,(SELECT payment_nameen FROM `ln_payment_method` WHERE id =lm.payment_method ) AS payment_nameen
//     	,(SELECT curr_nameen FROM `ln_currency` WHERE id=lm.currency_type) AS currency_type
//     	,lm.graice_period,
//     	lm.loan_number,lm.interest_rate,lm.amount_collect_principal,lm.semi,
//     	lm.client_id,lm.admin_fee,
//     	lm.pay_after,lm.pay_before,lm.other_fee
//     	,(SELECT name_kh FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_kh,
//     	(SELECT name_en FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_en,
//     	(SELECT group_code FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS group_code,
//     	(SELECT client_number FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_number,
//     	lm.total_capital,lm.interest_rate,lm.payment_method,
//     	lg.time_collect,
//     	lg.zone_id,
//     	(SELECT co_firstname FROM `ln_staff` WHERE co_id =lg.co_id LIMIT 1) AS co_enname,
//     	lg.status AS str ,lg.status FROM `ln_loan_group` AS lg,`ln_loan_member` AS lm
//     	WHERE lg.g_id = lm.group_id AND lm.member_id = $id LIMIT 1 ";
//     	return $this->getAdapter()->fetchRow($sql);
//     }

//     function round_up($value, $places)
//     {
//     	$mult = pow(10, abs($places));
//     	return $places < 0 ?
//     	ceil($value / $mult) * $mult :
//     	ceil($value * $mult) / $mult;
//     }
//     function round_up_currency($curr_id, $value,$places=-2){
//     	if ($curr_id==1){
//     		return $this->round_up($value, $places);
//     	}
//     	else{
//     		return round($value,0);
//     	}
//     }
    function getProperty($id){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `ln_properties` AS p WHERE p.`id`=".$id;
    	return $db->fetchRow($sql);
    }
    public function addDepositPayment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		if ($data['typesale']==2){//លក់ម្តងច្រើន
    			$ids_land = explode(',', $data['identity_land']);
    			$size = 0; $width=''; $height='';
    			$land_address='';
    			$land_code='';
    			$price = 0;
    			$land_price=0;
    			$house_price=0;
    			$property_type='';
    			foreach ($ids_land as $key => $i){
    				$this->_name="ln_properties";
    				$where = "id =".$ids_land[$key];
    				$arr = array(
    						"is_lock"=>1,
    				);
    				$this->update($arr, $where);
    				$newpro = $this->getProperty($ids_land[$key]);
    				$size = $size + $newpro['land_size'];
    		
    				$width = $width+$newpro['width'];
    				$height =$newpro['height'];
    		
    				$price = $price + $newpro['price'];
    				$land_price = $land_price+$newpro['land_price'];
    				$house_price = $house_price+$newpro['house_price'];
    				
    				if(!empty($land_address)){
    					//$land_address= $land_address.'&'.$newpro['land_address'];
    					$land_address= $land_address.','.$newpro['land_address'];
    				}else{ 
    					$land_address =$newpro['land_address'];
    				}
    				if(!empty($land_code)){
    					$land_code=$land_code.','.$newpro['land_code'];
    				}else{ $land_code =$newpro['land_code'];
    				}
    				$property_type = $newpro['property_type'];
    			}//end loop
    			
    			$newproperty = array(
    					'branch_id'=>$data['branch_id'],
    					'land_code'=>$land_code,
    					'land_address'=>$land_address,
    					'street'=>$newpro['street'],
    					'price'=>$price,
    					'land_price'=>$land_price,
    					'house_price'=>$house_price,
    					'property_type'=>$property_type,
//     					'width'=>$width,
//     					'height'=>$height,
//     					'land_size'=>$size,
    					'width'=>$data['width'],
    					'height'=>$data['height'],
    					'land_size'=>$data['land_size'],
    					'south'=>$data['south'],
    					'north'=>$data['north'],
    					'west'=>$data['west'],
    					'east'=>$data['east'],
    					"is_lock"=>1,
    					"status"=>-2,
    					"create_date"=>date("Y-m-d"),
    					"user_id"=>$this->getUserId(),
    					"old_land_id"=>$data['identity_land']
    			);
    			$this->_name="ln_properties";
    			$land_id = $this->insert($newproperty);
    			$data['land_code']=$land_id;
    		}else{
    			$this->_name="ln_properties";
    			$where = "id =".$data["land_code"];
    			$arr = array(
    				"is_lock"=>1
    			);
    			$this->update($arr, $where);
    			unset($datagroup);
    		}
    		$is_schedule = 0;
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		
    		$loan_number = $dbtable->getLoanNumber($data);
    		
    		$receipt = $data['receipt'];
    		$sql="SELECT id FROM ln_client_receipt_money WHERE receipt_no='$receipt' ORDER BY id DESC LIMIT 1 ";
    		$acc_no = $db->fetchOne($sql);
    		if($acc_no){
    			$dbc = new Application_Model_DbTable_DbGlobal();
    			$receipt = $dbc->getReceiptByBranch(array("branch_id"=>$data["branch_id"]));
    		}else{
    			$receipt = $data['receipt'];
    		}
    		
    		$property_info = $this->getProperty($data["land_code"]);
    		$key = new Application_Model_DbTable_DbKeycode();
    		$setting=$key->getKeyCodeMiniInv(TRUE);
    		$note_agreement = '';
    		if($setting['note_agreement']==1){
    			$note_agreement = $data['note_agreement'];
    		}
    		
    			 $arr = array(
    				'branch_id'=>$data['branch_id'],
    			   	'receipt_no'=>$receipt,
    				'sale_number'=>$loan_number,
    			   	'house_id'=>$data["land_code"],
    			   	'payment_id'=>$data["schedule_opt"],
    				'client_id'=>$data['member'],
    				'price_before'=>$data['total_sold'],
    				'discount_amount'=>$data['discount'],
    			   	'discount_percent'=>$data['discount_percent'],
    				'price_sold'=>$data['sold_price'],
    				'other_fee'=>0,
    				'paid_amount'=>$data['deposit'],
    				'balance'=>$data['balance'],
    				'buy_date'=>$data['date_buy'],
    				'end_line'=>($data['schedule_opt']==1)?$data['date_line']:$data['paid_date'],
    				'interest_rate'=>0,
    				'total_duration'=>1,
    			   	'startcal_date'=>$data['date_buy'],
    				'first_payment'=>$data['date_buy'],
    			   	'validate_date'=>$data['date_line'],
    				'payment_method'=>1,//$data['loan_type'],
    				'note'=>$data['note'],
    			   	'land_price'=>$property_info['house_price'],
    				'note_agreement'=>$note_agreement,
    				'typesale'=>$data['typesale'],
    				'is_reschedule'=>$is_schedule,
    			    'agreement_date'=>$data['agreement_date'],
    				'staff_id'=>$data['staff_id'],
    			   	'full_commission'=>$data['full_commission'],
    				'comission'=>0,//$data['commission'],
    			   	'second_depostit'=>$data['second_depostit'],
    				'create_date'=>date("Y-m-d"),
    				'user_id'=>$this->getUserId()
    				);
    		$this->_name='ln_sale';
    		$id = $this->insert($arr);//add group loan
    		$data['sale_id']=$id;
    		
    		$curr_type = 2;//$data['currency_type'];
    		$term_types=1;
    		$payment_method = $data["schedule_opt"];
    	    if($payment_method==2){//pay off
    			$this->_name="ln_saleschedule";
    			$datapayment = array(
    					'branch_id'=>$data['branch_id'],
    					'sale_id'=>$id,//good
    					'begining_balance'=>$data['sold_price'],//good
    					'begining_balance_after'=>0,//good
    					'principal_permonth'=>$data['sold_price'],//good
    					'principal_permonthafter'=>0,//good
    					'total_interest'=>0,//good
    					'total_interest_after'=>0,//good
    					'total_payment'=>$data['sold_price'],//good
    					'total_payment_after'=>0,//good
    					'ending_balance'=>0,
    					'cum_interest'=>0,
    					'amount_day'=>0,
    					'is_completed'=>1,
    					'date_payment'=>$data['date_buy'],
    					'percent'=>100,
    					'is_installment'=>1,
    					'no_installment'=>1,
    					'received_date'=>$data['date_buy'],
    					'received_userid'=> $this->getUserId(),
    				);
    			$this->insert($datapayment);
    	    }
    		
	    	if($data['deposit']>0){//insert payment
	    			$data['date_buy']=$data['paid_date'];
    		    	$pay_off = 0;
    		    	if($data["schedule_opt"]==2){
    		    		$pay_off = 1;
    		    	}
    		    	$array = array(
    		    			'branch_id'			=> $data['branch_id'],
    		    			'client_id'			=> $data['member'],
    		    			'receipt_no'		=> $receipt,
    		    			'date_pay'			=> $data['paid_date'],
    		    			'land_id'			=> $data['land_code'],
    		    			'sale_id'			=> $data['sale_id'],
    		    			'date_input'		=> $data['paid_date'],//paid_date
    		    			'outstanding'		=> $data['sold_price'],
    		    			'principal_amount'	=> $data['balance'],
    		    			'selling_price'     => $data['sold_price'],
    		    			'allpaid_before'=>$data['deposit'],
    		    			'total_principal_permonth'=>$data['deposit'],
    		    	    	'total_principal_permonthpaid'=>$data['deposit'],
    		    			'total_interest_permonth'	=>0,
    		    			'total_interest_permonthpaid'=>0,
    		    			'penalize_amount'			=>0,
    				    	'penalize_amountpaid'		=>0,
    				    	'service_charge'	=>0,
    				    	'service_chargepaid'=>0,
    				    	'total_payment'		=> $data['deposit'],//$data['sold_price'],
    				    	'amount_payment'	=> $data['deposit'],
    				    	'recieve_amount'	=> $data['deposit'],
    				    	'balance'			=> $data['balance'],
    				    	'payment_option'	=>($data['schedule_opt']==2)?4:1,//4 payoff,1normal
    				    	'is_completed'		=>($data['schedule_opt']==2)?1:0,
    		    			'payment_method'	=> $data['payment_method'],
    		    			'cheque'			=> $data['cheque'],
    				    	'status'			=> 1,
    				    	'note'				=> $data['note'],
    				    	'user_id'			=> $this->getUserId(),
    				    	'field3'			=> 1,// ជាប្រាក់កក់
    				    	'field2'=>1,
    				    	'is_payoff'=>$pay_off,
    				    	'payment_times'=>1,
    				    	);
    		    	$this->_name='ln_client_receipt_money';
    		    	$crm_id = $this->insert($array);
    		    	
    		        $this->_name='ln_client_receipt_money_detail';
    		    	$array = array(
    		    		  'crm_id'			=> $crm_id,
    		    		  'client_id'		=> $data['member'],
    		    		  'land_id'			=> $data['land_code'],
    		    		  'date_payment'	=> $data['date_buy'],
    		    		  'paid_date'       => $data['date_buy'],
    		    		  'last_pay_date'   => $data['date_buy'],
    		    		  'capital'			=> $data['sold_price'],
    		    		  'remain_capital'	=> $data['balance'],
    		    		  'principal_permonth'=>$data['deposit'],
    		    		  'old_principal_permonth'=>$data['deposit'],
    		    		  'total_interest'	=> 0,
    		    		  'total_payment'	=> $data['sold_price'],
    		    		  'total_recieve'	=> $data['deposit'],
    		    		  'service_charge'	=> 0,
    		    		  'penelize_amount'	=> 0,
    		    		  'is_completed'	=> ($data['schedule_opt']==2)?1:0,
    		    		  'status'			=> 1,
    		    		);
    		     $this->insert($array);
	    	}
	        $db->commit();
	        return $id;
        }catch (Exception $e){
            $db->rollBack();
            Application_Form_FrmMessage::message("INSERT_FAIL");
            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
        }
    }
    function updateLoanById($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbl = new Loan_Model_DbTable_DbLandpayment();
    		$row = $dbl->getTranLoanByIdWithBranch($data['id'],null);
    		if(!empty($row)){
    			if($row['typesale']==2){//multi sale
    				$ids = explode(',', $row['old_land_id']);
    				if (!empty($row['old_land_id'])){
	    				foreach($ids as $land){
	    					$this->_name="ln_properties";
	    					$arr = array(
	    							"is_lock"=>0
	    					);
	    					$where = "id =".$land;
	    					$this->update($arr, $where);
	    				}
    				}
    			}
    		}
    		
    		if($data['status_using']==0){//cancel
    			$this->_name="ln_sale";
    			$arr_update = array(
    					'status'=>0
    			);
    			$where = ' status = 1 AND id = '.$data['id'];
    			$this->update($arr_update, $where);
    			 
    			$this->_name = 'ln_saleschedule';
    			$where = ' is_completed = 0 AND status = 1 AND sale_id = '.$data['id'];
    			$this->delete($where);
    			
    			$this->_name='ln_properties';
    			if($data['land_code']!=$data['old_landid']){
    				$where =" id= ".$data['old_landid'];
    				$arr = array('is_lock'=>0);
    				$this->update($arr,$where);
    			}
    			
    			$this->_name="ln_client_receipt_money";
    			$where = ' status = 1 AND land_id ='.$data["old_landid"].' AND sale_id = '.$data['id'];   		
    			$this->update($arr_update, $where);
    			$db->commit();
    			return true;
    		}
    	
    		if(!empty($row)){
    			if($row['typesale']==2){//multi sale
    				if ($data['typesale']==2){//លក់ម្តងច្រើន
    					$ids_land = explode(',', $data['identity_land']);
    					$size = 0; $width=''; $height='';
    					$land_address='';
    					$land_code='';
    					$price = 0;
    					$land_price=0;
    					$house_price=0;
    					$property_type='';
    					foreach ($ids_land as $key => $i){
    						$this->_name="ln_properties";
    						$where = "id =".$ids_land[$key];
    						$arr = array(
    								"is_lock"=>1,
    						);
    						$this->update($arr, $where);
    						$newpro = $this->getProperty($ids_land[$key]);
    						$size = $size + $newpro['land_size'];
    				
    						$width = $width+$newpro['width'];
    						$height =$newpro['height'];
    				
    						$price = $price + $newpro['price'];
    						$land_price = $land_price+$newpro['land_price'];
    						$house_price = $house_price+$newpro['house_price'];
    				
    						if(!empty($land_address)){
    							//$land_address= $land_address.'&'.$newpro['land_address'];
    							$land_address= $land_address.','.$newpro['land_address'];
    						}else{
    							$land_address =$newpro['land_address'];
    						}
    						if(!empty($land_code)){
    							$land_code=$land_code.','.$newpro['land_code'];
    						}else{ $land_code =$newpro['land_code'];
    						}
    						$property_type = $newpro['property_type'];
    					}//end loop
    					 
    					$newproperty = array(
    							'branch_id'=>$data['branch_id'],
    							'land_code'=>$land_code,
    							'land_address'=>$land_address,
    							'street'=>$newpro['street'],
    							'price'=>$price,
    							'land_price'=>$land_price,
    							'house_price'=>$house_price,
    							'property_type'=>$property_type,
//     							'width'=>$width,
//     							'height'=>$height,
//     							'land_size'=>$size,
    							'width'=>$data['width'],
    							'height'=>$data['height'],
    							'land_size'=>$data['land_size'],
    							'south'=>$data['south'],
    							'north'=>$data['north'],
    							'west'=>$data['west'],
    							'east'=>$data['east'],
    							"is_lock"=>1,
    							"status"=>-2,
    							"create_date"=>date("Y-m-d"),
    							"user_id"=>$this->getUserId(),
    							"old_land_id"=>$data['identity_land']
    					);
    					$this->_name="ln_properties";
    					$whereNewPro = "id =".$data["old_landid"];
    					$land_id =$data["old_landid"];
    					$this->update($newproperty, $whereNewPro);
    				}else {
    					$this->_name = 'ln_properties';
    					$where="id = ".$data["old_landid"];
    					$this->delete($where);
    					
    					$this->_name="ln_properties";
    					$where = "id =".$data["land_code"];
    					$arr = array(
    							"is_lock"=>1
    					);
    					$this->update($arr, $where);
    					$land_id = $data["land_code"];
    				}
    			}else{
    				if ($data['typesale']==2){//លក់ម្តងច្រើន
    					$ids_land = explode(',', $data['identity_land']);
    					$size = 0; $width=''; $height='';
    					$land_address='';
    					$land_code='';
    					$price = 0;
    					$land_price=0;
    					$house_price=0;
    					$property_type='';
    					foreach ($ids_land as $key => $i){
    						$this->_name="ln_properties";
    						$where = "id =".$ids_land[$key];
    						$arr = array(
    								"is_lock"=>1,
    						);
    						$this->update($arr, $where);
    						$newpro = $this->getProperty($ids_land[$key]);
    						$size = $size + $newpro['land_size'];
    				
    						$width = $width+$newpro['width'];
    						$height =$newpro['height'];
    				
    						$price = $price + $newpro['price'];
    						$land_price = $land_price+$newpro['land_price'];
    						$house_price = $house_price+$newpro['house_price'];
    				
    						if(!empty($land_address)){
    							$land_address= $land_address.'&'.$newpro['land_address'];
    						}else{
    							$land_address =$newpro['land_address'];
    						}
    						if(!empty($land_code)){
    							$land_code=$land_code.','.$newpro['land_code'];
    						}else{ $land_code =$newpro['land_code'];
    						}
    						$property_type = $newpro['property_type'];
    					}//end loop
    					 
    					$newproperty = array(
    							'branch_id'=>$data['branch_id'],
    							'land_code'=>$land_code,
    							'land_address'=>$land_address,
    							'street'=>$newpro['street'],
    							'price'=>$price,
    							'land_price'=>$land_price,
    							'house_price'=>$house_price,
    							'property_type'=>$property_type,
    							'width'=>$width,
    							'height'=>$height,
    							'land_size'=>$size,
    							"is_lock"=>1,
    							"status"=>-2,
    							"create_date"=>date("Y-m-d"),
    							"user_id"=>$this->getUserId(),
    							"old_land_id"=>$data['identity_land']
    					);
    					$this->_name="ln_properties";
    					$land_id = $this->insert($newproperty);
//     					$data['land_code']=$land_id;
    				}else{
    					$this->_name="ln_properties";//ប្តូរពីទិញច្រើនមកទិញតែមួយ Error
    					$where = "id =".$data["old_landid"];
    					$arr = array(
    							"is_lock"=>0);
    					$this->update($arr, $where);
    					
    					$this->_name="ln_properties";
    					$where = "id =".$data["land_code"];
    					$arr = array(
    							"is_lock"=>1
    					);
    					$this->update($arr, $where);
    					$land_id = $data["land_code"];
    				}
    			}
    		}
    		
    		$key = new Application_Model_DbTable_DbKeycode();
    		$setting=$key->getKeyCodeMiniInv(TRUE);
    		$note_agreement = '';
    		if($setting['note_agreement']==1){
    			$note_agreement = $data['note_agreement'];
    		}
    		
    		$arr = array(
    				'branch_id'=>$data['branch_id'],
    			   	'receipt_no'=>$data['receipt'],
    				'sale_number'=>$data['sale_code'],
    			   	'house_id'=>$land_id,
    			   	'payment_id'=>$data["schedule_opt"],
    				'client_id'=>$data['member'],
    				'price_before'=>$data['total_sold'],
    				'discount_amount'=>$data['discount'],
    			   	'discount_percent'=>$data['discount_percent'],
    				'price_sold'=>$data['sold_price'],
    				'other_fee'=>0,
    				'paid_amount'=>$data['deposit'],
    				'balance'=>$data['balance'],
    				'buy_date'=>$data['date_buy'],
    				'end_line'=>$data['date_line'],
    				'interest_rate'=>0,
    				'total_duration'=>1,
    			   	'startcal_date'=>$data['date_buy'],
    				'first_payment'=>$data['date_buy'],
    			   	'validate_date'=>$data['date_line'],
    				'payment_method'=>1,
    				'note'=>$data['note'],
    				'typesale'=>$data['typesale'],
    			   	'land_price'=>0,
    				'is_reschedule'=>0,
    			    'agreement_date'=>$data['agreement_date'],
    				'staff_id'=>$data['staff_id'],
    				'full_commission'=>$data['full_commission'],
    				'comission'=>0,//$data['commission'],
    				'second_depostit'=>$data['second_depostit'],
    				'create_date'=>date("Y-m-d"),
    				'user_id'=>$this->getUserId(),
    				'note_agreement'=>$note_agreement,
    			);
    		
    		$id = $data['id'];
    		$this->_name='ln_sale';
    		$where = $db->quoteInto('id=?', $id);
    		$this->update($arr, $where);
    		
    		//if($data['deposit']>0){//insert payment
    			$data['date_buy']=$data['paid_date'];
    			$pay_off = 0;
    			if($data["schedule_opt"]==2){
    				$pay_off = 1;
    			}
    			$array = array(
    					'branch_id'			=>$data['branch_id'],
    					'client_id'			=>$data['member'],
    					'payment_method'	=>  $data['payment_method'],
    					'cheque'			=>	$data['cheque'],
    					'date_pay'			=>$data['paid_date'],
    					'land_id'			=>$data['land_code'],
    					'date_input'		=>$data['paid_date'],//paid_date
    					'outstanding'		=>$data['sold_price'],
    					'principal_amount'	=> $data['balance'],
    					'selling_price'=>$data['sold_price'],
    					'allpaid_before'=>$data['deposit'],
    					'total_principal_permonth'=>$data['deposit'],
    					'total_principal_permonthpaid'=>$data['deposit'],
    					'total_interest_permonth'	=>0,
    					'total_interest_permonthpaid'=>0,
    					'penalize_amount'			=>0,
    					'penalize_amountpaid'		=>0,
    					'service_charge'	=>0,
    					'service_chargepaid'=>0,
    					'total_payment'		=>$data['deposit'],//$data['sold_price'],
    					'amount_payment'	=>$data['deposit'],
    					'recieve_amount'	=>$data['deposit'],
    					'balance'			=>$data['balance'],
    					'payment_option'	=>($data['schedule_opt']==2)?4:1,//4 payoff,1normal
    					'is_completed'		=>($data['schedule_opt']==2)?1:0,
    					'status'			=>1,
    					'note'				=>$data['note'],
    					'user_id'			=>$this->getUserId(),
    					'field3'			=>1,// ជាប្រាក់កក់
    					'field2'=>1,
    					'is_payoff'=>$pay_off,
    					'payment_times'=>1,
    			);
    			$this->_name='ln_client_receipt_money';
    			$where="receipt_no='".$data['receipt']."'";
    			$this->update($array, $where);
    				
    			$this->_name='ln_client_receipt_money_detail';
    			$array = array(
    					'client_id'		=>$data['member'],
    					'land_id'		=>$data['land_code'],
    					'date_payment'	=>$data['date_buy'],
    					'paid_date'     =>$data['date_buy'],
    					'last_pay_date' =>$data['date_buy'],
    					'capital'	    =>$data['sold_price'],
    					'remain_capital'=>$data['balance'],
    					'principal_permonth'=>$data['deposit'],
    					'old_principal_permonth'=>$data['deposit'],
    					'total_interest'=>0,
    					'total_payment'	=>$data['sold_price'],
    					'total_recieve'	=>$data['deposit'],
    					'service_charge'=>0,
    					'penelize_amount'=>0,
    					'is_completed'	=>($data['schedule_opt']==2)?1:0,
    					'status'		=>1,
    			);
    			if(!empty($data['receipt'])){
    				$sql="SELECT id FROM `ln_client_receipt_money` WHERE receipt_no='".$data['receipt']."'";
    				$crm_id = $db->fetchOne($sql);
    				if(!empty($crm_id)){
    					$where="crm_id=".$crm_id;
    					$this->update($array, $where);
    				}
    			}
	        $db->commit();
	        return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    function recordhistory($_data,$sale_id){
    	$arr=array();
    	$stringold="";
    	$string="";
    	$labelactivity="";
    	$db_pro = new Project_Model_DbTable_DbProject();
    	$dbsale = new Loan_Model_DbTable_DbLandpayment();
    	$dbclient = new Group_Model_DbTable_DbClient();
    	$dbproper = new Project_Model_DbTable_DbLand();
    	$db_co = new Other_Model_DbTable_DbCreditOfficer();
    	
    	if (!empty($_data['id'])){
    
//     		$row=$this->getCancelById($_data['id']);
//     		$project = $db_pro->getBranchById($row['branch_id']);
//     		$rowsale = $dbsale->getTranLoanByIdWithBranch($row['sale_id'],null);
//     		$client = $dbclient->getClientById($rowsale['client_id']);
//     		$land = $dbproper->getClientById($rowsale['house_id']);
    			
//     		$stringold="Project : ID:".$row['branch_id']."-".$project['project_name']."<br />";
//     		$stringold.="SALE : ID:".$row['sale_id']."-".$rowsale['sale_number']."<br />";
//     		$stringold.="Customer : id=".$rowsale['client_id']."-".$client['name_kh']."<br />";
//     		$stringold.="Property : id=".$rowsale['house_id']."-".$land['land_address']." Street ".$land['street']."<br />";
    			
//     		$stringold.="Reason : ".$row['reason']."<br />";
//     		$stringold.="Paid Amount : ".$row['paid_amount']."<br />";
//     		$stringold.="Installment Paid : ".$row['installment_paid']."<br />";
//     		$stringold.="Return Amount : ".$row['return_back']."<br />";
    
    
//     		$project = $db_pro->getBranchById($_data['branch_id']);
//     		$rowsale = $dbsale->getTranLoanByIdWithBranch($sale_id,null);
//     		$client = $dbclient->getClientById($rowsale['client_id']);
//     		$land = $dbproper->getClientById($rowsale['house_id']);
    
//     		$string="Project : ID:".$_data['branch_id']."-".$project['project_name']."<br />";
//     		$string.="SALE : ID:".$sale_id."-".$rowsale['sale_number']."<br />";
//     		$string.="Customer : id=".$rowsale['client_id']."-".$client['name_kh']."<br />";
//     		$string.="Property : id=".$rowsale['house_id']."-".$land['land_address']." Street ".$land['street']."<br />";
    
//     		$string.="Reason : ".$_data['reason']."<br />";
//     		$string.="Paid Amount : ".$_data['paid_amount']."<br />";
//     		$string.="Installment Paid : ".$_data['installment_paid']."<br />";
//     		$string.="Return Amount : ".$_data['return_back']."<br />";
    			
//     		$labelactivity="Edit Cancel ";
    	}else{
    		$string="";
    			
    		$project = $db_pro->getBranchById($_data['branch_id']);
    		$rowsale = $dbsale->getTranLoanByIdWithBranch($sale_id,null);
    		$client = $dbclient->getClientById($rowsale['client_id']);
    		$land = $dbproper->getClientById($rowsale['house_id']);
    		$rowco = $db_co->getCOById($_data['staff_id']);
    		
    		$stringold="Project : ID:".$_data['branch_id']."-".$project['project_name']."<br />";
    		$stringold.="SALE : ID:".$sale_id."-".$rowsale['sale_number']."<br />";
    		$stringold.="Receipt : ".$rowsale['receipt_no']."<br />";
    		$stringold.="Customer : id=".$rowsale['client_id']."-".$client['name_kh']."<br />";
    		$stringold.="Property : id=".$rowsale['house_id']."-".$land['land_address']." Street ".$land['street']."<br />";
    		
    		$typesalelb="លក់តែមួយ";
    		if ($_data['typesale']==2){
    			$typesalelb="លក់ម្តងច្រើន";
    		}
    		$schedul_lb="កក់ទ្រនាប់ដៃ";
    		if ($_data['schedule_opt']==2){
    			$schedul_lb="បង់ផ្តាច់១០០%";
    		}
    		$agentName="";
    		if (!empty($rowco)){
    			$agentName=$rowco['co_khname'];
    		}
    		
    		$payment_method = "";
    		if ($_data['payment_method']==1){
    			$payment_method = "សាច់ប្រាក់";
    		}else if ($_data['payment_method']==2){
    			$payment_method = "ធនាគារ";
    		}else if ($_data['payment_method']==3){
    			$payment_method = "សែក";
    		}
    		$stringold.="Price Before : ".$rowsale['price_before']."<br />";
    		$stringold.="Discount Amount : ".$_data['discount']." And Disount Percent : ".$_data['discount_percent']."<br />";
    		$stringold.="Price Sold : ".$_data['sold_price']."<br />";
    		$stringold.="Deposit : ".$_data['deposit']."<br />";
    		$stringold.="Second Deposit : ".$_data['second_depostit']."<br />";
    		$stringold.="Balance : ".$_data['balance']."<br />";
    		$stringold.="Interest rate : ".$rowsale['interest_rate']."<br />";
    		$stringold.="Buy Date : ".date("Y-M-d",strtotime($_data['date_buy']))."<br />";
    		$stringold.="Agreement Date : ".date("Y-M-d",strtotime($_data['agreement_date']))."<br />";
    		$stringold.="Start Date : ".date("Y-M-d",strtotime($_data['date_buy']))."<br />";
    		$stringold.="First Date : ".date("Y-M-d",strtotime($_data['date_buy']))."<br />";
    		$stringold.="Validation Date : ".date("Y-M-d",strtotime($_data['date_line']))."<br />";
    		$stringold.="End Date : ".($_data['schedule_opt']==1)?$_data['date_line']:$_data['paid_date']."<br />";
    		$stringold.="Type Sale : ".$_data['typesale']."-".$typesalelb."<br />";
    		$stringold.="ប្រភេទបង់ : ".$_data['schedule_opt']."-".$schedul_lb."<br />";
    		
    		$stringold.="Paid Date : ".date("Y-M-d",strtotime($_data['paid_date']))."<br />";
    		$stringold.="Payment Method : ".$_data['payment_method']."-".$payment_method."<br />";
    		$stringold.="Cheque No : ".$_data['cheque']."<br />";
    		
    		$stringold.="Agent : ".$_data['staff_id']."-".$agentName."<br />";
    		$stringold.="កម្រៃជើងសារនឹងទទួល : ".$_data['full_commission']."<br />";
    		$stringold.="Note : ".$_data['note']."<br />";
    			
    		$labelactivity="Issue Deposit Sale : ".$rowsale['sale_number']." ".$client['name_kh']."-".$land['land_address']." Street ".$land['street'];
    	}
    	$arr['activityold']=$stringold;
    	$arr['after_edit_info']=$string;
    
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$_datas = array('description'=>$labelactivity,'activityold'=>$stringold,'after_edit_info'=>$string);
    	$dbgb->addActivityUser($_datas);
    
    	return $arr;
    }
}