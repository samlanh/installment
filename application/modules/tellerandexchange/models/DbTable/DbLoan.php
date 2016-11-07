<?php

class Loan_Model_DbTable_DbLoan extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_loan_group';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    public function addNewLoanGroup($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$datagroup = array(
    				'group_id'=>$data['group_id'],
    				'co_id'=>$data['co_id'],
    				'zone_id'=>$data['zone'],
    				'level'=>1,//$data[''],
    				'date_release'=>$data['release_date'],
    				'total_duration'=>$data['period'],
    				'first_payment'=>$data['first_payment'],
    				'time_collect'=>$data['time'],
    				'pay_term'=>$data['pay_every'],
    				'payment_method'=>$data['repayment_method'],
    				'holiday'=>$data['every_payamount'],
    				'is_renew'=>0,
    				);
    		$g_id = $this->insert($datagroup);//add group loan
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$tranlist = Zend_Json::decode($data['tran_list']);
    		foreach ($tranlist as $key => $row) {
    			$datamember = array(
    					'group_id'=>$g_id,
    					'client_id'=>$row['member_id'],
//     					'payment_method'=>$data['payment_method'],
    					'currency_type'=>$row['currency_type_id'],
    					'total_capital'=>$row['total_amount'],//$data[''],
    					'interest_rate'=>$data['interest_rate'],
    					'status'=>1,
    					'is_completed'=>0,
    					'branch_id'=>1
    			);
    			$this->_name='ln_loan_member';
    			$g_id = $this->insert($datamember);//add member loan
    			
    			$remain_principal = $row['total_amount'];
    			$next_month = $data['first_payment'];
    			$start_date = $data['release_date'];//loan release;
    			
    			$this->_name='ln_loanmember_funddetail';
    			for($i=1;$i<=$data['period'];$i++){
    				
    				$payment_method = $data['repayment_method'];
    				if($payment_method==1){
    					$pri_permonth = $row['total_amount']/$data['period'];
    					if($i!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					}
    					
    				}elseif($payment_method==2){
    					$pri_permonth=0;
    					if($i==$data['period']){
    						$pri_permonth = $row['total_amount'];
    						$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					}
    				}elseif($payment_method==3){
    					
    				}elseif($payment_method==4){
    					
    				}elseif($payment_method==5){
    					
    				}elseif($payment_method==6){
    					
    				}
    				
    				$day_perterm = $this->getSubDaysByPaymentTerm($data['pay_every']);//return amount day for payterm
    				if($i!=1){
//     					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$start_date = $next_month;
    					$d = new DateTime($next_month);
    					
    					$str_next = $this->getNextDateById($data['pay_every']);//for next,day,week,month;
    					
    					$d->modify($str_next);
    					$next_month_before =  $d->format( 'Y-m-d' );
    					$next_month = $dbtable->checkHolidayExist($str_next,$next_month_before);//copare if next month above not the same below
						
						$amount_day = $this->CountDayByDate($start_date,$next_month);
						$interest_paymonth = $remain_principal*($data['interest_rate']/100)*($amount_day/$day_perterm);
    					
    				}else{
    					$amount_day = $this->CountDayByDate($start_date,$next_month);
//     					return $amount_day;
    					$interest_paymonth = $row['total_amount']*($data['interest_rate']/100)*($amount_day/$day_perterm);
    					
    				}
    				
    				
    				
    				$datapayment = array(
    						'member_id'=>$g_id,
    						'total_principal'=>$remain_principal,
    						'principal_permonth'=> $pri_permonth,
    						'total_interest'=>$interest_paymonth,
    						'total_payment'=>$pri_permonth+$interest_paymonth,
    						'date_payment'=>$next_month,
    						'is_completed'=>0,
    						'branch_id'=>1,
    						'status'=>1,
    						'amount_day'=>$amount_day,
    				);
    				$this->insert($datapayment);
    			}
    		}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		return $e->getMessage();
    	}
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
  
}

