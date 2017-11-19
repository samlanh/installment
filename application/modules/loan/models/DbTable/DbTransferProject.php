<?php

class Loan_Model_DbTable_DbTransferProject extends Zend_Db_Table_Abstract
{

    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
   function getAllChangeProject($search){
   	$from_date =(empty($search['start_date']))? '1': " s.change_date >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " s.change_date <= '".$search['end_date']." 23:59:59'";
   	$where = " AND ".$from_date." AND ".$to_date;
   	$sql="SELECT cp.id,
   	(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.from_branchid LIMIT 1) AS from_branch,
	(SELECT sale_number FROM `ln_sale` WHERE id=cp.sale_id LIMIT 1) AS sale_number,
	c.name_kh,
	(SELECT CONCAT(land_address,street) FROM `ln_properties` WHERE ln_properties.id=cp.from_houseid LIMIT 1) from_property,
	cp.amount_before,cp.paid_before,
	(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.to_branchid LIMIT 1) AS to_branch,
	(SELECT CONCAT(land_address,street) FROM `ln_properties` WHERE ln_properties.id=cp.to_houseid LIMIT 1) to_propertype,
	cp.house_price,cp.paid_amount_after,cp.balance_after,cp.change_date,cp.status
	FROM `ln_change_project` AS cp,`ln_client` c WHERE c.client_id=cp.client_id ";
   	
   	$from_date =(empty($search['start_date']))? '1': " cp.change_date >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " cp.change_date <= '".$search['end_date']." 23:59:59'";
   	$where = " AND ".$from_date." AND ".$to_date;
   	if(!empty($search['adv_search'])){
   		$s_where = array();
//    		$s_search = addslashes(trim($search['adv_search']));
//    		$s_where[] = " cp.receipt_no LIKE '%{$s_search}%'";
//    		$s_where[] = " p.land_code LIKE '%{$s_search}%'";
//    		$s_where[] = " p.land_address LIKE '%{$s_search}%'";
//    		$s_where[] = " c.client_number LIKE '%{$s_search}%'";
//    		$s_where[] = " c.name_en LIKE '%{$s_search}%'";
//    		$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
//    		$s_where[] = " s.price_sold LIKE '%{$s_search}%'";
//    		$s_where[] = " s.comission LIKE '%{$s_search}%'";
//    		$s_where[] = " s.total_duration LIKE '%{$s_search}%'";
//    		$where .=' AND ( '.implode(' OR ',$s_where).')';
   	}
   	if($search['status']>-1){
   		$where.= " AND cp.status = ".$search['status'];
   	}
   	if(($search['client_name'])>0){
   		$where.= " AND `cp`.`client_id`=".$search['client_name'];
   	}
   	if(($search['branch_id'])>0){
   		$where.= " AND ( cp.from_branchid = ".$search['branch_id']." OR cp.to_branchid = ".$search['branch_id']." )";
   	}
   	
   	$order = " ORDER BY cp.id DESC";
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
    		return round($value,2);
    	}
    }
    public function addChangeProject($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{//need add schedule
    		$dbs = new Loan_Model_DbTable_DbLandpayment();
    		$id = $data['loan_number'];
    		$rows = $dbs->getTranLoanByIdWithBranch($id);
    		$arr = array(
    				'from_branchid'=>$data['branch_id'],
    				'from_houseid'=>$rows['house_id'],
    				'sale_id'=>$id,
    				'client_id'=>$data['member'],
    				'change_date'=>$data['date_buy'],
    				'payment_method_before'=>$rows['payment_method'],
    				'interestrate_before'=>$rows['interest_rate'],
    				'period_before'=>$rows['total_duration'],
    				'cal_startdate'=>$rows['startcal_date'],
    				'first_paymentbefore'=>$rows['first_payment'],
    				'end_datebefore'=>$rows['end_line'],
    				'amount_before'=>$data['total_sold'],
    				'paid_before'=>$data['paid_before'],
    				'balance_before'=>$data['balance_before'],
    				
    				'to_branchid'=>$data['to_branch_id'],
    				'to_houseid'=>$data['to_land_code'],
    				'house_price'=>$data['to_total_sold'],
    				'discount_after'=>$data['discount'],
    				'other_fee'=>$data['other_fee'],
    				'total_payment'=>$data['sold_price'],
    				
    				'paid_amount_after'=>$data['deposit'],
    				'balance_after'=>$data['balance'],
    				'period_after'=>$data['period'],
    				
    				'interest_after'=>$data['interest_rate'],
    				'start_date_after'=>$data['release_date'],
    				'first_payment_after'=>$data['first_payment'],
    				'end_date_after'=>$data['date_line'],
    				'noted'=>$data['note'],
    				'cheque'=>$data['cheque'],
    				'user_id'=>$this->getUserId()
    				);
	    		$this->_name="ln_change_project";
	    		$changeid = $this->insert($arr);
	    		
	    		$this->_name="ln_properties";
	    		$where=" id=".$rows['house_id'];
	    		$arr = array(
	    				'is_lock'=>0
	    				);
	    		$this->update($arr, $where);//unlock old house
	    		
	    		$where=" id=".$data['to_land_code'];
	    		$arr = array(
	    				'is_lock'=>1
	    		);
	    		$this->update($arr, $where);//lock new house
	    		if($data['branch_id']!=$data['to_branch_id']){//if transfer to other project
		    		$this->_name="ln_expense";
		    		$data = array(
		    				'branch_id'=>$data['branch_id'],
		    				'title'=>'Expense for change house to other project',//$data['title'],
    // 	    				'invoice'=>$data['invoice'],
	//						$data['category_id_expense'],expense exchange project
		    				'total_amount'=>$data['paid_before']+$data['deposit'],
		    				'category_id'=>3,
		    				'description'=>'Expense for transfer to other project',
		    				'date'=>$data['date_buy'],
		    				'status'=>1,
		    				'user_id'=>$this->getUserId(),
		    				'create_date'=>date('Y-m-d'),
		    		);
		    		$this->insert($data);
	    		}
	    		if($data['schedule_opt']==2){
	    			$is_complete = 1;
	    		}else{
	    			$is_complete = 0;
	    		}
	    		
	    		$sql=" SELECT SUM(total_principal_permonthpaid) AS total_permonth FROM `ln_client_receipt_money` WHERE sale_id =$id AND status=1 ";
	    		$paid_amount = $db->fetchOne($sql);
	    		$arr = array(
	    				'branch_id'=>$data['to_branch_id'],
	    				'house_id'=>$data["to_land_code"],
	    				'payment_id'=>$data["schedule_opt"],
	    				'price_before'=>$data['to_total_sold'],
	    				'discount_amount'=>$data['discount'],
	    				'discount_percent'=>$data['discount_percent'],
	    				'price_sold'=>$data['sold_price'],
// 	    				'other_fee'=>$data['other_fee'],
						//'buy_date'=>$data['date_buy'],
	    				//'end_line'=>$data['date_line'],
	    				//'sale_number'=>$loan_number,
	    				//'client_id'=>$data['member'],
	    				//$data['to_total_sold']-$paid_amount,
	    				'paid_amount'=>$paid_amount,
	    				'balance'=>$data['balance'],
	    				'is_reschedule'=>3,
	    				'interest_rate'=>$data['interest_rate'],
	    				'total_duration'=>$data['period'],
	    				'startcal_date'=>$data['release_date'],
	    				'first_payment'=>$data['first_payment'],
	    				'validate_date'=>$data['first_payment'],
	    				'payment_method'=>1,//$data['loan_type'],
	    				'note'=>$data['note'],
	    				'land_price'=>$data['house_price'],//true 
	    				'total_installamount'=>$data['total_installamount'],
	    				'agreement_date'=>$data['agreement_date'],
	    				'create_date'=>date("Y-m-d"),
	    				'user_id'=>$this->getUserId()
	    		);
	    		$data['sale_id']=$id;
	    		$where = " id = $id";
	    		$this->_name="ln_sale";
	    		$this->update($arr, $where);//add group loan
	    		
	    		$arr = array(
	    				'is_rescheule'=>1,
	    				);
	    		$where=" is_completed=1 AND sale_id = ".$data['sale_id'];
	    		$this->_name="ln_saleschedule";
	    		$this->update($arr, $where);//add group loan
	    		
	    		$where=" is_completed=0 AND sale_id = ".$data['sale_id'];
	    		$this->_name='ln_saleschedule';
	    		$this->delete($where);
	    		
	    		$sql=" SELECT count(id) FROM ln_saleschedule where is_completed=1 AND sale_id =".$data['sale_id'];
	    		$rs = $db->fetchOne($sql);
// 	    		if(!empty($rs)){
	    			$row_start = $rs;
	    			$dbtable = new Application_Model_DbTable_DbGlobal();
	    			$total_day=0;
	    			$old_remain_principal = 0;
	    			$old_pri_permonth = 0;
	    			$old_interest_paymonth = 0;
	    			$old_amount_day = 0;
	    			$cum_interest=0;
	    			$amount_collect = 1;
	    			$data['sold_price']=$data['balance'];
	    			$remain_principal = $data['sold_price'];
	    			$next_payment = $data['first_payment'];
	    			$from_date =  $data['release_date'];
	    			$curr_type = 2;//$data['currency_type'];
	    			
	    			$key = new Application_Model_DbTable_DbKeycode();
	    			$key=$key->getKeyCodeMiniInv(TRUE);
	    			$term_types = $key['install_by'];
	    			
	    			if($data["schedule_opt"]==3 OR $data["schedule_opt"]==6){
	    				$term_types=1;
	    			}
	    			$loop_payment = $data['period']*$term_types;
	    			$borrow_term = $data['period']*$term_types;
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
	    										'note'=>$data['remark'.$j],
	    										'is_installment'=>1,
	    										'no_installment'=>$key+$row_start,
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
	    					$pri_permonth = $data['fixed_payment']-$interest_paymonth;
	    					if($i==$loop_payment){//for end of record only
	    						$pri_permonth = $remain_principal;
	    					}
	    				}elseif($payment_method==6){
	    					$ids = explode(',', $data['identity']);
	    					$key = 1;
	    					foreach ($ids as $i){
	    						$old_pri_permonth = $data['total_payment'.$i];
	    						if($key==1){
	    							$old_remain_principal = $data['sold_price'];
	    								
	    						}else{
	    							$old_remain_principal = $old_remain_principal-$old_pri_permonth;
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
	    								'is_installment'=>1,
	    								'no_installment'=>$key+$row_start,
	    						);
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
	    							'no_installment'=>$i+$j+$row_start,
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
// 	    		}
	    		
	    			
    			$db->commit();
//     			$sql=" SELECT * FROM ln_saleschedule where is_completed=1 AND sale_id =".$data['sale_id'];
//     			print_r($db->fetchAll($sql));
//     			exit();
    			return 1;
    		}catch (Exception $e){
    			$db->rollBack();
    			$err =$e->getMessage();
    			echo $err;exit();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    }
    function getTransferProject($id){
    	$sql=" select * from ln_change_project where id= $id limit 1";
    	$db = $this->getAdapter();
    	return $db->fetchRow($sql);
    }

}
  


