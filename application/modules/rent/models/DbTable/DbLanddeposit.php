<?php

class Rent_Model_DbTable_DbLanddeposit extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_rent_property';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
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
  		`s`.`price_before`    AS `price_before`,
 		CONCAT(`s`.`discount_percent`,'%') AS `discount_percent`,
        `s`.`discount_amount` AS `discount_amount`,
 		`s`.`price_sold`     AS `price_sold`,
 		(SELECT
	     SUM((`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`))
	   FROM `ln_rent_receipt_money` `cr`
	   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `totalpaid_amount`,   
        `s`.`buy_date`        AS `buy_date`,
        (SELECT  first_name FROM rms_users WHERE id=s.user_id limit 1 ) AS user_name,
         s.status,
         CASE    
				WHEN  `s`.`is_cancel` = 0 THEN ' '
				WHEN  `s`.`is_cancel` = 1 THEN '".$tr->translate("CANCELED")."'
				END AS cancel
		FROM ((`ln_rent_property` `s`
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
    		
    		$loan_number = $this->getRentNumber($data);
    		
    		$receipt = $data['receipt'];
    		$sql="SELECT id FROM ln_rent_receipt_money WHERE receipt_no='$receipt' ORDER BY id DESC LIMIT 1 ";
    		$acc_no = $db->fetchOne($sql);
    		if($acc_no){
    			$receipt = $this->getRentReceiptByBranch(array("branch_id"=>$data["branch_id"]));
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
    			 		
    			 	//date
    				'buy_date'=>$data['date_buy'],
    			 	'startcal_date'=>$data['release_date'],
    			 	'first_payment'=>$data['first_payment'],
    			 	'validate_date'=>$data['first_payment'],
    				'end_line'=>$data['date_line'],
    			 	'agreement_date'=>$data['agreement_date'],
    			 		
    				'interest_rate'=>0,
    				'total_duration'=>1,
    				'payment_method'=>1,//$data['loan_type'],
    				
    				'note'=>$data['note'],
    			   	'land_price'=>$property_info['house_price'],
    				'note_agreement'=>$note_agreement,
    				'typesale'=>$data['typesale'],
    				'is_reschedule'=>$is_schedule,
    			    
    			 	// Agency Commission	
    				'staff_id'=>$data['staff_id'],
    			   	'full_commission'=>$data['full_commission'],
    				'comission'=>0,//$data['commission'],
    			   	'second_depostit'=>$data['second_depostit'],

    			 		
    			 	//Policy Rent
    			 	'setting_opt'=>$data['setting_opt'],
    			 	'total_duration'=>$data['period'],
    			 		
    			 	//user create
    				'create_date'=>date("Y-m-d H:i:s"),
    			 	'modify_date'=>date("Y-m-d H:i:s"),
    				'user_id'=>$this->getUserId()
    				);
    		$this->_name='ln_rent_property';
    		$id = $this->insert($arr);//add group loan
    		$data['sale_id']=$id;
    		
    		$setting_id = $data['setting_opt'];
	    	$dbtable = new Application_Model_DbTable_DbGlobal();
	    	$dbSetting = new Rent_Model_DbTable_DbSetting();
	    	$_row = $dbSetting->getSettingDetailById($setting_id);
	    	
	    	$soldPrice = $data['sold_price'];
	    	$period = $data['period'];
	    	$from_date =  $data['release_date'];
	    	$next_payment = $data['first_payment'];
	    	$str_next = '+1 month';
    		 
    		for ($i=1; $i<=$period; $i++){
    			$settingDetail = $this->getRowSettingDetail($setting_id, $i);
    			$rentPerMonth = ($soldPrice * $settingDetail['percent_value'])/100;
    		
    			if($i!=1){
    				$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    			}else{
    				$next_payment = $data['first_payment'];
    			}
    		
    			$this->_name="ln_rentschedule";
    			$datapayment = array(
    					'sale_id'=>$id,//good
    					'no_installment'=>$i,
    					'principal_permonth'=> $rentPerMonth,//good
    					'principal_permonthafter'=>$rentPerMonth,//good
    					'total_interest'=>0,//good
    					'total_interest_after'=>0,//good
    					'total_payment'=>$rentPerMonth,//good
    					'total_payment_after'=>$rentPerMonth,//good
    					'is_completed'=>0,
    					'date_payment'=>$next_payment,
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
    					'date_payment'		=> $data['date_buy'],
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
    					'create_date'=>date("Y-m-d H:i:s"),
    					'modify_date'=>date("Y-m-d H:i:s"),
    			);
    			$this->_name='ln_rent_receipt_money';
    			$crm_id = $this->insert($array);
    				
    			$this->_name='ln_rent_receipt_money_detail';
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
    			$ids = explode(',', $row['all_land_id']);
    			if (!empty($row['all_land_id'])){
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
    		
    		if($data['status_using']==0){//cancel
    			$this->_name="ln_rent_property";
    			$arr_update = array(
    					'status'=>0
    			);
    			$where = ' status = 1 AND id = '.$data['id'];
    			$this->update($arr_update, $where);
    			 
    			$this->_name = 'ln_rentschedule';
    			$where = ' is_completed = 0 AND status = 1 AND sale_id = '.$data['id'];
    			$this->delete($where);
    			
    			$this->_name='ln_properties';
    			if($data['land_code']!=$data['old_landid']){
    				$where =" id= ".$data['old_landid'];
    				$arr = array('is_lock'=>0);
    				$this->update($arr,$where);
    			}
    			
    			$this->_name="ln_rent_receipt_money";
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
    					);
    					if($data['identity_land']!=$row['house_id']){
    						$newproperty["old_land_id"]=$data['identity_land'];
    					}
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
    		$this->_name='ln_rent_property';
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
    			$this->_name='ln_rent_receipt_money';
    			$where="receipt_no='".$data['receipt']."' AND branch_id = ".$data['branch_id'];
    			$this->update($array, $where);
    				
    			$this->_name='ln_rent_receipt_money_detail';
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
    				$sql="SELECT id FROM `ln_rent_receipt_money` WHERE receipt_no='".$data['receipt']."' AND branch_id = ".$data['branch_id'];
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
    
    public function getRentReceiptByBranch($data=array('branch_id'=>1)){
    	$this->_name='ln_rent_receipt_money';
    	$db = $this->getAdapter();
    
    	$sql=" SELECT COUNT(id) FROM $this->_name WHERE 1 LIMIT 1 ";
    	$pre='№ ';
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<6;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    public function getRentNumber($data=array('branch_id'=>1,'is_group'=>0)){
    	$this->_name='ln_rent_property';
    	$db = $this->getAdapter();
    	$dbtable = new Application_Model_DbTable_DbGlobal();
    	$sql=" SELECT COUNT(id) FROM $this->_name WHERE branch_id=".$data['branch_id']." LIMIT 1 ";
    	$pre = $dbtable->getPrefixCode($data['branch_id'])."-R";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<3;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    
    function getRowSettingDetail($setting_id,$month){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `rn_rentsetting_detail` WHERE  settin_id =$setting_id AND max_month >=$month ORDER BY max_month ASC LIMIT 1";
    	return $db->fetchRow($sql);
    }
    public function addScheduleTestPayment($_data){
    	$db = $this->getAdapter();
    	
    	$sql=" TRUNCATE TABLE ln_rent_property_test ";
    	$db->query($sql);
    	$sql = "TRUNCATE TABLE ln_rentschedule_test";
    	$db->query($sql);
    	
    	$dbtable = new Application_Model_DbTable_DbGlobal();
    	$loan_number = $this->getRentNumber($_data);
    	$arr = array(
    			'branch_id'=>$_data['branch_id'],
    			'client_id'=>$_data['member'],
    			'price_before'=>$_data['total_sold'],
    			'discount_amount'=>$_data['discount'],
    			'price_sold'=>$_data['sold_price'],
    			'other_fee'=>0,
    			'balance'=>$_data['balance'],
    			'end_line'=>$_data['date_line'],
    			'total_duration'=>$_data['period'],
    			'first_payment'=>$_data['first_payment'],
    			'validate_date'=>$_data['first_payment'],
    			'payment_method'=>1,
    			'create_date'=>date("Y-m-d"),
    			'user_id'=>$this->getUserId()
    	);
    	$this->_name="ln_rent_property_test";
    	$id = $this->insert($arr);//add group loan
    	
    	$setting_id = $_data['setting_opt'];
    	$dbtable = new Application_Model_DbTable_DbGlobal();
    	$dbSetting = new Rent_Model_DbTable_DbSetting();
    	$_row = $dbSetting->getSettingDetailById($setting_id);
    	
    	$soldPrice = $_data['sold_price'];
    	$period = $_data['period'];
    	$from_date =  $_data['release_date'];
    	$next_payment = $_data['first_payment'];
    	$str_next = '+1 month';
    	
    	for ($i=1; $i<=$period; $i++){
    		$settingDetail = $this->getRowSettingDetail($setting_id, $i);
    		$rentPerMonth = ($soldPrice * $settingDetail['percent_value'])/100;
    		
    		if($i!=1){
    			$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$_data['first_payment']);
    		}else{
    			$next_payment = $_data['first_payment'];
    		}
    		
    		$this->_name="ln_rentschedule_test";
    		$datapayment = array(
//     				'begining_balance'=> $old_remain_principal,//good
    				'sale_id'=>$id,//good
    				'principal_permonth'=> $rentPerMonth,//good
    				'principal_permonthafter'=>$rentPerMonth,//good
    				'total_interest'=>0,//good
    				'total_interest_after'=>0,//good
    				'total_payment'=>$rentPerMonth,//good
    				'total_payment_after'=>$rentPerMonth,//good
//     				'ending_balance'=>$old_remain_principal-$old_pri_permonth,
//     				'cum_interest'=>$cum_interest,
//     				'amount_day'=>$old_amount_day,
    				'is_completed'=>0,
    				'date_payment'=>$next_payment,
    		);
    		$this->insert($datapayment);
    	}
    	
    	$sql = " SELECT t.* , DATE_FORMAT(t.date_payment, '%d-%m-%Y') AS date_payments,
    		DATE_FORMAT(t.date_payment, '%Y-%m-%d') AS date_name FROM
    		ln_rentschedule_test AS t WHERE t.sale_id = ".$id;
    	$rows = $db->fetchAll($sql);
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$string = "";
    	$string.='
    		<table id="table_row" border="1" width="100%" style="border-collapse: collapse; border:1px solid #ccc !important;">
    			<tr id="head-title" class="head-td" style="color: #fff;background: #060679;font-size: 12px;height: 30px;margin-bottom: 10px;" id="head_title" class="head-title" align="center">
    				<th>'.$tr->translate("NUM").'</th>
    				<th>'.$tr->translate("DATE_PAYMENT").'</th>
    				<th>'.$tr->translate("TOTAL_PAYMENT").'</th>
    				<th>'.$tr->translate("NOTE").'</th>
    			</tr>
    	';
    	if (!empty($rows)) foreach ($rows as $key => $re){
    		$index = $key+1;
    		$string.='
    			<tr class="hover" style="border-bottom:1px solid #ccc;" >
    				<td width="2%" align="center">'.($index).'</td>
    				<td>'.date("d/M/Y",strtotime($re['date_payment'])).'</td>
    				<td>'.number_format($re['total_payment'],2).'</td>
    				<td>'.$re['note'].'</td>
    			</tr>
    		';
    	}
    	$string.='</table>';
    	return array('template'=>$string);
    }
    
    function getAllRentNumber(){//type ==1 is ilPayment, type==2 is group payment
    	$db = $this->getAdapter();
    	$sql ="SELECT id,
    	CONCAT((SELECT CONCAT(name_kh,'-',name_en) FROM ln_client WHERE ln_client.client_id=ln_rent_property.`client_id` ),' - ',sale_number) AS sale_number
    	FROM
    	ln_rent_property
    	WHERE `is_completed` = 0
    	AND `is_reschedule` != 1
    	";
    	 
    	return $db->fetchAll($sql);
    }
}