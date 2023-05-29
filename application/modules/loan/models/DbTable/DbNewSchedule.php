<?php

class Loan_Model_DbTable_DbNewSchedule extends Zend_Db_Table_Abstract
{

    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllReschedule($search){
    	$from_date =(empty($search['start_date']))? '1': " s.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql="SELECT `s`.`id` AS `id`,
    	(SELECT `ln_project`.`project_name` FROM `ln_project` WHERE (`ln_project`.`br_id` = `s`.`branch_id`) LIMIT 1) AS `branch_name`,
    	(SELECT sale_number FROM `ln_sale` WHERE ln_sale.id=s.sale_id LIMIT 1) AS sale_number,
	    `c`.`name_kh` AS `name_kh`,
	    (SELECT land_address FROM `ln_properties` WHERE ln_properties.id=s.land_id LIMIT 1 ) AS `land_address`,
	    (SELECT name_en FROM `ln_view` WHERE key_code =s.`payment_method_before` AND type = 25 LIMIT 1) AS paymenttype,
  		`s`.`balane_before`    AS `balane_before`,
  		(SELECT name_en FROM `ln_view` WHERE key_code =s.`payment_method_after` AND type = 25 LIMIT 1) AS paymenttype_after,
  		`balance_after`,
  		`date`,
        `s`.`date`        AS `date`,
         s.status
		FROM ((`ln_reschedule` `s`
		    JOIN `ln_client` `c`)
		   JOIN `ln_properties` `p`)
		WHERE ((`c`.`client_id` = `s`.`client_id`)
        AND (`p`.`id` = `s`.`land_id`)) ";
    	$db = $this->getAdapter();
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
      	 	$s_where[] = " c.client_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_en LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND s.status = ".$search['status'];
    	}
    	if(($search['client_name'])>0){
    		$where.= " AND `s`.`client_id`=".$search['client_name'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND s.branch_id = ".$search['branch_id'];
    	}
    	if($search['land_id']>0){
    		$where.=" AND `s`.`land_id` = ".$search['land_id'];
    	}
    	if(($search['schedule_opt'])>0){
    		$where.= " AND ( s.payment_method_before = ".$search['schedule_opt'];
    		$where.= " OR s.payment_method_after = ".$search['schedule_opt']." )";
    	}
    		
    	$order = " ORDER BY s.id DESC";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("`s`.`branch_id`");  
    	
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function round_up($value, $places)
    {
    	$mult = pow(10, abs($places));
    	return $places < 0 ?
    	ceil($value / $mult) * $mult :
    	ceil($value * $mult) / $mult;
    }
    function round_up_currency($curr_id, $value,$places=-2){
    	//     	return (($curr_id==1)? $this->round_up($value, $places):$value);
    	if ($curr_id==1){
    		return $this->round_up($value, $places);
    	}
    	else{
    		$digit_value = DIGIT_VALUE_SCHEDULE;// default =2;
    		return round($value,$digit_value);
//     		return round($value,2);
    	}
    }
    function getSaleInfo($sale_id){
    	$db = $this->getAdapter();
    	$sql="select * from ln_sale where id = $sale_id";
    	return $db->fetchRow($sql);
    }    
    public function addNewSchedule($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		if($data['schedule_opt']==1 OR $data['schedule_opt']==2){
    			return false;
    		}
    		$data['deposit']=0;
    		$data['date_buy']=date("Y-m-d");
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$sale = $this->getSaleInfo($data['loan_number']);
			
			$propertyId = empty($data['land_code'])?$sale['house_id']:$data['land_code'];
			$clientId 	= empty($data['member'])?$sale['client_id']:$data['member'];
			
    		$array = array(
    				'branch_id'				=>$data['branch_id'],
    				'sale_id'				=>$data['loan_number'],
					
    				//'client_id'				=>$data['member'],
					//'land_id'			    =>$data['land_code'],
					
					'client_id'				=>$clientId,
					'land_id'			    =>$propertyId,
					
    				'reschedule_date'		=>$data['date_buy'],
    				
    				'amount_before'			=>$data['total_sold'],
    				'paid_before'			=>$data['paid_before'],
    				'interestrate_before'	=>$sale['interest_rate'],
    				'payment_method_before'	=>$sale['payment_method'],
    				'period_before'			=>$sale['total_duration'],
    				'cal_startdate'			=>$sale['startcal_date'],
    				'first_paymentbefore'	=>$sale['first_payment'],
    				'end_datebefore'		=>$sale['end_line'],
    				'discount_after'		=>$data['discount'],    				
    				'total_payment'			=>$data['sold_price'],
    				'payment_method_after'	=>$data['schedule_opt'],
    				'paid_amount_after'		=>0,//$data['deposit'],
    				'balance_after'			=>$data['balance'],
    				'period_after'			=>$data['period'],
    				'interest_after'		=>$data['interest_rate'],			
    				'fixed_payment_after'	=>$data['fixed_payment'],
    				'start_date_after'		=>$data['release_date'],
    				'first_payment_after'	=>$data['first_payment'],
    				'end_date_after'		=>$data['date_line'],
    				'date'=>date("Y-m-d"),
    				'status'				=>1,
    				'note'					=>$data['note'],
    				'grace_period'			=>$data['grace_period'],
    				'user_id'				=>$this->getUserId(),
    				
    				);
    		$this->_name='ln_reschedule';
    		$id = $this->insert($array);
    	   
    		$is_schedule=0;
    		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==4 OR $data["schedule_opt"]==6){//
    			$is_schedule=1;
    		}    		
    			if($data['old_paymentmethod']==1 OR $data['other_fee']>0){//total_sold
    				$this->_name='ln_sale';
    				$dbp = new Loan_Model_DbTable_DbLandpayment();
    				$row = $dbp->getTranLoanByIdWithBranch($data['id'],null);
    				$arr = array(
    						'paid_amount'=>$row['paid_amount']+$data['deposit'],
    						'balance'=>$data['sold_price']-($data['deposit']+$data['paid_before']),
    						'payment_id'=>$data["schedule_opt"],
//     						'price_before'=>$data['total_sold'],
    				        'price_sold'=>$data['total_sold']+$data['other_fee'],
    						'discount_amount'=>$data['discount']+$data['bdiscount_fixed'],
    						'discount_percent'=>$data['discount_percent']+$data['bdiscount_percent'],
    						'interest_rate'=>$data['interest_rate'],
    						'total_duration'=>$data['period'],
    						'startcal_date'=>$data['release_date'],
    						'first_payment'=>$data['first_payment'],
    						'validate_date'=>$data['first_payment'],
    						'end_line'=>$data['date_line'],
    						'payment_method'=>1,//$data['loan_type'],
    						'price_sold'=>$data['sold_price'],
    						'note'=>$data['note'],
    						'total_installamount'=>$data['total_installamount'],
    						'user_id'=>$this->getUserId(),
    						//'other_fee'=>$data['other_fee'],
    						'agreement_date'=>$data['agreement_date'],
    						'is_reschedule'=>$is_schedule,
    				);
    				$where= " id = ".$data['id'];
    				$this->update($arr, $where);
    			}
    			
    			if($data['schedule_opt']==2){
    				$is_complete = 1;
    			}else{
    				$is_complete = 0;
    			}
    			 $arr = array(
    				'is_reschedule'=>1,
    			);
	    		if(!empty($data['interest_policy'])){//lorn city
	    			 $arr['interest_policy']=$data['interest_policy'];
	    		}
    			
				$sale['total_duration'] = empty($sale['total_duration'])?0:$sale['total_duration'];
				$arr['total_duration']=empty($data['period'])?$sale['total_duration']:$data['period'];
				//$arr['total_installamount']=empty($data['total_installamount'])?0:$data['total_installamount'];
				$arr['interest_rate']=empty($data['interest_rate'])?0:$data['interest_rate'];
				$arr['payment_id']=empty($data['schedule_opt'])?0:$data['schedule_opt'];
				
    	    $this->_name='ln_sale';
    	    $where = "id =".$data["loan_number"];
    		$id = $this->update($arr, $where);//add group loan
    		unset($datagroup);
    		
    		$where = " (principal_permonth=0 OR is_completed=0) AND sale_id=".$data['loan_number'];
    		$this->_name="ln_saleschedule";
    		$this->delete($where);
    		
    		$id = $data['loan_number'];
    		$total_day=0;
    		$old_remain_principal = 0;
    		$old_pri_permonth = 0;
    		$old_interest_paymonth = 0;
    		$old_amount_day = 0;
    		$cum_interest=0;
    		$amount_collect = 1;
    		$remain_principal = $data['balance']+$data['other_fee'];
    		if($data["old_paymentmethod"]==1 AND $data['deposit']>0){//if before new schedule (just deposit)
    			$remain_principal = $data['sold_price'];
    		}else{
    			if($data["schedule_opt"]==4 AND $data['deposit']==0){//check later if deposit> or =0
    				$data['sold_price']= $data['balance'];
    				$remain_principal = $data['sold_price'];
    			}else{
    				$remain_principal = $data['balance'];//-$data['paid_before'];
    				$data['sold_price']= $data['balance'];//-$data['paid_before'];
    			}
    		}
    		$next_payment = $data['first_payment'];
    		$from_date =  $data['release_date'];
    		
    		$curr_type = 2;
    		$term_types = 1;
    		
    		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==6){
    			$term_types=1;
    		}
    		$loop_payment = $data['period']*$term_types;
    		$borrow_term = $data['period']*$term_types;
    		$payment_method = $data["schedule_opt"];
    		
