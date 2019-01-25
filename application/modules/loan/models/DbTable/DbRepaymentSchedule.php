<?php

class Loan_Model_DbTable_DbRepaymentSchedule extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_loan_group';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
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
	    (SELECT name_kh FROM `ln_view` WHERE key_code =s.`payment_method_before` AND type = 25 LIMIT 1) AS paymenttype,
  		`s`.`balane_before`    AS `balane_before`,
  		(SELECT name_kh FROM `ln_view` WHERE key_code =s.`payment_method_after` AND type = 25 LIMIT 1) AS paymenttype_after,
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
    	if($search['land_id']>0){
    		$where.=" AND s.`land_id` = ".$search['land_id'];
    	}
    	if(($search['client_name'])>0){
    		$where.= " AND `s`.`client_id`=".$search['client_name'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND s.branch_id = ".$search['branch_id'];
    	}
    	if(($search['schedule_opt'])>0){
    		$where.= " AND ( s.payment_method_before = ".$search['schedule_opt'];
    		$where.= " OR s.payment_method_after = ".$search['schedule_opt']." )";
    	}
    	$order = " ORDER BY s.id DESC";
    	$db = $this->getAdapter();    
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
    	if ($curr_id==1){
    		return $this->round_up($value, $places);
    	}
    	else{
    		return round($value,0);
    	}
    }
    
    function getSaleInfo($sale_id){
    	$db = $this->getAdapter();
    	$sql="select * from ln_sale where id = $sale_id LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
    
    public function addRepayMentSchedule($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$sale = $this->getSaleInfo($data['loan_number']);
    		$array = array(
    				'branch_id'				=>$data['branch_id'],
    				'sale_id'				=>$data['loan_number'],
    				'client_id'				=>$data['member'],
    				'reschedule_date'		=>$data['date_buy'],
    				'land_id'			    =>$data['land_code'],
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
    				'other_fee'				=>0,
    				'payment_method_after'	=>$data['schedule_opt'],
    				'paid_amount_after'		=>$data['deposit'],
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
    				'user_id'				=>$this->getUserId(),
    			);
    		$this->_name='ln_reschedule';
    		$id = $this->insert($array);
    		
    		$is_schedule=0;
    		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==4 OR $data["schedule_opt"]==6){//
    			$is_schedule=1;
    		}
    			if($data['old_paymentmethod']==1){
    				$this->_name='ln_sale';
    				$dbp = new Loan_Model_DbTable_DbLandpayment();
    				$row = $dbp->getTranLoanByIdWithBranch($data['id'],null);
    				
    				$key = new Application_Model_DbTable_DbKeycode();
    				$setting = $key->getKeyCodeMiniInv(TRUE);
    				$note_agreement = '';
    				if($setting['note_agreement']==1){
    					$note_agreement = $data['note_agreement'];
    				}
    				
    				$arr = array(
    						'paid_amount'=>$row['paid_amount']+$data['deposit'],
    						'balance'=>$data['sold_price']-($data['deposit']+$data['paid_before']),
    						'payment_id'=>$data["schedule_opt"],
    						'install_type'=>$data['install_type'],
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
    						'note_agreement'=>$note_agreement,
    						'user_id'=>$this->getUserId(),
    						'other_fee'=>0,
    						'agreement_date'=>$data['agreement_date'],
    						'build_start'=>$data['start_building'],
    						'amount_build'=>$data['amount_build'],
    						'is_reschedule'=>$is_schedule,
    						'is_completed'=>($data['schedule_opt']==2)?1:0,
    						'amount_daydelay'=>$data['delay_day']
    				);
    				$where= " id = ".$data['id'];
    				$this->update($arr, $where);
    			}
    			
    			if($data['schedule_opt']==2){
    				$is_complete = 1;
    			}else{
    				$is_complete = 0;
    			}
    			$dbtable = new Application_Model_DbTable_DbGlobal();
    			$receipt = $data['receipt'];
    			$sql="SELECT id FROM ln_client_receipt_money WHERE receipt_no='$receipt' ORDER BY id DESC LIMIT 1 ";
    			$acc_no = $db->fetchOne($sql);
    			if($acc_no){
    				$data['receipt'] = $dbtable->getReceiptByBranch(array("branch_id"=>$data["branch_id"]));
    			}
    			
    			if($data['schedule_opt']==2 AND $data['deposit']>0){//ករណីបង់ផ្តាច់
    				$this->_name='ln_client_receipt_money';
		    		$loan_number = $dbtable->getLoanNumber($data);
		    		$array = array(
		    				'field1'			=>$id,
		    				'field2'			=>2,
		    				'branch_id'			=>$data['branch_id'],
		    				'client_id'			=>$data['member'],
		    				'receipt_no'		=>$data['receipt'],
		    				'sale_id'			=>$data['loan_number'],
		    				'land_id'			=>$data['land_code'],
		    				'date_pay'			=>$data['paid_date'],
		    				'date_input'		=>$data['paid_date'],
		    				'allpaid_before'	=>$data['sold_price'],
		    				'selling_price'     =>$data['sold_price'],
		    				'outstanding'		=>$data['sold_price']-$data['paid_before'],
		    				'principal_amount'	=>$data['sold_price']-($data['paid_before']+$data['deposit']),
		    				'total_principal_permonth'	=>$data['sold_price']-($data['paid_before']),
		    				'total_principal_permonthpaid'=>($data['deposit']),
		    				'total_interest_permonth'	=>0,
		    				'total_interest_permonthpaid'=>0,
		    				'penalize_amount'	=>0,
		    				'penalize_amountpaid'=>0,
		    				'service_charge'	=>0,
		    				'service_chargepaid'=>0,
		    				'total_payment'		=>$data['deposit'],//$data['sold_price'],
		    				'amount_payment'	=>$data['deposit'],
		    				'recieve_amount'	=>$data['deposit'],
		    				'balance'			=>0,//
		    				'payment_option'	=>($data['schedule_opt']==2)?4:1,//4 payoff,1normal
		    				'is_completed'		=>($data['schedule_opt']==2)?1:0,
		    				'status'			=>1,
		    				'note'				=>$data['note'],
		    				'payment_method'	=>$data['payment_method'],
		    				'cheque'			=>$data['cheque'],
		    				'payment_times'		=>1,
		    				'is_payoff'			=>1,
		    				'user_id'			=>$this->getUserId(),
		    		);
		    		$crm_id = $this->insert($array);
		    		
		    		$this->_name="ln_saleschedule";
		    		$datapayment = array(
		    				'branch_id'=>$data['branch_id'],
		    				'sale_id'=>$data['loan_number'],//good
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
		    		);
		    		$recordid = $this->insert($datapayment);
		    		
		    		$this->_name='ln_client_receipt_money_detail';
		    		$array = array(
		    				'lfd_id'=>$recordid,
		    				'crm_id'				=>$crm_id,
		    				'land_id'				=>$data['loan_number'],
		    				'date_payment'			=>$data['date_buy'],
		    				'capital'				=>$data['sold_price']-$data['paid_before'],//ok
		    				'remain_capital'		=>0,
		    				'principal_permonth'	=>$data['deposit'],
		    				'total_interest'		=>0,
		    				'total_payment'			=>$data['deposit'],
		    				'total_recieve'			=>$data['deposit'],
		    				'service_charge'		=>0,
		    				'penelize_amount'		=>0,
		    				'is_completed'			=>($data['schedule_opt']==2)?1:0,
		    				'status'				=>1,
		    		);
		    		$this->insert($array);
		    		$db->commit();
		    		return 1;
    			}
    		
    			$arr = array(
    			'is_reschedule'=>1,
    			 'create_date'=>date("Y-m-d"),
    			);
    		$this->_name='ln_sale';
    	    $where = "id =".$data["loan_number"];
    		$id = $this->update($arr, $where);//add group loan
    		unset($datagroup);
    		
    		$where = " principal_permonth=principal_permonthafter AND is_completed=0 AND sale_id=".$data['loan_number'];
    		$this->_name="ln_saleschedule";
    		$this->delete($where);
    		
    		$id  = $data['loan_number'];
    		$total_day=0;
    		$old_remain_principal = 0;
    		$old_pri_permonth = 0;
    		$old_interest_paymonth = 0;
    		$old_amount_day = 0;
    		$cum_interest=0;
    		$amount_collect = 1;
    		$remain_principal = $data['balance'];
    		if($data["old_paymentmethod"]==1 AND $data['deposit']>0){
    			$remain_principal = $data['sold_price'];
    		}else{
    			if($data["schedule_opt"]==4 AND $data['deposit']==0){//check later if deposit> or =0
    				$data['sold_price']= $data['sold_price'];
    				$remain_principal = $data['sold_price'];
    			}else{
    				$remain_principal = $data['sold_price'];//-$data['paid_before'];
    				$data['sold_price']= $data['sold_price'];//-$data['paid_before'];
    			}
    		}
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
    		
    		$payment_method = $data["schedule_opt"];
    		$j=0;
    		$pri_permonth=0;
    		
    		$str_next = '+1 month';
    		for($i=1;$i<=$loop_payment;$i++){
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
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
    				}
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$total_day = $amount_day;
    				$interest_paymonth = 0;
    				$pri_permonth = round($data['sold_price']/$borrow_term,0);
    				if($i==$loop_payment){//for end of record only
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
    									'sale_id'=>$id,
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
			    						'percent_agree'=>$data['percent_agree'.$j],//
    									'note'=>$data['remark'.$j],
			    						'ispay_bank' => $data['pay_with'.$j],
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
			    		$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);//
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
    			   }
    			   elseif($payment_method==6 OR $payment_method==5){//តាមធនាគារ និងដំណាក់កាលមិនថេរ
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
    			   				'percent_agree'=>$data['percent_agree'.$i],
    			   				'is_installment'=>1,
    			   				'no_installment'=>$key,
    			   				'ispay_bank' => $data['pay_with'.$i],
    			   		);
    			   		
    			   		$sale_currid = $this->insert($datapayment);
    			   		$from_date = $data['date_payment'.$i];
    			   		$key = $key+1;
    			   	}
    			   	break;
    			   }
    			   if($payment_method==3 OR $payment_method==4){// រំលស់និងដំណាក់កាលថេរ	
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
			    			        );
		            		 
			    		$idsaleid = $this->insert($datapayment);
			    		$old_remain_principal = 0;
			    		$old_pri_permonth = 0;
			    		$old_interest_paymonth = 0;
			    		$old_amount_day = 0;
			    		$from_date=$next_payment;
    			   }
    		  }
    		  $data['new_deposit'] = $data['deposit'];
    		  $data['deposit'] = $data['deposit']+$data['paid_before'];
    		 
    		  $data['sale_id']=$data['loan_number'];
    		  $client_pay=0;
    		  if(($payment_method!=2)){//ខុសពីផ្តាច់
    		  	$client_pay = $this->addPaymenttoSale($data);
    		  }
    		  if(!empty($data['id'])){$dbtable->updateLateRecordSaleschedule($data['id']);}
    		  
    		  $dbpayment = new Loan_Model_DbTable_DbLoanILPayment();
    		  $rows = $dbpayment->getSaleScheduleById($data['id'], 1);
    		  if(empty($rows)){
    		  	$dbpayment->updatePayoff($data['loan_number'],$client_pay);
    		  }
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
    function countDepositTimes($sale_id){
    	$sql="SELECT COUNT(id) FROM ln_client_receipt_money WHERE sale_id = $sale_id AND field3=1 LIMIT 1";
    	$db = $this->getAdapter();
    	$rs = $db->fetchOne($sql);
    	return $rs+1;
    }
    function addPaymenttoSale($data){
    	$dbtable = new Application_Model_DbTable_DbGlobal();
    	$is_deposit='0';
    	$times = 1;
    	
    	if($data['schedule_opt']==1){
    		$is_deposit=1;
    		$times = $this->countDepositTimes($data['sale_id']);//កក់ច្រើនដង
    	}//បញ្ចាក់ថាប្រាក់កក
    	
    	$all_paidbefore = $dbtable->getAllPaidBefore($data['sale_id']);
    	
    	$array = array(
    			'branch_id'			=>$data['branch_id'],
    			'client_id'			=>$data['member'],
    			'receipt_no'		=>$data['receipt'],
    			'land_id'			=>$data['land_code'],
    			'sale_id'			=>$data['sale_id'],
    			'date_pay'			=>$data['paid_date'],
    			'date_input'		=>$data['paid_date'],
    			
    			'allpaid_before'	=>($data['deposit']-$data['paid_before'])+$all_paidbefore,
    			'selling_price'     =>$data['sold_price'],
    			
    			'outstanding'		=> $data['sold_price']-$data['paid_before'],//ok for id=3 បង់ថេ
    			'principal_amount'	=> $data['sold_price']-($data['deposit']),//ok for 3
    			'total_principal_permonth'	=>$data['deposit']-$data['paid_before'],//ok
    			'total_principal_permonthpaid'=>$data['deposit']-$data['paid_before'],
    			'total_interest_permonth'	=>0,
    			'total_interest_permonthpaid'=>0,
    			'penalize_amount'			=>0,
    			'penalize_amountpaid'		=>0,
    			'service_charge'	=>0,
    			'service_chargepaid'=>0,
    			'total_payment'		=>$data['sold_price'],
    			'amount_payment'	=>$data['deposit']-$data['paid_before'],
    			'recieve_amount'	=>$data['deposit']-$data['paid_before'],
    			'balance'			=>0,//$data['balance'],
    			'payment_option'	=>($data['schedule_opt']==2)?4:1,//4 payoff,1normal//បង់១ធានាគារមិនទាន់គិត
    			'is_completed'		=>($data['schedule_opt']==2)?1:0,
    			'status'			=>1,
    			'note'				=>$data['note'],
    			'user_id'			=>$this->getUserId(),
    			'field3'			=>$is_deposit,
    			'payment_method'	=>$data['payment_method'],
    			'cheque'=>$data['cheque'],
    			'payment_times'=>($data['schedule_opt']==1)?($times):1,
    	);
    	$crm_id=0;
    	if($data['new_deposit']>0){
	    	$this->_name='ln_client_receipt_money';
	    	$crm_id = $this->insert($array);
    	}
    	$rows = $this->getSaleScheduleById($data['sale_id'],1);
		$after_interest=0;
    	$paid_amount = $data['deposit'];
    	$remain_principal=0;
    	$interest_paid=0;
    	$total_interest_paid=0;
    	$principal_paid = 0;
    	$total_principal_paid=0;
    	if(!empty($rows)){
    		foreach ($rows as $key =>$row){
    			$statuscomplete=0;
    			$paid_amount = round($paid_amount-$row['principal_permonthafter'],2);
    			if($paid_amount>=0){
    				$remain_principal=0;
    				$statuscomplete=1;
    				$principal_paid = $row['principal_permonthafter'];
    			}else{
    				$remain_principal = abs($paid_amount);
    				$statuscomplete=0;
    				$principal_paid = $remain_principal;
    			}
    			if($remain_principal==0){
    				$statuscomplete=1;
    			}
    			$arra = array(
    					'begining_balance_after'=>$row['begining_balance_after']-($principal_paid),
    					"principal_permonthafter"=>$remain_principal,
    					'total_interest_after'=>$after_interest,
    					"total_payment_after"=>$remain_principal+$after_interest,
    					'is_completed'=>$statuscomplete,
    					'received_userid'=>($statuscomplete==1)?$this->getUserId():'',
    					'received_date'=>($statuscomplete==1)?$data['paid_date']:'',
    			);
    			$where = " id = ".$row['id'];
    			$this->_name="ln_saleschedule";
    			$this->update($arra, $where);
    			
    			$this->_name='ln_client_receipt_money_detail';
    			$array = array(
    					'crm_id'				=>$crm_id,
    					'lfd_id'				=>$row['id'],
    					'client_id'				=>$data['member'],
    					'land_id'				=>$data['land_code'],
    					'date_payment'			=>$row['date_payment'],
    					'paid_date'         	=>$data['paid_date'],
    					'capital'				=>$row['begining_balance'],
    					'remain_capital'		=>$row['begining_balance']-$principal_paid,
    					'principal_permonth'	=>$data['deposit'],
    					'last_pay_date'			=>$data['paid_date'],
    					'total_interest'		=>0,
    					'total_payment'			=>$principal_paid,
    					'total_recieve'			=>$principal_paid,
    					'service_charge'		=>0,
    					'penelize_amount'		=>0,
    					'is_completed'			=>$statuscomplete,
    					'status'				=>1,
    			);
    			if($data['new_deposit']>0){
    				$this->insert($array);
    			}
    			if($paid_amount<=0){
    				break;
    			}
    		}
    		
    	}else{
    		if($data["schedule_opt"]==1 OR $data["schedule_opt"]==5){
    			$this->_name='ln_client_receipt_money_detail';
    			$array = array(
    				'crm_id'				=>$crm_id,
    				'client_id'				=>$data['member'],
    				'land_id'				=>$data['land_code'],
    				'date_payment'			=>$data['paid_date'],
    				'paid_date'         	=>$data['paid_date'],
    				'capital'				=>$data['sold_price'],
    				'remain_capital'		=>$data['balance'],
    				'principal_permonth'	=>$data['deposit'],
    				'total_interest'		=>0,
    				'total_payment'			=>$data['sold_price'],
    				'total_recieve'			=>$data['deposit'],
    				'service_charge'		=>0,
    				'penelize_amount'		=>0,
    				'is_completed'			=>($data['schedule_opt']==2)?1:0,
    				'status'				=>1,
    		      );
	    		if($data['new_deposit']>0){
	    			$this->insert($array);
	    		}
    	   }
    	}
    	return $crm_id;
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
    public function getLoanInfoById($id){
    	$db=$this->getAdapter();
    	$sql=" SELECT
    	(SELECT SUM(total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` WHERE sale_id=$id AND status=1 LIMIT 1) AS total_principal,
    	(SELECT (total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` WHERE sale_id=$id AND status=1 ORDER BY date_input DESC,id DESC  LIMIT 1) AS extra_payment,
    	(SELECT date_input FROM `ln_client_receipt_money` WHERE sale_id=$id AND status=1 ORDER BY date_input DESC LIMIT 1) AS date_input,
    	(SELECT date_payment FROM `ln_saleschedule` WHERE is_completed = 0 AND status=1 AND sale_id=$id ORDER BY date_payment ASC LIMIT 1 ) AS date_payment,
    	(SELECT date_payment FROM `ln_saleschedule` WHERE is_completed = 1 AND status=1 AND sale_id=$id ORDER BY date_payment DESC LIMIT 1 ) AS startdate_calcualte,
    	(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=$id AND STATUS=1 AND is_completed=0 LIMIT 1) as intallment,
    	s.* 
    		FROM `ln_sale` AS s WHERE s.id=$id AND status=1 AND s.is_completed=0 ";
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
    
}
  