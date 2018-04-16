<?php

class Loan_Model_DbTable_DbNewSchedule extends Zend_Db_Table_Abstract
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
    	if(($search['schedule_opt'])>0){
    		$where.= " AND ( s.payment_method_before = ".$search['schedule_opt'];
    		$where.= " OR s.payment_method_after = ".$search['schedule_opt']." )";
    	}
    		
    	$order = " ORDER BY s.id DESC";
    	$db = $this->getAdapter();    
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function calCulateIRR($total_loan_amount,$loan_amount,$term,$curr){
    	$array =array();//array(-1000,107,103,103,103,103,103,103,103,103,103,103,103);
    	for($j=0; $j<= $term;$j++){
    		if($j==0){
    			$array[]=-$loan_amount;
    		}elseif($j==1){
    			$fixed_principal = round($total_loan_amount/$term,0, PHP_ROUND_HALF_DOWN);
    			$post_fiexed = $total_loan_amount/$term-$fixed_principal;
    			$total_add_first = $this->round_up_currency($curr,$post_fiexed*$term);
    
    			$array[]=($total_add_first+$fixed_principal);
    		}else{
    			$array[]=round($total_loan_amount/$term,0, PHP_ROUND_HALF_DOWN);
    		}
    
    	}
    	$array = array_values($array);
    	return Loan_Model_DbTable_DbIRRFunction::IRR($array);
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
    		return round($value,0);
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
    				//'other_fee'				=>$data['other_fee'],
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
    			   	//'create_date'=>date("Y-m-d"),
    				);
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
    		//$data['currency_type'];
//     		$key = new Application_Model_DbTable_DbKeycode();
//     		$key=$key->getKeyCodeMiniInv(TRUE);
    		$term_types = 1;
    		
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
	    		);
	    		$this->insert($datapayment);
	    		$start_id=$start_id+1;
    		}
    		if($data['other_fee']>0){
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
			    						'no_installment'=>$key+$start_id,
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
			    			$pri_permonth=$data['for_installamount']/($data['period']*12);
			    			$pri_permonth =$this->round_up_currency(2, $pri_permonth);
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
    			   		if($payment_method==5){//with bank
    			   			$datapayment['ispay_bank']= $data['pay_with'.$i];
    			   		}
    			   		$sale_currid = $this->insert($datapayment);
    			   		$from_date = $data['date_payment'.$i];
    			   			
    			   		$key = $key+1;
    			   	}
    			   	break;
    			   }
    			   if($payment_method==3 OR $payment_method==4){//កាលថេរ or រំលស់	
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
			    			        	'no_installment'=>$i+$j+$start_id,
	// 		    			        	'collect_by'=>1,
	// 		    			        	'payment_option'=>$cum_interest,
	// 		    			        	'penelize'=>,
	// 		    			        	'service_charge'=>,
	// 		    			        	'status'=>,
			    			        );
		            		 
			    		$idsaleid = $this->insert($datapayment);
	// 		    		$amount_collect=0;
			    		$old_remain_principal = 0;
			    		$old_pri_permonth = 0;
			    		$old_interest_paymonth = 0;
			    		$old_amount_day = 0;
			    		$from_date=$next_payment;
    			   }
    		  }
    		if($data['deposit']>0){//insert payment
    			
    		}else{//input information
    			//$data['sold_price']=$data['balance'];
    		}
    		  $data['new_deposit'] = $data['deposit'];
    		  $data['deposit'] = $data['deposit']+$data['paid_before'];
    		 
