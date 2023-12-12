<?php

class Loan_Model_DbTable_DbLandpayment extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_sale';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function getAllIndividuleLoan($search,$reschedule =null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$edit_sale = $tr->translate("EDITSALEONLY");
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
  		 `s`.`discount_amount` AS `discount_amount`,
 		CONCAT(`s`.`discount_percent`,'%') AS `discount_percent`,
       
 		`s`.`price_sold`     AS `price_sold`,
 		(SELECT
	     SUM((`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`)) + ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1))
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
				END AS modify_date
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
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	if($search['co_id']>0){
//     		$where.= " AND s.staff_id = ".$search['co_id'];
    		$condiction = $dbp->getChildAgency($search['co_id']);
    		if (!empty($condiction)){
    			$where.=" AND s.staff_id IN ($condiction)";
    		}else{
    			$where.=" AND s.staff_id=".$search['co_id'];
    		}
    	}
    	if($search['status']>-1){
    		$where.= " AND s.status = ".$search['status'];
    	}
    	if(!empty($search['land_id']) AND $search['land_id']>-1){
    		$where.= " AND (s.house_id = ".$search['land_id']." OR p.old_land_id LIKE '%".$search['land_id']."%')";
    	}
    	if(($search['client_name'])>0){
    		$where.= " AND `s`.`client_id`=".$search['client_name'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND s.branch_id = ".$search['branch_id'];
    	}
    	if(($search['schedule_opt'])>0){
    		$where.= " AND s.payment_id = ".$search['schedule_opt'];
    	}
    	if(!empty($search['streetlist'])){
    		$where.= " AND p.street = '".$search['streetlist']."'";
    	}	
    	$order = " ORDER BY s.id DESC";
    	
    	
    	$where.=$dbp->getAccessPermission("`s`.`branch_id`");
    	return $db->fetchAll($sql.$where.$order);
    }
    function getTranLoanByIdWithBranch($id,$is_newschedule=null){//group id
    	
    	$db = $this->getAdapter();
    	$sql="SELECT branch_id FROM ln_sale WHERE id = ".$id;
    	$branch_id = $db->fetchOne($sql);
    	
    	$sql = "SELECT s.*,
    	(SELECT total_principal_permonthpaid  FROM `ln_client_receipt_money` 
    		WHERE ln_client_receipt_money.receipt_no=s.receipt_no AND ln_client_receipt_money.branch_id = $branch_id LIMIT 1) AS paid_amount,
    	(SELECT date_input  FROM `ln_client_receipt_money` 
    		WHERE ln_client_receipt_money.receipt_no=s.receipt_no AND ln_client_receipt_money.branch_id = $branch_id LIMIT 1) AS date_input,

		(SELECT payment_method  FROM `ln_client_receipt_money` 
    	WHERE ln_client_receipt_money.receipt_no=s.receipt_no AND ln_client_receipt_money.branch_id = $branch_id LIMIT 1) AS Payment_Method,

		(SELECT bank_id  FROM `ln_client_receipt_money` 
    	WHERE ln_client_receipt_money.receipt_no=s.receipt_no AND ln_client_receipt_money.branch_id = $branch_id LIMIT 1) AS bank_id,

		(SELECT cheque  FROM `ln_client_receipt_money` 
    	WHERE ln_client_receipt_money.receipt_no=s.receipt_no AND ln_client_receipt_money.branch_id = $branch_id LIMIT 1) AS cheque,
    	

    	(SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id=s.house_id) AS old_land_id,
    	(SELECT CASE WHEN p.old_land_id  IS NULL THEN p.id ELSE p.old_land_id 	END  FROM `ln_properties` AS p WHERE p.id=s.house_id) AS all_land_id  
    		FROM `ln_sale` AS s
				WHERE s.id = ".$id;
    	$where="";
    	if($is_newschedule!=null){
    		$where.=" AND s.is_reschedule = 2 ";
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("`s`.`branch_id`");
    	$where.=" LIMIT 1 ";
    	
    	return $db->fetchRow($sql.$where);
    }
    function getSaleScheduleById($id,$payment_id){
    	$sql=" SELECT * FROM ln_saleschedule WHERE sale_id =$id AND status=1 AND is_completed=0 ";
    	if($payment_id==4){$sql.=" AND is_installment=1 ";};
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql);
    }
    function getSalePaidExist($id,$payment_id){
    	$sql=" SELECT * FROM ln_saleschedule WHERE sale_id =$id AND status=1 AND is_completed=1 ";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql);
    }
    

    function round_up($value, $places)
    {
    	$mult = pow(10, abs($places));
    	return $places < 0 ?
    	ceil($value / $mult) * $mult :
    	ceil($value * $mult) / $mult;
    }
    function round_up_currency($curr_id, $value,$places=-2){
    	//return $this->round_up($value, $places);
    	if ($curr_id==1){
    		return $this->round_up($value, $places);
    	}
    	else{
    		$digit_value = DIGIT_VALUE_SCHEDULE;// default =2;
    		return round($value,$digit_value);
//     		return round($value,2);
    	}
    }
    function getProperty($id){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `ln_properties` AS p WHERE p.`id`=".$id;
    	return $db->fetchRow($sql);
    }
    public function addSchedulePayment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		if($data['typesale']==2){//លក់ម្តងច្រើន
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
    				}else{ 
    					$land_code =$newpro['land_code'];
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
    				"old_land_id"=>$data['identity_land'],
    				'street_code'=>$newpro['street_code'],
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
    		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==4 OR $data["schedule_opt"]==5 OR $data["schedule_opt"]==6 OR $data["schedule_opt"]==7){//
    			$is_schedule=1;
    		}
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$loan_number = $dbtable->getLoanNumber($data);
    		$receipt = $dbtable->getReceiptByBranch($data);
    			   $arr = array(
    				'branch_id'=>$data['branch_id'],
    			   	'receipt_no'=>$receipt,
    				'sale_number'=>$loan_number,
    			   	'house_id'=>$data["land_code"],
    			   	'payment_id'=>$data["schedule_opt"],
    			   	'install_type'=>$data['install_type'],
    				'client_id'=>$data['member'],
    				'price_before'=>$data['total_sold'],
    				'discount_amount'=>$data['discount'],
    			   	'discount_percent'=>$data['discount_percent'],
    				'price_sold'=>$data['sold_price'],
    			   	'lastpayment_amount'=>$data['last_payment'],
    				'other_fee'=>0,
    				'paid_amount'=>$data['deposit'],
    				'balance'=>$data['balance'],
    				'buy_date'=>$data['date_buy'],
    				'end_line'=>$data['date_line'],
    				'interest_rate'=>$data['interest_rate'],
    				'total_duration'=>$data['period'],
    			   	'startcal_date'=>$data['release_date'],
    				'first_payment'=>$data['first_payment'],
    			   	'validate_date'=>$data['first_payment'],
    				'payment_method'=>1,
    				'note'=>$data['note'],
    			   	'land_price'=>0,//$data['house_price'],
    			   	'total_installamount'=>$data['total_installamount'],
    			   	'typesale'=>$data['typesale'],
    				'build_start'=>$data['start_building'],
    				'amount_build'=>$data['amount_build'],
    				'is_reschedule'=>$is_schedule,
    			    'agreement_date'=>$data['agreement_date'],
    				'staff_id'=>$data['staff_id'],
    				'comission'=>0,
    			   	'full_commission'=>$data['full_commission'],
    			   	'commission_times'=>$data['times_commission'],
    				'commission_amt'=>$data['commission_amt'],
    				'create_date'=>date("Y-m-d"),
    				'user_id'=>$this->getUserId(),
    			   	'amount_daydelay'=>$data['delay_day'],
    			   	'other_discount'=>$data['other_discount'],//Other Discount
					
    			   	'witness_i'=>$data['witness_i'],
    			   	'witness_ii'=>$data['witness_ii'],
    			   	'date_setcommission'=>$data['date_buy'],
					
    			   	'for_installamount'=>$data['for_installamount'],
    			);   
    		if(!empty($data['interest_policy'])){
    			 $arr['interest_policy']=$data['interest_policy'];
    		}
			if(!empty($data['agreement_for'])){
    			 $arr['agreement_for']=$data['agreement_for'];
    		}
			if(!empty($data['contract_issuer_id'])){
    			 $arr['contract_issuer_id']=$data['contract_issuer_id'];
    		}
    		$this->_name='ln_sale';
    		$id = $this->insert($arr);//add group loan
    		$data['sale_id']=$id;
    		if($data["schedule_opt"]==2){//បង់ផ្តាច់
    			$this->_name="ln_saleschedule";
    			$datapayment = array(
    					'branch_id'=>$data['branch_id'],
    					'sale_id'=>$id,//good
    					'begining_balance'=> $data['sold_price'],//good
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
    					'is_completed'=>0,
    					'date_payment'=>$data['paid_date'],
    					'percent'=>100,
    					'note'=>'',
    					'is_completed'=>1,
    					'is_installment'=>1,
    					'no_installment'=>1,
    					'received_userid'=>$this->getUserId(),
    					'commission'=>$data['full_commission'],
    			);
    			$recordid = $this->insert($datapayment);
    		}
    		
    		$total_day=0;
    		$old_remain_principal = 0;
    		$old_pri_permonth = 0;
    		$old_interest_paymonth = 0;
    		$old_amount_day = 0;
    		$cum_interest=0;
    		$amount_collect = 1;
    		$remain_principal = $data['sold_price'];
    		$next_payment = $data['first_payment'];
    		$from_date =  $data['release_date'];
    		$curr_type = 2;
    		
    		$key = new Application_Model_DbTable_DbKeycode();
    		$key = $key->getKeyCodeMiniInv(TRUE);
    		$term_types=$key['install_by'];
    		
    		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==6 OR $data["schedule_opt"]==5){
    			$term_types=1;
    		}
    		$loop_payment = $data['period']*$term_types;
    		$borrow_term = $data['period']*$term_types;
    		$payment_method = $data["schedule_opt"];
    		$j=0;
    		$pri_permonth=0;
    		$old_interestrate=0;
    		$str_next = '+1 month';
    		
    		for($i=1;$i<=$loop_payment;$i++){
    			$paid_receivehouse=1;
    			if($payment_method==3){//pay by times//check date payment
    				if($i!=1){
    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    				}else{
    					$next_payment = $data['first_payment'];   			
    				}
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$total_day = $amount_day;
    				$interest_paymonth = 0;
    				$pri_permonth = round($data['sold_price']/$borrow_term,0);
    				if($i==$loop_payment){//for end of record only
    					$pri_permonth = $remain_principal;
    					$paid_receivehouse = $data['paid_receivehouse'];
    				}
    			}elseif($payment_method==4){//បង់រំលស់
    				if($i!=1){
			    			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
			    			$start_date = $next_payment;
			    			$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
			    		}else{
			    			//​​បញ្ចូលចំនូនត្រូវបង់ដំបូងសិន
			    			if(!empty($data['identity'])){
			    				$ids = explode(',', $data['identity']);
			    				$key = 1;
			    				foreach ($ids as $j){
			    					if($key==1){
    								    $old_remain_principal = $data['sold_price'];
    								    $old_pri_permonth = $data['total_payment'.$j];
			    					}else{
			    						$old_remain_principal = $old_remain_principal-$old_pri_permonth;
			    						$old_pri_permonth = $data['total_payment'.$j];
			    					}
			    					$old_interest_paymonth = 0;
			    						
			    					$cum_interest = $cum_interest+$old_interest_paymonth;
			    					$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$j]);
			    						
			    					$this->_name="ln_saleschedule";
			    					$datapayment = array(
			    						'branch_id'=>$data['branch_id'],
    									'sale_id'=>$id,//good
    									'begining_balance'=> $old_remain_principal,//good
    									'begining_balance_after'=> $old_remain_principal,//good
    									'principal_permonth'=> $data['total_payment'.$j],//good
    									'principal_permonthafter'=>$old_pri_permonth,//good
    									'total_interest'=>$old_interest_paymonth,//good
    									'total_interest_after'=>$old_interest_paymonth,//good
    									'total_payment'=>$old_interest_paymonth+$old_pri_permonth,//good
    									'total_payment_after'=>$old_interest_paymonth+$old_pri_permonth,//good
    									'ending_balance'=>$old_remain_principal-$old_pri_permonth,
    									'cum_interest'=>$cum_interest,
    									'amount_day'=>$amount_day,
    									'is_completed'=>0,
    									'date_payment'=>$data['date_payment'.$j],
    									'percent'=>$data['percent'.$j],
			    						'percent_agree'=>$data['percent_agree'.$j],
			    						'ispay_bank'=>$data['pay_with'.$j],
    									'note'=>$data['remark'.$j],
    									'is_installment'=>1,
			    						'no_installment'=>$key,
			    						'commission'=>($data['times_commission']>=$j)?$data['commission_amt']:0
			    					);
			    					$key = $key+1;
			    					$this->insert($datapayment);
			    					$from_date = $data['date_payment'.$j];
			    				}
			    				$j=$key-1;
			    			}
			    			
			    			$old_remain_principal=0;
			    			$old_pri_permonth = 0;
			    			$old_interest_paymonth = 0;
			    			if(!empty($data['identity'])){
			    				$remain_principal = $data['sold_price']-$data['total_installamount'];//check here 
			    			}
			    			$next_payment = $data['first_payment'];
			    			$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
			    		}
			    		$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
			    		$total_day = $amount_day;
			    		if(!empty($data['interest_policy'])){//lorn city
			    			$interst_rate = $dbtable->getInterestRatebySetting($data['interest_policy'],$i);
			    			$newperiod=$data['period'];
			    			if($old_interestrate!=$interst_rate){
			    				if($i>1){
			    					$newperiod = $data['period']-$i+1;
			    				}
			    				$rsfixed = $dbtable->getFixePaymentbyInterest($interst_rate,$remain_principal,$newperiod);
			    				$data['fixed_payment']=$rsfixed;
			    			}
			    			$interest_paymonth = $remain_principal*$interst_rate/12/100;
			    			$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
			    			$old_interestrate = $interst_rate;
			    		}else{
				    		$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
				    		$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
			    		}
			    		if($data['install_type']==2){//ថយ
			    			$pri_permonth=$data['for_installamount']/($data['period']*$term_types);
			    			$pri_permonth = round($pri_permonth,0);
			    		}else{//ថេរ
			    			$pri_permonth = $data['fixed_payment']-$interest_paymonth;
			    		}
			    		
			    		if($i==$loop_payment){//for end of record only
			    			$pri_permonth = $remain_principal;
			    			$paid_receivehouse = $data['paid_receivehouse'];
			    		}
    			   }elseif($payment_method==6 OR $payment_method==5){//បង់មិនថេរ
	    			   	$ids = explode(',', $data['identity']);
	    			   	$key = 1;
	    			   	foreach ($ids as $i){
	    			   		if($key==1){
	    			   			$old_remain_principal = $data['sold_price'];
	    			   		}else{
	    			   			$old_remain_principal = $old_remain_principal-$old_pri_permonth;
	    			   		}
	    			   		$old_pri_permonth = $data['total_payment'.$i];
	    			   		if(end($ids)==$i){
	    			   			$paid_receivehouse = $data['paid_receivehouse'];
	    			   		}
	    			   			
	    			   		$old_interest_paymonth = ($data['interest_rate']==0)?0:$this->round_up_currency(1,($old_remain_principal*$data['interest_rate']/12/100));
	    			   		$cum_interest = $cum_interest+$old_interest_paymonth;
	    			   		$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$i]);
	    			   		
	    			   		$this->_name="ln_saleschedule";
	    			   		$datapayment = array(
    			   				'branch_id'=>$data['branch_id'],
    			   				'sale_id'=>$id,//good
    			   				'begining_balance'=> $old_remain_principal,//good
    			   				'begining_balance_after'=> $old_remain_principal,//good
    			   				'principal_permonth'=> $data['total_payment'.$i],//good
    			   				'principal_permonthafter'=>$data['total_payment'.$i],//good
    			   				'total_interest'=>$old_interest_paymonth,//good
    			   				'total_interest_after'=>$old_interest_paymonth,//good
    			   				'total_payment'=>$old_interest_paymonth+$old_pri_permonth,//good
    			   				'total_payment_after'=>$old_interest_paymonth+$old_pri_permonth,//good
    			   				'ending_balance'=>$old_remain_principal-$old_pri_permonth,
    			   				'cum_interest'=>$cum_interest,
    			   				'amount_day'=>$old_amount_day,
    			   				'is_completed'=>0,
    			   				'date_payment'=>$data['date_payment'.$i],
    			   				'note'=>$data['remark'.$i],
    			   				'percent'=>$data['percent'.$i],
    			   				'percent_agree'=>$data['percent_agree'.$i],
    			   				'is_installment'=>1,
    			   				'no_installment'=>$key,
    			   				'last_optiontype'=>$paid_receivehouse,
    			   				'ispay_bank'=>$data['pay_with'.$i],
    			   				'commission'=>($data['times_commission']>=$i)?$data['commission_amt']:0
	    			   		);
	    			   		
	    			   		$sale_currid = $this->insert($datapayment);
	    			   		$from_date = $data['date_payment'.$i];
	    			   		$key = $key+1;
	    			   	}
	    			   	break;
    			   }elseif($payment_method==7){//បង់រំលស់ខ្លះ
    				//$last_payment = 0;
	    				$data['sold_price'] = $data['sold_price']-$data['last_payment'];
	    				if($i!=1){
			    			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
			    			$start_date = $next_payment;
			    			$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
				    	}else{
			    			//​​បញ្ចូលចំនូនត្រូវបង់ដំបូងសិន
			    			if(!empty($data['identity'])){
			    				$ids = explode(',', $data['identity']);
			    				$key = 1;
			    				foreach ($ids as $j){
			    					if($key==1){
    								    $old_remain_principal = $data['sold_price'];
    								    $old_pri_permonth = $data['total_payment'.$j];
			    					}else{
			    						$old_remain_principal = $old_remain_principal-$old_pri_permonth;
			    						$old_pri_permonth = $data['total_payment'.$j];
			    					}
			    					$old_interest_paymonth = 0;
			    						
			    					$cum_interest = $cum_interest+$old_interest_paymonth;
			    					$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$j]);
			    						
			    					$this->_name="ln_saleschedule";
			    					$datapayment = array(
			    						'branch_id'=>$data['branch_id'],
    									'sale_id'=>$id,//good
    									'begining_balance'=> $old_remain_principal,//good
    									'begining_balance_after'=> $old_remain_principal,//good
    									'principal_permonth'=> $data['total_payment'.$j],//good
    									'principal_permonthafter'=>$old_pri_permonth,//good
    									'total_interest'=>$old_interest_paymonth,//good
    									'total_interest_after'=>$old_interest_paymonth,//good
    									'total_payment'=>$old_interest_paymonth+$old_pri_permonth,//good
    									'total_payment_after'=>$old_interest_paymonth+$old_pri_permonth,//good
    									'ending_balance'=>$old_remain_principal-$old_pri_permonth,
    									'cum_interest'=>$cum_interest,
    									'amount_day'=>$amount_day,
    									'is_completed'=>0,
    									'date_payment'=>$data['date_payment'.$j],
    									'percent'=>$data['percent'.$j],
			    						'percent_agree'=>$data['percent_agree'.$j],
			    						'ispay_bank'=>$data['pay_with'.$j],
    									'note'=>$data['remark'.$j],
    									'is_installment'=>1,
			    						'no_installment'=>$key,
			    						'commission'=>($data['times_commission']>=$j)?$data['commission_amt']:0
			    					);
			    					$key = $key+1;
			    					$this->insert($datapayment);
			    					$from_date = $data['date_payment'.$j];
			    				}
			    				$j=$key-1;
			    			}
			    			
			    			$old_remain_principal=0;
			    			$old_pri_permonth = 0;
			    			$old_interest_paymonth = 0;
			    			if(!empty($data['identity'])){
			    				$remain_principal = $data['sold_price']-$data['total_installamount'];//check here 
			    			}
			    			$next_payment = $data['first_payment'];
			    			$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
			    		}
			    		$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
			    		$total_day = $amount_day;
			    		$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
			    		$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
			    		if($data['install_type']==2){//ថយ
			    			$pri_permonth=$data['for_installamount']/($data['period']*$term_types);
			    			$pri_permonth = round($pri_permonth,0);
			    		}else{//ថេរ
			    			$pri_permonth = $data['fixed_payment']-$interest_paymonth;
			    		}
			    		if($i==$loop_payment){//for end of record only
			    			$pri_permonth = $remain_principal;
			    			$paid_receivehouse = $data['paid_receivehouse'];
			    			if($data['paid_receivehouse']==1){
			    				$paid_receivehouse=0;
			    			}
			    			if($data['paid_receivehouse']==0){
			    				$paid_receivehouse=1;
			    			}
			    		}
    			   }
    			   if($payment_method==3 OR $payment_method==4 OR $payment_method==7){
			    		$old_remain_principal =$old_remain_principal+$remain_principal;
			    		$old_pri_permonth = $old_pri_permonth+$pri_permonth;
			    		$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
			    		if($payment_method==4 AND $data['install_type']==2){//រំលស់ថយ
			    			$old_interest_paymonth = round($old_interest_paymonth,0);
			    		}
			    		$cum_interest = $cum_interest+$old_interest_paymonth;
			    		$old_amount_day =$old_amount_day+ $amount_day;
			    		$this->_name="ln_saleschedule";
    			        $datapayment = array(
    			        	'branch_id'=>$data['branch_id'],
    			        	'sale_id'=>$id,//good
    			        	'begining_balance'=> $old_remain_principal,//good
    			        	'begining_balance_after'=> $old_remain_principal,//good
    			        	'principal_permonth'=> $old_pri_permonth,//good
    			        	'principal_permonthafter'=>$old_pri_permonth,//good
    			        	'total_interest'=>$old_interest_paymonth,//good
    			        	'total_interest_after'=>$old_interest_paymonth,//good
    			        	'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    			        	'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    			        	'ending_balance'=>$old_remain_principal-$old_pri_permonth,
    			        	'cum_interest'=>$cum_interest,
    			        	'amount_day'=>$old_amount_day,
    			        	'is_completed'=>0,
    			        	'date_payment'=>$next_payment,
    			        	'no_installment'=>$i+$j,
    			        	//'last_optiontype'=>$paid_receivehouse,
    			        	'commission'=>($data['times_commission']>=($i+$j))?$data['commission_amt']:0,
    			        	'interest_rate'=>$old_interestrate
    			        );
    			        
    			        if($i==$loop_payment AND $payment_method!=7){//for end of record only
    			        	$datapayment['ispay_bank'] = $data['paid_receivehouse'];
    			        	if($data['paid_receivehouse']==1){
    			        		$datapayment['ispay_bank']=0;
    			        	}
    			        	if($data['paid_receivehouse']==0){
    			        		$datapayment['ispay_bank']=1;
    			        	}
    			        }
		            		 
			    		$idsaleid = $this->insert($datapayment);
			    		$old_remain_principal = 0;
			    		$old_pri_permonth = 0;
			    		$old_interest_paymonth = 0;
			    		$old_amount_day = 0;
			    		$from_date=$next_payment;
    			   }
    		}
    		if($payment_method==7 AND $data['last_payment']>0){
    				$this->_name="ln_saleschedule";
    				$old_remain_principal = $data['last_payment'];
    				$old_pri_permonth = $old_remain_principal;
    				$old_interest_paymonth=0;
    				$old_amount_day=0;
    				$cum_interest=0;
    				$datapayment = array(
    					'branch_id'=>$data['branch_id'],
    					'sale_id'=>$id,//good
    					'begining_balance'=> $old_remain_principal,//good
    					'begining_balance_after'=> $old_remain_principal,//good
    					'principal_permonth'=> $old_pri_permonth,//good
    					'principal_permonthafter'=>$old_pri_permonth,//good
    					'total_interest'=>$old_interest_paymonth,//good
    					'total_interest_after'=>$old_interest_paymonth,//good
    					'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    					'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    					'ending_balance'=>$old_remain_principal-$old_pri_permonth,
    					'cum_interest'=>$cum_interest,
    					'amount_day'=>$old_amount_day,
    					'is_completed'=>0,
    					'date_payment'=>$next_payment,
    					'no_installment'=>$i+$j,
    					'ispay_bank'=>$paid_receivehouse,
    					'commission'=>($data['times_commission']>=($i+$j))?$data['commission_amt']:0
    			);
    				
//     				if($data['paid_receivehouse']>0){
//     					$datapayment['ispay_bank'] = $data['paid_receivehouse'];
//     				}
    			$this->insert($datapayment);
    		}
	    	if($data['deposit']>0){//insert payment
	    		$data['date_buy']=$data['paid_date'];
	    		$this->addPaymenttoSale($data,null);
	    	}
	        $db->commit();
	        return 1;
	        }catch (Exception $e){
	            $db->rollBack();
	            Application_Form_FrmMessage::message("INSERT_FAIL");
	            Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	        }
    }
    function addPaymenttoSale($data,$action=null){
    	$dbtable = new Application_Model_DbTable_DbGlobal();
    	if($action!=null){//edit
    		$receipt=$data['receipt'];
    	}else{
    		$receipt = $dbtable->getReceiptByBranch($data);
    	}
    	$pay_off = 0;
    	if($data["schedule_opt"]==2){
    		$pay_off = 1;
    	}
    	$field3 = 1;//deposit
    	if($data['total_installamount']>0){
    		$field3 = 3;//monthly payment
    	}
		$datePaymentForReceipt = $data['date_buy']; // set defualt
    	$array = array(
    			'branch_id'			=>$data['branch_id'],
    			'client_id'			=>$data['member'],
    			'receipt_no'		=>$receipt,
    			'date_pay'			=>$data['date_buy'],
    			'land_id'			=>$data['land_code'],
    			'sale_id'			=>$data['sale_id'],
    			'date_input'		=>$data['date_buy'],//paid_date
    			'outstanding'		=> $data['sold_price'],
    			'principal_amount'	=> $data['balance'],
    			'penalize_amount'	=>0,
    			'penalize_amountpaid'=>0,
    			'service_charge'	=>0,
    			'service_chargepaid'=>0,
    			'total_payment'		=>$data['deposit'],
    			'amount_payment'	=>$data['deposit'],
    			'recieve_amount'	=>$data['deposit'],
    			'allpaid_before'	=>$data['deposit'],
    			'selling_price'     => $data['sold_price'],
    			'total_principal_permonth'	=>$data['deposit'],
    			'total_principal_permonthpaid'=>$data['deposit'],
    			'total_interest_permonth'	=>0,
    			'total_interest_permonthpaid'=>0,
    			'balance'			=>$data['balance'],
    			'payment_option'	=>($data['schedule_opt']==2)?4:1,//4 payoff,1normal
    			'is_completed'		=>($data['schedule_opt']==2)?1:0,
    			'status'			=>1,
    			'note'				=>$data['note'],
    			'user_id'			=>$this->getUserId(),
    			'field3'			=>$field3,// ជាប្រាក់កក់
    			'field2'=>1,
    			'is_payoff'=>$pay_off,
    			'payment_times'=>1,
    			//additional 26-Aug-2019
    			'payment_method'	=> empty($data['payment_method'])?1:$data['payment_method'],
    			'cheque'			=> empty($data['cheque'])?"N/A":$data['cheque'],
				
				'date_payment'			=> $datePaymentForReceipt,
    	);
    	
    	$this->_name='ln_client_receipt_money';
    	if($action==null){//edit
    		$crm_id = $this->insert($array);
    	}else{
    		$where = ' status = 1 AND land_id ='.$data["old_landid"].' AND sale_id = '.$data['id'];
    		$this->update($array, $where);
    		$sql=" SELECT id FROM `ln_client_receipt_money` WHERE receipt_no = '".$receipt."' limit 1 ";
    		$db = $this->getAdapter();
    		$crm_id =  $db->fetchOne($sql);
    		if(empty($crm_id)){
    			$crm_id = $this->insert($array);
    		}
    	}
    	$rows = $this->getSaleScheduleById($data['sale_id'], 1);//why write like this 
    	$paid_amount = $data['deposit'];
    	$remain_principal=0;
    	$statuscomplete=0;
    	$principal_paid = 0;
    	$total_interest = 0;
    	$total_principal=0;
    	$total_interestpaid =0;
    	$remain_principal=0;
    	$old_paid=0;
    	$old_interest = 0;
    	$total_interestafter=0;
    	if(!empty($rows)){
    		foreach ($rows as $row){
    			$old_interest=$paid_amount;
    			$paid_amount = round($paid_amount-$row['total_interest_after'],2);
    			if($paid_amount>=0){
    				$total_interestafter=0;
    				$total_interestpaid=$row['total_interest_after'];
    				$old_paid = $paid_amount;
    				$paid_amount = round($paid_amount-$row['principal_permonthafter'],2);
    				
    				if($paid_amount>=0){
    					$principal_paid = $row['principal_permonthafter'];
    					$statuscomplete=1;
    					$remain_principal=0;
    				}else{
    					$principal_paid = ($old_paid);
    					$remain_principal=abs($paid_amount);
    					$statuscomplete=0;
    				}
    			}else{
    				$remain_principal = $row['principal_permonthafter'];
    				$statuscomplete=0;
    				$principal_paid=0;
    				$total_interestpaid=($old_interest);
    				$total_interestafter=abs($paid_amount);
    			}
    			$total_interest=$total_interest+$total_interestpaid;//ok
    			$total_principal = $total_principal+$principal_paid;
    			
    			$pyament_after = $row['total_payment_after']-($total_interestpaid+$principal_paid);//ប្រាក់ត្រូវបង់លើកក្រោយសំរាប់ installmet 1 1
    			$user_id = $this->getUserId();
    			$arra = array(
    				'principal_permonthafter'=>$remain_principal,
    				'total_interest_after'	=> $total_interestafter,
    				'begining_balance_after'=> $row['begining_balance_after']-($data['deposit']-$total_interest),
    				'is_completed'			=> $statuscomplete,
    				'paid_date'				=> $data['date_buy'],
    				'total_payment_after'	=> $pyament_after,
    				'received_userid'=>($statuscomplete==1)?$user_id:0,
    			);
    			$this->_name="ln_saleschedule";
    			$where="id = ".$row['id'];
    			$this->update($arra, $where);
    			
    			if(AUTO_PAYCOMMISSION==1 AND $statuscomplete==1 AND $row['commission']>0){
    				$__data = array(
    						'branch_id'      => $data['branch_id'],
    						'sale_id'	     => $data['sale_id'],
    						'sale_no' 		 => $data['sale_id'],
    						'title'	         => '',
    						'return_back'    => $row['commission'],
    						'cheque'	     => '',
    						'cheque_issuer'  => '',
    						'other_invoice'  => '',
    						'property_id'    => $data['land_code'],
    						'income_category'=> 16,
    						'staff_id'		 => $data['staff_id'],
    						'payment_type'   => 1,
    						'note'           => '',
    						'date'           => $data['paid_date'],
    						'supplier_id'    => '',
    				);
    				$db_exp = new Incexp_Model_DbTable_DbComission();
    				$db_exp->addSaleComission($__data);
    			}
    			
    			$this->_name='ln_client_receipt_money_detail';
				
				$datePaymentForReceipt =$row['date_payment'];
    			$array = array(
    					'crm_id'				=>$crm_id,
    					'lfd_id'				=>$row['id'],
    					'client_id'				=>$data['member'],
    					'land_id'				=>$data['land_code'],
    					'date_payment'			=>$row['date_payment'],
    					'paid_date'             =>$data['date_buy'],
    					'last_pay_date'			=>$data['date_buy'],
    					'remain_capital'		=>$row['begining_balance']-$principal_paid,
    					'principal_permonth'	=>$data['deposit'],
    					'total_interest'		=>0,
    					'total_payment'			=>$principal_paid,
    					'total_recieve'			=>$principal_paid,
    					'service_charge'		=>0,
    					'penelize_amount'		=>0,
    					'is_completed'			=>$statuscomplete,
    					'status'				=>1,    					
    					'capital'				=>$row['begining_balance'],
    					'old_principal_permonth'=>$row["principal_permonthafter"],
    					'old_interest'			 =>$row["total_interest_after"],
    					'old_total_payment'	 =>$row["total_payment_after"],
    			);
    			if($action==null){//edit
    				$this->insert($array);
    			}else{
    				$where = ' crm_id = '.$crm_id;
    				$this->update($array, $where);
    			}
    			if($paid_amount<=0){
    				break;
    			}
    			
    		}

    		$arr = array(
	    		'outstanding'		=> $data['sold_price'],
	    		'principal_amount'	=> $data['sold_price']-$total_principal,//សល់ពីបង់
    			'allpaid_before'	=> $total_principal,
    			'total_principal_permonth'=>$total_principal,
	    		'total_principal_permonthpaid'=>$total_principal,
    			'allpaid_before'=>$total_principal,
	    		'total_interest_permonth'	=>$total_interest,
	    		'total_interest_permonthpaid'=>$total_interest,
				'date_payment'			=> $datePaymentForReceipt,
    			);
    		
    		$this->_name='ln_client_receipt_money';
    		$where="id = ".$crm_id;
    		$crm_id = $this->update($arr, $where);
    	}else{
    		if($data["schedule_opt"]==1 OR $data["schedule_opt"]==2 OR $data["schedule_opt"]==5){//only កក់ ផ្តាច់ បង់១ធនាគារ
	    		$this->_name='ln_client_receipt_money_detail';
	    		$array = array(
    				'crm_id'				=> $crm_id,
    				'client_id'				=> $data['member'],
    				'land_id'				=> $data['land_code'],
    				'date_payment'			=> $data['date_buy'],
    				'paid_date'             => $data['date_buy'],
    				'last_pay_date'			=> $data['date_buy'],
    				'capital'				=> $data['sold_price'],
    				'remain_capital'		=> $data['balance'],
    				'principal_permonth'	=> $data['deposit'],
    				'old_principal_permonth'=> $data['deposit'],
    				'total_interest'		=> 0,
    				'total_payment'			=> $data['sold_price'],
    				'total_recieve'			=> $data['deposit'],
    				'service_charge'		=> 0,
    				'penelize_amount'		=> 0,
    				'is_completed'			=> ($data['schedule_opt']==2)?1:0,
    				'status'				=>1);
    		if($action==null){//edit
    				$crm_id = $this->insert($array);
    			}else{
    				$where = ' crm_id = '.$crm_id;
    				$this->update($array, $where);
    			}
    		}
    	}
    }
    function updateLoanById($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbp = new Loan_Model_DbTable_DbLandpayment();
    		$row = $dbp->getTranLoanByIdWithBranch($data['id'],null);
    		if(!empty($row)){
    			if($row['typesale']==2){//multi sale
    				$ids = explode(',', $row['old_land_id']);
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
    			return 1;
    		}
    		$is_schedule=0;
    		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==4 OR $data["schedule_opt"]==6){//
    			$is_schedule=1;
    		}
    		  $arr = array(
    		  		'branch_id'=>$data['branch_id'],
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
    		  		'end_line'=>$data['date_line'],
    		  		'interest_rate'=>$data['interest_rate'],
    		  		'total_duration'=>$data['period'],
    		  		'startcal_date'=>$data['release_date'],
    		  		'first_payment'=>$data['first_payment'],
    		  		'validate_date'=>$data['first_payment'],
    		  		'payment_method'=>1,//$data['loan_type'],
    		  		'note'=>$data['note'],
    		  		'land_price'=>0,//$data['house_price'],
    		  		'total_installamount'=>$data['total_installamount'],
    		  		'agreement_date'=>$data['agreement_date'],
    		  		'staff_id'=>$data['staff_id'],
    		  		'full_commission'=>$data['full_commission'],
    		  		'comission'=>0,//$data['commission'],
    		  		'create_date'=>date("Y-m-d"),
    		  		'user_id'=>$this->getUserId(),
    		  		'is_reschedule'=>$is_schedule,
    		  		'status'=>$data['status_using'],
					
					'witness_i'=>empty($data['witness_i'])?"":$data['witness_i'],
    			   	'witness_ii'=>empty($data['witness_ii'])?"":$data['witness_ii'],
    			   	'date_setcommission'=>$data['date_buy'],
    		  );
    		  
    		$id = $data['id'];
    		$this->_name='ln_sale';
    		$where = 'id='.$id;
    		$this->update($arr, $where);
    		unset($schedule);
    		
    		$this->_name="ln_properties";
    		$where = "id =".$data["old_landid"];
    		$arr = array(
    				"is_lock"=>0
    		);
    		$this->update($arr, $where);
    		
    		$this->_name="ln_properties";
    		$where = "id =".$data["land_code"];
    		$arr = array(
    				"is_lock"=>1
    		);
    		$this->update($arr, $where);
    		
    		$this->_name='ln_saleschedule';
    		$where = 'sale_id = '.$data['id'];
    		$this->delete($where);
    		
    		$total_day=0;
    		$old_remain_principal = 0;
    		$old_pri_permonth = 0;
    		$old_interest_paymonth = 0;
    		$old_amount_day = 0;
    		$cum_interest=0;
    		$amount_collect = 1;
    		$remain_principal = $data['sold_price'];
    		$next_payment = $data['first_payment'];
    		$from_date =  $data['release_date'];
    		$curr_type = 2;//$data['currency_type'];
//     		$term_types = 12;
    		$key = new Application_Model_DbTable_DbKeycode();
    		$key = $key->getKeyCodeMiniInv(TRUE);
    		$term_types=$key['install_by'];
    		
    		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==6 OR $data["schedule_opt"]==5){
    			$term_types=1;
    		}
    		$loop_payment = $data['period']*$term_types;
    		$borrow_term = $data['period']*$term_types;
    		$payment_method = $data["schedule_opt"];
    		$j=0;
    		$pri_permonth=0;
    		
    		$str_next = '+1 month';
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		
    		for($i=1;$i<=$loop_payment;$i++){
    			$paid_receivehouse=1;
    		if($payment_method==1){
    				break;
    			}elseif($payment_method==2){
    				break;
    			}elseif($payment_method==3){//pay by times//check date payment
    				if($i!=1){
    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    				}else{
    					$next_payment = $data['first_payment'];
    					
    				}
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$total_day = $amount_day;
    				$interest_paymonth = 0;
    				$pri_permonth = round($data['sold_price']/$borrow_term,0);
    				if($i==$loop_payment){//for end of record only
    					$pri_permonth = $remain_principal;
    					$paid_receivehouse = $data['paid_receivehouse'];
    				}
    			}elseif($payment_method==4){
    				if($i!=1){
    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    				}else{
    					//​​បញ្ចូលចំនូនត្រូវបង់ដំបូងសិន
    					if(!empty($data['identity'])){
    						$ids = explode(',', $data['identity']);
    						$key = 1;
    						foreach ($ids as $j){
    							if($key==1){
    								$old_remain_principal = $data['sold_price'];
    								$old_pri_permonth = $data['total_payment'.$j];
    							}else{
    								$old_remain_principal = $old_remain_principal-$old_pri_permonth;
    								$old_pri_permonth = $data['total_payment'.$j];
    							}
    							$old_interest_paymonth = 0;
    							 
    							$cum_interest = $cum_interest+$old_interest_paymonth;
    							$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$j]);
    							 
    							$this->_name="ln_saleschedule";
    							$datapayment = array(
    									'branch_id'=>$data['branch_id'],
    									'sale_id'=>$id,//good
    									'begining_balance'=> $old_remain_principal,//good
    									'begining_balance_after'=> $old_remain_principal,//good
    									'principal_permonth'=> $data['total_payment'.$j],//good
    									'principal_permonthafter'=>$old_pri_permonth,//good
    									'total_interest'=>$old_interest_paymonth,//good
    									'total_interest_after'=>$old_interest_paymonth,//good
    									'total_payment'=>$old_interest_paymonth+$old_pri_permonth,//good
    									'total_payment_after'=>$old_interest_paymonth+$old_pri_permonth,//good
    									'ending_balance'=>$old_remain_principal-$old_pri_permonth,
    									'cum_interest'=>$cum_interest,
    									'amount_day'=>$amount_day,
    									'is_completed'=>0,
    									'date_payment'=>$data['date_payment'.$j],
    									'percent'=>$data['percent'.$j],
    									'percent_agree'=>$data['percent_agree'.$j],
    									'note'=>$data['remark'.$j],
    									'is_installment'=>1,
    									'no_installment'=>$key,
    							);
    							$key = $key+1;
    							$this->insert($datapayment);
    							$from_date = $data['date_payment'.$j];
    						}
    						$j=$key-1;
    					}
    				
    					$old_remain_principal=0;
    					$old_pri_permonth = 0;
    					$old_interest_paymonth = 0;
    					if(!empty($data['identity'])){
    						$remain_principal = $data['sold_price']-$data['total_installamount'];//check here
    					}
    					$next_payment = $data['first_payment'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
    				}
    				 
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$total_day = $amount_day;
    				$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
    				$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    				if($data['install_type']==2){
    					$pri_permonth=$data['for_installamount']/($data['period']*$term_types);
    					$pri_permonth =$this->round_up_currency(2, $pri_permonth);
    				}else{
    					$pri_permonth = $data['fixed_payment']-$interest_paymonth;
    				}
    				if($i==$loop_payment){//for end of record only
    					$pri_permonth = $remain_principal;
    					$paid_receivehouse = $data['paid_receivehouse'];
    				}
    			   }elseif($payment_method==6){
	    			   	$ids = explode(',', $data['identity']);
	    			   	$key = 1;
	    			   	foreach ($ids as $i){
	    			   		if($key==1){
	    			   			$old_remain_principal = $data['sold_price'];
	    			   		}else{
	    			   			$old_remain_principal = $old_remain_principal-$old_pri_permonth;
	    			   		}
	    			   		$old_pri_permonth = $data['total_payment'.$i];
	    			   		if(end($ids)==$i){
	    			   			$paid_receivehouse = $data['paid_receivehouse'];
	    			   		}
	    			   		$old_interest_paymonth = ($data['interest_rate']==0)?0:$this->round_up_currency(1,($old_remain_principal*$data['interest_rate']/12/100));
	    			   		$cum_interest = $cum_interest+$old_interest_paymonth;
	    			   		$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$i]);
	    			   	
	    			   		$this->_name="ln_saleschedule";
	    			   		$datapayment = array(
	    			   				'branch_id'=>$data['branch_id'],
	    			   				'sale_id'=>$id,//good
	    			   				'begining_balance'=> $old_remain_principal,//good
	    			   				'begining_balance_after'=> $old_remain_principal,//good
	    			   				'principal_permonth'=> $data['total_payment'.$i],//good
	    			   				'principal_permonthafter'=>$data['total_payment'.$i],//good
	    			   				'total_interest'=>$old_interest_paymonth,//good
	    			   				'total_interest_after'=>$old_interest_paymonth,//good
	    			   				'total_payment'=>$old_interest_paymonth+$old_pri_permonth,//good
	    			   				'total_payment_after'=>$old_interest_paymonth+$old_pri_permonth,//good
	    			   				'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	    			   				'cum_interest'=>$cum_interest,
	    			   				'amount_day'=>$old_amount_day,
	    			   				'is_completed'=>0,
	    			   				'date_payment'=>$data['date_payment'.$i],
	    			   				'note'=>$data['remark'.$i],
	    			   				'percent'=>$data['percent'.$i],
	    			   				'percent_agree'=>$data['percent_agree'.$i],
	    			   				'is_installment'=>1,
	    			   				'no_installment'=>$key,
	    			   				'last_optiontype'=>$paid_receivehouse,
	    			   		);
	    			   		//if($payment_method==5){//with bank
	    			   			$datapayment['ispay_bank']= $data['pay_with'.$i];
	    			   		//}
	    			   		$sale_currid = $this->insert($datapayment);
	    			   		$from_date = $data['date_payment'.$i];
	    			   		$key = $key+1;
	    			   	}
	    			   	break;
    			   }
    			   if($payment_method==3 OR $payment_method==4){
    			   	$old_remain_principal =$old_remain_principal+$remain_principal;
    			   	$old_pri_permonth = $old_pri_permonth+$pri_permonth;
    			   	$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
    			   	$cum_interest = $cum_interest+$old_interest_paymonth;
    			   	$old_amount_day =$old_amount_day+ $amount_day;
    			   	$this->_name="ln_saleschedule";
    			   	$datapayment = array(
    			   			'branch_id'=>$data['branch_id'],
    			   			'sale_id'=>$id,//good
    			   			'begining_balance'=> $old_remain_principal,//good
    			   			'begining_balance_after'=> $old_remain_principal,//good
    			   			'principal_permonth'=> $old_pri_permonth,//good
    			   			'principal_permonthafter'=>$old_pri_permonth,//good
    			   			'total_interest'=>$old_interest_paymonth,//good
    			   			'total_interest_after'=>$old_interest_paymonth,//good
    			   			'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    			   			'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    			   			'ending_balance'=>$old_remain_principal-$old_pri_permonth,
    			   			'cum_interest'=>$cum_interest,
    			   			'amount_day'=>$old_amount_day,
    			   			'is_completed'=>0,
    			   			'date_payment'=>$next_payment,
    			   			'no_installment'=>$i+$j,
    			   			'last_optiontype'=>$paid_receivehouse,
    			   	);
    			   	 
	    			   	$idsaleid = $this->insert($datapayment);
	    			   	$old_remain_principal = 0;
	    			   	$old_pri_permonth = 0;
	    			   	$old_interest_paymonth = 0;
	    			   	$old_amount_day = 0;
	    			   	$from_date=$next_payment;
    			   	}
    			  }
    			if($data['deposit']>0){//insert payment
    				$data['sale_id']=$id;
    				$this->addPaymenttoSale($data,1);
    		   }
	        $db->commit();
	        return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function updateSaleOnlyById($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{  
    		$arr = array(
    				'branch_id'=>$data['branch_id'],
    				'house_id'=>$data["land_code"],
    				'payment_id'=>$data["schedule_opt"],
    				'client_id'=>$data['member'],
    				'price_before'=>$data['total_sold'],
    				'discount_amount'=>$data['discount'],
    				'discount_percent'=>$data['discount_percent'],
    				'price_sold'=>$data['sold_price'],
    				'other_fee'=>0,
    				'buy_date'=>$data['date_buy'],
    				'end_line'=>$data['date_line'],
    				'interest_rate'=>$data['interest_rate'],
    				'total_duration'=>$data['period'],
    				'startcal_date'=>$data['release_date'],
    				'first_payment'=>$data['first_payment'],
    				'validate_date'=>$data['first_payment'],
    				'payment_method'=>1,
    				'house_id'=>$data["land_code"],
    				'build_start'=>$data['start_building'],
    				'amount_build'=>$data['amount_build'],
    				'land_price'=>0,
    				'agreement_date'=>$data['agreement_date'],
    				'staff_id'=>$data['staff_id'],
    				'comission'=>0,
    				'user_id'=>$this->getUserId(),
    				'status'=>$data['status_using'],
    				'full_commission'=>$data['full_commission'],
    				'other_discount'=>$data['other_discount'],//Other Discount
    				'store_number'=>$data['store_number'],
					'witness_i'=>empty($data['witness_i'])?"":$data['witness_i'],
    			   	'witness_ii'=>empty($data['witness_ii'])?"":$data['witness_ii'],
    			   	'date_setcommission'=>$data['date_buy'],
					
    			   	'for_installamount'=>$data['for_installamount'],
    		);
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		$rs_user = $dbg->getUserInfo();
    		if($rs_user['level']==1){
    			//$arr['full_commission']=$data['full_commission'];
    		}
    		if(!empty($data['note_agreement'])){
    			$arr['note_agreement']=$data['note_agreement'];
    		}
    		if(!empty($data['agreement_for'])){
    			 $arr['agreement_for']=$data['agreement_for'];
    		}
			if(!empty($data['contract_issuer_id'])){
    			 $arr['contract_issuer_id']=$data['contract_issuer_id'];
    		}
			if(!empty($data['interest_policy'])){
    			 $arr['interest_policy']=$data['interest_policy'];
    		}
    		$id = $data['id'];
    		$this->_name='ln_sale';
    		$where = $db->quoteInto('id=?', $id);
    		$this->update($arr, $where);
    		
    		if($data['schedule_opt']==1){
	    		$this->_name='ln_saleschedule';
	    		$where = $db->quoteInto('sale_id=?', $id);
	    		$this->delete($where);
    		}
			
    		$this->_name="ln_properties";
    		$where = "id =".$data["old_landid"];
    		$arr = array(
    				"is_lock"=>0
    		);
    		$this->update($arr, $where);
    		
    		if($data['status_using']>0){
	    		$this->_name="ln_properties";
	    		$where = "id =".$data["land_code"];
	    		$arr = array(
	    				"is_lock"=>1
	    		);
	    		$this->update($arr, $where);
    		}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function addScheduleTestPayment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$sql=" TRUNCATE TABLE ln_sale_test ";
    		$db->query($sql);
    		$sql = "TRUNCATE TABLE ln_saleschedule_test";
    		$db->query($sql);
    		
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$loan_number = $dbtable->getLoanNumber($data);
    		$arr = array(
    				'branch_id'=>$data['branch_id'],
    				'payment_id'=>$data["schedule_opt"],
    				'client_id'=>$data['member'],
    				'price_before'=>$data['total_sold'],
    				'discount_amount'=>$data['discount'],
    				'price_sold'=>$data['sold_price'],
    				'other_fee'=>0,
    				'balance'=>$data['balance'],
    				'end_line'=>$data['date_line'],
    				'interest_rate'=>$data['interest_rate'],
    				'total_duration'=>$data['period'],
    				'first_payment'=>$data['first_payment'],
    				'validate_date'=>$data['first_payment'],
    				'payment_method'=>1,
    				'note'=>$data['note'],
    				'create_date'=>date("Y-m-d"),
    				'user_id'=>$this->getUserId()
    		);
    		$this->_name="ln_sale_test";
    		$id = $this->insert($arr);//add group loan
    
    		$total_day=0;
    		$old_remain_principal = 0;
    		$old_pri_permonth = 0;
    		$old_interest_paymonth = 0;
    		$old_amount_day = 0;
    		$cum_interest=0;
    		$amount_collect = 1;
    		
    		$remain_principal = $data['sold_price'];
    		$next_payment = $data['first_payment'];
    		$from_date =  $data['release_date'];
    		$curr_type = 2;
    		
    		$key = new Application_Model_DbTable_DbKeycode();
    		$key = $key->getKeyCodeMiniInv(TRUE);
    		$term_types=$key['install_by'];
    		
    		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==6){
    			$term_types=1;
    		}
    		$loop_payment = $data['period']*$term_types;
    		$borrow_term = $data['period']*$term_types;
    		$payment_method = $data["schedule_opt"];
    		$old_interestrate=0;
    		$str_next = '+1 month';
    		for($i=1;$i<=$loop_payment;$i++){
				$paid_receivehouse=1;
    			if($payment_method==3){//pay by times//check date payment
    				if($i!=1){
    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    				}else{
    					$next_payment = $data['first_payment'];
    				}
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$total_day = $amount_day;
    				$interest_paymonth = 0;
    				$pri_permonth = round($data['sold_price']/$borrow_term,0);
    				if($i==$loop_payment){//for end of record only
    					$pri_permonth = $remain_principal;
						$paid_receivehouse = $data['paid_receivehouse'];
    				}
					
    			}elseif($payment_method==4){
    				if($i!=1){
    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    				}else{
    					//​​បញ្ចូលចំនូនត្រូវបង់ដំបូងសិន    					
    					if(!empty($data['identity'])){
	    					$ids = explode(',', $data['identity']);
	    					$key = 1;
	    					foreach ($ids as $j){
	    						if($key==1){
	    							$remain_principal=$data['sold_price'];
	    							$old_pri_permonth = $data['total_payment'.$j];
	    						}else{
	    							$remain_principal = $remain_principal-$old_pri_permonth;
	    							$old_pri_permonth = $data['total_payment'.$j];
	    						}
	    						$key = $key+1;
	    						$old_interest_paymonth = 0;

	    						$cum_interest = $cum_interest+$old_interest_paymonth;
	    						$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$j]);
	    							
	    						$this->_name="ln_saleschedule_test";
	    						$datapayment = array(
	    								'branch_id'=>$data['branch_id'],
	    								'sale_id'=>$id,//good
	    								'begining_balance'=> $remain_principal,//good
	    								'principal_permonth'=> $old_pri_permonth,//good
	    								'principal_permonthafter'=>$old_pri_permonth,//good
	    								'total_interest'=>$old_interest_paymonth,//good
	    								'total_interest_after'=>$old_interest_paymonth,//good
	    								'total_payment'=>$old_interest_paymonth+$old_pri_permonth,//good
	    								'total_payment_after'=>$old_interest_paymonth+$old_pri_permonth,//good
	    								'ending_balance'=>$remain_principal-$old_pri_permonth,
	    								'cum_interest'=>$cum_interest,
	    								'amount_day'=>$old_amount_day,
	    								'is_completed'=>0,
	    								'date_payment'=>$data['date_payment'.$j],
										'ispay_bank'=>$data['pay_with'.$j],
	    						);
	    						$this->insert($datapayment);
	    						$from_date = $data['date_payment'.$j];
	    					}
	    				   }
	    					$old_remain_principal=0;
	    					$old_pri_permonth = 0;
	    					$old_interest_paymonth = 0;
	    					if(!empty($data['identity'])){$remain_principal = $data['sold_price']-$data['total_installamount'];}
	    					
	    					$next_payment = $data['first_payment'];
	    					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
	    				}
	    				
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$total_day = $amount_day;
    				if(!empty($data['interest_policy'])){//lorn city
    					$interst_rate = $dbtable->getInterestRatebySetting($data['interest_policy'],$i);
    					$newperiod=$data['period'];
    					if($old_interestrate!=$interst_rate){
    						if($i>1){
    							$newperiod = $data['period']-$i+1;
    						}
	    					$rsfixed = $dbtable->getFixePaymentbyInterest($interst_rate,$remain_principal,$newperiod);
	    					$data['fixed_payment']=$rsfixed;
    					}
    					$interest_paymonth = $remain_principal*$interst_rate/12/100;
    					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    					$old_interestrate = $interst_rate;    					
    				}else{
    					$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
    					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    				}
    				if($data['install_type']==2){//ថយ
    					$pri_permonth=$data['for_installamount']/($data['period']*$term_types);
    					$pri_permonth =round($pri_permonth,0);
    				}else{//ថេរ
    					$pri_permonth = $data['fixed_payment']-$interest_paymonth;
    				}
    				if($i==$loop_payment){//for end of record only
    					$pri_permonth = $remain_principal;
						$paid_receivehouse = $data['paid_receivehouse'];
    				}
    				
    			}elseif($payment_method==6 OR $payment_method==5){
    			   	 $ids = explode(',', $data['identity']);
    			   	 $key = 1;
    			   	 foreach ($ids as $i){
    			   	 	if($key==1){
    			   	 		$old_remain_principal = $data['sold_price'];
    			   	 		$old_pri_permonth = $data['total_payment'.$i];
    			   	 	}else{
    			   	 		$old_remain_principal = $old_remain_principal-$old_pri_permonth;
    			   	 		$old_pri_permonth = $data['total_payment'.$i];
    			   	 	}
    			   	 	
    			   	 	$key = $key+1;
    			   	 	$old_interest_paymonth = ($data['interest_rate']==0)?0:$this->round_up_currency(1,($old_remain_principal*$data['interest_rate']/12/100));

    			   	 	$cum_interest = $cum_interest+$old_interest_paymonth;
    			   	 	$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$i]);
    			   	 	
    			   	 	$this->_name="ln_saleschedule_test";
						
						if(end($ids)==$i){
							$paid_receivehouse = $data['paid_receivehouse'];
						}
    			   	 	$datapayment = array(
    			   	 			'branch_id'=>$data['branch_id'],
    			   	 			'sale_id'=>$id,//good
    			   	 			'begining_balance'=> $old_remain_principal,//good
//     			   	 			'begining_balance_after'=> $old_remain_principal,//good
    			   	 			'principal_permonth'=> $old_pri_permonth,//good
    			   	 			'principal_permonthafter'=>$old_pri_permonth,//good
    			   	 			'total_interest'=>$old_interest_paymonth,//good
    			   	 			'total_interest_after'=>$old_interest_paymonth,//good
    			   	 			'total_payment'=>$old_interest_paymonth+$old_pri_permonth,//good
    			   	 			'total_payment_after'=>$old_interest_paymonth+$old_pri_permonth,//good
    			   	 			'ending_balance'=>$old_remain_principal-$old_pri_permonth,
    			   	 			'cum_interest'=>$cum_interest,
    			   	 			'amount_day'=>$old_amount_day,
    			   	 			'is_completed'=>0,
    			   	 			'date_payment'=>$data['date_payment'.$i],
								'ispay_bank'=>$data['pay_with'.$i],
								'last_optiontype'=>$paid_receivehouse,
    			   	 	);
    			   	 	$this->insert($datapayment);
    			   	 	$from_date = $data['date_payment'.$i];
						
    			   	 }
    			   	 break;
    			   }elseif($payment_method==7){
	    			   	$data['sold_price'] = $data['sold_price']-$data['last_payment'];
	    			   	if($i!=1){
	    			   		$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
	    			   		$start_date = $next_payment;
	    			   		$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
	    			   	}else{
	    			   		if(!empty($data['identity'])){
	    			   			$ids = explode(',', $data['identity']);
	    			   			$key = 1;
	    			   			foreach ($ids as $j){
									
									if(end($ids)==$j){
										$paid_receivehouse=$data['paid_receivehouse'];
									}
									

	    			   				if($key==1){
	    			   					$remain_principal=$data['sold_price'];
	    			   					$old_pri_permonth = $data['total_payment'.$j];
	    			   				}else{
	    			   					$remain_principal = $remain_principal-$old_pri_permonth;
	    			   					$old_pri_permonth = $data['total_payment'.$j];
	    			   				}
	    			   				$key = $key+1;
	    			   				$old_interest_paymonth = 0;
	    			   
	    			   				$cum_interest = $cum_interest+$old_interest_paymonth;
	    			   				$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$j]);
	    			   
	    			   				$this->_name="ln_saleschedule_test";
	    			   				$datapayment = array(
	    			   						'branch_id'=>$data['branch_id'],
	    			   						'sale_id'=>$id,//good
	    			   						'begining_balance'=> $remain_principal,//good
	    			   						'principal_permonth'=> $old_pri_permonth,//good
	    			   						'principal_permonthafter'=>$old_pri_permonth,//good
	    			   						'total_interest'=>$old_interest_paymonth,//good
	    			   						'total_interest_after'=>$old_interest_paymonth,//good
	    			   						'total_payment'=>$old_interest_paymonth+$old_pri_permonth,//good
	    			   						'total_payment_after'=>$old_interest_paymonth+$old_pri_permonth,//good
	    			   						'ending_balance'=>$remain_principal-$old_pri_permonth,
	    			   						'cum_interest'=>$cum_interest,
	    			   						'amount_day'=>$old_amount_day,
	    			   						'is_completed'=>0,
	    			   						'date_payment'=>$data['date_payment'.$j],
											'ispay_bank'=>$data['pay_with'.$j],
											'last_optiontype'=>$paid_receivehouse,
	    			   				);
	    			   				$this->insert($datapayment);
	    			   				$from_date = $data['date_payment'.$j];
	    			   			}
	    			   		}
	    			   		$old_remain_principal=0;
	    			   		$old_pri_permonth = 0;
	    			   		$old_interest_paymonth = 0;
	    			   		if(!empty($data['identity'])){
	    			   			$remain_principal = $data['sold_price']-$data['total_installamount'];
	    			   		}
	    			   
	    			   		$next_payment = $data['first_payment'];
	    			   		$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
	    			   	}
	    			   	 
	    			   	$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	    			   	$total_day = $amount_day;
	    			   	$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
	    			   	$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
	    			   
	    			   	if($data['install_type']==2){//ថយ
	    			   		$pri_permonth=$data['for_installamount']/($data['period']*$term_types);
	    			   		$pri_permonth =round($pri_permonth,0);
	    			   	}else{//ថេរ
	    			   		$pri_permonth = $data['fixed_payment']-$interest_paymonth;
	    			   	}
	    			   	if($i==$loop_payment){//for end of record only
	    			   		$pri_permonth = $remain_principal;
	    			   	}
    			   }
    			if($payment_method==3 OR $payment_method==4 OR $payment_method==7){
	    			$old_remain_principal =$old_remain_principal+$remain_principal;
	    			$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	    			$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
	    			if($payment_method==4 AND $data['install_type']==2){//រំលស់ថយ
	    				$old_interest_paymonth = round($old_interest_paymonth,0);
	    			}
	    			$cum_interest = $cum_interest+$old_interest_paymonth;
	    			$old_amount_day =$old_amount_day+ $amount_day;
	    			$this->_name="ln_saleschedule_test";
	    			$datapayment = array(
	    					'branch_id'=>$data['branch_id'],
	    					'sale_id'=>$id,//good
	    					'begining_balance'=> $old_remain_principal,//good
	    					'principal_permonth'=> $old_pri_permonth,//good
	    					'principal_permonthafter'=>$old_pri_permonth,//good
	    					'total_interest'=>$old_interest_paymonth,//good
	    					'total_interest_after'=>$old_interest_paymonth,//good
	    					'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
	    					'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
	    					'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	    					'cum_interest'=>$cum_interest,
	    					'amount_day'=>$old_amount_day,
	    					'is_completed'=>0,
	    					'date_payment'=>$next_payment,
	    					//'last_optiontype'=>$paid_receivehouse,
	    			);  
					if($i==$loop_payment AND $payment_method!=7){//for end of record only
						$datapayment['ispay_bank'] = $data['paid_receivehouse'];
						if($data['paid_receivehouse']==1){
							$datapayment['ispay_bank']=0;
						}
						if($data['paid_receivehouse']==0){
							$datapayment['ispay_bank']=1;
						}
					}
	    			$this->insert($datapayment);
	    			$old_remain_principal = 0;
	    			$old_pri_permonth = 0;
	    			$old_interest_paymonth = 0;
	    			$old_amount_day = 0;
	    			$from_date=$next_payment;
    			}
    			
    		}
    		
    		if($payment_method==7 AND $data['last_payment']>0){
    			$this->_name="ln_saleschedule_test";
    			$old_remain_principal = $data['last_payment'];
    			$old_pri_permonth = $old_remain_principal;
    			$old_interest_paymonth=0;
    			$old_amount_day=0;
    			$cum_interest=0;
    			$datapayment = array(
    					'branch_id'=>$data['branch_id'],
    					'sale_id'=>$id,//good
    					'begining_balance'=> $old_remain_principal,//good
    					'principal_permonth'=> $old_pri_permonth,//good
    					'principal_permonthafter'=>$old_pri_permonth,//good
    					'total_interest'=>$old_interest_paymonth,//good
    					'total_interest_after'=>$old_interest_paymonth,//good
    					'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    					'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    					'ending_balance'=>$old_remain_principal-$old_pri_permonth,
    					'cum_interest'=>$cum_interest,
    					'amount_day'=>$old_amount_day,
    					'is_completed'=>0,
    					'date_payment'=>$next_payment,
    			);
    			$this->insert($datapayment);
    		}
    		
    		if($payment_method==3 OR $payment_method==4 OR $payment_method==6 OR $payment_method==7){
	    		$sql = " SELECT t.* , DATE_FORMAT(t.date_payment, '%d-%m-%Y') AS date_payments,
	    		DATE_FORMAT(t.date_payment, '%Y-%m-%d') AS date_name,
				(SELECT name_kh FROM ln_view WHERE type =29 AND key_code = t.ispay_bank LIMIT 1) AS payment_type
				FROM
	    		ln_saleschedule_test AS t WHERE t.sale_id = ".$id;
	    		$rows = $db->fetchAll($sql);
    		}else{
    			$sql = " SELECT *,'row_id',(SELECT name_kh FROM ln_view WHERE type =29 AND key_code = ln_sale_test.ispay_bank LIMIT 1) AS payment_type FROM ln_sale_test WHERE id = ".$id;
    			$rows = $db->fetchRow($sql);
    		}
    		$db->commit();
    		return $rows;
    	}catch (Exception $e){
    		echo $e->getMessage();exit();
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }

   

   
    public function getLastPayDate($data){
    	$loanNumber = $data['loan_numbers'];
    	$db = $this->getAdapter();
    	$sql ="SELECT cd.`date_payment` FROM `ln_client_receipt_money_detail` AS cd,`ln_client_receipt_money` AS c WHERE c.`id` = cd.`crm_id` AND c.`loan_number`='$loanNumber' ORDER BY cd.`id` DESC";
    	
    	return $db->fetchOne($sql);
    }
    
    public function addStaff($data){
    	
    	$db = $this->getAdapter();
   
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$staff_id = $_db->getStaffNumberByBranch($data['branch_id_pop']);  // get new staff code by branch
    	
    	$this->_name="ln_staff";
    	$array = array(
    			'branch_id'		=>$data['branch_id_pop'],		
    			'position_id'	=>1, // 1 => sale agent
    			//'co_code'		=>$staff_id,
    			'co_khname'		=>$data['kh_name'],
    			'sex'			=>$data['sex'],
    			'tel'			=>$data['phone'],
    			'note'			=>$data['note_pop'],
    			'create_date'	=>date('Y-m-d'),
    			
    			);
    	return $this->insert($array);
    	
//     	$sql = "select co_id as id , CONCAT(co_khname,' - ',co_code) as name_code from ln_staff where co_id = $id limit 1";
//     	return  $db->fetchRow($sql);
    	
    }
    
    public function addClient($data){
    	 
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$client_code=$_db->getNewClientIdByBranch($data['client_branch_id']);
    	
    	$this->_name="ln_client";
    	$array = array(
    			'branch_id'		=>$data['client_branch_id'],
    			'client_number'	=>$client_code,
    			'name_kh'		=>$data['client_name'],
    			'sex'			=>$data['client_sex'],
    			'client_d_type'	=>$data['client_doc_type'],
    			'nation_id'		=>$data['national_id'],
    			'phone'			=>$data['client_phone'],
    			'remark'		=>$data['client_note'],
    			'create_date'	=>date('Y-m-d'),
    			 
    	);
    	$id = $this->insert($array);
    	$result = array(
    			"id"=>$id,
    			'customer_code'=>$client_code);
    	return $result;       
    }
  function getDepositFirstPayment($sale_id){//old but not true if many deposit
  	$sql="SELECT total_principal_permonthpaid FROM `ln_client_receipt_money` WHERE sale_id=$sale_id ORDER BY id ASC LIMIT 1 ";
  	$db = $this->getAdapter();
  	return $db->fetchOne($sql);
  }
  function getDepositFirstPaymentMoneyOnly($sale_id){//ប្រើបានករណើតែបង់កកតែ១លើក
  	$sql="SELECT SUM(cr.total_principal_permonthpaid) AS total_principal_permonthpaid  FROM `ln_client_receipt_money` as cr
  	WHERE cr.sale_id=$sale_id AND field3=1 GROUP BY cr.sale_id  ORDER BY cr.id ASC LIMIT 1 ";
  	$db = $this->getAdapter();
  	return $db->fetchOne($sql);
  }
  
  function getDepositFirstPaymentMoney($sale_id){//new from above
  	$sql="SELECT (cr.total_principal_permonthpaid) AS total_principal_permonthpaid,cr.date_input  FROM `ln_client_receipt_money` as cr
  		 WHERE cr.sale_id=$sale_id AND field3=1  AND cr.total_principal_permonthpaid>0 ORDER BY cr.id ASC ";
  	$db = $this->getAdapter();
  	return $db->fetchAll($sql);
  }
  function getReceiptMoneyPaid($sale_id){//new from above
  	$sql="SELECT * FROM `ln_client_receipt_money` as cr
  	WHERE cr.sale_id=$sale_id AND total_principal_permonthpaid>0 ORDER BY cr.id ASC  ";
  	$db = $this->getAdapter();
  	return $db->fetchAll($sql);
  }
  function getPaymentPayoff($sale_id){
  	$sql="SELECT outstanding,date_pay FROM `ln_client_receipt_money` WHERE sale_id=$sale_id ORDER BY id DESC  LIMIT 1 ";
  	$db = $this->getAdapter();
  	return $db->fetchRow($sql);
  }
  public function demoSchedule($data){
  	$db = $this->getAdapter();
  	$db->beginTransaction();
  	try{
  
  		$sql=" TRUNCATE TABLE ln_sale_test ";
  		$db->query($sql);
  		$sql = "TRUNCATE TABLE ln_saleschedule_test";
  		$db->query($sql);
  
  		$dbtable = new Application_Model_DbTable_DbGlobal();
  		$loan_number = $dbtable->getLoanNumber($data);
  		$arr = array(
  				//'client_id'=>$data['member'],
  				//'price_before'=>$data['total_sold'],
  				//'discount_amount'=>$data['discount'],
  				'branch_id'=>$data['branch_id'],
  				'payment_id'=>$data["schedule_opt"],
  				'price_sold'=>$data['sold_price'],
  				'paid_amount'=>$data['deposit'],
  				'balance'=>$data['balance'],
  				'buy_date'=>$data['date_buy'],
  				'end_line'=>$data['date_line'],
  				'interest_rate'=>$data['interest_rate'],
  				'total_duration'=>$data['period'],
  				'first_payment'=>$data['first_payment'],
  				'validate_date'=>$data['first_payment'],
  				'create_date'=>date("Y-m-d"),
  				//'user_id'=>$this->getUserId(),
  				'payment_method'=>1,//$data['loan_type'],
  				//'note'=>$data['note'],
  				//     				'staff_id'=>$data['staff_id'],
  		//     				'comission'=>$data['commission'],
  				
  		);
  		$this->_name="ln_sale_test";
  		$id = $this->insert($arr);//add group loan
  
  		$total_day=0;
  		$old_remain_principal = 0;
  		$old_pri_permonth = 0;
  		$old_interest_paymonth = 0;
  		$old_amount_day = 0;
  		$cum_interest=0;
  		$amount_collect = 1;
  		//$remain_principal = $data['balance'];
  		$remain_principal = $data['sold_price'];
  		$next_payment = $data['first_payment'];
  		$from_date =  $data['release_date'];
  		$curr_type = 2;//$data['currency_type'];
  		//$term_types = 12;
  		$key = new Application_Model_DbTable_DbKeycode();
  		$key = $key->getKeyCodeMiniInv(TRUE);
  		$term_types=$key['install_by'];
  		
  		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==6){
  			$term_types=1;
  		}
  		$loop_payment = $data['period']*$term_types;
  		$borrow_term = $data['period']*$term_types;
  		$payment_method = $data["schedule_opt"];
  
  		$str_next = '+1 month';
  		for($i=1;$i<=$loop_payment;$i++){
  			if($payment_method==1){//booking
  
  			}elseif($payment_method==2){//payoff
  
  			}elseif($payment_method==3){//pay by times//check date payment
  				if($i!=1){
  					//     				$old_remain_principal=0;
  					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
  					$start_date = $next_payment;
  					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
  				}else{
  					$next_payment = $data['first_payment'];
  					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
  				}
  				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
  				$total_day = $amount_day;
  				$interest_paymonth = 0;
  				$pri_permonth = round($data['sold_price']/$borrow_term,0);
  				if($i==$loop_payment){//for end of record only
  					$pri_permonth = $remain_principal;
  				}
  			}elseif($payment_method==4){
  				if($i!=1){
  					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
  					$start_date = $next_payment;
  					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
  
  				}else{
  					//​​បញ្ចូលចំនូនត្រូវបង់ដំបូងសិន
  					if(!empty($data['identity'])){
  						$ids = explode(',', $data['identity']);
  						$key = 1;
  						foreach ($ids as $j){
  							if($key==1){
  								// 	    							$old_remain_principal = $data['sold_price'];
  								$remain_principal=$data['sold_price'];
  								$old_pri_permonth = $data['total_payment'.$j];
  							}else{
  								// 	    							$old_remain_principal = $old_remain_principal-$old_pri_permonth;
  								$remain_principal = $remain_principal-$old_pri_permonth;
  								$old_pri_permonth = $data['total_payment'.$j];
  							}
  							$key = $key+1;
  							$old_interest_paymonth = 0;
  								
  							$cum_interest = $cum_interest+$old_interest_paymonth;
  							$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$j]);
  
  							$this->_name="ln_saleschedule_test";
  							$datapayment = array(
  									'branch_id'=>$data['branch_id'],
  									'sale_id'=>$id,//good
  									'begining_balance'=> $remain_principal,//good
  									'principal_permonth'=> $old_pri_permonth,//good
  									'principal_permonthafter'=>$old_pri_permonth,//good
  									'total_interest'=>$old_interest_paymonth,//good
  									'total_interest_after'=>$old_interest_paymonth,//good
  									'total_payment'=>$old_interest_paymonth+$old_pri_permonth,//good
  									'total_payment_after'=>$old_interest_paymonth+$old_pri_permonth,//good
  									'ending_balance'=>$remain_principal-$old_pri_permonth,
  									'cum_interest'=>$cum_interest,
  									'amount_day'=>$old_amount_day,
  									'is_completed'=>0,
  									'date_payment'=>$data['date_payment'.$j],
  							);
  							$this->insert($datapayment);
  							$from_date = $data['date_payment'.$j];
  						}
  					}
  					$old_remain_principal=0;
  					$old_pri_permonth = 0;
  					$old_interest_paymonth = 0;
  					if(!empty($data['identity'])){
  						$remain_principal = $data['sold_price']-$data['total_installamount'];
  					}
  
  					$next_payment = $data['first_payment'];
  					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
  				}
  				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
  				$total_day = $amount_day;
  				$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
  				$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
  				 
  				$pri_permonth = $data['fixed_payment']-$interest_paymonth;
  				if($i==$loop_payment){//for end of record only
  					$pri_permonth = $remain_principal;
  				}
  			}elseif($payment_method==5){//bank
  
  			}elseif($payment_method==6){
  				$ids = explode(',', $data['identity']);
  				$key = 1;
  				foreach ($ids as $i){
  					if($key==1){
  						$old_remain_principal = $data['sold_price'];
  						//     			   	 		$old_pri_permonth = $data['total_payment'.$i]-$data['deposit'];
  						$old_pri_permonth = $data['total_payment'.$i];
  					}else{
  						$old_remain_principal = $old_remain_principal-$old_pri_permonth;
  						$old_pri_permonth = $data['total_payment'.$i];
  					}
  						
  					$key = $key+1;
  					$old_interest_paymonth = ($data['interest_rate']==0)?0:$this->round_up_currency(1,($old_remain_principal*$data['interest_rate']/12/100));
  
  					$cum_interest = $cum_interest+$old_interest_paymonth;
  					$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$i]);
  						
  					$this->_name="ln_saleschedule_test";
  					$datapayment = array(
  							'branch_id'=>$data['branch_id'],
  							'sale_id'=>$id,//good
  							'begining_balance'=> $old_remain_principal,//good
  							//     			   	 			'begining_balance_after'=> $old_remain_principal,//good
  							'principal_permonth'=> $old_pri_permonth,//good
  							'principal_permonthafter'=>$old_pri_permonth,//good
  							'total_interest'=>$old_interest_paymonth,//good
  							'total_interest_after'=>$old_interest_paymonth,//good
  							'total_payment'=>$old_interest_paymonth+$old_pri_permonth,//good
  							'total_payment_after'=>$old_interest_paymonth+$old_pri_permonth,//good
  							'ending_balance'=>$old_remain_principal-$old_pri_permonth,
  							'cum_interest'=>$cum_interest,
  							'amount_day'=>$old_amount_day,
  							'is_completed'=>0,
  							'date_payment'=>$data['date_payment'.$i],
  					);
  					$this->insert($datapayment);
  					$from_date = $data['date_payment'.$i];
  				}
  				break;
  			}
  			if($payment_method==3 OR $payment_method==4){
  				$old_remain_principal =$old_remain_principal+$remain_principal;
  				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
  				$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
  				$cum_interest = $cum_interest+$old_interest_paymonth;
  				$old_amount_day =$old_amount_day+ $amount_day;
  				$this->_name="ln_saleschedule_test";
  				$datapayment = array(
  						'branch_id'=>$data['branch_id'],
  						'sale_id'=>$id,//good
  						'begining_balance'=> $old_remain_principal,//good
  						'principal_permonth'=> $old_pri_permonth,//good
  						'principal_permonthafter'=>$old_pri_permonth,//good
  						'total_interest'=>$old_interest_paymonth,//good
  						'total_interest_after'=>$old_interest_paymonth,//good
  						'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
  						'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
  						'ending_balance'=>$old_remain_principal-$old_pri_permonth,
  						'cum_interest'=>$cum_interest,
  						'amount_day'=>$old_amount_day,
  						'is_completed'=>0,
  						'date_payment'=>$next_payment,
  				);
  				$this->insert($datapayment);
  				$old_remain_principal = 0;
  				$old_pri_permonth = 0;
  				$old_interest_paymonth = 0;
  				$old_amount_day = 0;
  				$from_date=$next_payment;
  			}
  		}
  		if($payment_method==3 OR $payment_method==4 OR $payment_method==6){
  			$sql = " SELECT t.* , DATE_FORMAT(t.date_payment, '%d-%m-%Y') AS date_payments,
  			DATE_FORMAT(t.date_payment, '%Y-%m-%d') AS date_name FROM
  			ln_saleschedule_test AS t WHERE t.sale_id = ".$id;
  			$rows = $db->fetchAll($sql);
  		}else{
  			$sql = " SELECT *,'row_id' FROM ln_sale_test WHERE id = ".$id;
  			$rows = $db->fetchRow($sql);
  		}
  		$db->commit();
  		return $rows;
  	}catch (Exception $e){
  		$db->rollBack();
  		Application_Form_FrmMessage::message("INSERT_FAIL");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  	}
  
  
  }
  
  function updateEnddateSale($sale_id){
  		$db = $this->getAdapter();
// //   	$sql="  SELECT s.id,s.end_line,
// // 		(SELECT date_payment FROM `ln_saleschedule` WHERE s.id=ln_saleschedule.sale_id ORDER BY ln_saleschedule.id DESC LIMIT 1) AS payment_date
// // 		FROM `ln_sale` AS s WHERE s.payment_id=3 OR s.payment_id=6 ";
// //   	$sql="  SELECT s.id,
// //   	(SELECT ln_saleschedule.id FROM `ln_saleschedule` WHERE s.id=ln_saleschedule.sale_id ORDER BY ln_saleschedule.id DESC LIMIT 1) AS last_recordid
// //   	FROM `ln_sale` AS s WHERE s.payment_id=3 OR s.payment_id=6 ";
//   $rows = $db->fetchAll($sql);
  	  $sql="  SELECT s.id,s.date_payment FROM ln_saleschedule AS s WHERE sale_id=$sale_id ORDER BY id ASC ";
  	  $rows = $db->fetchAll($sql);
	
	  if(!empty($rows)){
	  	$this->_name='ln_saleschedule';
	  	$nextpayment = $rows['buy_date'][0];
		  	foreach($rows as $rs){
		  		$arr = array(
		  				'date_payment'=>$nextpayment,
		  				);
		  		$where = " id = ".$rs['id'];
		  		$this->update($arr, $where);
		  		
		  		$sql1=" SELECT cr.id FROM ln_client_receipt_money AS cr,ln_client_receipt_money_detail AS cmd
		  		WHERE cr.id= cmd.crm_id AND lfd_id=".$rs['id'];
		  		$rsmoney = $db->fetchAll($sql1);
		  		
		  		$arr = array(
		  				'date_pay'=>$nextpayment,
		  				'date_input'=>$nextpayment,
		  		);
		  		$this->_name='ln_client_receipt_money';
		  		$where = " id = ".$rsmoney['id'];
		  		$this->update($arr, $where);
		  		
		  		$arr=array(
		  				'date_pay'=>$nextpayment,
		  				'date_input'=>$nextpayment,
		  		
		  		);
		  		$this->_name='ln_client_receipt_money_detail';
		  		$where = " lfd_id = ".$rs['id'];
		  		$this->update($arr, $where);
		  		
		  		$nextpayment = date("Y-m-d",strtotime("$nextpayment +30 day"));
		  	}
	    }
 	 }
	 
	public function updateRecordSchedule($data){
			$db = $this->getAdapter();
			$db->beginTransaction();
			try{
		
				$arr = array(
						'begining_balance'	=>$data['beginingBalance'],
						'principal_permonth'	=>$data['principalPermonth'],
						'total_interest'	=>$data['totalInterest'],
						'ending_balance'	=>$data['endingBalance'],
				);
				$where=" id = ".$data['id']." AND sale_id = ".$data['saleId'];
				$this->_name="ln_saleschedule";
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



