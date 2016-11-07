<?php

class Loan_Model_DbTable_DbLoanILtest extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_paymentschedule_test';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
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
    		return round($value,2);
    	}
    }
    public function addNewLoanILTest($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$sql=" TRUNCATE TABLE ln_paymentschedule_test ";
    		$db->query($sql);
    		$sql = "TRUNCATE TABLE ln_paymentschedule_detailtest";
    		$db->query($sql);
    	
    		$dbtable = new Application_Model_DbTable_DbGlobal();
//     		$loan_number = $dbtable->getLoanNumber($data);
    			   $schedule = array(
    					'client_id'=>$data['customer_code'],
    					'land_id'=>$data['land_code'],//$data['loan_code'],
    					'price'=>$data['land_price'],
    					'deposit'=>$data['deposit'],
    					'balance'=>$data['balance'],//$data[''],
//     					'old_balance'=>$data['old_balance'],//$data[''],
    					'payment_type'=>$data['schedule_opt'],
    					'amount_month'=>$data['period'],
    					'interest_rate'=>$data['interest_rate'],
    					'panelty'=>$data['pay_late'],
    					'date_register'=>date("Y-m-d"),
    					'date_buy'=>$data['release_date'],
    					'first_datepay'=>$data['first_payment'],
    					'end_date'=>$data['date_line'],
    					'staff_id'=>$data['co_id'],
    					'commission'=>$data['commission'],
    					'is_reschedule'=>0,
    					'is_completed'=>0,
    					'is_cancel'=>0
    			  );
    			    
    			$id = $this->insert($schedule);//add member loan
    			unset($schedule);
    			
    			$old_remain_principal = 0;
    			$old_pri_permonth = 0;
    			$old_interest_paymonth = 0;
    			$old_amount_day = 0;
    			$amount_collect = 1;
    			$interest_paymonth=0;
    			
    			$remain_principal = $data['balance'];
    			$next_payment = $data['first_payment'];
    			$start_date = $data['release_date'];
    			$from_date = $data['release_date'];
    			
	            $str_next = $dbtable->getNextDateById(3,1);//for next,day,week,month;
    			$percent = array(0=>10,1=>5,2=>5,3=>5,4=>5);
    			
    			$this->_name='ln_paymentschedule_detailtest';
	            if($data['schedule_opt']==1 AND $data['sold_price']>$data['deposit']){
	               for($index=0;$index<=4;$index++){
	               		if($index!=0){
	               			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	               			//$pri_permonth = $remain_principal*($percent[$index]/100);
	               			$pri_permonth = ($data['sold_price'])*($percent[$index]/100);
	               			$pri_permonth = round($pri_permonth,2);
		               		$start_date = $next_payment;
		               		$next_payment = $dbtable->getNextPayment($str_next, $next_payment,1,2,$data['first_payment']);
		               		$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	               		}else{
	               			//$pri_permonth = $remain_principal*($percent[$index]/100);
	               			$pri_permonth = ($data['sold_price'])*($percent[$index]/100)-$data["deposit"];
		               		$next_payment = $data['first_payment'];
		               		$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
		               		$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	               		}
	               	
	               	$old_remain_principal =$old_remain_principal+$remain_principal;
	               	$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	               	$old_interest_paymonth = round($old_interest_paymonth+$interest_paymonth);
	                $old_amount_day =$old_amount_day+ $amount_day;
	               	 
	               	$datapayment = array(
	               			'paymentid'=>$id,
	               			'outstanding'=>$remain_principal,//good
	               			'principal_permonth'=> $old_pri_permonth,//good
	               			'principal_after'=> $old_pri_permonth,//good
	               			'total_interest'=>$old_interest_paymonth,//good
	               			'total_interest_after'=>$old_interest_paymonth,//good
	               			'total_payment'=>$old_pri_permonth,//good
	               			'total_payment_after'=>$old_pri_permonth,//good
	               			'amount_day'=>$old_amount_day,//good
	               			'date_payment'=>$next_payment,
	               			'penelize'=>0,
	               			'penelize_after'=>0,
	               			'is_completed'=>0,
	               			'status'=>1
	               	);
	               	
	               	$this->insert($datapayment);

	               	$from_date=$next_payment;
	               	if($index!=0){
	               			$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, 1,2,$data['first_payment']);
	               	}
	               	
	               	$amount_collect=0;
	               	$old_remain_principal = 0;
	               	$old_pri_permonth = 0;
	               	$old_interest_paymonth = 0;
	               	$old_amount_day = 0;
	               }
	               
	               $remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	               $oldtotalprincipal = $remain_principal;
	               $pri_permonth = $remain_principal/$data['period'];
	               $pri_permonth = round($pri_permonth,2);
	               for($i=1;$i<=$data['period'];$i++){//remain of 5 months first payment
	               		if($i!=1){
	               			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	               			$start_date = $next_payment;
	               			$interest_paymonth =$oldtotalprincipal*$data['interest_rate']/100;//$remain_principal*$data['interest_rate']/100;
	               			$next_payment = $dbtable->getNextPayment($str_next, $next_payment,1,2,$data['first_payment']);
	               			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	               			if($i==$data['period']){//end of record
	               				$pri_permonth=$remain_principal;
	               			}
	               		}else{
	               			$interest_paymonth =$oldtotalprincipal*$data['interest_rate']/100;
	               			$next_payment = $data['first_payment'];
	               			$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
	               			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	               		}
	               		 
	               		$old_remain_principal =$old_remain_principal+$remain_principal;
	               		$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	               		$old_interest_paymonth = round($old_interest_paymonth+$interest_paymonth,2);
	               		$old_amount_day =$old_amount_day+ $amount_day;
	               	
	               		$datapayment = array(
	               				'paymentid'=>$id,
	               				'outstanding'=>$remain_principal,//good
	               				'principal_permonth'=> $old_pri_permonth,//good
	               				'principal_after'=> $old_pri_permonth,//good
	               				'total_interest'=>$old_interest_paymonth,//good
	               				'total_interest_after'=>$old_interest_paymonth,//good
	               				'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
	               				'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
	               				'amount_day'=>$old_amount_day,//good
	               				'date_payment'=>$next_payment,
	               				'penelize'=>0,
	               				'penelize_after'=>0,
	               				'is_completed'=>0,
	               				'status'=>1
	               		);
	               		 
	               		$this->insert($datapayment);
	               	
	               		$from_date=$next_payment;
	               		if($index!=0){
	               			$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, 1,2,$data['first_payment']);
	               		}
	               		 
	               		$amount_collect=0;
	               		$old_remain_principal = 0;
	               		$old_pri_permonth = 0;
	               		$old_interest_paymonth = 0;
	               		$old_amount_day = 0;
	               	
	               }
	            }elseif($data['schedule_opt']==2 AND $data['balance']>0){
	            	$percent1 = array(0=>30,1=>30,2=>40);
	            	$discount = $data['discount']/100;
	            	//$remain_principal = ($data['land_price']-($data['land_price']*$discount));//$data['balance'];
	            	for($index=0;$index<=2;$index++){
	            		if($index!=0){
	            			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	            			$pri_permonth = ($data['sold_price'])*($percent1[$index]/100);
	            			//$pri_permonth = ($mainbalance)*($percent1[$index]/100);
	            			$pri_permonth = round($pri_permonth,2);
	            			
	            			$start_date = $next_payment;
	            			$next_payment = $dbtable->getNextPayment($str_next, $next_payment,1,2,$data['first_payment']);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            			if($index==2){
	            				$remain_principal=$pri_permonth;
	            			}
	            		}else{
	            			$mainbalance = $remain_principal;
	            			$pri_permonth = ($data['sold_price'])*($percent1[$index]/100)-$data["deposit"];
	            			$pri_permonth = round($pri_permonth,2);
	            			$next_payment = $data['first_payment'];
	            			$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            		}
	            		$old_remain_principal =$old_remain_principal+$remain_principal;
	            		$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	            		$old_interest_paymonth = round($old_interest_paymonth+$interest_paymonth);
	            		$old_amount_day =$old_amount_day+ $amount_day;
	            	
	            		$datapayment = array(
	            				'paymentid'=>$id,
	            				'outstanding'=>$remain_principal,//good
	            				'principal_permonth'=> $old_pri_permonth,//good
	            				'principal_after'=> $old_pri_permonth,//good
	            				'total_interest'=>$old_interest_paymonth,//good
	            				'total_interest_after'=>$old_interest_paymonth,//good
	            				'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
	            				'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
	            				'amount_day'=>$old_amount_day,//good
	            				'date_payment'=>$next_payment,
	            				'penelize'=>0,
	            				'penelize_after'=>0,
	            				'is_completed'=>0,
	            				'status'=>1
	            		);
	            		 
	            		$this->insert($datapayment);
	            		$from_date=$next_payment;
	            		if($index!=0){
	            			$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, 1,2,$data['first_payment']);
	            		}
	            		 
	            		$amount_collect=0;
	            		$old_remain_principal = 0;
	            		$old_pri_permonth = 0;
	            		$old_interest_paymonth = 0;
	            		$old_amount_day = 0;
	            	}
	            	
	            	 }elseif($data['schedule_opt']==3 AND $data['balance']>0){
	            		$percent3 = array(0=>20,1=>20,2=>20,3=>20,4=>20);
	            	for($index=0;$index<=4;$index++){
	            		if($index!=0){
// 	            			$pri_permonth = ($data['land_price']-($data['land_price']*$discount))*($percent3[$index]/100);
	            			$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
	            			$pri_permonth = ($data['sold_price'])*($percent3[$index]/100);
	            			$pri_permonth = round($pri_permonth,2);
	            			$start_date = $next_payment;
	            			$next_payment = $dbtable->getNextPayment($str_next, $next_payment,1,2,$data['first_payment']);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            		}else{
	            			
	            			//$pri_permonth =($remain_principal)*($percent3[$index]/100);
	            			$pri_permonth = ($data['sold_price'])*($percent3[$index]/100)-$data["deposit"];
	            			
	            			$mainbalance = $remain_principal;
	            			$pri_permonth = round($pri_permonth,2);
	            			$next_payment = $data['first_payment'];
	            			$next_payment = $dbtable->checkFirstHoliday($next_payment,2);
	            			$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	            		}
	            		$old_remain_principal =$old_remain_principal+$remain_principal;
	            		$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	            		$old_interest_paymonth = round($old_interest_paymonth+$interest_paymonth);
	            		$old_amount_day =$old_amount_day+ $amount_day;
	            	
	            		$datapayment = array(
	            				'paymentid'=>$id,
	            				'outstanding'=>$remain_principal,//good
	            				'principal_permonth'=> $old_pri_permonth,//good
	            				'principal_after'=> $old_pri_permonth,//good
	            				'total_interest'=>$old_interest_paymonth,//good
	            				'total_interest_after'=>$old_interest_paymonth,//good
	            				'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
	            				'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,//good
	            				'amount_day'=>$old_amount_day,//good
	            				'date_payment'=>$next_payment,
	            				'penelize'=>0,
	            				'penelize_after'=>0,
	            				'is_completed'=>0,
	            				'status'=>1
	            		);
	            	
	            		$this->insert($datapayment);
	            		$from_date=$next_payment;
	            		if($index!=0){
	            			$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, 1,2,$data['first_payment']);
	            		}
	            	
	            		$amount_collect=0;
	            		$old_remain_principal = 0;
	            		$old_pri_permonth = 0;
	            		$old_interest_paymonth = 0;
	            		$old_amount_day = 0;
	            	}
	            }else{
	            	$interest_paymonth =($data['balance'])*$data['interest_rate']/100;//$remain_principal*$data['interest_rate']/100;
	            	if($data['balance']>0){//balance >0
	            		$datapayment = array(
	            				'paymentid'=>$id,
	            				'outstanding'=>$data['balance'],//good
	            				'principal_permonth'=> $data['balance'],//good
	            				'principal_after'=> $data['balance'],//good
	            				'total_interest'=> $interest_paymonth,//good
	            				'total_interest_after'=>$interest_paymonth,//good
	            				'total_payment'=>$data['balance']+$interest_paymonth,//good
	            				'total_payment_after'=>$data['balance']+$interest_paymonth,//good
	            				'amount_day'=>0,//good
	            				'date_payment'=>$next_payment,
	            				'penelize'=>0,
	            				'penelize_after'=>0,
	            				'is_completed'=>0,
	            				'status'=>1
	            		);
	            		$this->insert($datapayment);
	            	}
	            }

    		$sql = " SELECT f.* , DATE_FORMAT(f.date_payment, '%d-%m-%Y') AS date_payments,
    					DATE_FORMAT(f.date_payment, '%Y-%m-%d') AS date_name FROM 
    			     ln_paymentschedule_detailtest AS f WHERE paymentid = ".$id;
    		$rows =  $db->fetchAll($sql);
    		$db->commit();
    		return $rows;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
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
}