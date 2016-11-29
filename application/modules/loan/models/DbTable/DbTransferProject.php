<?php

class Loan_Model_DbTable_DbTransferProject extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_loan_group';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
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
    		return round($value,2);
    	}
    }
    public function addChangeProject($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
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
    				'user_id'=>$this->getUserId()
    				);
	    		$this->_name="ln_change_project";
	    		$this->insert($arr);
	    		
	    		$this->_name="ln_properties";
	    		$where=" id=".$rows['house_id'];
	    		$arr = array(
	    				'is_lock'=>0
	    				);
	    		$this->update($arr, $where);//unlock old house
	    		
	    		$where=" id=".$data['land_code'];
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
	//$data['category_id_expense'],expense exchange project
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
	    		
	    		$this->_name='ln_client_receipt_money';
	    		$array = array(
	    				'branch_id'				=>$data['branch_id'],
	    				'client_id'				=>$data['member'],
	    				'receipt_no'			=>$data['receipt'],
	    				'date_pay'				=>$data['date_buy'],
	    				'land_id'				=>$data['loan_number'],
	    				'date_input'			=>date('Y-m-d'),
	    				'outstanding'			=>$data['sold_price'],
	    				'principal_amount'		=>$data['balance'],
	    				 
	    				'total_principal_permonth'		=>$data['deposit'],
	    				'total_principal_permonthpaid'	=>$data['deposit'],
	    				'total_interest_permonth'		=>0,
	    				'total_interest_permonthpaid'	=>0,
	    				'penalize_amount'				=>0,
	    				'penalize_amountpaid'			=>0,
	    				 
	    				'service_charge'				=>$data['other_fee'],
	    				'service_chargepaid'			=>$data['other_fee'],
	    				'total_payment'					=>$data['sold_price'],
	    				'amount_payment'				=>$data['deposit'],
	    				'recieve_amount'				=>$data['deposit'],
	    				'balance'						=>$data['balance'],
	    				'payment_option'				=>$data['schedule_opt'],
	    				 
	    				'is_completed'					=>$is_complete,
	    				'status'						=>1,
	    				'note'							=>$data['note'],
	    				'user_id'						=>$this->getUserId(),
	    		);
	    		$crm_id = $this->insert($array);
	    		
    			$db->commit();
    			return 1;
    		}catch (Exception $e){
    			$db->rollBack();
    			$err =$e->getMessage();
    			echo $err;exit();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    }

}
  


