<?php

class Loan_Model_DbTable_DbTransferProject extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_loan_group';
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
	c.client_number,c.name_kh,
	(SELECT land_address FROM `ln_properties` WHERE ln_properties.id=cp.from_houseid LIMIT 1) from_property,
	cp.house_price,
	(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.to_branchid LIMIT 1) AS to_branch,
	(SELECT land_address FROM `ln_properties` WHERE ln_properties.id=cp.to_houseid LIMIT 1) to_propertype,
	cp.amount_before,cp.paid_before,cp.balance_before,cp.change_date,cp.status
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
   	
   	$order = " ORDER BY s.id DESC";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql.$where);
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
	    		if($data['deposit']>0){
		    		$array = array(
		    				'group_id'=>$changeid,
		    				'branch_id'			=>$data['branch_id'],
		    				'client_id'			=>$data['member'],
		    				'receipt_no'		=>$data['receipt'],
		    				'date_pay'			=>$data['date_buy'],
		    				'land_id'			=>$data['loan_number'],
		    				'date_input'		=>date('Y-m-d'),
		    				'outstanding'		=>$data['sold_price'],
		    				'principal_amount'	=>$data['balance'],
		    				'total_principal_permonth'	=>$data['deposit'],
		    				'total_principal_permonthpaid'=>$data['deposit'],
		    				'total_interest_permonth'	=>0,
		    				'total_interest_permonthpaid'=>0,
		    				'penalize_amount'			=>0,
		    				'penalize_amountpaid'		=>0,	    				 
		    				'service_charge'	=>$data['other_fee'],
		    				'service_chargepaid'=>$data['other_fee'],
		    				'total_payment'		=>$data['sold_price'],
		    				'amount_payment'	=>$data['deposit'],
		    				'recieve_amount'	=>$data['deposit'],
		    				'balance'			=>$data['balance'],
		    				'payment_option'	=>($data['schedule_opt']==2)?4:1,
		    				'is_completed'		=>$is_complete,
		    				'status'			=>1,
		    				'note'				=>$data['note'],
		    				'user_id'			=>$this->getUserId(),
		    		);
		    		$this->_name='ln_client_receipt_money';
		    		$crm_id = $this->insert($array);
	    		
		    		$this->_name='ln_client_receipt_money_detail';
		    		$array = array(
		    				'crm_id'				=>$crm_id,
		    				'land_id'				=>$data['loan_number'],
		    				'date_payment'			=>$data['date_buy'],
		    				'capital'				=>$data['sold_price'],
		    				'remain_capital'		=>$data['balance'],
		    				'principal_permonth'	=>$data['deposit'],
		    				'total_interest'		=>0,
		    				'total_payment'			=>$data['sold_price'],
		    				'total_recieve'			=>$data['deposit'],
		    				'service_charge'		=>$data['other_fee'],
		    				'penelize_amount'		=>0,
		    				'is_completed'			=>$is_complete,
		    				'status'				=>1,
		    		);
		    		$this->insert($array);
	    		}
	    		
    			$db->commit();
    			return 1;
    		}catch (Exception $e){
    			$db->rollBack();
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    }
    function getTransferProject($id){
    	$sql=" select * from ln_change_project where id= $id limit 1";
    	$db = $this->getAdapter();
    	return $db->fetchRow($sql);
    }

}
  


