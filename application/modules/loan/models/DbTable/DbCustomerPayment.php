<?php

class Loan_Model_DbTable_DbCustomerPayment extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_client_receipt_money';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    	 
    }
    public function getAllIndividuleLoan($search){
		$start_date = $search['start_date'];
    	$end_date = $search['end_date'];
    	//(SELECT land_address FROM `ln_properties` WHERE id=lcrm.land_id limit 1) AS land_id,
    	//(SELECT street FROM `ln_properties` WHERE id=lcrm.land_id limit 1) AS street,
    	$db = $this->getAdapter();
    	$sql = "SELECT lcrm.`id`,
    	            (SELECT project_name FROM `ln_project` WHERE br_id=lcrm.branch_id LIMIT 1) AS branch_name,
					(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=lcrm.`client_id` limit 1) AS team_group ,
					lcrm.`receipt_no`,
					lcrm.`total_principal_permonth`,
					lcrm.`total_interest_permonth`,
					lcrm.`penalize_amount`,
					lcrm.service_charge,
					lcrm.`total_payment`,
					lcrm.`recieve_amount`,
				    (SELECT lcrmd.`date_payment` from ln_client_receipt_money_detail AS lcrmd WHERE lcrm.id=lcrmd.`crm_id` ORDER BY lcrmd.id DESC LIMIT 1 ) AS date_payment,
					lcrm.`date_input`,
					(SELECT name_en FROM `ln_view` WHERE type=3 and key_code = lcrm.status),
					'បោះពុម្ភ'
				FROM `ln_client_receipt_money` AS lcrm WHERE field3=3 ";
    	$where ='';
    	if(!empty($search['advance_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    		//$s_where[] = "lcrmd.`loan_number` LIKE '%{$s_search}%'";
    		$s_where[] = " lcrm.`receipt_no` LIKE '%{$s_search}%'";
    		
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']!=""){
    		$where.= " AND status = ".$search['status'];
    	}
    	
    	if(!empty($search['start_date']) or !empty($search['end_date'])){
    		$where.=" AND lcrm.`date_input` BETWEEN '$start_date' AND '$end_date'";
    	}
    	if($search['client_name']>0){
    		$where.=" AND lcrm.`client_id`= ".$search['client_name'];
    	}
    	if($search['paymnet_type']>0){
    		$where.=" AND lcrm.`payment_option`= ".$search['paymnet_type'];
    	}
    	if($search['land_id']>0){
    		$where.=" AND lcrm.`land_id`= ".$search['land_id'];
    	}
    	
    	$order = " ORDER BY id DESC";
//     	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getAllQuickIndividuleLoan($search){
    	$start_date = $search['start_date'];
    	$end_date = $search['end_date'];
    	 
    	$db = $this->getAdapter();
    	$sql = "SELECT lcrm.`id`,
    				(SELECT b.`branch_namekh` FROM `ln_branch` AS b WHERE b.`br_id`=lcrm.`branch_id`) AS branch,
    				
					lcrm.`receipt_no`,
					lcrm.`total_principal_permonth`,
					lcrm.`total_payment`,
					lcrm.`recieve_amount`,
					lcrm.`total_interest`,
					lcrm.`penalize_amount`,
					lcrm.`date_input`
				FROM `ln_client_receipt_money` AS lcrm WHERE lcrm.is_group=2";
    	$where ='';
    	if(!empty($search['advance_search'])){
    		//print_r($search);
    		$s_where = array();
    		$s_search = $search['advance_search'];
    		$s_where[] = "lcrm.`loan_number` LIKE '%{$s_search}%'";
    		$s_where[] = " lcrm.`receipt_no` LIKE '%{$s_search}%'";
    
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']!=""){
    		$where.= " AND status = ".$search['status'];
    	}
    	 
    	if(!empty($search['start_date']) or !empty($search['end_date'])){
    		$where.=" AND lcrm.`date_input` BETWEEN '$start_date' AND '$end_date'";
    	}
    	if($search['client_name']>0){
    		$where.=" AND lcrm.`group_id`= ".$search['client_name'];
    	}
    	if($search['branch_id']>0){
    		$where.=" AND lcrm.`branch_id`= ".$search['branch_id'];
    	}
    	if($search['co_id']>0){
    		$where.=" AND lcrm.`co_id`= ".$search['co_id'];
    	}
    	if($search['paymnet_type']>0){
    		$where.=" AND lcrm.`payment_option`= ".$search['paymnet_type'];
    	}
    	 
    	//$where='';
    	$order = " ORDER BY receipt_no DESC";
    	//echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    
	function getIlPaymentByID($id){
		$db = $this->getAdapter();
		$sql="SELECT 
				  *,
				  rm.id AS paymentid,
				  rm.status as status_parent,
				  rm.total_payment as total_payment_parent,
				  rm.service_charge as service_charge_parent,
				  rm.penalize_amount as penalize_amount_parent,
				  rm.total_interest_permonth as total_interest_permonth_parent
				FROM
				  `ln_client_receipt_money` AS rm ,
				  `ln_client_receipt_money_detail` AS rmd
				WHERE rm.id = $id
				AND rm.id=rmd.`crm_id`";
		//echo $sql;
		return $db->fetchRow($sql);
	}
	public function getIlDetail($id){
		$db = $this->getAdapter();
// 		$sql="SELECT 
// 					(SELECT d.`date_payment` FROM `ln_client_receipt_money_detail` AS d WHERE d.`loan_number`=crmd.loan_number AND d.id NOT IN($id) ORDER BY d.`date_payment` DESC LIMIT 1) AS installment_date ,
// 					(SELECT crm.`date_input` FROM `ln_client_receipt_money` AS crm,`ln_client_receipt_money_detail` AS crmd WHERE crm.`id`=crmd.`crm_id` AND crm.`id` != $id 
// AND crmd.`lfd_id` = (SELECT c.`lfd_id` FROM `ln_client_receipt_money_detail` AS c WHERE c.`crm_id`=$id) ORDER BY crm.`id` DESC LIMIT 1)  AS last_pay_date ,
// 					(SELECT `currency_id` FROM `ln_client_receipt_money_detail` WHERE crm_id = $id LIMIT 1) AS `currency_type`,
// 					(SELECT crm.`recieve_amount` FROM `ln_client_receipt_money` AS crm WHERE crm.`id`=$id ) AS recieve_amount,
// 					(SELECT crm.`receiver_id` FROM `ln_client_receipt_money` AS crm WHERE crm.`id`=$id ) AS receiver_id,
// 					(SELECT c.`client_number` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id` LIMIT 1) AS client_number,
// 					(SELECT c.`name_kh` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id` LIMIT 1) AS name_kh,
// 					crmd.* 
// 				FROM
// 					`ln_client_receipt_money_detail` AS crmd 
// 				WHERE crmd.`crm_id` =$id";
		$sql="SELECT
		(SELECT d.`date_payment` FROM `ln_client_receipt_money_detail` AS d WHERE d.`loan_number`=crmd.loan_number AND d.id NOT IN($id) ORDER BY d.`date_payment` DESC LIMIT 1) AS installment_date ,
		(SELECT crm.`date_input` FROM `ln_client_receipt_money` AS crm,`ln_client_receipt_money_detail` AS crmd WHERE crm.`id`=crmd.`crm_id` AND crm.`id` != $id
		AND crmd.`lfd_id` = (SELECT c.`lfd_id` FROM `ln_client_receipt_money_detail` AS c WHERE c.`crm_id`=$id LIMIT 1) ORDER BY crm.`id` DESC LIMIT 1)  AS last_pay_date ,
		(SELECT `currency_id` FROM `ln_client_receipt_money_detail` WHERE crm_id = $id LIMIT 1) AS `currency_type`,
		(SELECT crm.`recieve_amount` FROM `ln_client_receipt_money` AS crm WHERE crm.`id`=$id ) AS recieve_amount,
		(SELECT crm.`receiver_id` FROM `ln_client_receipt_money` AS crm WHERE crm.`id`=$id ) AS receiver_id,
		(SELECT c.`client_number` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id` LIMIT 1) AS client_number,
		(SELECT c.`name_kh` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id` LIMIT 1) AS name_kh,
		crmd.*
		FROM
		`ln_client_receipt_money_detail` AS crmd
		WHERE crmd.`crm_id` =$id";
		return $db->fetchAll($sql);
	}
	
	public function getAllIlDetail($id){
		$db = $this->getAdapter();
		$sql="SELECT
	
			(SELECT `currency_id` FROM `ln_client_receipt_money_detail` WHERE crm_id = crmd.`crm_id` LIMIT 1) AS `currency_type`,
			(SELECT c.`client_number` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id`) AS client_number,
			(SELECT c.`name_kh` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id`) AS name_kh,
			crmd.*
		FROM
			`ln_client_receipt_money_detail` AS crmd WHERE crmd.`crm_id` = $id";
		//return $sql;
		return $db->fetchAll($sql);
	}
    function getTranLoanByIdWithBranch($id){
//     	$sql = "SELECT lg.g_id,lg.level,lg.co_id,lg.zone_id,lg.pay_term,lm.payment_method,
//     		lm.interest_rate,lm.amount_collect_principal,
//     		lm.client_id,lm.admin_fee,
// 	    	(SELECT name_kh FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_kh,
// 	  		(SELECT name_en FROM `ln_client` WHERE client_id = lm.client_id LIMIT 1) AS client_name_en,
// 	  		lm.total_capital,lm.interest_rate,lm.payment_method,
// 	    	lg.time_collect,
// 	    	lg.zone_id,

// 	    	lg.status AS str ,lg.status FROM `ln_loan_group` AS lg,`ln_loan_member` AS lm
// 			WHERE lg.g_id = lm.group_id AND lg.g_id = $id LIMIT 1 ";
//     	return $this->getAdapter()->fetchRow($sql);
    }
    
    function getPrefixCode($branch_id){
    	$db  = $this->getAdapter();
    	$sql = " SELECT prefix FROM `ln_project` WHERE br_id = $branch_id  LIMIT 1";
    	return $db->fetchOne($sql);
    }
    
    public function getIlPaymentNumber($branch_id=1){
    	$this->_name='ln_client_receipt_money';
    	$db = $this->getAdapter();
    	$sql=" SELECT id  FROM $this->_name WHERE branch_id = $branch_id  ORDER BY id DESC LIMIT 1 ";
    	
    	$pre = "";
    	$pre = $this->getPrefixCode($branch_id)."-P";
    	
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	//$pre = "";
    	//$pre_fix="PM-";
    	for($i = $acc_no;$i<3;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
public function addPaymentByCustomer($data){
		$db = $this->getAdapter();
    	$db->beginTransaction();
    	$session_user=new Zend_Session_Namespace('authinstall');
    	$user_id = $session_user->user_id;
    	try{
	    	$reciept_no = $data['reciept_no'];
	    	$sql="SELECT id  FROM ln_client_receipt_money WHERE receipt_no='$reciept_no' ORDER BY id DESC LIMIT 1 ";
	    	$acc_no = $db->fetchOne($sql);
	    	if($acc_no){
	    		$dbc = new Application_Model_DbTable_DbGlobal();
	    		$reciept_no = $dbc->getReceiptByBranch(array("branch_id"=>$data["branch_id"]));
	    	}else{
	    		$reciept_no = $data['reciept_no'];
	    	}
    	
		    	$amount_receive = $data["amount_receive"];
		    	$total_payment = $data["total_payment"];
		    	$return = 0;
		    	$option_pay = $data["option_pay"];
    	
    	if($amount_receive>$total_payment){
    		$amount_payment = $amount_receive - $return;
    		$is_compleated = 1;
    	}elseif($amount_receive<$total_payment){
    		$amount_payment = $amount_receive;
    		$is_compleated = 0;
    	}else{
    		$amount_payment = $total_payment;
    		$is_compleated = 1;
    	}
    	
    	$principle = $data["os_amount"];//ប្រាក់ដើមនៅសល់បន្ទាប់ពីបង់
    	$penelize  = $data["penalize_amount"];
    	$service_charge = $data["service_charge"];
    	$interest = $data["total_interest"];
    	$total_pay = $data["total_payment"];//ប្រាក់ត្រូវបង់សរុប
    	$recieve = $data["amount_receive"];//-$data["amount_return"];
    	
    	$new_service = $recieve-$service_charge;
    	if($new_service>=0){
    		$service = $service_charge;
    		$new_penelize = $new_service - $penelize;
    		if($new_penelize>=0){
    			$penelize_amount =  $penelize;
    			$new_interest = $new_penelize - $interest;
    			if($new_interest>=0){
    				$interest_amount = $interest;
    				$new_printciple = $new_interest - $principle;
    				if($new_printciple>=0){
    					$principle_amount = $principle;
    				}else{
    					$principle_amount = abs($new_interest);
    				}
    			}else{
    				$interest_amount = abs($new_penelize);
    				$principle_amount=0;
    			}
    		}else{
    			$penelize_amount = abs($new_service);
    			$interest =0;
    			$principle_amount=0;
    		}
    	}else{
    		$service = abs($recieve);
    		$penelize_amount = 0;
    		$interest =0;
    		$principle_amount=0;
    	}
    	
    		$date_collect = $data["collect_date"];
    	    $identify = explode(',',$data['identity']);
    		foreach($identify as $i){
    		   if(empty($data['mfdid_'.$i])){
    		   		continue;
    		   }
    			$service_charge= $data["service_charge"];
    			$penalize = $data["penalize_amount"];
    			$arr_client_pay = array(
    					'branch_id'						=>	$data["branch_id"],
    					'receipt_no'					=>	$reciept_no,
    					'date_pay'					    =>	$data['collect_date'],
    					'date_input'					=>	date("Y-m-d"),
    					'client_id'                     =>	$data['client_id'],
    					'sale_id'						=>	$data['sale_id'.$i],
    					'land_id'						=>	$data['house_id'.$i],
    					'outstanding'                   =>	$data["total_priciple_".$i], //$data['priciple_amount']+$principle_amount,//ប្រាក់ដើមមុនបង់
    					'total_principal_permonth'		=>	$data["principal_permonth_".$i],//ប្រាក់ដើមត្រូវបង់
    					'total_interest_permonth'		=>	$data["interest_".$i],
    					'penalize_amount'				=>	$data['penelize_'.$i],
    					'service_charge'				=>	$data['service_'.$i],
    					'principal_amount'				=>	$data["total_priciple_".$i]-$data["principal_permonth_".$i],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
    					'total_principal_permonthpaid'	=>	$data["principal_permonth_".$i],//$principle_amount,//ok ប្រាក់ដើមបានបង
    					'total_interest_permonthpaid'	=>	$data["interest_".$i],//$interest_amount,//ok ការប្រាក់បានបង
    					'penalize_amountpaid'			=>	$data['penelize_'.$i],//$penelize_amount,// ok បានបង
    					'service_chargepaid'			=>	$data['service_'.$i],//$service,// okបានបង
    					'balance'						=>	$data["total_priciple_".$i]-$data["principal_permonth_".$i],
    					'total_payment'					=>  $data["payment_".$i],//ប្រាក់ត្រូវបង់ok
    					'recieve_amount'				=>	$data["payment_".$i],//ok
    					'amount_payment'				=>	$data["payment_".$i],//brak ban borng
    					'return_amount'					=>	$return,//ok
    					'note'							=>	$data['note'],
    					'cheque'						=>	$data['cheque'],
    					'user_id'						=>	$user_id,
    					'payment_option'				=>	$data["option_pay"],
    					'status'						=>	1,
    					'is_completed'					=>	$is_compleated,
    					'field1'			=>3,
    					'field3'			=>3,
    					'payment_times'=>$data['paid_times']//not sure
    			);
    			$this->_name = "ln_client_receipt_money";
    			$client_pay = $this->insert($arr_client_pay);
    			
    			if($option_pay==1){//normal
    				$total_recieve = $data["amount_receive"];
    				if($total_recieve>=$data["total_payment"]){
    					$is_compleated = 1;
    				}else{
    					$is_compleated = 0;
    				}
    			}else{
    				$total_recieve=$data["payment_".$i];
    				$is_compleated = 1;
    			}
    			$sub_recieve_amount = $data["amount_receive"];
    			$sub_service_charge = $data["service_".$i];
    			$sub_peneline_amount = $data["penelize_".$i];
    			$sub_interest_amount = $data["interest_".$i];
    			$sub_principle= $data["principal_permonth_".$i];
    			$sub_total_payment = $data["payment_".$i];
//     			$loan_number = $data["loan_number"];
    			$date_payment = $data["date_payment_".$i];
    		           $arr_money_detail = array(
    						'crm_id'				=>		$client_pay,
    						'land_id'			    =>		$data['house_id'.$i],//ook
    						'lfd_id'				=>		$data["mfdid_".$i],//ook
    						'date_payment'			=>	    $data["date_payment_".$i], // 00k ថ្ងៃដែលត្រូវបង់
    						'capital'				=>		$data["total_priciple_".$i],//ook
    						'remain_capital'		=>		$data["total_priciple_".$i]-$data["principal_permonth_".$i],//ook
    						'principal_permonth'	=>		$data["principal_permonth_".$i],//ook $principle_amount,
    						'total_interest'		=>		$data["interest_".$i],//ook
    						'total_payment'			=>		$data["payment_".$i],//ook
    						'total_recieve'			=>		$total_recieve,//ook
    						'penelize_amount'		=>		$data['penelize_'.$i],
    						'service_charge'		=>		$data['service_'.$i],
    						'penelize_new'			=>		$data['penelize_'.$i]-$data['old_penelize_'.$i],
    						'service_charge_new'	=>		$data["service_charge"]-$data['service_'.$i],
    		           		'old_penelize'			=>		$data['old_penelize_'.$i],
    						'old_service_charge'	=>		$data['old_service_'.$i],
    						'old_interest'			=>		$data["old_interest_".$i],
    		           		'old_principal_permonth'=>		$data['old_principal_permonth_'.$i],
    		           		'old_total_payment'		=>		$data['old_payment_'.$i],
    		           		'old_total_priciple'	=>		$data["old_total_priciple_".$i],
    		           		'last_pay_date'			=>		$data["last_date_payment_".$i],
    						'is_completed'			=>		$is_compleated,//ook
    						'status'				=>		1
    				);
    				
    				$db->insert("ln_client_receipt_money_detail", $arr_money_detail);
    				$loan_number = $data['sale_id'.$i];
    				//////foreach here 
    				if($option_pay==1){//normal
	    				if($sub_recieve_amount>=$total_payment){//normall and paid
		    		// get amount record if paid all update tb_sale to complete
		    				$rows = $this->getSaleScheduleById($loan_number, 1);
		    				if(!empty($rows)){
		    					$paid_amount = $data['amount_receive']-$data['penalize_amount'];
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
		    					
		    					foreach ($rows as $row){
		    						$old_interest=$paid_amount;
		    						$paid_amountbefore = $paid_amount;
		    						$paid_amount = $paid_amount-$row['total_interest_after'];
		    						if($paid_amount>=0){
		    							$total_interestafter=0;
		    							$total_interestpaid=$row['total_interest_after'];
		    							$old_paid = $paid_amount;
		    							$paid_amount = $paid_amount-$row['principal_permonthafter'];
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
		    							$remain_principal = 0;
		    							$statuscomplete=0;
		    							$principal_paid=0;
		    							$total_interestpaid=($old_interest);
		    							$total_interestafter=$total_interestpaid;
		    						}
		    						$total_interest=$total_interest+$total_interestpaid;//ok
		    						$total_principal = $total_principal+$principal_paid;
		    						$pyament_after = $row['total_payment_after']-($principal_paid);//ប្រាក់ត្រូវបង់លើកក្រោយសំរាប់ installmet 1 1
		    						$arra = array(
		    								"principal_permonthafter"=>$remain_principal,
		    								'total_interest_after'=>$row['total_interest_after']-$total_interestpaid,
		    								'begining_balance_after'=>$row['begining_balance_after']-($principal_paid),
		    								'ending_balance'=>$row['begining_balance_after']-($principal_paid+$remain_principal),
		    								'is_completed'=>$statuscomplete,
		    								'paid_date'	=>	$data['collect_date'],
		    								'total_payment_after'=>	$remain_principal+($row['total_interest_after']-$total_interestpaid),
		    								'payment_option'	=>	$data["option_pay"],
		    								'paid_date'			=> 	$data['collect_date'],
		    								);
		    						$where = " id = ".$row['id'];
		    						$this->_name="ln_saleschedule";
		    						$this->update($arra, $where);
		    						if($paid_amount<=0){
		    							break;
		    						}
		    						break;
		    					}//end foreach 
		    				}else{
		    				}
	    				}else{ //ករណីបង់មិនគ្រប់
			   					$new_sub_interest_amount = $data["interest_".$i];
			   					$new_sub_penelize = $data["penalize_amount"];
			   					$new_sub_service_charge = $data["service_charge"];
			   					$principle_after = $data["principal_permonth_".$i];
			   					$pyament_after = $total_payment-$amount_receive;
				   				if($sub_recieve_amount>0){//if received >0
				   					$new_amount_after_service = $sub_recieve_amount-$new_sub_service_charge;
				   					if($new_amount_after_service>=0){
				   						$new_sub_service_charge = 0;
				   						$new_amount_after_penelize = $new_amount_after_service - $new_sub_penelize;
				   						
				   						if($new_amount_after_penelize>=0){
				   							$new_sub_penelize = 0;
				   							$new_amount_after_interest = $new_amount_after_penelize - $new_sub_interest_amount;
				   							
				   							if($new_amount_after_interest>=0){
				   								$new_sub_interest_amount = 0;
				   								$principle_after = $principle_after - $new_amount_after_interest;
				   								$begining_balance_after = $data['total_priciple_'.$i] - $new_amount_after_interest;
				   							}else{
				   								$new_sub_interest_amount = abs($new_amount_after_interest);
				   								$begining_balance_after = $data['total_priciple_'.$i] ;
				   							}
				   						}else{
				   							$new_sub_penelize = abs($new_amount_after_penelize);
				   							$begining_balance_after = $data['total_priciple_'.$i] ;
				   						}
				   					}else{
				   						$new_sub_service_charge = abs($new_amount_after_service);
				   						$begining_balance_after = $data['total_priciple_'.$i] ;
				   					}
				   				}
				   				
				   				$arr_update_fun_detail = array(
				   						'is_completed'			=> 	0,
				   						'principal_permonthafter'=>	$principle_after,
				   						'begining_balance_after'=>	$begining_balance_after,
				   						'total_interest_after'	=>  $new_sub_interest_amount,
				   						'total_payment_after'	=>	$pyament_after,
				   						'penelize'				=>	$new_sub_penelize,
				   						'service_charge'		=>	$new_sub_service_charge,
				   						'payment_option'		=>	1,
				   						'paid_date'				=> 	$data['collect_date'],  // to know the last paid date
				   				);
				   				$this->_name="ln_saleschedule";
				   				$where = $db->quoteInto("id=?", $data["mfdid_".$i]);
				   				$this->update($arr_update_fun_detail, $where);
			   				}
    					}else{//pay off
    						$arr_update_fun_detail = array(
    								'is_completed'			=> 	1,
    								'payment_option'		=>	$data["option_pay"]
    						);
    						$this->_name="ln_saleschedule";
    						$where = " is_completed = 0 AND sale_id=".$loan_number;
    						$this->update($arr_update_fun_detail, $where);
    					// update tbl_sale to complete when pay off
    						$this->_name="ln_sale";
    						$update_sale = array(
    										'is_completed'=>1,
    										);
    						$where=" id = $loan_number ";
    						$this->update($update_sale, $where);
    					}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getSaleScheduleById($loan_number,$orderby=1){
    	$db = $this->getAdapter();
    	$sql="select * from  ln_saleschedule where is_completed=0 and  sale_id=$loan_number ";
    	if($orderby==1){
    		$sql.=" ORDER BY id ASC ";
    	}else{
    		$sql.=" ORDER BY id DESC ";
    	}
		return $db->fetchAll($sql); 
    }
    function updateIlPayment($data,$id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$is_set = 0;
    	try{
    		if($data['status']==0){
    			if($data['option_pay']==4){//payoff update  ទៅជាOld History សិនទើប update  ក្រោយ Check it again 
    				$sql1="select sale_id as sale_id ,lfd_id as saleschedule_id from ln_client_receipt_money_detail where crm_id=$id";
    				$result = $db->fetchAll($sql1);
    				if(!empty($result)){
    					foreach ($result as $_data){
    						$array = array(
    								'is_completed'=>0,
    								);
    						$where=" id = ".$_data['saleschedule_id']." and sale_id = ".$_data['sale_id'];
    						$this->_name="ln_saleschedule";
    						$this->update($array, $where);
    					}
    				}
    				
    				$arr = array(
    						'status'=>0,
    						);
    				$where = " id = $id";
    				$this->_name="ln_client_receipt_money";
    				$this->update($arr, $where);
    				
    				$where1 = " crm_id = $id";
    				$this->_name="ln_client_receipt_money_detail";
    				$this->update($arr, $where1);
    			}else{//normal
    				
    				$old_rs = $this->getAllReceiptMoneyDetail($id);//if payoff?
    				if(!empty($old_rs)){
    					foreach($old_rs AS $rowrm){
    						$array =  array(
    								'begining_balance_after'	=>$rowrm['capital'],
    								'ending_balance'=>$rowrm['remain_capital'],
    								'principal_permonthafter'	=>$rowrm['old_principal_permonth'],
    								'total_interest_after'		=>$rowrm['old_interest'],
    								'total_payment_after'		=>$rowrm['old_total_payment'],
    								'penelize'					=>$rowrm['old_penelize'],
    								'service_charge'			=>$rowrm['old_service_charge'],
    								'is_completed'				=>0,
    						);
    						$where=" id = ".$rowrm['lfd_id'];
    						$this->_name="ln_saleschedule";
    						$this->update($array, $where);
    					}
    				} 	    			
	    			$arr = array(
    						'status'=>0,
    						);
    				$where = " id = $id";
    				$this->_name="ln_client_receipt_money";
    				$this->update($arr, $where);
    				
    				$where1 = " crm_id = $id";
    				$this->_name="ln_client_receipt_money_detail";
    				$this->update($arr, $where1);
    			}
    		}else{	//status active
    			$old_rs = $this->getAllReceiptMoneyDetail($id);//if payoff?
    			if(!empty($old_rs)){
    				if($data['option_pay']==1){
    					foreach($old_rs AS $rowrm){
    						$array =  array(
    								'begining_balance_after'	=>$rowrm['capital'],
    								'ending_balance'=>$rowrm['remain_capital'],
    								'principal_permonthafter'	=>$rowrm['old_principal_permonth'],
    								'total_interest_after'		=>$rowrm['old_interest'],
    								'total_payment_after'		=>$rowrm['old_total_payment'],
    								'penelize'					=>$rowrm['old_penelize'],
    								'service_charge'			=>$rowrm['old_service_charge'],
    								'is_completed'				=>0,);
    						$where=" id = ".$rowrm['lfd_id'];
    						$this->_name="ln_saleschedule";
    						$this->update($array, $where);
    					}
    					
    				    //echo $data["mfdid_".$i];exit();
    					$this->_name="ln_client_receipt_money_detail";
    					$where = " crm_id = $id";
    					$this->delete($where);
    					
    					$paid_amount = $data['amount_receive']-$data['extrapayment'];
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
    					$penalize=0;$service_amount=0;
    					$rows = $this->getSaleScheduleById($data['loan_number'], 1);
    					if(!empty($rows)){
    						foreach ($rows as $row){
    							$old_interest=$paid_amount;
    							$paid_amountbefore = $paid_amount;
    							$paid_amount= $paid_amount-$data['service_charge'];
    							if($paid_amount>=0){
    								$service_amount =$data['service_charge'];
    								$paid_amount = $paid_amount-$data['penalize_amount'];
    								if($paid_amount>=0){
    									$penalize = 0;//$data['penalize_amount'];
    								}else{
    									$penalize = $data['penalize_amount']- abs($paid_amount);
    								}
    							}else{
    								$service_amount= $data['service_charge'] - abs($paid_amount);
    							}
    							
    							$paid_amount = $paid_amount-$row['total_interest_after'];
    							if($paid_amount>=0){
    								$total_interestafter=0;
    								$total_interestpaid=$row['total_interest_after'];
    								$old_paid = $paid_amount;
    								$paid_amount = $paid_amount-$row['principal_permonthafter'];
    								
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
    								$remain_principal = 0;
    								$statuscomplete=0;
    								$principal_paid=0;
    								$total_interestpaid=($old_interest);
    								$total_interestafter=$total_interestpaid;
    							}
    							
    							$total_interest=$total_interest+$total_interestpaid;//ok
    							$total_principal = $total_principal+$principal_paid;
    							$pyament_after = $row['total_payment_after']-($principal_paid);//ប្រាក់ត្រូវបង់លើកក្រោយសំរាប់ installmet 1 1
    							$arra = array(
    									"principal_permonthafter"=>$remain_principal,
    									'total_interest_after'=>$row['total_interest_after']-$total_interestpaid,
    									'begining_balance_after'=>$row['begining_balance_after']-($principal_paid),
    									'ending_balance'=>$row['begining_balance_after']-($principal_paid+$remain_principal),
    									'is_completed'=>$statuscomplete,
    									'penelize'=>$penalize,
    									'paid_date'			=> 	$data['collect_date'],
    									'total_payment_after'	=>	$remain_principal+($row['total_interest_after']-$total_interestpaid),
    							);
    							$where = " id = ".$row['id'];
    							$this->_name="ln_saleschedule";
    							$this->update($arra, $where);
    							
    							$this->_name='ln_client_receipt_money_detail';
    							$array = array(
    									'crm_id'				=>$id,
    									'lfd_id'				=>$row['id'],
    									'client_id'				=>$data['client_id'],
    									'land_id'				=>$data['loan_number'],
    									'date_payment'			=>$row['date_payment'],
    									'paid_date'             =>$data['collect_date'],
    									'capital'				=>$row['begining_balance'],
    									'remain_capital'		=>$row['begining_balance']-$principal_paid,
    									'principal_permonth'	=>$principal_paid,
    									'total_interest'		=>0,
    									'total_payment'			=>$principal_paid+$total_interestpaid,
    									'total_recieve'			=>$principal_paid+$total_interestpaid,
    									'service_charge'		=>0,
    									'penelize_amount'		=>0,
    									'is_completed'			=>$statuscomplete,
    									'status'				=>1,
    									'old_interest'			 =>$row["total_interest_after"],
    									'old_principal_permonth'=>$row["principal_permonthafter"],
    									'old_total_payment'	 =>$row["total_payment_after"],
    							);
    							$this->insert($array);
    							if($paid_amount<=0){
    								break;
    							}
    						}
    						/////////////update receipt money
    						$loan_number = $data['loan_number'];
    						$amount_receive = $data["amount_receive"];
    						$total_payment = $data["total_payment"];
    						$return =0;// $data["amount_return"];
    						$option_pay = $data["option_pay"];
    						
    						if($amount_receive>$total_payment){
    							$amount_payment = $amount_receive - $return;
    							$is_compleated = 1;
    						}elseif($amount_receive<$total_payment){
    							$amount_payment = $amount_receive;
    							$is_compleated = 0;
    						}else{
    							$amount_payment = $total_payment;
    							$is_compleated = 1;
    						}
    						$principle = $data["os_amount"];
    						$penelize  = $data["penalize_amount"];
    						$service_charge = $data["service_charge"];
    						$interest = $data["total_interest"];
    						$total_pay = $data["total_payment"];
    						$recieve = $data["amount_receive"]-$data["extrapayment"];//-$data['amount_return'];
    						
    						$new_service = $recieve-$service_charge;
    						if($new_service>=0){
    							$service = $service_charge;
    							$new_penelize = $new_service - $penelize;
    							if($new_penelize>=0){
    								$penelize_amount =  $penelize;
    								$new_interest = $new_penelize - $interest;
    								if($new_interest>=0){
    									$interest_amount = $interest;
    									$new_printciple = $new_interest - $principle;
    									if($new_printciple>=0){
    										$principle_amount = $principle;
    									}else{
    										$principle_amount = abs($new_interest);
    									}
    								}else{
    									$interest_amount = abs($new_penelize);
    									$principle_amount=0;
    								}
    							}else{
    								$penelize_amount = abs($new_service);
    								$interest =0;
    								$principle_amount=0;
    							}
    						}else{
    							$service = abs($recieve);
    							$penelize_amount = 0;
    							$interest =0;
    							$principle_amount=0;
    						}
    						
    						$service_charge= $data["service_charge"];
    						$penalize = $data["penalize_amount"];
    						$arr_client_pay = array(
    								'branch_id'						=>	$data["branch_id"],
    								'receipt_no'					=>	$data['reciept_no'],
    								'date_pay'					    =>	$data['collect_date'],
    								'date_input'					=>	date("Y-m-d"),
    								'client_id'                     =>	$data['client_id'],
    								'sale_id'						=>	$data['loan_number'],
    								'land_id'						=>	$data['property_id'],
    								'outstanding'                   =>	$data['priciple_amount']+$principle_amount,//ប្រាក់ដើមមុនបង់
    								'total_principal_permonth'		=>	$data["os_amount"],//ប្រាក់ដើមត្រូវបង់
    								'total_interest_permonth'		=>	$data["total_interest"],
    								'penalize_amount'				=>	$penalize,
    								'service_charge'				=>	$data["service_charge"],
    								'principal_amount'				=>	$data['priciple_amount'],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
    								'total_principal_permonthpaid'	=>	$principle_amount,//ok ប្រាក់ដើមបានបង
    								'total_interest_permonthpaid'	=>	$interest_amount,//ok ការប្រាក់បានបង
    								'penalize_amountpaid'			=>	$penelize_amount,// ok បានបង
    								'service_chargepaid'			=>	$service,// okបានបង
    								'balance'						=>	$data["remain"],
    								'total_payment'					=>	$data["total_payment"],//ប្រាក់ត្រូវបង់ok
    								'recieve_amount'				=>	$data['amount_receive'],//ok
    								'amount_payment'				=>	$amount_payment,//brak ban borng
    								'return_amount'					=>	$return,//ok
    								'note'							=>	$data['note'],
    								'cheque'						=>	$data['cheque'],
    								//     							'user_id'						=>	$user_id,
    								'payment_option'				=>	$data["option_pay"],
    								'status'						=>	1,
    								'is_completed'					=>	$is_compleated,
    								'field3'			=>3,
//     								'extra_payment' =>$data["extrapayment"],
    						);
    						$this->_name = "ln_client_receipt_money";
    						$where = "id = ".$id;
    						$this->update($arr_client_pay, $where);
    						$client_pay=$id;
//     						print_r($arr_client_pay);exit();
//     						$arr = array(
//     								'outstanding'		=> $data['sold_price'],
//     								'principal_amount'	=> $data['sold_price']-$total_principal,//សល់ពីបង់
//     								'total_principal_permonth'	=>$total_principal,
//     								'total_principal_permonthpaid'=>$total_principal,
//     								'total_interest_permonth'	=>$total_interest,
//     								'total_interest_permonthpaid'=>$total_interest
//     						);
//     						//need balance
    					
//     						$this->_name='ln_client_receipt_money';
//     						$where="id = ".$id;
//     						$crm_id = $this->update($arr, $where);
    					}
    					
	    					if($option_pay==1){//normal
	    					}else{//pay off
	    					}

    					if($data['extrapayment']>0){
				    			$extrapayment = $data['extrapayment'];
				    			$rs = $this->getSaleScheduleById($loan_number,2);
				    			if(!empty($rs)){
				    				foreach ($rs as $row){
				    						$total_interestafter=0;
				    						$extrapayment = $extrapayment-$row['principal_permonthafter'];
				    						if($extrapayment>=0){
				    							$principal_paid = $row['principal_permonthafter'];
				    							$statuscomplete=1;
				    							$remain_principal=0;
				    						}else{
				    							$principal_paid = abs($extrapayment);
				    							$remain_principal=$principal_paid;
				    							$statuscomplete=0;
				    						}
				    				
				    					$total_principal = $total_principal+$principal_paid;
				    					 
				    					$pyament_after = $row['total_payment_after']-($principal_paid);//ប្រាក់ត្រូវបង់លើកក្រោយសំរាប់ installmet 1 1
				    					$arra = array(
				    							"principal_permonthafter"=>$remain_principal,
				    							'total_interest_after'=>$total_interestafter,
				    							'begining_balance_after'=>$row['begining_balance_after']-$principal_paid,
				    							'is_completed'=>$statuscomplete,
				    							'paid_date'			=> 	$data['date_buy'],
				    							'total_payment_after'	=>	$pyament_after,
				    					);
				    					$where = " id = ".$row['id'];
				    					$this->_name="ln_saleschedule";
				    					$this->update($arra, $where);
				    					if($extrapayment<=0){
				    						break;
				    					}
				    				}
				    				
				    			}
				    		}//end of extra payment
    				}
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
//     function updateIlPayment($data,$id){
//     	$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	$is_set = 0;
//     	try{
//     		if($data['status']==0){
//     			if($data['option_pay']==4){//payoff
//     				$sql1="select land_id as sale_id ,lfd_id as saleschedule_id from ln_client_receipt_money_detail where crm_id=$id";
//     				$result = $db->fetchAll($sql1);
//     				if(!empty($result)){
//     					foreach ($result as $_data){
    
//     						$array = array(
//     								'is_completed'=>0,
//     						);
//     						$where=" id = ".$_data['saleschedule_id']." and sale_id = ".$_data['sale_id'];
//     						$this->_name="ln_saleschedule";
//     						$this->update($array, $where);
    
//     						//     						$this->_name="ln_sale";
//     						//     						$where1=" id = ".$_data['sale_id'];
//     						//     						$this->update($array, $where1);
    
//     					}
//     				}
    
//     				$arr = array(
//     						'status'=>0,
//     				);
//     				$where = " id = $id";
//     				$this->_name="ln_client_receipt_money";
//     				$this->update($arr, $where);
    
//     				$where1 = " crm_id = $id";
//     				$this->_name="ln_client_receipt_money_detail";
//     				$this->update($arr, $where1);
    
//     			}else{
    
//     				$identify = explode(',',$data['identity']);
//     				foreach($identify as $i){
//     					$array =  array(
//     							'begining_balance_after'	=>$data['old_total_priciple_'.$i],
//     							'principal_permonthafter'	=>$data['old_principal_permonth_'.$i],
//     							'total_interest_after'		=>$data['old_interest_'.$i],
//     							'total_payment_after'		=>$data['old_payment_'.$i],
//     							'penelize'					=>$data['old_penelize_'.$i],
//     							'service_charge'			=>$data['old_service_'.$i],
//     							'is_completed'				=>0,
//     							'paid_date'					=>null,
//     							'payment_option'			=>null,
//     					);
//     					$where=" id = ".$data['mfdid_'.$i];
//     					$this->_name="ln_saleschedule";
//     					$this->update($array, $where);
//     				}
    
//     				$arr = array(
//     						'status'=>0,
//     				);
//     				$where = " id = $id";
//     				$this->_name="ln_client_receipt_money";
//     				$this->update($arr, $where);
    
//     				$where1 = " crm_id = $id";
//     				$this->_name="ln_client_receipt_money_detail";
//     				$this->update($arr, $where1);
    
//     			}
    			 
//     		}else{
//     			$old_rs = $this->getAllReceiptMoneyDetail($id);
//     			//$sql="select lfd_id as saleschedule from ln_client_receipt_money_detail where id=$id ";
//     			//$result = $db->fetchAll($sql);
    			 
//     			if($data['option_pay']==1){
//     				$identify = explode(',',$data['identity']);
//     				foreach($identify as $i){
//     					$array =  array(
//     							'begining_balance_after'	=>$data['old_total_priciple_'.$i],
//     							'principal_permonthafter'	=>$data['old_principal_permonth_'.$i],
//     							'total_interest_after'		=>$data['old_interest_'.$i],
//     							'total_payment_after'		=>$data['old_payment_'.$i],
//     							'penelize'					=>$data['old_penelize_'.$i],
//     							'service_charge'			=>$data['old_service_'.$i],
//     							'is_completed'				=>0,
//     					);
//     					$where=" id = ".$data['mfdid_'.$i];
//     					$this->_name="ln_saleschedule";
//     					$this->update($array, $where);
//     				}
    
    
//     				$loan_number = $data['loan_number'];
    	    
//     				$amount_receive = $data["amount_receive"];
//     				$total_payment = $data["total_payment"];
//     				$return = $data["amount_return"];
//     				$option_pay = $data["option_pay"];
    	    
//     				if($amount_receive>$total_payment){
//     					$amount_payment = $amount_receive - $return;
//     					$is_compleated = 1;
//     				}elseif($amount_receive<$total_payment){
//     					$amount_payment = $amount_receive;
//     					$is_compleated = 0;
//     				}else{
//     					$amount_payment = $total_payment;
//     					$is_compleated = 1;
//     				}
    	    
//     				$principle = $data["os_amount"];
//     				$penelize  = $data["penalize_amount"];
//     				$service_charge = $data["service_charge"];
//     				$interest = $data["total_interest"];
//     				$total_pay = $data["total_payment"];
//     				$recieve = $data["amount_receive"]-$data["amount_return"];
    	    
//     				$new_service = $recieve-$service_charge;
    	    
//     				if($new_service>=0){
//     					$service = $service_charge;
//     					$new_penelize = $new_service - $penelize;
//     					if($new_penelize>=0){
//     						$penelize_amount =  $penelize;
//     						$new_interest = $new_penelize - $interest;
//     						if($new_interest>=0){
//     							$interest_amount = $interest;
//     							$new_printciple = $new_interest - $principle;
//     							if($new_printciple>=0){
//     								$principle_amount = $principle;
//     							}else{
//     								$principle_amount = abs($new_interest);
//     							}
//     						}else{
//     							$interest_amount = abs($new_penelize);
//     							$principle_amount=0;
//     						}
//     					}else{
//     						$penelize_amount = abs($new_service);
//     						$interest =0;
//     						$principle_amount=0;
//     					}
//     				}else{
//     					$service = abs($recieve);
//     					$penelize_amount = 0;
//     					$interest =0;
//     					$principle_amount=0;
//     				}
    	    
    	    
//     				$service_charge= $data["service_charge"];
//     				$penalize = $data["penalize_amount"];
//     				$arr_client_pay = array(
//     						'branch_id'						=>	$data["branch_id"],
//     						//'receipt_no'					=>	$reciept_no,
//     						'date_pay'					    =>	$data['collect_date'],
//     						//'date_input'					=>	date("Y-m-d"),
//     						'client_id'                     =>	$data['client_id'],
//     						'land_id'						=>	$data['loan_number'],
//     						'outstanding'                   =>	$data['priciple_amount']+$principle_amount,//ប្រាក់ដើមមុនបង់
//     						'total_principal_permonth'		=>	$data["os_amount"],//ប្រាក់ដើមត្រូវបង់
//     						'total_interest_permonth'		=>	$data["total_interest"],
//     						'penalize_amount'				=>	$penalize,
//     						'service_charge'				=>	$data["service_charge"],
    
//     						'principal_amount'				=>	$data['priciple_amount'],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
//     						'total_principal_permonthpaid'	=>	$principle_amount,//ok ប្រាក់ដើមបានបង
//     						'total_interest_permonthpaid'	=>	$interest_amount,//ok ការប្រាក់បានបង
//     						'penalize_amountpaid'			=>	$penelize_amount,// ok បានបង
//     						'service_chargepaid'			=>	$service,// okបានបង
//     						'balance'						=>	$data["remain"],
    							
//     						'total_payment'					=>	$data["total_payment"],//ប្រាក់ត្រូវបង់ok
//     						'recieve_amount'				=>	$amount_receive,//ok
//     						'amount_payment'				=>	$amount_payment,//brak ban borng
//     						'return_amount'					=>	$return,//ok
    							
//     						'note'							=>	$data['note'],
//     						'cheque'						=>	$data['cheque'],
//     						//'payment_option'				=>	$data["option_pay"],
//     						'status'						=>	1,
    							
//     						'is_completed'					=>	$is_compleated
//     				);
//     				$where = "id = $id";
//     				$this->_name = "ln_client_receipt_money";
//     				$this->update($arr_client_pay,$where);
    
    
    
//     				$date_collect = $data["collect_date"];
//     				$identify = explode(',',$data['identity']);
//     				foreach($identify as $i){
    
//     					//echo $data["mfdid_".$i];exit();
//     					$this->_name="ln_client_receipt_money_detail";
//     					$where = "crm_id = $id";
//     					$this->delete($where);
    					 
    
//     					if($option_pay==1){//normal
//     						$total_recieve = $data["amount_receive"];
//     						if($total_recieve>=$data["total_payment"]){
//     							$is_compleated = 1;
//     						}else{
//     							$is_compleated = 0;
//     						}
//     					}else{
//     						$total_recieve=$data["payment_".$i];
//     						$is_compleated = 1;
//     					}
//     					$sub_recieve_amount = $data["amount_receive"];
//     					$sub_service_charge = $data["service_".$i];
//     					$sub_peneline_amount = $data["penelize_".$i];
//     					$sub_interest_amount = $data["interest_".$i];
//     					$sub_principle= $data["principal_permonth_".$i];
//     					$sub_total_payment = $data["payment_".$i];
//     					$loan_number = $data["loan_number"];
//     					$date_payment = $data["date_payment_".$i];
    
//     					$arr_money_detail = array(
//     							'crm_id'				=>		$id,
//     							'land_id'			    =>		$data['loan_number'],//ok
//     							'lfd_id'				=>		$data["mfdid_".$i],//ok
//     							'date_payment'			=>	    $data["date_payment_".$i],
//     							'capital'				=>		$data["total_priciple_".$i],
//     							'remain_capital'		=>		$data["priciple_amount"],  // remain balance after paid
//     							'principal_permonth'	=>		$data["principal_permonth_".$i],
//     							'total_interest'		=>		$data["interest_".$i],
//     							'total_payment'			=>		$data["payment_".$i],
//     							'total_recieve'			=>		$total_recieve,
//     							//     						'currency_id'			=>		$data["currency_type"],
//     							'pay_after'				=>		$data['multiplier_'.$i],
//     							'penelize_amount'		=>		$data['penelize_'.$i],
//     							'service_charge'		=>		$data['service_'.$i],
//     							'penelize_new'			=>		$data['penelize_'.$i]-$data['old_penelize_'.$i],
//     							'service_charge_new'	=>		$data["service_charge"]-$data['service_'.$i],
    
//     							'old_penelize'			=>		$data['old_penelize_'.$i],
//     							'old_service_charge'	=>		$data['old_service_'.$i],
//     							'old_interest'			=>		$data["old_interest_".$i],
//     							'old_principal_permonth'=>		$data['old_principal_permonth_'.$i],
//     							'old_total_payment'		=>		$data['old_payment_'.$i],
//     							'old_total_priciple'	=>		$data["old_total_priciple_".$i],
//     							'last_pay_date'			=>		$data["last_date_payment_".$i],
    
//     							'is_completed'			=>		$is_compleated,
//     							'status'				=>		1
//     					);
    
//     					$db->insert("ln_client_receipt_money_detail", $arr_money_detail);
//     					//     				print_r($arr_money_detail);exit();
//     					if($option_pay==1){//normal
//     						if($sub_recieve_amount>=$total_payment){//normall and paid
//     							$arr_update_saleschedule = array(
//     									'is_completed'		=> 	1,
//     									'payment_option'	=>	$data["option_pay"],
//     									'paid_date'			=> 	$data['collect_date'],  // to know the last paid date
//     							);
//     							$this->_name="ln_saleschedule";
//     							$where = $db->quoteInto("id=?", $data["mfdid_".$i]);
//     							$this->update($arr_update_saleschedule, $where);
    
    
//     							// get amount record if paid all update tb_sale to complete
    
//     							$sql1= "select * from  ln_saleschedule where is_completed=0 and  sale_id=$loan_number";
//     							$record_remain = $db->fetchAll($sql1);
    
//     							// 		    				print_r($record_remain);exit();
    
//     							if(!empty($record_remain)){
    									
//     							}else{
//     								$this->_name="ln_sale";
//     								$update_sale = array(
//     										'is_completed'=>1,
//     								);
//     								$where=" id = $loan_number ";
//     								$this->update($update_sale, $where);
//     							}
    
//     						}else{
//     							$new_sub_interest_amount = $data["interest_".$i];
//     							$new_sub_penelize = $data["penalize_amount"];
//     							$new_sub_service_charge = $data["service_".$i]+$data["service_charge"];
//     							$principle_after = $data["principal_permonth_".$i];
//     							$pyament_after = $total_payment-$amount_receive;
//     							if($sub_recieve_amount>0){//if received >0
//     								$new_amount_after_service = $sub_recieve_amount-$new_sub_service_charge;
//     								if($new_amount_after_service>=0){
//     									$new_sub_service_charge = 0;
//     									$new_amount_after_penelize = $new_amount_after_service - $new_sub_penelize;
//     									if($new_amount_after_penelize>=0){
//     										$new_sub_penelize = 0;
//     										$new_amount_after_interest = $new_amount_after_penelize - $sub_interest_amount;
//     										if($new_amount_after_interest>=0){
    												
//     											$new_sub_interest_amount = 0;
    												
//     											$principle_after = $principle_after - $new_amount_after_interest;
    
//     											$begining_balance_after = $data['total_priciple_'.$i] - $new_amount_after_interest;
    
//     										}else{
//     											$new_sub_interest_amount = abs($new_amount_after_interest);
//     											$begining_balance_after = $data['total_priciple_'.$i];
//     										}
//     									}else{
//     										$new_sub_penelize = abs($new_amount_after_penelize);
//     										$begining_balance_after = $data['total_priciple_'.$i];
//     									}
//     								}else{
//     									$new_sub_service_charge = abs($new_amount_after_service);
//     									$begining_balance_after = $data['total_priciple_'.$i];
//     								}
//     							}
    
//     							$arr_update_fun_detail = array(
//     									'is_completed'			=> 	0,
//     									'principal_permonthafter'=>	$principle_after,
//     									'begining_balance_after'=>	$begining_balance_after,
//     									'total_interest_after'	=>  $new_sub_interest_amount,
//     									'total_payment_after'	=>	$pyament_after,
//     									'penelize'				=>	$new_sub_penelize,
//     									'service_charge'		=>	$new_sub_service_charge,
//     									'payment_option'		=>	1,
//     									'paid_date'				=> 	$data['collect_date'],  // to know the last paid date
//     							);
//     							$this->_name="ln_saleschedule";
//     							$where = $db->quoteInto("id=?", $data["mfdid_".$i]);
//     							$this->update($arr_update_fun_detail, $where);
//     						}
//     					}
    
//     				}
//     			}else{
    
//     				echo 'here';exit();
    
//     			}
//     		}
//     		$db->commit();
//     	}catch (Exception $e){
//     		$db->rollBack();
//     		echo $e->getMessage();
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     	}
//     }
   function getAllPaymentListBySender($client_id){
   		$db = $this->getAdapter();
   		$sql = " CALL `stgetAllPaymentById`($client_id)";
   		return $db->fetchAll($sql);
   }
   function getLoanPaymentByLoanNumber($data){
    	$db = $this->getAdapter();
	    		$sql ="SELECT 
						 (SELECT CONCAT(ln_properties.land_address,',',ln_properties.street) AS land_address  FROM `ln_properties` WHERE ln_properties.id=s.`house_id` LIMIT 1) AS property_address,
						 (SELECT ln_properties.land_code  FROM `ln_properties` WHERE ln_properties.id=s.`house_id` LIMIT 1) AS property_code,
						 (SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id=(SELECT p.property_type FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1)) AS property_type,
						  s.*,
						  (SELECT SUM(crm.total_principal_permonthpaid+crm.extra_payment) FROM `ln_client_receipt_money` as crm WHERE crm.sale_id=s.id and crm.status=1 ) as total_principal_permonthpaid,
						  (SELECT hname_kh FROM `ln_client` WHERE client_id=s.client_id) AS buy_with,
						  (SELECT ss.id FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1  ) AS record_id,
						  (SELECT ss.no_installment FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1  ) AS no_installment,
						  (SELECT ss.begining_balance FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS begining_balance,
						  (SELECT ss.begining_balance_after FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS begining_balance_after,
						  (SELECT ss.ending_balance FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS ending_balance,
						  (SELECT ss.principal_permonth FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS principal_permonthafter,
						  (SELECT ss.principal_permonthafter FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS principal_permonthafter,
						  (SELECT ss.total_interest FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS total_interest,
						  (SELECT ss.total_interest_after FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS total_interest_after,
						  (SELECT ss.total_payment FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS total_payment,
						  (SELECT ss.total_payment_after FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS total_payment_after,
						  (SELECT ss.paid_date FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS paid_date,
						  (SELECT ss.date_payment FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS date_payment,
						  (SELECT ss.`penelize` FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS penelize,
						  (SELECT ss.`service_charge` FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS service_charge,	
						  (SELECT DATE_FORMAT(ss.date_payment, '%d-%m-%Y') FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS date_payments,
						  (SELECT ss.penelize FROM  `ln_saleschedule` AS ss  WHERE s.id = ss.`sale_id` AND ss.is_completed = 0  ORDER BY ss.id ASC LIMIT 1 ) AS penelize
						FROM
						  `ln_sale` AS s
						WHERE 
						  s.status = 1 
						  AND s.`payment_id` NOT IN (1,2)
						  AND s.is_cancel=0
						  AND s.client_id = ".$data['client_id'];
    		
    	return $db->fetchAll($sql);
   }
function getLoanPaymentschedulehistory($data){//used page edit il payment
   	$rows = $this->getReceiptMoneyDetailByID($data['crm_id']);
   	$s_where="";
   	$where='';
   	if(!empty($rows)){
   		$s_where = array();
   		foreach($rows as $rs){
   					$s_where[] = " ss.id = {$rs['lfd_id']}";
   			}
   			$where .=' '.implode(' OR ',$s_where).'';
   	}
   	$db = $this->getAdapter();
   	if($data['type']==1){
   		$sql ="SELECT
   		(SELECT CONCAT(ln_properties.land_address,',',ln_properties.street) AS land_address  FROM `ln_properties` WHERE ln_properties.id=s.`house_id` LIMIT 1) AS property_address,
   		(SELECT ln_properties.land_code  FROM `ln_properties` WHERE ln_properties.id=s.`house_id` LIMIT 1) AS property_code,
   		(SELECT t.type_nameen FROM `ln_properties_type` as t WHERE t.id=(SELECT p.property_type FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1)) As property_type,
   		s.*,
   		ss.*,
   		ss.begining_balance_after AS begining_balance_afternew,
   		(ss.begining_balance_after+( (SELECT principal_permonth FROM `ln_client_receipt_money_detail` WHERE lfd_id = ss.id  AND ln_client_receipt_money_detail.status=1 LIMIT 1)) ) AS begining_balance_after,
   		(SELECT principal_permonth FROM `ln_client_receipt_money_detail` WHERE lfd_id = ss.id  AND ln_client_receipt_money_detail.status=1 LIMIT 1)  AS principal_permonthpaid,
   		(SELECT total_payment FROM `ln_client_receipt_money_detail` WHERE lfd_id = ss.id  AND ln_client_receipt_money_detail.status=1 LIMIT 1)  AS total_paymentbefore,
   		DATE_FORMAT(ss.date_payment, '%d-%m-%Y') AS date_payments
   		FROM
   		`ln_sale` AS s,
   		`ln_saleschedule` AS ss
   		WHERE s.id = ss.`sale_id`
   		AND s.status = 1
   		AND (ss.is_completed = 0 OR $where)
   		AND s.id = ".$data['loan_number'];
   	}

   	return $db->fetchAll($sql);
}   
function getLoanPaymentByLoanNumberEdit($data){
   	$db = $this->getAdapter();
	   	if($data['type']==1){
	   		$sql ="SELECT
	   		s.*,
	   		ss.*
	   		FROM
	   		`ln_sale` AS s,
	   		`ln_saleschedule` AS ss
	   		WHERE s.id = ss.`sale_id`
	   		AND s.status = 1
	   		AND s.id = ".$data['loan_number']." 
	   		AND (ss.is_completed = 0 or ss.id=".$data['edit_record'].")";
	   	}
   	return $db->fetchAll($sql);
   }
   
   function getAllLoanPaymentByLoanNumber($data){
	   	$db = $this->getAdapter();
	   	$loan_number= $data['loan_number'];
	   	$sql = "select * from ln_sale as s ,ln_saleschedule as scd where s.id=scd.sale_id and sale_id = $loan_number ORDER BY no_installment ASC";
   		return $db->fetchAll($sql);
   	}

   function getAllCo(){
   			$db = $this->getAdapter();
   			$sql="SELECT `co_id` AS id,CONCAT(`co_firstname`,' ',`co_lastname`,'- ',`co_khname`) AS `name`,`branch_id` FROM `ln_staff` WHERE `position_id`=1 AND (`co_khname`!=''  OR `co_firstname`!='')" ;
   			return $db->fetchAll($sql);
   		
   }
   function getAllClient(){
   	$db = $this->getAdapter();
   	$sql = "SELECT c.`client_id` AS id ,c.`name_kh` AS name FROM `ln_client` AS c WHERE c.`name_kh`!='' " ;
   	return $db->fetchAll($sql);
   }
   
   function getAllClientCode(){
   	$db = $this->getAdapter();
   	$sql = "SELECT c.`client_id` AS id ,c.`client_number` AS name FROM `ln_client` AS c WHERE c.`name_kh`!='' " ;
   	return $db->fetchAll($sql);
   }
   
   public function getLastPayDate($data){
   	$loanNumber = $data['loan_numbers'];
   	$db = $this->getAdapter();
   	//$sql = "SELECT c.`date_input` FROM `ln_client_receipt_money` AS c WHERE c.`loan_number`='$loanNumber' ORDER BY c.`date_input` DESC LIMIT 1";
   	$sql ="SELECT 
			  lf.`date_payment`
			FROM
			  `ln_loanmember_funddetail` AS lf,
			  `ln_client_receipt_money` AS c,
			  `ln_loan_member` AS lm
			WHERE c.`loan_number` = lm.`loan_number`
			  AND lm.`member_id` = lf.`member_id`
			  AND c.`loan_number` = '$loanNumber' 
			  AND lf.`is_completed`=1
			ORDER BY lf.`id` DESC LIMIT 1";
   	//return $sql;
   	return $db->fetchOne($sql);
   }
   
   public function getLastPaymentDate($data){
   	$loanNumber = $data['loan_numbers'];
   	$fn_id = $data["fn_id"];
   	$db = $this->getAdapter();
   	$sql = "SELECT 
			  c.`date_input` 
			FROM
			  `ln_client_receipt_money` AS c,
			  `ln_client_receipt_money_detail` AS cr 
			WHERE c.`loan_number` = '$loanNumber' 
			  AND c.`id` = cr.`crm_id` 
			  AND cr.`lfd_id` = $fn_id 
			ORDER BY c.`receipt_no` DESC 
			LIMIT 1";
   	//return $sql;
   	return $db->fetchOne($sql);
   }
   public function getLaonHasPayByLoanNumber($loan_number){
   	$db= $this->getAdapter();
   	$sql=" SELECT 
			  (SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=crmd.`client_id`) AS client_name,
			  (SELECT c.`client_number` FROM `ln_client` AS c WHERE c.`client_id`=crmd.`client_id`) AS client_code,
			  crm.`receipt_no`,
			  crm.`land_id`,
			  DATE_FORMAT(crm.date_input, '%d-%m-%Y') AS `date_input`,
			  crm.outstanding,
			  crm.`principal_amount`,
			  crm.`total_principal_permonth`,
			  SUM(total_principal_permonthpaid) AS total_principal_permonthpaid,
			  crm.`total_payment`,
			  crm.`total_interest_permonth`,
			  crm.`amount_payment`,
			  crm.`recieve_amount`,
			  crm.`return_amount`,
			  crm.`service_charge`,
			  crm.`penalize_amount`,
			  crm.`group_id`,
			  crm.`is_completed`,
			  crmd.`capital`,
			  crmd.`total_payment`,
			  (SELECT ln_sale.price_sold FROM `ln_sale` WHERE ln_sale.id=crm.sale_id) AS price_sold,
			  DATE_FORMAT(crmd.date_payment, '%d-%m-%Y') AS `date_payment`
			FROM
			  `ln_client_receipt_money` AS crm,
			  `ln_client_receipt_money_detail` AS crmd 
			WHERE crm.`id` = crmd.`crm_id` 
			  AND crm.status=1
			  AND crm.`sale_id` = '$loan_number' GROUP BY crm.`id` ORDER BY crmd.`crm_id` DESC ";
   	return $db->fetchAll($sql);
   }
   
   function getAllLoanByCoId($data){ //quick Il Payment
		return array();
   }
   function getFunByGroupId($id){
   		$db = $this->getAdapter();
   		$sql="SELECT lf.`id` FROM `ln_loanmember_funddetail` AS lf, `ln_loan_member` AS lm WHERE lm.`member_id` = lf.`member_id` AND lm.`group_id` = $id AND lf.`is_completed`=0";
   		return $db->fetchAll($sql);
   }
  
   public function getLoanFunByGroupId($id,$payDate){
   	$db = $this->getAdapter();
   	$sql = "SELECT 
			  lf.`id`,
			  lf.`principle_after`,
			  lf.`total_interest_after`,
			  lf.`total_principal`,
			  lf.`penelize`,
			  lf.`service_charge`,
			  lf.`date_payment`,
			  lf.`total_payment_after`,
			  lm.`client_id`,
			  lm.`loan_number`,
			  lm.`total_capital`,
			  lm.`group_id`,
			  lf.`is_completed`
			FROM
			  `ln_loanmember_funddetail` AS lf,
			  `ln_loan_member` AS lm 
			WHERE lm.`member_id` = lf.`member_id` 
			   	AND lm.`group_id` = $id 
			   	AND lf.`date_payment` ='$payDate'";
   	$rs_fun = $db->fetchAll($sql);
   	return $rs_fun;
   }   
   public function getReceiptMoneyById($id){
   	$db = $this->getAdapter();
   	$sql = "SELECT lc.id,lc.`service_charge`,lc.`penalize_amount`,lc.`payment_option`,lc.`recieve_amount`,lc.`total_interest`,lc.`total_payment` FROM `ln_client_receipt_money` AS lc WHERE lc.`id`=$id";
   	return $db->fetchRow($sql);
   }
    
   public function getReceiptMoneyDetailByID($id){
   	$db = $this->getAdapter();
   	$sql = "SELECT lc.`crm_id`,lc.`lfd_id`,lc.`land_id`,lc.`service_charge`,lc.`penelize_amount`,lc.`total_interest`,lc.`total_payment`,lc.`total_recieve`,lc.`principal_permonth`,old_penelize,old_service_charge FROM `ln_client_receipt_money_detail` AS lc WHERE lc.`crm_id`=$id";
   	return $db->fetchAll($sql);
   }
   public function getFunDetailById($id){
   	$db = $this->getAdapter();
   	$sql = "SELECT lf.`is_completed` FROM `ln_loanmember_funddetail` AS lf WHERE lf.id =$id";
   	return $db->fetchOne($sql);
   }
	public function getAllLoanNumberByBranch($branch_id){
		$db = $this->getAdapter();
// 		if($type==1){
// 			$sql="SELECT m.`loan_number` AS id,m.`loan_number` AS `name`,g.`branch_id` FROM `ln_loan_member` AS m,`ln_loan_group` AS g WHERE m.`group_id`= g.`g_id` AND g.`loan_type`=1 AND m.status=1 AND m.is_reschedule!=1 ";
// 			return $db->fetchAll($sql);
// 		}else{
// 			$sql="SELECT m.`loan_number` AS id,m.`loan_number` AS `name`,g.`branch_id` FROM `ln_loan_member` AS m,`ln_loan_group` AS g WHERE m.`group_id`= g.`g_id` AND g.`loan_type`=2 AND m.status=1 AND m.is_reschedule!=1 GROUP BY m.`loan_number` ";
// 			return $db->fetchAll($sql);
// 		}

		//$sql="select id,sale_number as name	from `ln_sale` where status=1 and is_completed=0 and branch_id=$branch_id";
		
		$sql= "SELECT id,
				  CONCAT((SELECT name_kh FROM ln_client WHERE ln_client.client_id=ln_sale.`client_id` LIMIT 1),' - ',sale_number) AS name
				FROM
				  ln_sale 
				WHERE status=1 
				  AND branch_id=$branch_id ";
		
		return $db->fetchAll($sql);
		
	}
	public function getAllLoanNumberByBranchEdit($branch_id){
		$db = $this->getAdapter();
		$sql="select id,sale_number as name	from `ln_sale` where status=1  and branch_id=$branch_id";
		return $db->fetchAll($sql);
	
	}
	
	function getAllReceiptMoneyDetail($id){
		$db = $this->getAdapter();
		$sql = "select * from ln_client_receipt_money_detail where crm_id = $id AND status=1 ";
		return $db->fetchAll($sql);
	}
	function getPropertyInfo($property_id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  s.*,
				 (SELECT t.type_nameen FROM `ln_properties_type` as t WHERE t.id=(SELECT p.property_type FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1)) As property_type,
				 (SELECT p.land_address FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1) AS property_address ,
				 (SELECT p.land_code FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1) AS property_code,
				 (SELECT p.street FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1) AS street
				FROM
				  ln_sale AS s 
				WHERE id = $property_id  ";
		$rs = $db->fetchRow($sql);
		$rs['house_type']=ltrim(strstr($rs['property_type'], '('), '.');
		return $rs;
	}
	
	function getLastDatePayment($loan_number){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  date_pay ,
				  (SELECT date_payment FROM ln_saleschedule,ln_sale WHERE ln_saleschedule.`sale_id` = `ln_sale`.`id` AND `ln_sale`.id = $loan_number ORDER BY `ln_saleschedule`.id DESC LIMIT 1) AS datepaymentlastrecord
				FROM
				  ln_client_receipt_money 
				WHERE land_id = $loan_number 
				ORDER BY id DESC LIMIT 1";
		//$order = " order by id DESC ";
		return $db->fetchRow($sql);
	}
	
	
}

