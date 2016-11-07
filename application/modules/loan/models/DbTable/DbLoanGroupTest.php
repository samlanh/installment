<?php

class Loan_Model_DbTable_DbLoanGroupTest extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_test_loan_group';
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
    	if ($curr_id==1){
    		return $this->round_up($value, $places);
    	}
    	else{
    		return round($value,2);
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
    public function addNewLoanGroupTest($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		
    		$sql=" TRUNCATE TABLE ln_test_loan_group ";
    		$db->query($sql);
    		$sql = "TRUNCATE TABLE ln_test_loan_member";
    		$db->query($sql);
    		$sql="TRUNCATE TABLE ln_test_loanmember_funddetail";
    		$db->query($sql);
    		
    		$datagroup = array(
    				'group_id'=>$data['group_id'],
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
    				'loan_type'=>2,
    				'collect_typeterm'=>$data['collect_termtype'],
    				'for_loantype'=>$data['loan_type'],
    				'user_id'=>$this->getUserId()
    		);
    		
    		$g_id = $this->insert($datagroup);//add group loan
    		unset($datagroup);
    		
    		$curr_type = $data['currency_type'];
    		$tranlist = explode(',',$data['record_row']);
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		foreach ($tranlist as $i) {
    			
//     			$loan_number = $dbtable->getLoanNumber();//get loan number by location
    			$datamember = array(
    					'group_id'=>$g_id,
		    			'loan_number'=>$data['loan_codes'],
		    			'client_id'=>$data['member_id'.$i],
		    			'payment_method'=>$data['repayment_method'],
		    			'currency_type'=>$data['currency_type'],
		    			'admin_fee'=>$data['admin_fee'.$i],
		    			'other_fee'=>$data['other_fee'],
		    			'total_capital'=>$data['debt_amount'.$i],//$data[''],
		    			'interest_rate'=>$data['interest_rate'],
		    			'is_completed'=>0,
		    			'branch_id'=>$data['branch_id'],
		    			'pay_after'=>$data['pay_late'],
		    			'graice_period'=>$data['graice_pariod'],
		    			'amount_collect_principal'=>$data['amount_collect'],
		    			'collect_typeterm'=>$data['collect_termtype'],
		    			'loan_purpose'=>$data['note'.$i],
		    			'semi'=>$data['fixed_payment'.$i]
    			);
    			
    			
    			$this->_name='ln_test_loan_member';
    			$member_id = $this->insert($datamember);//add member loan
    			unset($datamember);
    			
    			$remain_principal = $data['debt_amount'.$i];
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
    				$term_install = $data['period'];
    				$loan_amount = $data['debt_amount'.$i];
    				$total_loan_amount = $loan_amount+($loan_amount*$data['interest_rate']/100*$term_install);
    				$irr_interest = $this->calCulateIRR($total_loan_amount,$loan_amount,$term_install,$curr_type);
    			}
    			//end IRR method
    			
    			$this->_name='ln_test_loanmember_funddetail';
    			
    			$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    			$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
    			
    			$fund_term = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],null);//return amount day for payterm
    			$amount_fund_term = $fund_term*$data['amount_collect'];
    			
    			$loop_payment = ($amount_borrow_term)/($amount_fund_term);
    			$payment_method = $data['repayment_method'];
    			$str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    			for($j=1;$j<=$loop_payment;$j++){
    				//return amount next day collection
    				$amount_collect = $data['amount_collect'];
    				$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],$amount_collect);//return amount day for payterm
    				
    				//$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],$amount_collect);//return amount day for payterm
    				
    				if($payment_method==1){//decline//completed
    					$pri_permonth = $data['debt_amount'.$i]/(($amount_borrow_term-($data['graice_pariod']*$borrow_term))/$amount_fund_term);
    					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    					
    					if($j*$amount_collect<=$data['graice_pariod']){//check here//for graice period
    						$pri_permonth = 0;
    					}
    					if($j!=1){
    						if($data['graice_pariod']!=0){//if collect =1 not other check
    							if($j*$amount_collect>$data['graice_pariod']+$amount_collect){//not wright
    								$remain_principal = $remain_principal-$pri_permonth;
    							}else{
    								
    							}
    						}else{
    							$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
    						}
    						if($j==$loop_payment){//check condition here//for end of record only
    							$pri_permonth = $data['debt_amount'.$i]-$pri_permonth*($j-(($data['graice_pariod']/$amount_collect)+1));//code error here
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					}
    				}elseif($payment_method==2){//baloon
    					$pri_permonth=0;
    					if(($j*$amount_fund_term)==$amount_borrow_term){//end record//if j == loop
    						$pri_permonth = ($curr_type==1)?round($data['debt_amount'.$i],-2):$data['debt_amount'.$i];
    						$pri_permonth =$this->round_up_currency($curr_type, $pri_permonth);
    						$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					
    					}
    					if($j!=1){
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					
    					}else{
    						$next_payment = $data['first_payment'];//$dbtable->getNextPayment($str_next, $start_date, $data['amount_collect'],$data['every_payamount']);
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					}
    					$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					//$interest_paymonth = $data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    				}elseif($payment_method==3){//fixed rate
    					$pri_permonth = ($data['debt_amount'.$i]/($amount_borrow_term/$amount_fund_term));
    					$pri_permonth =$this->round_up_currency($curr_type,$pri_permonth);
    					
    					if($j!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						if($j==$loop_payment){//for end of record only
    							$pri_permonth = $remain_principal;
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					}
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100/$borrow_term)*$amount_day;
    						
    				}elseif($payment_method==4){//fixed payment full last period yes
    					if($j!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						
    						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100)*($amount_day/$day_perterm);
    							
    					}else{
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    					}
    					$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    					$interest_paymonth = $remain_principal*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    						
    					$pri_permonth = $data['fixed_payment'.$i]-$interest_paymonth;//don't understand
    					if($j==$loop_payment){//for end of record only
    						$pri_permonth = $remain_principal;
    					}
    				}elseif($payment_method==5){//semi baloon//ok
    					if($j!=1){
    						$ispay_principal++;
    						$is_subremain++;
    						$pri_permonth=0;
								if(($is_subremain-1)==$data['amount_collect_pricipal']){
    								$pri_permonth = ($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    								$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    								$is_subremain=1;
    							}
    							if(($ispay_principal-1)==$data['amount_collect_pricipal']+1){
    								$remain_principal = $remain_principal-($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    								$ispay_principal=2;
    							}
    							if($j==$loop_payment){//check condition here//for end of record only
    								$pri_permonth = $remain_principal;
    							}
    							
    							$start_date = $next_payment;
    							$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    							$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    							$interest_paymonth = $remain_principal*($data['interest_rate']/100)*($amount_day/$day_perterm);
    					}else{
    						$pri_permonth = 0;//check if get pri first too much change;
    						$next_payment = $data['first_payment'];
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					}
    				}else{//IRR
    					if($j!=1){
//     						$start_date = $next_payment;
//     						$remain_principal = $remain_principal-$pri_permonth;
//     						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
//     						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     						$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
//     						$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
//     						$pri_permonth = $fixed_principal-$interest_paymonth;
    						
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
    						$remain_principal = $remain_principal-$pri_permonth;
    						$interest_paymonth = $this->round_up_currency($curr_type,$remain_principal*$irr_interest);
    						$fixed_principal = intval($total_loan_amount/$term_install);
    						$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
    						$pri_permonth = $fixed_principal-$interest_paymonth;
    						
    						if($j==$loop_payment){//for end of record only
//     							$pri_permonth = $remain_principal;
//     							$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);
//     							$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
//     							$interest_paymonth = $fixed_principal-$remain_principal;
    							
    							$pri_permonth = $remain_principal;
    							$fixed_principal = intval($total_loan_amount/$term_install);
    							$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
    							$interest_paymonth = $fixed_principal-$remain_principal;
    						}
    							
    					}else{
//     						$fixed_principal = round($total_loan_amount/$term_install,0, PHP_ROUND_HALF_DOWN);//fixed
//     						$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
//     						$post_fiexed = $total_loan_amount/$term_install-$fixed_principal;
//     						$total_payment_first = $this->round_up_currency($curr_type,$post_fiexed*$term_install);
//     						$pri_permonth = $fixed_principal+$total_payment_first;
//     						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
//     						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
//     						$interest_paymonth = $this->round_up_currency($curr_type,$loan_amount*($irr_interest));
//     						$pri_permonth = ($fixed_principal+$total_payment_first)-$interest_paymonth;
    						
    						$fixed_principal = intval($total_loan_amount/$term_install);//fixed 'ex: 100.70=>100
    						$fixed_principal= $this->round_up_currency($curr_type,$fixed_principal);
    						$post_fiexed = $total_loan_amount/$term_install-$fixed_principal;
    						$total_payment_first = $this->round_up_currency($curr_type,$post_fiexed*$term_install);
    						$pri_permonth = $fixed_principal+$total_payment_first;
    						$next_payment = $dbtable->checkFirstHoliday($next_payment,$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
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
    							'member_id'=>$member_id,
    							'total_principal'=>$remain_principal,//good
    							'principal_permonth'=> $old_pri_permonth,//good
//     							'principle_after'=> $pri_permonth,//good
    							'total_interest'=>$old_interest_paymonth,//good
    							'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
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
    					if($j!=1){
    						$next_payment = $dbtable->checkDefaultDate($str_next, $start_date, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					}
    					
    				}else{
    				}
    				$amount_collect++;
    			}
    		if(($amount_borrow_term)%($amount_fund_term)!=0){///end for record odd number only
	    				$start_date = $next_payment;//$dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
	    				$next_payment = $dbtable->checkFirstHoliday($data['date_line'],$data['every_payamount']);
	    				$amount_day = $amount_day = $dbtable->CountDayByDate($start_date,$data['date_line']);
    				if($payment_method==1){
    					$pri_permonth = $remain_principal-$pri_permonth; // $pri_permonth*($amount_day/$amount_fund_term);//check it if khmer currency
    					$interest_paymonth = $pri_permonth*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				}elseif($payment_method==2){
    					$pri_permonth = $this->round_up_currency($curr_type, $data['debt_amount'.$i]);
    					$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				}elseif($payment_method==3){
    					$pri_permonth = $remain_principal-$pri_permonth;
    					$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100/$borrow_term)*$amount_day;
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				}elseif($payment_method==4){
    					$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100/$borrow_term)*$amount_day;
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
    						'member_id'=>$member_id,
    						'total_principal'=>$pri_permonth,//good
    						'principal_permonth'=> $pri_permonth,//good
    						'principle_after'=> $pri_permonth,//good
    						'total_interest'=>$interest_paymonth,//good
    						'total_payment_after'=>$interest_paymonth,//good
    						'total_payment'=>$interest_paymonth+$pri_permonth,//good
    						'date_payment'=>$next_payment,//good
    						'is_completed'=>0,
    						'branch_id'=>$data['branch_id'],
    						'status'=>1,
    						'amount_day'=>$amount_day,
    						'branch_id'=>$data['branch_id'],
    						'collect_by'=>$data['co_id']
    				);
    				$this->insert($datapayment);
    			}
    		}
    		$sql = " SELECT f.id,f.member_id,SUM(f.total_principal) AS total_principal ,SUM(f.principal_permonth) AS principal_permonth,
						SUM(f.total_interest)AS total_interest,
						SUM(f.total_payment)AS total_payment ,f.amount_day,f.status,
						DATE_FORMAT(f.date_payment, '%d-%m-%Y') AS date_payments,
						DATE_FORMAT(f.date_payment, '%Y-%m-%d') AS date_name 
						FROM `ln_test_loanmember_funddetail` AS f,`ln_test_loan_member` AS m
						WHERE m.member_id=f.member_id AND m.group_id= $g_id
						GROUP BY m.group_id,f.date_payment ";
    		 $rows =  $db->fetchAll($sql);
    		 $db->commit();
    		 return $rows;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("INSERT_FAIL");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function upDateLoanDisburse($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$datagroup = array(
    				'group_id'=>$data['group_id'],
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
    				'loan_type'=>2,
    				'collect_typeterm'=>$data['collect_termtype']
    		);
    		
    		
    		$g_id = $data['id'];//add group loan
    		$where = $db->quoteInto('g_id=?', $g_id);
    		$this->update($datagroup, $where);
    		unset($datagroup);
    		
    		$this->_name='ln_loan_member';//update member 
    		$arr =array('status'=>0);
    		$where = $db->quoteInto('group_id=?', $g_id);
    		$this->update($arr, $where);
    		
    		$rows= $this->getAllMemberLoanById($g_id);
    		$s_where = array();
    		$where = '';
    		foreach ($rows as $id => $row){
					$s_where[] = "`member_id` = ".$row['member_id'];
    		}
    		$where .= implode(' OR ',$s_where);
    		
    		$this->_name='ln_loanmember_funddetail';//update schedule detail of member 
    		$this->update($arr, $where);
    		
    		$this->_name='ln_loan_member';//update member
    		$next_payment = $data['first_payment'];
    		$start_date = $data['release_date'];//loan release;
    		$curr_type = $data['currency_type'];
    		
    		$tranlist = explode(',',$data['record_row']);
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		foreach ($tranlist as $i) {
    			$this->_name='ln_loan_member';//update member
    			$loan_number = $dbtable->getLoanNumber($data);
    			$datamember = array(
    					'group_id'=>$g_id,
    					'loan_number'=>$loan_number,//can have problem
    					'client_id'=>$data['member_id'.$i],
    					'payment_method'=>$data['repayment_method'],
    					'currency_type'=>$data['currency_type'],
    					'admin_fee'=>$data['admin_fee'.$i],
    					'total_capital'=>$data['debt_amount'.$i],//$data[''],
    					'interest_rate'=>$data['interest_rate'],
    					'branch_id'=>$data['branch_id'],
    					'pay_before'=>$data['pay_before'],
    					'pay_after'=>$data['pay_late'],
    					'graice_period'=>$data['graice_pariod'],
    					'amount_collect_principal'=>$data['amount_collect'],
    					'collect_typeterm'=>$data['collect_termtype'],
    					'loan_purpose'=>$data['note'.$i],
    					'semi'=>$data['amount_collect_pricipal']
    			);
    			 
    			$member_id = $this->insert($datamember);//add member loan
    			unset($datamember);
    			$old_remain_principal = 0;
    			$old_pri_permonth = 0;
    			$old_interest_paymonth = 0;
    			$old_amount_day = 0;
    			$amount_collect = 1;
    			$ispay_principal=2;//for payment type = 5;
    			$is_subremain = 2;
    			$remain_principal = $data['debt_amount'.$i];
    			 
    			 
    			$this->_name='ln_loanmember_funddetail';
    			 
    			$borrow_term = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],null);//return amount day for payterm
    			$amount_borrow_term = $borrow_term*$data['period'];//amount of borrow
    			 
    			$fund_term = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],null);//return amount day for payterm
    			$amount_fund_term = $fund_term*$data['amount_collect'];
    			 
    			$loop_payment = ($amount_borrow_term)/($amount_fund_term);
    			$payment_method = $data['repayment_method'];
    			
    			$this->_name='ln_loanmember_funddetail';
    			for($j=1;$j<=$loop_payment;$j++){
    				//return amount next day collection
    				$amount_collect = $data['amount_collect'];
    				$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['collect_termtype'],$amount_collect);//return amount day for payterm
    
    				//$day_perterm = $dbtable->getSubDaysByPaymentTerm($data['pay_every'],$amount_collect);//return amount day for payterm
    				$str_next = $dbtable->getNextDateById($data['collect_termtype'],$data['amount_collect']);//for next,day,week,month;
    
    				if($payment_method==1){//decline//completed
    					$pri_permonth = $data['debt_amount'.$i]/(($amount_borrow_term-($data['graice_pariod']*$borrow_term))/$amount_fund_term);
    					$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    					
    					
    					if($j*$amount_collect<=$data['graice_pariod']){//check here//for graice period
    						$pri_permonth = 0;
    					}
    					if($j!=1){
    						if($data['graice_pariod']!=0){//if collect =1 not other check
    							if($j*$amount_collect>$data['graice_pariod']+$amount_collect){//not wright
    								$remain_principal = $remain_principal-$pri_permonth;
    							}else{
    
    							}
    						}else{
    							$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា}
    						}
    						if($j==$loop_payment){//check condition here//for end of record only
    							$pri_permonth = $data['debt_amount'.$i]-$pri_permonth*($j-(($data['graice_pariod']/$amount_collect)+1));//code error here
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = $remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    							
    					}else{
    						$next_payment = $data['first_payment'];
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = ($data['debt_amount'.$i])*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    					}
    				}elseif($payment_method==2){//baloon
    					$pri_permonth=0;
    					if(($j*$amount_fund_term)==$amount_borrow_term){//end record//if j == loop
    						$pri_permonth = $data['debt_amount'.$i];
    						$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    							
    					}
    					if($j!=1){
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						//     						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100)*($amount_day/$day_perterm);
    					}else{
    						$next_payment = $data['first_payment'];//$dbtable->getNextPayment($str_next, $start_date, $data['amount_collect'],$data['every_payamount']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					}
    					$interest_paymonth = $data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    				}elseif($payment_method==3){//fixed rate
    					$pri_permonth = ($data['debt_amount'.$i]/($amount_borrow_term/$amount_fund_term));
    					$pri_permonth =$this->round_up_currency($curr_type,$pri_permonth);
    						
    					if($j!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						if($i==$loop_payment){//for end of record only
    							$pri_permonth = $remain_principal;
    						}
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    					}else{
    						$next_payment = $data['first_payment'];
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					}
    					$interest_paymonth = $data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm);
    
    				}elseif($payment_method==4){//fixed payment full last period yes
    					if($j!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    
    						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100)*($amount_day/$day_perterm);
    							
    					}else{
    						$next_payment = $data['first_payment'];
    					}
    					$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					$interest_paymonth = $remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm);
    					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
    						
    					$pri_permonth = $data['amount_collect_pricipal']-$interest_paymonth;//don't understand
    					if($j==$loop_payment){//for end of record only
    						$pri_permonth = $remain_principal;
    					}
    				}elseif($payment_method==5){//semi baloon//ok
    					if($j!=1){
    						$ispay_principal++;
    						$is_subremain++;
//     						$pri_permonth = ($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
//     						$pri_permonth = ($curr_type==1)?round($pri_permonth,-2):$pri_permonth;
    						$pri_permonth=0;
    						if(($is_subremain-1)==$data['amount_collect_pricipal']){
    							$pri_permonth = ($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    							$pri_permonth = $this->round_up_currency($curr_type, $pri_permonth);
    							$is_subremain=1;
    						}
    						if(($ispay_principal-1)==$data['amount_collect_pricipal']+1){
    							$remain_principal = $remain_principal-($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
    							$ispay_principal=2;
    						}
    						if($i==$loop_payment){//check condition here//for end of record only
    							$pri_permonth = $remain_principal;
    						}
//     						if($j==($data['period']/$data['amount_collect'])){//check condition here//for end of record only
//     							$pri_permonth = ($data['debt_amount'.$i]/$data['period'])*$data['amount_collect_pricipal'];
//     							$pri_permonth = $data['debt_amount'.$i]-$pri_permonth*($j-(($data['graice_pariod']/$amount_collect)+1));//code error here
//     						}
    							
    						$start_date = $next_payment;
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = $remain_principal*($data['interest_rate']/100)*($amount_day/$day_perterm);
    							
    					}else{
    						$pri_permonth = 0;//check if get pri first too much change;
    						$next_payment = $data['first_payment'];
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    						$interest_paymonth = $data['debt_amount'.$i]*($data['interest_rate']/100)*($amount_day/$day_perterm);
    					}
    				}else{//    fixed payment with fixed rate
    					$pri_permonth = $data['debt_amount'.$i]/$data['period']*$amount_collect;
    					$pri_permonth =$this->round_up_currency($curr_type, $pri_permonth);
    					if($j!=1){
    						$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    						$start_date = $next_payment;
    						if($j==$loop_payment){//check condition here//for end of record only
    							$pri_permonth = $remain_principal;
    						}
    						$next_payment = $dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount'],$data['first_payment']);
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					}else{
    						$next_payment = $data['first_payment'];
    						$amount_day = $dbtable->CountDayByDate($start_date,$next_payment);
    					}
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)/$data['period']*($day_perterm/$day_perterm));
    				}
    				$old_remain_principal =$old_remain_principal+$remain_principal;
    				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
    
    				$old_interest_paymonth = $old_interest_paymonth+$interest_paymonth;
    				$old_amount_day =$old_amount_day+ $amount_day;
    
    				if($data['amount_collect']==$amount_collect){
    						
    					$datapayment = array(
    							'member_id'=>$member_id,
    							'total_principal'=>$remain_principal,//good
    							'principal_permonth'=> $old_pri_permonth,//good
    							'principle_after'=> $old_pri_permonth,//good
    							'total_interest'=>$old_interest_paymonth,//good
    							'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
    							'date_payment'=>$next_payment,//good
    							'is_completed'=>0,
    							'branch_id'=>1,
    							'status'=>1,
    							'amount_day'=>$old_amount_day,
    					);
    					$this->insert($datapayment);
    					
    					$amount_collect=0;
    					$old_remain_principal = 0;
    					$old_pri_permonth = 0;
    					$old_interest_paymonth = 0;
    					$old_amount_day = 0;
    						
    				}else{
    						
    				}
    				$amount_collect++;
    			}
    			if(($amount_borrow_term)%($amount_fund_term)!=0){///end for record odd number only
    				$start_date = $next_payment;//$dbtable->getNextPayment($str_next, $next_payment, $data['amount_collect'],$data['every_payamount']);
    				$amount_day = $amount_day = $dbtable->CountDayByDate($start_date,$data['date_line']);
    			if($payment_method==1){
    					
    					$pri_permonth = $remain_principal-$pri_permonth; // $pri_permonth*($amount_day/$amount_fund_term);//check it if khmer currency
    					$interest_paymonth = ($pri_permonth*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    					
    				}elseif($payment_method==2){
    					
    					$pri_permonth = $this->round_up_currency($curr_type, $data['debt_amount'.$i]);
    					$remain_principal = $pri_permonth;//$remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				
    				}elseif($payment_method==3){

    					$pri_permonth = $remain_principal-$pri_permonth;
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($day_perterm/$day_perterm));
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    						
    				}elseif($payment_method==4){
    					
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    					$pri_permonth = $remain_principal-$pri_permonth;
    					
    				}elseif($payment_method==5){
    					
    					$pri_permonth = $remain_principal;
    					$interest_paymonth = ($remain_principal*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    					
    				}elseif($payment_method==6){
    					
    					$pri_permonth = $pri_permonth*($amount_day/$amount_fund_term);//if odd number
    					$interest_paymonth = ($data['debt_amount'.$i]*((($amount_fund_term*$data['interest_rate'])/$borrow_term)/100)*($amount_day/$day_perterm));
    					$interest_paymonth = $this->round_up_currency($curr_type,$interest_paymonth);
    				}
    
    				$datapayment = array(
    						'member_id'=>$member_id,
    						'total_principal'=>$pri_permonth,//good
    						'principal_permonth'=> $pri_permonth,//good
    						'principle_after'=>$pri_permonth,
    						'total_interest'=>$interest_paymonth,//good
    						'total_payment'=>$interest_paymonth+$pri_permonth,//good
    						'date_payment'=>$data['date_line'],//good
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
    public function getAllMemberLoanById($member_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT lm.member_id ,lm.client_id,lm.group_id ,lm.loan_number,
    	         (SELECT name_kh FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_kh,
    	         (SELECT name_en FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_en,
    	         (SELECT client_number FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_number,
    			lm.total_capital,lm.admin_fee,lm.loan_purpose FROM `ln_loan_member` AS lm
    			WHERE lm.status =1 AND lm.group_id = $member_id ";
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
function getLoanPaymentByLoanNumber($data){
    	$db = $this->getAdapter();
    	$loan_number= $data['loan_number'];
    	if($data['type']!=1){
    		$where =($data['type']==2)?'client_number = '.$loan_number:'client_id='.$loan_number;
    		$sql ="SELECT 
    				  (SELECT lc.`client_id` FROM `ln_client` AS lc WHERE lc.`parent_id`=lc.`client_id`) AS group_id,
    				  (SELECT lc.`client_number` FROM `ln_client` AS lc WHERE lc.`parent_id`=lc.`client_id`) AS group_number,
					  lc.`client_id`,
					  lc.`client_number`,
					  lc.`name_kh`,
					  lm.`loan_number`,
					  lm.`currency_type`,
					  lm.`branch_id`,
					  lg.`co_id`,
					  lg.`payment_method`,   
					  lf.*
					FROM
					  `ln_client` AS lc,
					  `ln_loan_member` AS lm ,
					  `ln_loan_group` AS lg,
					  `ln_loanmember_funddetail` AS lf
					WHERE lc.`is_group` = 1 
					AND lc.`parent_id`=(SELECT client_id FROM `ln_client` WHERE $where LIMIT 1)
					  AND lg.`g_id`=lm.`group_id`
					  AND lf.`member_id`=lm.`member_id`
					  AND lm.`client_id`=lc.`client_id`
					  AND lg.`group_id`=lc.`parent_id`
    					AND lf.`is_completed`=0
  						GROUP BY lc.`client_id`
    				";
    	}elseif($data['type']==1){
    		$where = 'lm.`loan_number`='.$loan_number;
    		$sql ="SELECT 
					  (SELECT lc.`client_id` FROM `ln_client` AS lc WHERE lc.`parent_id`=lc.`client_id`) AS group_id,
					  (SELECT lc.`client_number` FROM `ln_client` AS lc WHERE lc.`parent_id`=lc.`client_id`) AS group_number,
					  lc.`client_id`,
					  lc.`client_number`,
					  lc.`name_kh`,
					  lm.`loan_number`,
					  lm.`currency_type`,
					  lm.`branch_id`,
					  lg.`co_id`,
					  lg.`payment_method`,   
					  lf.*
					FROM
					  `ln_client` AS lc,
					  `ln_loan_member` AS lm ,
					  `ln_loan_group` AS lg,
					  `ln_loanmember_funddetail` AS lf
					WHERE lc.`is_group` = 1 
					AND lc.`parent_id`=(SELECT client_id FROM `ln_client` LIMIT 1)
					  AND lg.`g_id`=lm.`group_id`
					  AND lf.`member_id`=lm.`member_id`
					  AND lm.`client_id`=lc.`client_id`
					  AND lg.`group_id`=lc.`parent_id`
					  AND $where
    				AND lf.`is_completed`=0
  					GROUP BY lc.`client_id`";
    				
 	}
    	return $db->fetchAll($sql);
   }
    public function addGroupPayment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		
    		$db->commit();
    	}catch (Exception $e){
    		
    	}
    }
    function getAllMemberByGroup($group_member){
    	$db = $this->getAdapter();
    	$sql = "SELECT client_id,name_en FROM `ln_client` WHERE 
    	(parent_id = $group_member OR client_id = $group_member) AND status=1 ";
    	return $db->fetchAll($sql);
    }
  
}