    		$payment_method = $data["schedule_opt"];
    		$j=0;
    		$index=0;
    		$pri_permonth=0;
    		$is_graceperiod=0;
    		
    		$str_next = '+1 month';
    		/*សម្រាប់បញ្ចូលថាប្រាក់ដើមប្រានរំលស់*/
    		$sql="SELECT COUNT(id) FROM ln_saleschedule WHERE status=1 AND sale_id= ".$data['loan_number'];
    		$start_id = $db->fetchOne($sql);
    		
    		$this->_name="ln_saleschedule";
    		if($data['principal_paid']>0){
	    		$datapayment = array(
    				'branch_id'=>$data['branch_id'],
    				'sale_id'=>$id,//good
    				'begining_balance'=> $data['sold_price'],//good
    				'begining_balance_after'=> $data['sold_price'],//good
    				'principal_permonth'=> $data['principal_paid'],//good
    				'principal_permonthafter'=>$data['principal_paid'],//good
    				'total_interest'=>0,//good
    				'total_interest_after'=>0,//good
    				'total_payment'=>$data['principal_paid'],//good
    				'total_payment_after'=>$data['principal_paid'],//good
    				'ending_balance'=>$data['principal_paid'],
    				'cum_interest'=>0,
    				'amount_day'=>0,
    				'is_completed'=>0,
    				'date_payment'=>$data['paid_pricipaldate'],
    				'percent'=>0,
    				'is_installment'=>0,
    				'no_installment'=>$start_id,
    				'status'=>0,
    				'collect_by'=>2,
					'note'=>$data['other_feenote'],
					'received_userid'=>$this->getUserId(),
	    		);
	    		$this->insert($datapayment);
	    		$start_id=$start_id+1;
    		}
    		if(!empty($data['other_fee'])){
    			$datapayment = array(
    					'branch_id'=>$data['branch_id'],
    					'sale_id'=>$id,//good
    					'begining_balance'=> $data['sold_price'],//good
    					'begining_balance_after'=> $data['sold_price'],//good
    					'principal_permonth'=> $data['principal_paid'],//good
    					'principal_permonthafter'=>$data['principal_paid'],//good
    					'total_interest'=>0,//good
    					'total_interest_after'=>0,//good
    					'total_payment'=>$data['other_fee'],//good
    					'total_payment_after'=>$data['other_fee'],//good
    					'ending_balance'=>$data['other_fee'],
    					'cum_interest'=>0,
    					'amount_day'=>0,
    					'is_completed'=>0,
    					'date_payment'=>$data['paid_pricipaldate'],
    					'percent'=>0,
    					'note'=>$data['other_feenote'],
    					'is_installment'=>0,
    					'no_installment'=>$start_id,
    					'status'=>0,
    					'collect_by'=>2,
    			);
    			$this->insert($datapayment);
    		}
    		$old_interestrate=0;
    		for($i=1;$i<=$loop_payment;$i++){
    			$grace_period = $data['grace_period'];
    			if($grace_period>0 AND $is_graceperiod==0){
    				$old_interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
    				$old_interest_paymonth = $this->round_up_currency($curr_type, $old_interest_paymonth);
    			
    				for($index=1;$index<=$grace_period;$index++){
    					$is_graceperiod=1;
    					if($index!=1){
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    					}
    			
    					$this->_name="ln_saleschedule";
    					$datapayment = array(
    							'branch_id'=>$data['branch_id'],
    							'sale_id'=>$id,//good
    							'begining_balance'=> $remain_principal,//good
    							'begining_balance_after'=> $remain_principal,//good
    							'principal_permonth'=> 0,//good
    							'principal_permonthafter'=>0,//good
    							'total_interest'=>$old_interest_paymonth,//good
    							'total_interest_after'=>$old_interest_paymonth,//good
    							'total_payment'=>$old_interest_paymonth,//good
    							'total_payment_after'=>$old_interest_paymonth,//good
    							'ending_balance'=>$remain_principal,
    							'cum_interest'=>0,
    							'amount_day'=>0,
    							'is_completed'=>0,
    							'date_payment'=>$next_payment,
    							'no_installment'=>$index+$start_id,
    					);
    					$this->insert($datapayment);
    					 
    					$from_date=$next_payment;
    					if($grace_period==$index){//to continue new date for installment
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    						$data['first_payment'] = $next_payment;
    					}
    				}
    				$old_interest_paymonth=0;
    			}
    			
    			if($payment_method==3){//pay by times//check date payment
    				if($i!=1){
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
    				if($i==$loop_payment){ //for end of record only
    					$pri_permonth = $remain_principal;
    				}
    			}elseif($payment_method==4){//រំលស់
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
    									'note'=>$data['remark'.$j],
    									'is_installment'=>1,
			    						'no_installment'=>$index+$key+$start_id,
			    					);
// 			    					if($i==$loop_payment){//for end of record only
// 			    						$datapayment['last_optiontype'] = $data['paid_receivehouse'];
// 			    					}
			    					
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
			    			$pri_permonth=$data['for_installamount']/($data['period']);
			    			$pri_permonth = round($pri_permonth,0);
			    		}else{
			    			$pri_permonth = $data['fixed_payment']-$interest_paymonth;
			    		}
			    		if($i==$loop_payment){//for end of record only
			    			$pri_permonth = $remain_principal;
			    			$paid_receivehouse = $data['paid_receivehouse'];
			    		}
    			   }
    			   elseif($payment_method==6 OR $payment_method==5){
    			   	$ids = explode(',', $data['identity']);
    			   	$key = 1;
    			   	foreach ($ids as $i){
    			   		if($key==1){
    			   			$old_remain_principal = $data['sold_price'];
    			   		}else{
    			   			$old_remain_principal = $old_remain_principal-$old_pri_permonth;
    			   		}
    			   		$old_pri_permonth = $data['total_payment'.$i];
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
    			   			'is_installment'=>1,
    			   			'no_installment'=>$key+$start_id,
    			   		);
    			   		$datapayment['ispay_bank']= $data['pay_with'.$i];
    			   		$sale_currid = $this->insert($datapayment);
    			   		$from_date = $data['date_payment'.$i];
    			   		$key = $key+1;
    			   	}
    			   	break;
    			   }elseif($payment_method==7){//បង់រំលស់ខ្លះ
    			   	//$last_payment = 0;
    			   	$data['sold_price'] = $data['balance']-$data['last_payment'];
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
	    			   		}else{
	    			   			$remain_principal = $data['sold_price'];
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
    			   if($payment_method==3 OR $payment_method==4 OR $payment_method==7){//កាលថេរ or រំលស់	
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
	    			        	'no_installment'=>$index+$i+$j+$start_id,
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
    		  		//'no_installment'=>$i+$j,
    		  		'no_installment'=>$index+$i+$j+$start_id+1,
    		  		'ispay_bank'=>$paid_receivehouse,
    		  	);
    		  	$this->insert($datapayment);
    		  }
    		
    		  $data['new_deposit'] = $data['deposit'];
    		  $data['deposit'] = $data['deposit']+$data['paid_before'];

    		  if(!empty($data['id'])){$dbtable->updateLateRecordSaleschedule($data['id']);}
    		  $db->commit();
	          return 1;
	        }catch (Exception $e){
            	$db->rollBack();
            	Application_Form_FrmMessage::message("INSERT_FAIL");
            	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	        }
    }
    function getSaleScheduleById($id,$payment_id){
    	$sql=" SELECT * FROM ln_saleschedule WHERE sale_id =$id  AND status=1 AND is_completed=0 ";
    	if($payment_id==4){
    		$sql.=" AND is_installment=1 ";
    	};
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql);
    }
   
    public function getNextDateById($pay_term){
    	if($pay_term==3){
    		$str_next = 'next month';
    	}elseif($pay_term==2){
    		$str_next = 'next week';
    	}else{
    		$str_next = 'next day';
    	}
    	return $str_next;
    }
    public function getSubDaysByPaymentTerm($pay_term){
    	if($pay_term==3){
    		$amount_days =30;
    	}elseif($pay_term==2){
    		$amount_days =7;
    	}else{
    		$amount_days =1;
    	}
    	return $amount_days;
    	
    }
    public function CountDayByDate($start,$end){
    	$db = new Application_Model_DbTable_DbGlobal();
    	return ($db->countDaysByDate($start,$end));
    }
    public function CalculateByMethod($method_type){
    	
    }
   
    public function getLoanInfoById($id){
    	$db=$this->getAdapter();
    	$sql=" SELECT
    	(SELECT SUM(total_principal_permonthpaid) FROM `ln_client_receipt_money` WHERE sale_id=$id AND status=1 LIMIT 1) AS total_principal,
    	s.* FROM `ln_sale` AS s WHERE s.id=$id AND status=1 AND s.is_completed=0 ";
    	return $db->fetchRow($sql);
    }
    public function getSaleInfoById($id){
    	$db=$this->getAdapter();
    	$sql=" SELECT
    	(SELECT SUM(total_principal_permonthpaid) FROM `ln_client_receipt_money` WHERE sale_id=$id AND status=1 LIMIT 1) AS total_principal,
    	s.* FROM `ln_sale` AS s WHERE s.id=$id AND status=1  ";
    	return $db->fetchRow($sql);
    }
    
    public function getClientByTypes($type){
    	$this->_name='ln_loan_member';
    	$sql ="SELECT
    	(SELECT c.client_number FROM `ln_client` AS c WHERE lm.client_id=c.client_id LIMIT 1 )AS client_number,
    	(SELECT c.name_en FROM `ln_client` AS c WHERE lm.client_id=c.client_id LIMIT 1 )AS name_en,
    	lm.client_id ,lm.loan_number
    	FROM `ln_loan_member` AS lm WHERE is_completed = 0 AND status=1 ";
    	$db = $this->getAdapter();
    	$rows = $db->fetchAll($sql);
    	$options=array(0=>'------Select------');
    	if(!empty($rows))foreach($rows AS $row){
    		if($type==1){
    			$lable = $row['client_number'];
    		}elseif($type==2){
    			$lable = $row['name_en'];
    		}
    		else{$lable = $row['loan_number'];
    		}
    		$options[$row['client_id']]=$lable;
    	}
    	return $options;
    }
     public function getAllMemberLoanById($member_id){//for get id fund detail for update
    	$db = $this->getAdapter();
    	$sql = "SELECT lm.member_id ,lm.client_id,lm.group_id ,lm.loan_number,
    	(SELECT name_kh FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_kh,
    	(SELECT name_en FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_en,
    	(SELECT client_number FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_number,
    	lm.total_capital,lm.admin_fee,lm.loan_purpose FROM `ln_loan_member` AS lm
    	WHERE lm.status =1 AND lm.group_id = $member_id ";
    	return $db->fetchAll($sql);
    }
    function  getRescheduleById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM `ln_reschedule` where id = $id";
    	return $db->fetchRow($sql);
    }
    public function addScheduleTestPayment($data){//used only new schedule because duraction convert to month
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
    				'balance'=>$data['balance'],
    				'end_line'=>$data['date_line'],
    				'interest_rate'=>$data['interest_rate'],
    				'total_duration'=>$data['period'],
    				'first_payment'=>$data['first_payment'],
    				'validate_date'=>$data['first_payment'],
    				'payment_method'=>1,//$data['loan_type'],
    				'note'=>$data['note'],
    				//'staff_id'=>$data['staff_id'],
    		        //'comission'=>$data['commission'],
    				//'other_fee'=>$data['other_fee'],
    				//'paid_amount'=>$data['deposit'],
    				//'buy_date'=>$data['date_buy'],
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
    		//$remain_principal = $data['balance'];
    		$data['sold_price']= $data['balance'];
    		$remain_principal = $data['sold_price'];
    		$next_payment = $data['first_payment'];
    		$from_date =  $data['release_date'];
    		$curr_type = 2;//$data['currency_type'];
    		$key = new Application_Model_DbTable_DbKeycode();
    		//$key = $key->getKeyCodeMiniInv(TRUE);
    		//$term_types=$key['install_by'];
    		$term_types=12;
    		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==6){
    			$term_types=1;
    		}
    		$loop_payment = $data['period']*1;
    		$borrow_term = $data['period']*1;
    		$payment_method = $data["schedule_opt"];
    		$data['grace_period'] =empty($data['grace_period'])?0:$data['grace_period'];
    
    		$str_next = '+1 month';
    		$is_graceperiod=0;
    		$old_interestrate=0;
    		for($i=1;$i<=$data['period'];$i++){
				$paid_receivehouse=1;
    			$grace_period = $data['grace_period'];
    			if($grace_period>0 AND $is_graceperiod==0){
    				$old_interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
    				$old_interest_paymonth = $this->round_up_currency($curr_type, $old_interest_paymonth);
    				$is_graceperiod=1;
    				for($index=1;$index<=$grace_period;$index++){
    					if($index!=1){
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    					}
    					$this->_name="ln_saleschedule_test";
    					$datapayment = array(
    							'branch_id'=>$data['branch_id'],
    							'sale_id'=>$id,//good
    							'begining_balance'=> $remain_principal,//good
    							'principal_permonth'=> 0,//good
    							'principal_permonthafter'=>0,//good
    							'total_interest'=>$old_interest_paymonth,//good
    							'total_interest_after'=>$old_interest_paymonth,//good
    							'total_payment'=>$old_interest_paymonth,//good
    							'total_payment_after'=>$old_interest_paymonth,//good
    							'ending_balance'=>$remain_principal,
    							'cum_interest'=>0,
    							'amount_day'=>0,
    							'is_completed'=>0,
    							'date_payment'=>$next_payment,
    					);
    					$this->insert($datapayment);
    						
    					$from_date=$next_payment;
    					if($grace_period==$index){
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
    						$data['first_payment'] = $next_payment;
    					}
    				}
    				$old_interest_paymonth=0;
    			}
    			
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
    			}elseif($payment_method==4){//រំលស់
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
    					if(!empty($data['identity'])){
    						$remain_principal = $data['sold_price']-$data['total_installamount'];
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
    				if($data['install_type']==2){
    					$pri_permonth=$data['for_installamount']/($data['period']);
    					$pri_permonth =$this->round_up_currency(2, $pri_permonth);
    				}else{
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
    						
						if(end($ids)==$i){
							$paid_receivehouse = $data['paid_receivehouse'];
						}
						
    					$this->_name="ln_saleschedule_test";
    					$datapayment = array(
    							'branch_id'=>$data['branch_id'],
    							'sale_id'=>$id,//good
    							'begining_balance'=> $old_remain_principal,//good
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
    							if($key==1){
    								$remain_principal=$data['sold_price'];
    								$old_pri_permonth = $data['total_payment'.$j];
    							}else{
    								$remain_principal = $remain_principal-$old_pri_permonth;
    								$old_pri_permonth = $data['total_payment'.$j];
    							}
								
								if(end($ids)==$j){
									$paid_receivehouse=$data['paid_receivehouse'];
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
    					}else{
    						$remain_principal = $data['sold_price'];
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
    		
    		if($payment_method==3 OR $payment_method==4 OR $payment_method==5 OR $payment_method==6 OR $payment_method==7){
    			$sql = " SELECT t.* , DATE_FORMAT(t.date_payment, '%d-%m-%Y') AS date_payments,
    			DATE_FORMAT(t.date_payment, '%Y-%m-%d') AS date_name,
				(SELECT name_kh FROM ln_view WHERE type =29 AND key_code = t.ispay_bank LIMIT 1) AS payment_type FROM
    			ln_saleschedule_test AS t WHERE t.sale_id = ".$id;
    			$rows = $db->fetchAll($sql);
    		}else{
    			$sql = " SELECT *,'row_id',(SELECT name_kh FROM ln_view WHERE type =29 AND key_code = ln_sale_test.ispay_bank LIMIT 1) AS payment_type FROM ln_sale_test WHERE id = ".$id;
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
    
}
  