//     		  $data['sale_id']=$data['loan_number'];
//     		  if(($payment_method!=2)){//ខុសពីផ្តាច់
//     		  	$this->addPaymenttoSale($data);
//     		  }
    		  if(!empty($data['id'])){$dbtable->updateLateRecordSaleschedule($data['id']);}
    		  $db->commit();
	          return 1;
	        }catch (Exception $e){
	            	$db->rollBack();
	            	echo $e->getMessage();
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
    public function getLoanInfo($id){
    	$db=$this->getAdapter();
    $sql=" SELECT 
        (SELECT lf.total_principal FROM `ln_loanmember_funddetail` AS lf WHERE lf. member_id= l.member_id AND status=1 AND lf.is_completed=0 LIMIT 1)  AS total_principal
    	,l.currency_type ,l.interest_rate , l.loan_number,l.payment_method, l.group_id,
    	(SELECT co_id FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1 ) AS co_id , 
    	(SELECT zone_id FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1) AS zone_id,
    	(SELECT level FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1) AS level  
    	 FROM `ln_loan_member` AS l WHERE l.client_id=$id AND status=1 AND l.is_completed=0 ";
    	return $db->fetchRow($sql);
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
    public function updateRepaymentSchedule($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		if($data['status_using']==0){
    			$arr_update = array(
    					'status'=>0
    			);
    			$where = ' status = 1 AND g_id = '.$data['id'];
    			$this->update($arr_update, $where);
    			 
    			$this->_name = 'ln_loan_member';
    			$where = ' is_completed = 0 AND status = 1 AND group_id = '.$data['id'].' AND client_id = '.$data["member"];
    			$this->update($arr_update, $where);
    			 
    			 
    			$rows= $this->getAllMemberLoanById($data['id']);
    			$s_where = array();
    			$where = '';
    			foreach ($rows as $id => $row){
    				$s_where[] = "`member_id` = ".$row['member_id'];
    			}
    			$where .= implode(' OR ',$s_where);
    			$where.=" AND status=1 AND is_completed=0 ";
    			 
    			$arr = array(
    					'status'=>0
    			);
    			$this->_name='ln_loanmember_funddetail';
    			 
    			$db->commit();
    			return 1;
    		}
    	
    		$datagroup = array(
    				'group_id'=>$data['member'],
    				'co_id'=>$data['co_id'],
    				'zone_id'=>$data['zone'],
    				'level'=>$data['level'],
    				'date_release'=>$data['release_date'],
    				'date_line'=>$data['date_line'],
    				'create_date'=>date("Y-m-d"),
    				'branch_id'=>$data['branch_id'],
    				'total_duration'=>$data['period'],
    				'first_payment'=>$data['first_payment'],
    				'time_collect'=>$data['time'],
    				'pay_term'=>$data['pay_every'],
    				'payment_method'=>$data['repayment_method'],
    				'holiday'=>$data['every_payamount'],
    				'is_renew'=>0,
    				'loan_type'=>1,
    				'collect_typeterm'=>$data['collect_termtype'],
    				'user_id'=>$this->getUserId()
    		);
    		$g_id = $data['id'];
    		$where = $db->quoteInto('g_id=?', $g_id);
    		$this->update($datagroup, $where);
    		unset($datagroup);
    	
    		$datamember = array(
    				'group_id'=>$g_id,
//     				'loan_number'=>$data['loan_code'],
    				'client_id'=>$data['member'],
    				'payment_method'=>$data['repayment_method'],
    				'currency_type'=>$data['currency_type'],
    				'total_capital'=>$data['total_amount'],//$data[''],
    				'other_fee'=>$data['other_fee'],
    				'admin_fee'=>$data['loan_fee'],
    				'interest_rate'=>$data['interest_rate'],
    				'status'=>1,
    				'is_completed'=>0,
    				'branch_id'=>$data['branch_id'],
    				//'pay_before'=>$data['pay_before'],
    				'pay_after'=>$data['pay_late'],
    				'graice_period'=>$data['graice_pariod'],
    				'amount_collect_principal'=>$data['amount_collect'],
    				'collect_typeterm'=>$data['collect_termtype'],
    				'semi'=>$data['amount_collect_pricipal']
    		);
    		$this->_name='ln_loan_member';
    	
    		$where = $db->quoteInto('group_id=?', $data['id']);
    		$this->update($datamember, $where);
    		unset($datamember);
    	
    		$rows= $this->getAllMemberLoanById($data['id']);
    		$s_where = array();
    		$where = '';
    		foreach ($rows as $id => $row){
    			$s_where[] = "`member_id` = ".$row['member_id'];
    		}
    		$where .= implode(' OR ',$s_where);
    		$where.=" AND status=1 AND is_completed=0 ";
    	
    		$arr = array(
    				'status'=>0
    		);
    		$this->_name='ln_loanmember_funddetail';
    		$this->delete($where);
//     		$this->update($arr, $where);
    		 
    		//     		$arr =array(
    		//     				'branch_id'=>$data['branch_id'],
    		//     				'receipt_number'=>$data['loan_code'],
    		//     				'date'=>$data['release_date'],
    		//     				'create_date'=>date('Y-m-d'),
    		//     				'note'=>'from loan disburse',
    		//     				'from_location'=>1,
    		//     				'user_id'=>$this->getUserId(),
    		//     				'balance'=>$data['total_amount'],
    		//     				'loan_fee'=>$data['loan_fee'],
    		//     				'client_id'=>$data['member'],
    		//     				'currency_type'=>$data['currency_type']
    			
    		//     		);
    		//     		//     			$this->insert($arr);
    		//     		//     			unset($arr);
    		//     		$db_j = new Accounting_Model_DbTable_DbJournal();
    		//     		$jur_id = $db_j->addTransactionJournal($arr);
    		 
    		$remain_principal = $data['total_amount'];
    		$next_payment = $data['first_payment'];
    		$start_date = $data['release_date'];//loan release;
    		$from_date =  $data['release_date'];
    		 
    		$old_remain_principal = 0;
    		$old_pri_permonth = 0;
    		$old_interest_paymonth = 0;
    		$old_amount_day = 0;
    		$amount_collect = 1;
    		$ispay_principal=2;//for payment type = 5;
    		$is_subremain = 2;
    		$curr_type = $data['currency_type'];
    	
    		//for IRR method
    		if($data['repayment_method']==6){
	    		$term_install = $data['period']*12;
	    		$loan_amount = $data['total_amount'];
	    		$total_loan_amount = $loan_amount+($loan_amount*$data['interest_rate']/100*$term_install);
	    		$irr_interest = $this->calCulateIRR($total_loan_amount,$loan_amount,$term_install,$curr_type);
    		//end of IRR
    		}
    		 
    		$this->_name='ln_loanmember_funddetail';
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    		$amount_borrow_term = $borrow_term*$data['period']*12;//amount of borrow
    		 
    		$fund_term = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],null);//return amount day for payterm
    		$amount_fund_term = $fund_term*$data['amount_collect'];
    		 
    		$loop_payment = ($amount_borrow_term)/($amount_fund_term);
    		$payment_method = $data['repayment_method'];
    		//     			for($i=1;$i<=($data['period']/$data['amount_collect']);$i++){
    		for($i=1;$i<=$loop_payment;$i++){
    			$amount_collect = $data['amount_collect'];
    			$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],$amount_collect);//return amount day for payterm
    			 
    			//$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],$amount_collect);//return amount day for payterm
    			$str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    			 
    			if($payment_method==1){//decline//completed
    				//     					$pri_permonth = ($data['total_amount']/($data['period']-$data['graice_pariod'])*$amount_collect);
    				$pri_permonth = $data['total_amount']/(($amount_borrow_term-($data['graice_pariod']*$borrow_term))/$amount_fund_term);
    				$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    				if($i*$amount_collect<=$data['graice_pariod']){//check here//for graice period
    					$pri_permonth = 0;
    				}
    				if($i!=1){
    					if($data['graice_pariod']!=0){//if collect =1 not other check
    						if($i*$amount_collect>$data['graice_pariod']+$amount_collect){//not wright
    							$remain_principal = $remain_principal-$pri_permonth;
    						}else{
    	
    						}
    					}else{
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
    					}
    					if($i==$loop_payment){//check condition here//for end of record only
    						//     							echo $remain_principal;
    						$pri_permonth = $data['total_amount']-$pri_permonth*($i-(($data['graice_pariod']/$amount_collect)+1));//code error here
    							
    					}
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    	
    					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					//     						$interest_paymonth = $remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    	
    				}else{
    					$next_payment = $data['first_payment'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    				}
    			}elseif($payment_method==2){//baloon
    				$pri_permonth=0;
    				if(($i*$amount_fund_term)==$amount_borrow_term){//check here
    					$pri_permonth = ($curr_type==1)?round($data['total_amount'],-2):$data['total_amount'];
    					$pri_permonth =$this->round_up_currency($curr_type, $pri_permonth);
    					$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    				}
    				if($i!=1){
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				}else{
    					$next_payment = $data['first_payment'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				}
    				$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    				//     					$interest_paymonth = $data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    					
    			}elseif($payment_method==3){//fixed rate
    				$pri_permonth = ($data['total_amount']/($amount_borrow_term/$amount_fund_term));
    				$pri_permonth =$this->round_up_currency($curr_type,$pri_permonth);
    				if($i!=1){
    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					if($i==$loop_payment){//for end of record only
    						$pri_permonth = $remain_principal;
    					}
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    				}else{
    					$next_payment = $data['first_payment'];
    				}
    				$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    				
    				$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    				//     					    $interest_paymonth = $data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm);
    	
    			}elseif($payment_method==4){//fixed payment full last period yes
    				if($i!=1){
    					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					//     						$interest_paymonth = $data['total_amount']*($data['interest_rate']/100)*($amount_day/$day_perterm);
    						
    				}else{
    					$next_payment = $data['first_payment'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    				}
    				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    				$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    				$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    				$pri_permonth = $data['amount_collect_pricipal']-$interest_paymonth;
    				if($i==$loop_payment){//for end of record only
    					$pri_permonth = $remain_principal;
    				}
    			}elseif($payment_method==5){//semi baloon//ok
    				if($i!=1){
    					$ispay_principal++;
    					$is_subremain++;
    					$pri_permonth=0;
    					if(($is_subremain-1)==$data['amount_collect_pricipal']){
    						$pri_permonth = ($data['total_amount']/$data['period']*12)*$data['amount_collect_pricipal'];
    						$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    						$is_subremain=1;
    					}
    					if(($ispay_principal-1)==$data['amount_collect_pricipal']+1){
    						$remain_principal = $remain_principal-($data['total_amount']/$data['period']*12)*$data['amount_collect_pricipal'];
    						$ispay_principal=2;
    					}
    					if($i==$loop_payment){//check condition here//for end of record only
    						$pri_permonth = $remain_principal;
    					}
    					$start_date = $next_payment;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					//     							$interest_paymonth = ($remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
    				}else{
    					$pri_permonth = 0;//check if get pri first too much change;
    					$next_payment = $data['first_payment'];
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					//     						$interest_paymonth = ($data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
    				}
    			}else{//    fixed payment with fixed rate
    				if($i!=1){
    					$remain_principal = $remain_principal-$pri_permonth;
    					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
    					$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
    					$pri_permonth = $fixed_principal-$interest_paymonth;
    					if($i==$loop_payment){//for end of record only
    						$pri_permonth = $remain_principal;
    							
    						$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
    						$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
    						$interest_paymonth = $fixed_principal-$remain_principal;
    					}
    						
    				}else{
    					$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);//fixed
    					$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
    					$post_fiexed = $total_loan_amount/$term_install-$fixed_principal;
    					$total_payment_first = $this->round_up_currency($curr_type,$post_fiexed*$term_install);
    					$pri_permonth = $fixed_principal+$total_payment_first;
    					
    					$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						
    					$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					$interest_paymonth = $this->round_up_currency($curr_type,$loan_amount*($irr_interest));
    					$pri_permonth = ($fixed_principal+$total_payment_first)-$interest_paymonth;
    				}
    			}
    			$old_remain_principal =$old_remain_principal+$remain_principal;
    			$old_pri_permonth = $old_pri_permonth+$pri_permonth;
    			$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
    			$old_amount_day =$old_amount_day+ $amount_day;
    			 
    			 
    			if($data['amount_collect']==$amount_collect){
    					
    				$datapayment = array(
    						'member_id'=>$g_id,
    						'total_principal'=>$remain_principal,//good
    						'principal_permonth'=> $old_pri_permonth,//good
    						'principle_after'=> $old_pri_permonth,//good
    						'total_interest'=>$old_interest_paymonth,//good
    						'total_interest_after'=>$old_interest_paymonth,//good
    						'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    						'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    						'date_payment'=>$next_payment,//good
    						'is_completed'=>0,
    						'branch_id'=>$data['branch_id'],
    						'status'=>1,
    						'amount_day'=>$old_amount_day,
    						'collect_by'=>$data['co_id']
    				);
    	
    				$this->insert($datapayment);
    				$amount_collect=0;
    				$old_remain_principal = 0;
    				$old_pri_permonth = 0;
    				$old_interest_paymonth = 0;
    				$old_amount_day = 0;
    				
    				$from_date=$next_payment;
    				if($i!=1){
    					$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    				}
    			}else{
    	
    			}
    			$amount_collect++;
    		}
    		if(($amount_borrow_term)%($amount_fund_term)!=0){///end for record odd number only
    			$start_date = $next_payment;//$dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    			$next_payment = $dbtable->checkFirstHoliday($data['date_line'],$data['every_payamount']);
    			$amount_day = $amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    			if($payment_method==1){
    				$pri_permonth = $remain_principal-$pri_permonth; // $pri_permonth*($amount_day/$amount_fund_term);//check it if khmer currency
    				$interest_paymonth = $pri_permonth*($data['interest_rate']/100/$borrow_term)*$amount_day;
    				$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    			}elseif($payment_method==2){
    				$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    				$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    				$interest_paymonth = $pri_permonth*($data['interest_rate']/100/$borrow_term)*$amount_day;
    				$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    			}elseif($payment_method==3){
    				$pri_permonth = $remain_principal-$pri_permonth;
    				$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    				$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    			}elseif($payment_method==4){
    				$interest_paymonth = $data['total_amount']*($data['interest_rate']/100/$borrow_term)*$amount_day;
    				//     					$interest_paymonth = ($data['total_amount']*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    				$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				$pri_permonth = $remain_principal-$pri_permonth;
    			}elseif($payment_method==5){
    				$pri_permonth = $remain_principal;
    				$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    				$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    			}elseif($payment_method==6){
    				$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
    				$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
    				$pri_permonth = $remain_principal;
    			}
    			 
    			$datapayment = array(
    					'member_id'=>$g_id,
    					'total_principal'=>$pri_permonth,//good
    					'principal_permonth'=> $pri_permonth,//good
    					'principle_after'=> $pri_permonth,//good
    					'total_interest'=>$interest_paymonth,//good
    					'total_interest_after'=>$interest_paymonth,//good
    					'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    					'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
    					'date_payment'=>$next_payment,//good
    					'is_completed'=>0,
    					'branch_id'=>$data['branch_id'],
    					'status'=>1,
    					'amount_day'=>$old_amount_day,
    					'collect_by'=>$data['co_id']
    			);
    			$this->insert($datapayment);
    		}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
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
    
    public function getLaoForRepaymentSchedule($member_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT
			    	lf.`date_payment`,
			    	lf.`principle_after`,
			    	lf.`total_interest_after`,
			    	lf.`service_charge`,
			    	lf.`penelize`,
			    	lf.`total_payment_after`,
			    	(SELECT lg.`date_release` FROM `ln_loan_group` AS lg WHERE lg.`g_id`=l.`group_id`) AS release_date,
			    	(SELECT lc.`date_input` FROM `ln_client_receipt_money` AS lc,`ln_client_receipt_money_detail` AS lcd WHERE lc.`id`=lcd.`crm_id` AND lcd.`loan_number`=l.`loan_number` AND lcd.`lfd_id`=lf.`id` ORDER BY lc.`date_input` DESC LIMIT 1) AS last_pay_date,
			    	l.client_id,l.currency_type ,l.interest_rate , l.loan_number,l.payment_method, l.group_id,l.branch_id,l.`pay_after`,l.`collect_typeterm`,
			    	(SELECT co_id FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1 ) AS co_id ,
			    	(SELECT zone_id FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1) AS zone_id,
			    	(SELECT LEVEL FROM `ln_loan_group` WHERE g_id  = l.group_id LIMIT 1) AS LEVEL
			    FROM `ln_loan_member` AS l,`ln_loanmember_funddetail` AS lf WHERE lf.`member_id`=l.`member_id` AND lf.`is_completed`=0 AND l.member_id=$member_id AND l.`status`=1 AND l.is_completed=0";
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
    				//'other_fee'=>$data['other_fee'],
    				//'paid_amount'=>$data['deposit'],
    				'balance'=>$data['balance'],
    				//'buy_date'=>$data['date_buy'],
    				'end_line'=>$data['date_line'],
    				'interest_rate'=>$data['interest_rate'],
    				'total_duration'=>$data['period'],
    				'first_payment'=>$data['first_payment'],
    				'validate_date'=>$data['first_payment'],
    				'payment_method'=>1,//$data['loan_type'],
    				'note'=>$data['note'],
    				//     				'staff_id'=>$data['staff_id'],
    		//     				'comission'=>$data['commission'],
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
    
    		$str_next = '+1 month';
    		for($i=1;$i<=$data['period'];$i++){
    			if($payment_method==1){//booking
    
    			}elseif($payment_method==2){//payoff
    
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
    				if($data['install_type']==2){
    					$pri_permonth=$data['for_installamount']/($data['period']*12);
    					$pri_permonth =$this->round_up_currency(2, $pri_permonth);
    				}else{
    					$pri_permonth = $data['fixed_payment']-$interest_paymonth;
    				}
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
    
}
  


